<?php
/**
 * General class of expression
 */
class Expression {
	static $currentDataSet;

	/**
	 * get omics data by gene.
	 * @param string $geneId the id of the selected gene
	 * @return array the omics data [:conditionId => :expressionLevel]
	 */
	public static function get ($geneId) {
		return OmicsGene::getByGene($geneId);
	}

	/**
	 * get all the conditions.
	 * @return [ExperimentalCondition] all conditions
	 */
	public static function getConditions () {
		$all = ExperimentalCondition::getAll("1 order by id");
		// get max or min value
		foreach ($all as &$con) {
			$con->category = $con->getCategory();
		}
		return $all;
	}

	/**
	 * get details about a experimental condition
	 * @param int $id the id of the select condition
	 * @return ExperimentalCondition the condition
	 */
	public static function getCondition ($id) {
		$con = ExperimentalCondition::get($id);
		if ($con) {
			$con->category = $con->getCategory();
		}
		return $con;
	}

	/**
	 * get all types of conditions
	 * @return [string] all types
	 */
	public static function getAllConditionTypes () {
		return ExperimentalCondition::$types;
	}

	/**
	 * get the data of a certain condition
	 * @param int $condition the id of the condition
	 * @param [string] the ids of genes, if empty then all genes
	 * @return array in the format of [:geneID => :expressionData]
	 */
	public static function getByCondition ($condition, $geneIds = []) {
		return OmicsGene::getByCondition($condition, $geneIds);
	}

	/**
	 * get the expression data (position-based) by range
	 * @param int $condition the condition
	 * @param int $start the start position
	 * @param int $stop the stop position
	 * @param int $strand the strand
	 * @param double $sampling the sampling rate, the higher, less data is included
	 * @return array in the format of [:position => :expressionLevel]
	 */
	public static function getByRange ($condition, $start, $stop, $strand, $sampling) {
		return OmicsPosition::getByRange ($condition, $start, $stop, $strand, $sampling);
	}

	/**
	 * import a data set (condition).
	 * @param  string $title
	 * @param  string $description
	 * @param  string $type
	 * @param  [double] $values
	 * @return object/boolean the result of the import, success or failue, if success, the dataset is returned
	 */
	public static function importDataSet ($title, $description, $type, $values) {
		// create the data set object
		$dataSet = new ExperimentalCondition();
		$dataSet->title = $title;
		$dataSet->description = $description;
		$dataSet->type = $type;
		$dataSet->category = $dataSet->getCategory();

		$conn = \Kiwi\Application::$conn;
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
	 * start import, will start and keep an transaction on.
	 */
	public static function startImport ($dataSet) {
		$conn = \Kiwi\Application::$conn;
		$dataSet->category = $dataSet->getCategory();
		self::$currentDataSet = $dataSet;
		return $conn->beginTransaction();
	}

	/**
	 * end the import process.
	 * @return void
	 */
	public static function endImport () {
		$conn = \Kiwi\Application::$conn;
		$conn->commit();
		self::$currentDataSet = null;
	}

	/**
	 * import data to the data set.
	 * @param  ExperimentalCondition $dataSet (optional)
	 * @param  [double] $values
	 * @param  boolean $useTransaction (optional) default $dataSet != null, whether use transaction for inserting the rows
	 * @return boolean import successful or not
	 */
	public static function importData ($values) {
		$dataSet = self::$currentDataSet;
		$conn = \Kiwi\Application::$conn;
		$queryOkay = true;
		if ($dataSet->category === "position-based") {
			$sql = "insert into OmicsData_position (position,dataSet,value) values ";
			foreach($values as $position => $val) {
				$sql .= " ($position, {$dataSet->id}, $val)";
			}
			$sql .= " on duplicate key update value = VALUES(value)";
			$queryOkay = $conn->doQuery($sql);
		} else {
			foreach($values as $locus => $val) {
				$sql = "insert into OmicsData_gene (gene,dataSet,value) select id, {$dataSet->id}, $val from Gene where _locus like ? on duplicate key update value = VALUES(value)";
				if (!$conn->doQuery($sql, [$locus])) {
					$queryOkay = false;
					break;
				}
			}
		}
		return $queryOkay;
	}

	/**
	 * remove a condition and all related data
	 * @param ExperimentalCondition $condition the condition to be removed
	 * @return boolean whether the operation is successful
	 */
	public static function deleteCondition (ExperimentalCondition $condition) {
		$conn = \Kiwi\Application::$conn;
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