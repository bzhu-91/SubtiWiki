<?php
/**
 * Class for Gene (as abstraction for DNA, transribed RNA and translated Protein)
 */
class Gene extends \Kiwi\Model {
	/**
	 * use ReferenceCache trait to create a in-memory cache of table
	 */
	use \Kiwi\ReferenceCache;

	static $tableName = "Gene";

	/**
	 * relationships of the entity Gene
	 */
	static $relationships = [
		"categories" => [
			"tableName" => "GeneCategory",
			"mapping" => [
				"gene" => "Gene",
				"category" => "Category"
			],
			"position" => 1
		],
		"paralogues" => [
			"tableName" => "ParalogousProtein",
			"mapping" => [
				"prot1" => "Protein",
				"prot2" => "Protein"
			],
			"ordered" => false,
			"position" => 1
		],
		"interaction" => [
			"tableName" => "Interaction",
			"mapping" => [
				"prot1" => "Protein",
				"prot2" => "Protein"
			],
			"ordered" => false,
		],
		"operons" => [
			"tableName" => "ViewGeneOperon",
			"mapping" => [
				"gene" => "Gene",
				"operon" => "Operon"
			],
			"position" => 1,
		],
		"otherRegulations" => [
			"tableName" => "Regulation",
			"mapping" => [
				"regulator" => "mixed",
				"regulated" => "mixed"
			],
			"position" => 2
		],
	];

	/**
	 * get the gene instance by id, the attribute "The protein" will be instantialized with the Protein class
	 * @param string $id the id of the gene
	 * @return Gene the instance or null
	 */
	public static function get ($id) {
		$ins = parent::get($id);
		if ($ins) {
			if (property_exists($ins, "The protein")) {
				$ins->{"The protein"} = Protein::withData($ins->{"The protein"});
				$ins->{"The protein"}->id = $ins->id;
				$ins->{"The protein"}->title = ucfirst($ins->title);
				$ins->{"The protein"}->patch();
			}
			$ins->patch();
		}
		return $ins;
	}

	/**
	 * get the sequences (DNA, amino acids)
	 */
	public function fetchSequences () {
		if ($this->id) {
			$con = \Kiwi\Application::$conn;
			$result = $con->select("Sequence",["dna", "aminos"], "gene like ?", [$this->id]);
			if ($result) {
				$this->DNA = $result[0]["dna"];
				$this->aminos = $result[0]["aminos"];
			}
		}
	}

	/**
	 * patch the {{this}} reference in the data
	 */
	public function patch () {
		$results = \Kiwi\Utility::deepSearch($this, "[[this]]");
		foreach ($results as $keypath) {
			$keypath = new \Kiwi\KeyPath($keypath);
			$data = null;
			if ((string) $keypath == "categories") {
				$data = "{{this}}";
			}
			if ((string) $keypath == "Expression and Regulation->Operons") {
				$data = $this->fetchOperons();
			}
			if ((string) $keypath == "Expression and Regulation->Other regulations") {
				$data = $this->fetchOtherRegulations();
			}
			if ((string) $keypath == "Gene->Coordinates") {
				$data = $this->fetchCoordinates();
			}
			if ((string) $keypath == "regulons") {
				$data = "{{this}}";
			}
			if ((string) $keypath == "genomicContext") {
				$data = "{{this}}";
			}
			if ($data == null) {
				$keypath->unset($this);
			} else {
				$keypath->set($this, $data);
			}
		}
		\Kiwi\Utility::clean($this); 
	}

	/**
	 * get the operons.
	 * @return array array of Relationship objects
	 */
	public function fetchOperons () {
		if ($this->id) {
			$relationships = $this->has("operons");
			if ($relationships) {
				foreach ($relationships as &$row) {
					$row->operon = Operon::get($row->operon->id);
				}
				return array_column($relationships, "operon");
			}
		}
	}

