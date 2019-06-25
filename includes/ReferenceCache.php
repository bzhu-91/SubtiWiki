<?php
namespace Kiwi;

/**
 * A memory cache of look up table and validate table.
 * Most of the tables in SubtiWiki has two columns, namely id and title. The id column is used as a surrogate primary key and the title is the human-friendly primary key.
 * There are often operations to get the title by id or get the id by searching for the title. So this trait is implemented to create a memory cache of a look-up-table to reduced the numbers of database operations and boost up the speed.
 */
trait ReferenceCache {
	static $lookupTable = [];
	static $validateTable = [];

	/**
	 * init the look-up table
	 */
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

	/**
	 * get a reference of an Model instance
	 * @param string/number the id of the instance
	 * @return instance/null a reference instance of the called class
	 */
	public static function simpleLookUp ($id) {
		static::initLookupTable();
		return static::$lookupTable[strtolower($id)];
	}

	/**
	 * get the Model instance, patch function is called to further retrieve other data
	 * @param string/number the id of the instance
	 * @return instance/null a full instance of the called class
	 */
	public static function get ($id) {
		$ins = static::raw($id);
		if ($ins) {
			$ins->patch();
		}
		return $ins;
	}

	/**
	 * the patch function, where a reference instance retrieve all properties from the DB
	 */
	abstract function patch ();

	/**
	 * init the validate table
	 */
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
			} else throw new BaseException("Should set the connection and table_name for Entity");
		}
	}

	/**
	 * get a reference instance by look up the title
	 * @param string title the title of the instance
	 * @return instance/null a reference instance of the called class
	 */
	public static function simpleValidate ($title) {
		static::initValidateTable();
		return static::$validateTable[strtolower($title)];
	}
}
?>