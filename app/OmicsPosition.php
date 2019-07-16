<?php
/**
 * For omics data which is position-based
 */
class OmicsPosition extends \Kiwi\Model {
	static $tableName = "OmicsData_position";

	
	/**
	 * getByRange: get the tilling array or other position based expression data.
	 * @param  int $condition id of the condition 
	 * @param  int $start start position on the genome
	 * @param  int $stop stop position on the genome, should always larger than start
	 * @param  int $strand the strand
	 * @param  int $sampling the sampling rate, default is 1 (this is to reduce the memory use)
	 * @return array/null in the format of [:position => :expressionLevel]
	 */
	public static function getByRange ($condition, $start, $stop, $strand = 1, $sampling = 1) {
		$sql = "select position, value from ".self::$tableName." where position >= ? and position <= ? and strand = ? and mod(position, $sampling) = 0 and dataSet = ?";
		if ($start && $stop && $start < $stop && ($strand == 0 || $strand == 1)) {
			$conn = \Kiwi\Application::$conn;
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

	/**
	 * remove the data of a condition.
	 * @param ExperimentalCondition $condition the condition to be removed
	 * @return boolean whether the condition is successful or not
	 */
	public static function deleteByCondition (ExperimentalCondition $condition) {
		$sql = "delete from `".self::$tableName."` where dataSet = ?";
		return \Kiwi\Application::$conn->doQuery($sql, [$condition->id]);
	}
}