<?php
namespace Monkey;
/**
 * to extend the Model class
 */
trait ModelExtra {
	public function updateCount() {
		$sql = "update `".static::$tableName."` set count = count + 1 where `".static::$primaryKeyName."` = ?";
		$conn = Application::$conn;
		return $conn->doQuery($sql, [$this->{static::$primaryKeyName}]);
	}
}
?>