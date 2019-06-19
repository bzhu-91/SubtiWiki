<?php
class Operon extends \Monkey\Model {
	use \Monkey\ReferenceCache, \Monkey\Markup;

	static $tableName = "Operon";

	static $relationships = [
		"regulation" => [
			"tableName" => "Regulation",
			"mapping" => [
				"regulator" => "mixed",
				"regulated" => "mixed"
			],
			"position" => "2"
		]
	];

	/**
	 * patch function
	 * @return none
	 */
	public function patch () {
		if ($this->id) {
			$this->fetchRegulations();
		}
	}

	/**
	 * fetch regulations of this operon and integrate into $this
	 * @return none
	 */
	public function fetchRegulations () {
		if ($this->id) {
			$relationships = $this->has("regulation");
			if ($relationships) {
				foreach ($relationships as $row) {
					$sigmaFactors = [];
					$transcriptionFactors = [];
					foreach ($relationships as &$row) {
						if ($row->regulator) {
							$id = $row->regulator->id;
							$title = ($row->regulator instanceof Protein) ? $row->regulator->title." regulon" : $row->regulator->title;
							$str = $row->regulator->toLinkMarkup().": ".$row->mode;
							if (trim($row->description)) {
								$str .= ", ".trim($row->description);
							}
							$str .= ", in [regulon|$id|$title]";
						}
						if ($str) {
							if($row->mode == "sigma factor") {
								$sigmaFactors[] = $str;
							} else {
								$transcriptionFactors[] = $str;
							}
						}
					}
					if ($transcriptionFactors) {
						\Monkey\Utility::insertAfter($this, "regulatory mechanism", $transcriptionFactors, "description");
					}
					if ($sigmaFactors) {
						\Monkey\Utility::insertAfter($this, "sigma factors", $sigmaFactors, "description");
					}
				}	
			}
		}
	}

	/**
	 * try pase the genes, and generate the hash
	 * @throws  BaseException when gene is not found
	 * @return none
	 */
	public function validateGenes () {
 		$genes = explode("-", $this->genes);
 		foreach ($genes as &$each) {
 			if (strlen($each) != 49) {
				throw new \Monkey\BaseException("There is an error in the genes of this operon", 1);
			}
 		}
		$this->hash = strtoupper(sha1($this->genes));
	}

	/**
	 * inserts into database
	 * @throws BaseException gene not parsable
	 * @return boolean true if successful, false it not
	 */
	public function insert () {
		\Monkey\Utility::encodeLink($this);
		$this->validateGenes();

		if (!$this->id) {
			$this->id = $this->hash;
		}
		$conn = \Monkey\Application::$conn;
		$conn->beginTransaction();
		if (parent::insert() && History::record($this, "add") && MetaData::track($this)) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	/**
	 * update
	 * @overload
	 * @throws  BaseException when gene is not parsable
	 * @return boolean true if successful, false if not
	 */
	public function update () {
		\Monkey\Utility::encodeLink($this);
		$this->validateGenes();

		$this->lastUpdate = date("Y-m-d H:i:s");
		$conn = \Monkey\Application::$conn;
		$conn->beginTransaction();
		if (parent::update() && History::record($this, "update")) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	public function replace () {
		\Monkey\Utility::encodeLink($this);
		$this->validateGenes();

		$conn = \Monkey\Application::$conn;
		$conn->beginTransaction();
		if (parent::replace(["id", "count"]) && History::record($this, "update") && MetaData::track($this)) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}	
	}

	/**
	 * delete, inserts a record of history
	 * @return boolean true if successful, false if not
	 */
	public function delete () {
		if ($this->id) {
			$conn = \Monkey\Application::$conn;
			$conn->beginTransaction();
			if (History::record($this, "remove") && parent::delete()) {
				$conn->commit();
				return true;
			} else {
				$conn->rollback();
				return false;
			}
		}
	}

	public function toLinkMarkup () {
		$title = preg_replace_callback("/\[\[gene\|([a-f0-9]{40})\]\]/i",function($match){
			$gene = Gene::simpleGet($match[1]);
			return "''".$gene->title."''";
		}, $this->title);
		return "[operon|".$this->id."|".$title." operon]";
	}
}
?>