	/**
	 * get the coordinates on the genome.
	 * @return string the position in the format of start_stop_strand, where strand can be 1 (for forward) or 0 (for reverse)
	 */
	public function fetchCoordinates() {
		$pos = Genome::getAll(["object" => (string) $this]);
		if ($pos) {
			return $pos[0]->start." - ".$pos[0]->stop." (".($pos[0]->strand == 1? "+" :"-").")";
		}
	}

	/**
	 * get the non-transcriptional regulations
	 * @return [string] the regulations in string representation
	 */
	public function fetchOtherRegulations () {
		if ($this->id) {
			$relationships = $this->has("otherRegulations");
			if ($relationships) {
				$presentation = [];
				foreach ($relationships as $row) {
					$regulator = $row->regulator->toLinkMarkup();
					$str = $regulator.": ".$row->mode;
					if (property_exists($row, "description") && $row->description) {
						$str .= ", ".$row->description;
					}
					$presentation[] = $str;
				}
				return $presentation;
			}
		}
	}

	/**
	 * override the initLookupTable from ReferenceCache trait, include "locus" and "function" as extra columns
	 */
	public static function initLookupTable () {
		// if use self::lookupTable, this will be shared between all classes 	which use this trait
		// if use get_called_class()::lookupTable, each class will has its 		own copy
		$primaryKeyName = static::$primaryKeyName;
		if (!static::$lookupTable) {
			$con = \Kiwi\Application::$conn;
			if ($con && static::$tableName) {
				$result = $con->select(static::$tableName, ["id", "title", "locus", "function"], "1");
				$keys = array_column($result, $primaryKeyName);
				foreach ($keys as &$key) {
					$key = strtolower($key);
				}
				foreach ($result as &$row) {
					$row = static::withData($row);
				}
				static::$lookupTable = array_combine($keys, $result);
			}
		}
	}

	/**
	 * override the initValidateTable from ReferenceCache trait, include "locus" and "function" as extra columns
	 */
	public static function initValidateTable() {
		$className = get_called_class();
		if (!$className::$validateTable) {
			$con = \Kiwi\Application::$conn;
			$className = get_called_class();
			if ($con && static::$tableName) {
				$result = $con->select(static::$tableName, "*", "1");
				$keys = array_column($result, "title");
				$locus = array_column($result, "locus");
				foreach ($keys as &$key) {
					$key = strtolower($key);
				}
				foreach ($locus as &$key) {
					$key = strtolower($key);
				}
				foreach ($result as &$row) {
					$row = $className::withData($row);
				}
				$table1 = array_combine($keys, $result);
				$table2 = array_combine($locus, $result);
				$className::$validateTable = \Kiwi\Utility::arrayMerge($table1, $table2);
			} else throw new Exception("Should set the connection and table_name for Entity");
		}
	}

	/**
	 * override the update function in Model class, include versioning
	 * @return boolean whether the update is successful or not
	 */
	public function update () {
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

	/**
	 * override the insert function in Model class, include versioning
	 * @return boolean whether the update is successful or not
	 */
	public function insert () {
		if (!$this->id) {
			$this->id = sha1(json_encode($this));
		}
		$conn = \Kiwi\Application::$conn;
		$conn->beginTransaction();
		if (History::record($this, "add") && parent::insert($this) && MetaData::track($this)) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	/**
	 * override the replace function in Model class, including versioning and schema tracking
	 * @return boolean whether the update is successsful or not
	 */
	public function replace () {
		$conn = \Kiwi\Application::$conn;
		$conn->beginTransaction();
		if (History::record($this, "update") && parent::replace(["count", "id"]) && MetaData::track($this)) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	/**
	 * override the delete function in Model class, including versioning
	 * @return boolean whether the deletion is successful or not
	 */
	public function delete () {
		$conn = \Kiwi\Application::$conn;
		$conn->beginTransaction();
		if (History::record($this, "remove") && parent::delete()) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}
};
?>
