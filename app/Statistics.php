<?php
class Statistics extends \Kiwi\Model {
	static $tableName = "Statistics";
	static $primaryKeyName = "item";

	public static function getSum ($className) {
		$tableName = $className::$tableName;
		$sql = "select sum(count) as count from $tableName";
		$result = \Kiwi\Application::$conn->doQuery($sql);
		if ($result) {
			return $result[0]["count"];
		}
	}

	public static function get ($item) {
		$re = parent::get($item);
		if ($re) {
			return $re->count;
		}
	}

	public static function increment ($item) {
		$sql = "update `".self::$tableName."` set count = count + 1 where item = ?";
		\Kiwi\Application::$conn->doQuery($sql, [$item]);
	}

	public static function getCount ($tableName) {
		$sql = "select count(*) as count from $tableName";
		$result = \Kiwi\Application::$conn->doQuery($sql);
		if ($result) {
			return $result[0]["count"];
		}	
	}
}
?>