<?php
class Regulon extends \Kiwi\Model {
	static $tableName = "Regulon";

	public $id;
	protected $_regulator;
	protected $_title;
	protected $_genes;
	protected $_new;

	public function getRegulator () {
		if ($this->id) {
			$this->_regulator = \Kiwi\Model::parse("{".str_replace(":", "|", $this->id)."}");
		}
		return $this->_regulator;
	}

	public function getTitle () {
		if (!$this->_title) {
			$this->getRegulator();
			if ($this->_regulator) {
				if ($this->_regulator instanceof Protein) {
					$this->_title = $this->_regulator->title." regulon";
				} else {
					$this->_title = $this->_regulator->title;
				}
			}
		}
		return $this->_title;
	}

	public function getGenes () {
		if (!$this->_genes) {
			$this->getRegulator();
			if ($this->_regulator){
				$operons = \Kiwi\Application::$conn->doQuery("select mode, _genes as genes from Operon join Regulation on regulated = concat('{operon|', Operon.id, '}') where regulator like ?", [(string) $this->_regulator]);
				$genes = \Kiwi\Application::$conn->doQuery("select mode, regulated as gene from Regulation where regulated like '{gene|%}' and regulator like ?", [(string) $this->_regulator]);
				$groups = [];
				foreach ($operons as $row) {
					\Kiwi\Utility::decodeLinkForView($row["genes"]);
					if (!array_key_exists($row["mode"], $groups)) {
						$groups[$row["mode"]] = [];
					}
					$groups[$row["mode"]][] = $row["genes"];
				}
				foreach ($genes as $row) {
					$gene = \Kiwi\Model::parse($row["gene"]);
					if (!array_key_exists($row["mode"], $groups)) {
						$groups[$row["mode"]] = [];
					}
					$groups[$row["mode"]][] = $gene->toLinkMarkup();;
				}
				$this->_genes = $groups;
			}
		}
		return $this->_genes;
	}

	public function isNew () {
		return $this->_new;
	}

	public static function get ($id) {
		$instance = parent::raw($id);
		if (!$instance) {
			$instance = new Regulon();
			$instance->id = $id;
			$instance->_new = true;
		}
		$instance->getTitle();
		$instance->getGenes();
		if ($instance->_genes) {
			return $instance;
		}
	}

	public static function simpleGet ($id) {
		$instance = parent::raw($id);
		if (!$instance) {
			$instance = new Regulon();
			$instance->id = $id;
			$instance->_new = true;
		}
		$instance->getTitle();
		return $instance;
	}

	public static function getByRegulator ($obj) {
		if (is_string($obj)) {
			$id = str_replace("|", ":", trim($obj, "{}"));
		} elseif (is_object($obj)) {
			if ($obj instanceof Protein || $obj instanceof Riboswitch) {
				$id = lcfirst(get_class($obj)).":".$obj->id;
			}
		}
		if ($id) {
			return self::simpleGet($id);
		}
	}

	public static function getAll () {
		$conn = \Kiwi\Application::$conn;
		$others = $conn->doQuery("select distinct regulator from Regulation where regulator not in (select concat('{',replace(id,':','|'),'}') from Regulon)");
		$all = parent::getAll("1");
		if ($all) {
			foreach ($all as &$regulon) {
				$regulon->getTitle();
			}
		}
		if ($others) {
			foreach ($others as $row) {
				$regulon = self::getByRegulator($row["regulator"]);
				$all[] = $regulon;
			}
		}
		usort($all, function($a, $b){
			return strcmp($a->_title, $b->_title);
		});
		return $all;
	}

	public function updateCount () {
		if ($this->_new) {
			$this->insert();
		}
		parent::updateCount();
	}

	public function toLinkMarkup () {
		$this->getTitle();
		if ($this->_title) {
			return "[regulon|".$this->id."|".$this->_title."]";
		}
	}

	/**
	 * update the regulon in the database, history record generated
	 * @return boolean true, if successful, false if failed
	 */
	public function update () {
		if ($this->id) {
			$conn = \Kiwi\Application::$conn;
			$conn->beginTransaction();
			if (History::record($this, "update") && parent::update()) {
				$conn->commit();
				return true;
			} else {
				$conn->rollback();
				return false;
			}
		}
	}

	/**
	 * insert the regulon, no history recording
	 * @return boolean true if successful, false if failed
	 */
	public function insert () {
		if ($this->_regulator) {
			$conn = \Kiwi\Application::$conn;
			$result = $conn->doQuery("select regulator from Regulation where regulator like ?", [(string) $this->_regulator]);
			if ($result) {
				if (parent::insert()) {
					$this->_new = false;
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
}
?>