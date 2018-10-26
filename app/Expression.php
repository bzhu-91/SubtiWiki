<?php

class ExperimentalCondition extends Model{
	static $tableName = "DataSet";
	static $types = [
		"transcript level (fold change)",
		"transcript level (expression index)",
		"transcript level (raw intensity)",
		"protein level (copies per cell)",
		"protein level (existence)",
		"tilling array"
	];

	public function getCategory () {
		if ($this->type) {
			if ($this->type == "tilling array") {
				return "position-based";
			} else {
				return "gene-based";
			}
		}
	}
}

class OmicsGene extends Model {
	static $tableName = "OmicsData_gene";

	public static function getByCondition ($condition, $geneIds = []) {
		$sql = "select gene, value from ".self::$tableName." where dataSet = ?";
		if ($geneIds) {
			$sql .= " and gene in (";
			$qmarks = array_fill(0, count($geneIds), "?");
			$sql .= implode(",", $qmarks).")";
		}
		$vals = array_merge([$condition], $geneIds);
		$result = Application::$conn->doQuery($sql, $vals);
		$ids = array_column($result, "gene");
		$values = array_column($result, "value");
		return array_combine($ids, $values);
	}

	public static function getByGene ($geneId) {
		$sql = "select dataSet,value from ".self::$tableName." where gene = ?";
		$result = Application::$conn->doQuery($sql, [$geneId]);
		$dataSets = array_column($result, "dataSet");
		$values = array_column($result, "value");
		return array_combine($dataSets, $values);
	}

	public static function getMaxMin ($condition) {
		$sql = "select max(value) as max, min(value) as min from ".self::$tableName." where dataSet = ?";
		$result = Application::$conn->doQuery($sql, [$condition]);
		if ($result) {
			return $result[0];
		}
	}

	public static function deleteByCondition (ExperimentalCondition $condition) {
		$sql = "delete from `".self::$tableName."` where dataSet = ?";
		return Application::$conn->doQuery($sql, [$condition->id]);
	}
}


class OmicsPosition extends Model {
	static $tableName = "OmicsData_position";

	
	/**
	 * getByRange: get the tilling array or other position based expression data
	 *
	 * @param  Int $condition id of the condition 
	 * @param  Int $start start position on the genome
	 * @param  Int $stop stop position on the genome, should always larger than start
	 * @param  Int $strand the strand
	 * @param  Int $sampling the sampling rate, default is 1 (this is to reduce the memory use)
	 *
	 * @return Array/null
	 */
	public static function getByRange ($condition, $start, $stop, $strand = 1, $sampling = 1) {
		$sql = "select position, value from ".self::$tableName." where position >= ? and position <= ? and strand = ? and mod(position, $sampling) = 0 and dataSet = ?";
		if ($start && $stop && $start < $stop && ($strand == 0 || $strand == 1)) {
			$conn = Application::$conn;
			$stmt = $conn->prepare($sql);
			if ($stmt) {
				if ($stmt->bindValue(1, $start, PDO::PARAM_INT)
					&& $stmt->bindValue(2, $stop, PDO::PARAM_INT)
					&& $stmt->bindValue(3, $strand, PDO::PARAM_INT)
					&& $stmt->bindValue(4, $condition, PDO::PARAM_INT)
					&& $stmt->execute()
				) {
					$result = $stmt->fetchAll(PDO::FETCH_NUM);
					return $result;
				}
			}			
		}
	}

	public static function deleteByCondition (ExperimentalCondition $condition) {
		$sql = "delete from `".self::$tableName."` where dataSet = ?";
		return Application::$conn->doQuery($sql, [$condition->id]);
	}
}

class Expression {
	static $currentDataSet;

	public static function get ($geneId) {
		return OmicsGene::getByGene($geneId);
	}

	public static function getConditions () {
		$all = ExperimentalCondition::getAll("1 order by id");
		// get max or min value
		foreach ($all as &$con) {
			$con->category = $con->getCategory();
		}
		return $all;
	}

	public static function getCondition ($id) {
		$con = ExperimentalCondition::get($id);
		if ($con) {
			$con->category = $con->getCategory();
		}
		return $con;
	}

