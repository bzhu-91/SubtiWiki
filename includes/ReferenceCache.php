<?php
trait ReferenceCache {
	static $lookupTable = [];
	static $validateTable = [];

	public static function initLookupTable () {
		// if use self::lookupTable, this will be shared between all classes which use this trait
		// if use get_called_class()::lookupTable, each class will has its own copy
		$className = get_called_class();
		$primaryKeyName = $className::$primaryKeyName;
		if (!$className::$lookupTable) {
			$con = Application::$conn;
			$className = get_called_class();
			if ($con && static::$tableName) {
				$result = $con->select(static::$tableName, ["id", "title"], "1");
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

	/** override get function, get from cache to reduce quries */
	public static function getRefWithId ($id) {
		static::initLookupTable();
		return static::$lookupTable[strtolower($id)];
	}

	public static function get ($id) {
		$ins = static::raw($id);
		if ($ins) {
			$ins->patch();
		}
		return $ins;
	}

	public static function simpleGet ($id) {
		return static::getRefWithId($id);
	}

	public static function initValidateTable() {
		$className = get_called_class();
		if (!$className::$validateTable) {
			$con = Application::$conn;
			$className = get_called_class();
			if ($con && static::$tableName) {
				$result = $con->select(static::$tableName, "*", "1");
				$keys = array_column($result, "title");
				foreach ($keys as &$key) {
					$key = strtolower($key);
				}
				foreach ($result as &$row) {
					$row = $className::withData($row);
				}
				$className::$validateTable = array_combine($keys, $result);
			} else throw new Exception("Should set the connection and table_name for Entity");
		}
	}

	public static function getRefWithTitle ($title) {
		static::initValidateTable();
		return static::$validateTable[strtolower($title)];
	}
}
?>