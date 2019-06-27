<?php
/**
 * For the Omics data which is gene-based.
 */
class OmicsGene extends \Kiwi\Model {
	static $tableName = "OmicsData_gene";

	/**
	 * get the dataset by condition.
	 * @param int $condition the id of the condition
	 * @param array $geneIds the ids of the genes to be included, if empty, then all
	 * @return array which is [:geneId => :expressionLevel]
	 */
	public static function getByCondition ($condition, $geneIds = []) {
		$sql = "select gene, value from ".self::$tableName." where dataSet = ?";
		if ($geneIds) {
			$sql .= " and gene in (";
			$qmarks = array_fill(0, count($geneIds), "?");
			$sql .= implode(",", $qmarks).")";
		}
		$vals = array_merge([$condition], $geneIds);
		$result = \Kiwi\Application::$conn->doQuery($sql, $vals);
		$ids = array_column($result, "gene");
		$values = array_column($result, "value");
		return array_combine($ids, $values);
	}

	/**
	 * get all data about a gene.
	 * @param string $geneId the id of the selected gene
	 * @return array which is [:conditionId => :expressionLevel]
	 */
	public static function getByGene ($geneId) {
		$sql = "select dataSet,value from ".self::$tableName." where gene = ?";
		$result = \Kiwi\Application::$conn->doQuery($sql, [$geneId]);
		$dataSets = array_column($result, "dataSet");
		$values = array_column($result, "value");
		return array_combine($dataSets, $values);
	}

	/**
	 * get the max or min of a certain condition, used to get the heatmap legend.
	 * @param int $condition the id of the condition
	 * @return array ["max" => :max, "min" => :min]
	 */
	public static function getMaxMin ($condition) {
		$sql = "select max(value) as max, min(value) as min from ".self::$tableName." where dataSet = ?";
		$result = \Kiwi\Application::$conn->doQuery($sql, [$condition]);
		if ($result) {
			return $result[0];
		}
	}

	/**
	 * remove a condition.
	 * @param ExperimentalCondition $condition the condition
	 * @return boolean whether the operation is successful or not
	 */
	public static function deleteByCondition (ExperimentalCondition $condition) {
		$sql = "delete from `".self::$tableName."` where dataSet = ?";
		return \Kiwi\Application::$conn->doQuery($sql, [$condition->id]);
	}
}