	public static function getAllConditionTypes () {
		return ExperimentalCondition::$types;
	}

	public static function getByCondition ($condition, $geneIds = []) {
		return OmicsGene::getByCondition($condition, $geneIds);
	}

	public static function getByRange ($condition, $start, $stop, $strand, $sampling) {
		return OmicsPosition::getByRange ($condition, $start, $stop, $strand, $sampling);
	}

	/**
	 * import
	 *
	 * @param  string $title
	 * @param  string $description
	 * @param  string $type
	 * @param  array $values
	 *
	 * @return object/boolean the result of the import, success or failue, if success, the dataset is returned
	 */
	public static function importDataSet ($title, $description, $type, $values) {
		// create the data set object
		$dataSet = new ExperimentalCondition();
		$dataSet->title = $title;
		$dataSet->description = $description;
		$dataSet->type = $type;
		$dataSet->category = $dataSet->getCategory();

		$conn = Application::$conn;
		$conn->beginTransaction();

		if ($dataSet->insert() && $dataSet->id && self::importData($dataSet, $values, false)){
			$conn->commit();
			return $dataSet;
		} else {
			$conn->rollback();
			return false;
		}
	}

	/**
	 * startImport
	 * start import, will start and keep an transaction on
	 * @param  string $title
	 * @param  string $description
	 * @param  string $type
	 * @param  Array $values (optional) position => value or locus => value, optional
	 *
	 * @return boolean/ExperimentalCondition
	 */
	public static function startImport ($title, $description, $type) {
		// create the data set object
		$dataSet = new ExperimentalCondition();
		$dataSet->title = $title;
		$dataSet->description = $description;
		$dataSet->type = $type;
		$dataSet->category = $dataSet->getCategory();

		$conn = Application::$conn;
		$conn->beginTransaction();

		$queryOkay = $dataSet->insert() && $dataSet->id;

		self::$currentDataSet = $dataSet;
		
		if (!$queryOkay) {
			$conn->rollback();
		}
		return $queryOkay;
	}

	/**
	 * endImport
	 * end the import process
	 * @return void
	 */
	public static function endImport () {
		$conn = Application::$conn;
		$conn->commit();
		self::$currentDataSet = null;
	}

	/**
	 * importData
	 * import data to the data set
	 * @param  ExperimentalCondition $dataSet (optional)
	 * @param  Array $values
	 * @param  boolean $useTransaction (optional) default $dataSet != null, whether use transaction for inserting the rows
	 *
	 * @return boolean import successful or not
	 */
	public static function importData ($dataSet, $values, $useTransaction) {
		$useTransaction = $useTransaction === null ? ($dataSet != null) : $useTransaction;
		$dataSet = $dataSet == null ? self::$currentDataSet : $dataSet;
		$conn = Application::$conn;
		if ($useTransaction) {
			$conn->beginTransaction();
		}
		$queryOkay = true;
		if ($dataSet->category === "position-based") {
			$sql = "insert into OmicsData_position (position,dataSet,value) values ";
			foreach($values as $position => $val) {
				$sql .= " ($position, {$dataSet->id}, $val)";
			}
			$queryOkay = $conn->doQuery($sql);
		} else {
			foreach($values as $locus => $val) {
				$sql = "insert into OmicsData_gene (gene,dataSet,value) select id, {$dataSet->id}, $val from Gene where _locus like ?";
				if (!$conn->doQuery($sql, [$locus])) {
					$queryOkay = false;
					break;
				}
			}
		}
		if ($useTransaction) {
			if ($queryOkay) $conn->commit();
			else $conn->rollback();
		}
		return $queryOkay;
	}

	public static function deleteCondition (ExperimentalCondition $condition) {
		$conn = Application::$conn;
		$conn->beginTransaction();
		$queryOkay = true;
		if ($condition->category == "gene-based") {
			$queryOkay = $queryOkay && OmicsGene::deleteByCondition($condition);
		} else {
			$queryOkay = $queryOkay && OmicsPosition::deleteByCondition($condition);
		}
		$queryOkay = $queryOkay && $condition->delete();
		if ($queryOkay) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}
}
?>