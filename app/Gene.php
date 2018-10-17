<?php
class Gene extends Model {
	use ReferenceCache;

	static $tableName = "Gene";

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

	public function fetchSequences () {
		if ($this->id) {
			$con = Application::$conn;
			$result = $con->select("Sequence",["dna", "aminos"], "gene like ?", [$this->id]);
			if ($result) {
				$this->DNA = $result[0]["dna"];
				$this->aminos = $result[0]["aminos"];
			}
		}
	}

	public function patch () {
		$results = Utility::deepSearch($this, "[[this]]");
		foreach ($results as $keypath) {
			$data = null;
			if ($keypath == "categories") {
				$data = "{{this}}";
			}
			if ($keypath == "Expression and Regulation->Operons") {
				$data = $this->fetchOperons();
			}
			if ($keypath == "Expression and Regulation->Other regulations") {
				$data = $this->fetchOtherRegulations();
			}
			if ($keypath == "regulons") {
				$data = "{{this}}";
			}
			if ($keypath == "genomicContext") {
				$data = "{{this}}";
			}
			if ($data == null) {
				Utility::unsetValueFromKeyPath($this, $keypath);
			} else {
				Utility::setValueFromKeyPath($this, $keypath, $data);
			}
		}
		Utility::clean($this); 
	}

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

	public static function initLookupTable () {
		// if use self::lookupTable, this will be shared between all classes 	which use this trait
		// if use get_called_class()::lookupTable, each class will has its 		own copy
		$className = get_called_class();
		$primaryKeyName = $className::$primaryKeyName;
		if (!$className::$lookupTable) {
			$con = Application::$conn;
			$className = get_called_class();
			if ($con && static::$tableName) {
				$result = $con->select(static::$tableName, ["id", "title", "locus", "function"], "1");
				$keys = array_column($result, $primaryKeyName);
				foreach ($keys as &$key) {
					$key = strtolower($key);
				}
				foreach ($result as &$row) {
					$row = $className::withData($row);
				}
				$className::$lookupTable = array_combine($keys, $result);
			}
		}
	}

	/**
	 * @override
	 */
	public static function initValidateTable() {
		$className = get_called_class();
		if (!$className::$validateTable) {
			$con = Application::$conn;
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
				$className::$validateTable = Utility::arrayMerge($table1, $table2);
			} else throw new Exception("Should set the connection and table_name for Entity");
		}
	}

	public function update () {
		$conn = Application::$conn;
		$conn->beginTransaction();
		if (History::record($this, "update") && parent::update()) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	public function insert () {
		if (!$this->id) {
			$this->id = sha1(json_encode($this));
		}
		$conn = Application::$conn;
		$conn->beginTransaction();
		if (History::record($this, "add") && parent::insert($this) && MetaData::track($this)) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	// only the replace function need to trace the meta scheme
	public function replace () {
		$conn = Application::$conn;
		$conn->beginTransaction();
		if (History::record($this, "update") && parent::replace(["count", "id"]) && MetaData::track($this)) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	public function delete () {
		$conn = Application::$conn;
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