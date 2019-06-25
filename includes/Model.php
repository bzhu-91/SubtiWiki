<?php
namespace Kiwi;

/**
 * This class presents a data model
 */
abstract class Model {
	use ModelExtra, Markup;
	
	static $tableName;
	static $primaryKeyName = "id";
	static $relationships = [];
	protected $_cache = [];

	/**
	 * create an instance with give data
	 * @param  array/object $data data to be copied
	 * @return instance instance of the called calss
	 */
	public static function withData ($data) {
		\Kiwi\Utility::clean($data);
		\Kiwi\Utility::toObject($data);
		$className = get_called_class();
		$instance = new $className();
		foreach ($data as $key => $value) {
			$instance->{$key} = $value;
		}
		return $instance;
	}

	/**
	 * find the instance by id
	 * @param  string/number $id id of the instance
	 * @throws BaseException when table name is not set
	 * @return instance instance of the called class
	 */
	public static function get ($id) {
		$conn = Application::$conn;
		if (static::$tableName) {
			$result = $conn->select(static::$tableName, "*", [static::$primaryKeyName => $id]);
			if ($result) {
				$first = $result[0];
				return static::withData($first);
			}
		} else throw new BaseException("table name for specified for model $className", 1);
	}


	/**
	 * find the raw data by id
	 * @param string/number $id the id the of instance
	 * @return instance the instance of the called class
	 */
	public static function simpleGet ($id) {
		return static::raw($id);
	}

	/**
	 * count the number of rows in the table by a search criteria
	 * @return int row count
	 */
	public static function count ($where = "1", $vals = []) {
		$conn = Application::$conn;
		if (static::$tableName) {
			$result = $conn->doQuery("select count(`".static::$primaryKeyName."`) as c from `".static::$tableName."` where ".$where, $vals);
			if ($result) {
				return $result[0]["c"];
			}
		}
	}

	/**
	 * return the instance without any further process
	 * @param  string/number $id id
	 * @throws BaseException when table name is not set
	 * @return instance instance of called class
	 */
	public static final function raw ($id) {
		$conn = Application::$conn;
		if (static::$tableName) {
			if (is_object($id) && method_exists($id, "__toString")) {
				$id = (string) $id;
			} else if (is_object($id) || is_array($id)) {
				$id = json_encode($id);
			}
			$result = $conn->select(static::$tableName, "*", [static::$primaryKeyName => $id]);
			if ($result) {
				$first = $result[0];
				return self::withData($first);
			}
		} else throw new BaseException("table name for specified for model $className", 1);
	}

	/**
	 * find all instances by where clause and related values
	 * @param  string $where where clause, but with no "where"
	 * @param  array/object $values  values to replace ? in the where clause
	 * @throws BaseException when table name is not set
	 * @return array instances of called class
	 */
	public static function getAll($where = "1", $values = []) {
		$conn = Application::$conn;
		if (static::$tableName) {
			$result = $conn->select(static::$tableName, "*", $where, $values);
			if ($result) {
				foreach ($result as &$row) {
					$row = static::withData($row);
				}
				return $result;
			}
		} else throw new BaseException("table name for specified for model $className", 1);
	}

	/**
	 * get the key-value pairs of this instance and save in an array, properties whose name starts with an "_" will be ignored
	 * @return array the data
	 */
	public function getData () {
		$data = [];
		foreach ($this as $key => $value) {
			if ($key[0] != "_") {
				$data[$key] = $value;
			}
		}
		return $data;
	}

	/**
	 * insert the instance to the table
	 * @throws BaseException when table name is not set
	 * @return bool true if success, false if not
	 */
	public function insert () {
		$conn = Application::$conn;
		if (static::$tableName) {
			$result = $conn->insert(static::$tableName, $this->getData());
			if (is_numeric($result)) {
				$this->{static::$primaryKeyName} = $result; // in case of auto-increment primary key
			}
			return $result;
		} else throw new BaseException("table name for specified for model $className", 1);
	}

	/**
	 * update the instance in the table
	 * @throws BaseException when table name is not set
	 * @return bool true if success, false if not
	 */
	public function update () {
		$conn = Application::$conn;
		if (static::$tableName) {
			return $conn->update(static::$tableName, $this->getData(), [static::$primaryKeyName => $this->{static::$primaryKeyName}]);
		} else throw new BaseException("table name for specified for model $className", 1);
	}

	/**
	 * update the instance in the table, use replace method instead of update method
	 * @param array $ignore the columns to be ignored during update
	 * @throws BaseException when table name is not set
	 * @return bool true if success, false if not
	 */
	public function replace ($ignore = []) {
		$conn = Application::$conn;
		if (static::$tableName) {
			return $conn->replace(static::$tableName, $this->getData(), [static::$primaryKeyName => $this->{static::$primaryKeyName}], null, $ignore);
		} else throw new BaseException("table name for specified for model $className", 1);
	}

	/**
	 * delete the instance from the DB
	 * @throws BaseException when table name is not set
	 * @return boolean 
	 */
	public function delete () {
		$conn = Application::$conn;
		if (static::$tableName) {
			return $conn->delete(static::$tableName, [static::$primaryKeyName => $this->{static::$primaryKeyName}]);
		} else throw new BaseException("table name for specified for model $className", 1);
	}

	/**
	 * find the relationship by name, name should be defined in static::$relationships already
	 * @param  string $relationshipName name of the relationship
	 * @param boolean $force force re-retrieve from the database
	 * @return array array of relationship, can be empty
	 */
	public function has ($relationshipName, $force = false) {
		if (array_key_exists($relationshipName, static::$relationships)) {
			if (array_key_exists($relationshipName, $this->_cache) && !$force) {
				return $this->_cache[$relationshipName];
			} else {
				$def = static::$relationships[$relationshipName];
				$relationshipPrototype = static::hasPrototype($relationshipName);
				if (array_key_exists("position", $def) || $def["ordered"] == false) {
					if ($def["position"] == 1) {
						$result = $relationshipPrototype->get($this, null);
					} else {
						$result = $relationshipPrototype->get(null, $this);
					}
					$this->_cache[$relationshipName] = $result;
					return $result;
				} else throw new BaseException("should specify the position", 1);
			}
		} else throw new BaseException("Definition for relationship $relationshipName not given", 1);
	}

	/**
	 * get the prototype relationship object
	 * @param  srting  $relationshipName name of the relationship, need to be defined
	 * @throws BaseException When no definition is there
	 * @return mixed relationship prototype or false if SQL error has happened,
	 */
	public static function hasPrototype ($relationshipName) {
		if (array_key_exists($relationshipName, static::$relationships)) {
			$def = static::$relationships[$relationshipName];
			$primaryKeyName = array_key_exists("primaryKeyName", $def) ? $def["primaryKeyName"] : null;
			$ordered = array_key_exists("ordered", $def) ? $def["ordered"] : null;
			$relationshipFactory = new Relationship($def["tableName"], $def["mapping"], $relationshipName, $primaryKeyName, $ordered);
			return $relationshipFactory;
		} else throw new BaseException("Definition for relationship $relationshipName not given", 1);
	}

	/**
	 * create a string presentation of the instance, in the form of {gene|123}, where 123 is the id of the instance
	 * @return string the string presention of this instance
	 */
	public function __toString(){
		if ($this->id) {
			$className = lcfirst(get_called_class());
			return "{".$className."|".$this->id."}";
		}
	}
}
?>