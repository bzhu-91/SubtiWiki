<?php
namespace Kiwi;

/**
 * This class implements the virtualization mentioned in the "Document of the database structure".
 * The static property $VirtualColumnName and $NativeJSON should be set in the Config.php
 */
class DocumentRecord extends ActiveRecord {
	// name of the virtual column where data is hosted in json format
	// mysql 5.8+ support native json data type
	// however the PDO drive does not do the decoding
	public static $VirtualColumnName = "data";
	public static $NativeJSON = false;
	
	/**
	 * Takes the same parameters as the constructor of PDO class, details see the documents for PDO class
	 * copy the static vals as default because virtual col name and data tpye can differ from table to table
	 */
	function __construct($dsn, $user, $pass){
		parent::__construct($dsn, $user, $pass);
		$this->virtualColumnName = self::$VirtualColumnName;
	}

	/**
	 * get the real column name from the virtual column name
	 * @param  string $table_name  name of the table
	 * @param  string $column_name name of the virtual column
	 * @return string the real column name, escaped
	 */
	public function getTrueColumnName ($table_name, $column_name) {
		$columns = $this->getColumnNames($table_name);
		if (in_array($column_name, $columns)) {
			return "`".$column_name."`";
		} else if (in_array("_".$column_name, $columns)) {
			return "`_".$column_name."`";
		} else if (self::$NativeJSON) {
			if (strpos($column_name, " ")) {
				$path = "'$.\"{$column_name}\"'";
			} else {
				$path = "'$.{$column_name}'";
			}
			return $this->virtualColumnName.'->>'.$path;
		} else {
			return $column_name;
		}
	}
	/**
	 * create where clause based on the input, only "like" or "=" operator are used, "and" is used to connect all clauses. For example 
	 * [
	 * 		"name" => "Chris",
	 * 		"gender" => "M"
	 * ] will become 
	 * `name` like "Chris" and `gender` like "M"
	 * @param  array/object $array input
	 * @param  string $table_name table name
	 * @return array "where" => the where clause, "values" => the values for the ? in the where clause
	 */
	public function getWhereClause ($array, $table_name) {
		$array = self::objectToArray($array);
		$where = [];
		if (self::$NativeJSON) {
			$columns = $this->getColumnNames($table_name);
			foreach ($array as $key => $value) {
				$key = $this->getTrueColumnName($table_name, $key);
				if (is_numeric($value)) {
					$operator = " = ";
				} else {
					$operator = " like ";
				}
				$where[] = $key.$operator."?";
			}
		} else {
			foreach ($array as $key => $value) {
				if (is_numeric($value)) {
					$where[] = "`".$key."` = ?";
				} else {
					$where[] = "`".$key."` like ?";
				}
			}
		}
		$where = implode(" and ", $where);
		$vals = array_values($array);
		foreach ($vals as &$val) {
			if (is_array($val) || is_object($val)) {
				throw new BaseException("There is an error in the where clause, object or array is not allowed");
			}
		}
		return [
			"where" => $where,
			"values" => $vals
		];
	}
	/**
	 * deep decode all json strings in the data
	 * @param array/object &$data the object to be decoded
	 * @return null        
	 */
	public static function deepDecode (&$data) {
		if (is_string($data)) {
			$try = json_decode($data, true);
			if ($try !== null) {
				$data = $try;
				self::deepDecode($data);
			}
		} else if (is_array($data) || is_object($data)) {
			$data = (array) $data;
			foreach ($data as $key => &$value) {
				self::deepDecode($value);
			}
		}
	}

	/**
	 * process the result set for the virtualization
	 * @param  array/json  &$result result set
	 * @param  boolean $decode  do deep json decode or not
	 * @return null           
	 */
	public function processResultSet (&$result, $decode = true) {
		if ($decode) {
			self::deepDecode($result);
		}
		if (is_array($result)) {
			if (array_key_exists($this->virtualColumnName, $result)) {
				// need to keep the order
				// so all keys in "data" col will replace "data"
				// index columns will be ignored if there is copy in "data"
				// "_" will be removed from index column names when no copy exists
				$obj = [];
				foreach ($result as $key => $value) {
					if ($key == $this->virtualColumnName) {
						if(is_array($value) && $value) $obj = array_merge($obj, $value);
					} else if ($key[0] != "_") {
						$obj[$key] = $value;
					} else if (!array_key_exists(substr($key, 1), $result)) {
						$obj[substr($key, 1)] = $value;
					}
				}
				$result = $obj;
			}
			foreach ($result as $key => &$value) {
				$this->processResultSet($value, false);
			}
		}
	}
	/**
	 * execute a select statement
	 * @param  string $table_name table name
	 * @param  string/array $column_names column names for selection, functions or "as" are not allowed here!!
	 * @param  string/array $where citeria to find the table row
	 * @param  array  $vals values to replace ? in the where clause
	 * @return array/boolean result sets or false if SQL error has happened       
	 */
	public function select ($table_name, $column_names = "*", $where, $vals = []) {
		$columns = $this->getColumnNames($table_name);
		if (!$columns) {
			$this->lastError = "table with name $table_name does not exist";
			return false;
		}
		if (is_array($where)) {
			$clause = $this->getWhereClause($where, $table_name);
			$where = $clause["where"];
			$vals = $clause["values"];
		}
		if (self::$NativeJSON && $column_names != "*" && in_array($this->virtualColumnName, $columns)) {
			$where = self::objectToArray($where);
			$vals = self::objectToArray($vals);
			// now things are getting cooler
			$selects = [];
			// can not use array_diff function because order matters here, has to be purely data driven
			foreach ($column_names as $name) {
				if (in_array($name, $columns)) {
					$selects[] = "`".$name."`";
				} else {
					$selects[] = $this->getTrueColumnName($table_name, $name)." as `".$name."`";
				}
			}
			$sql = "select ".implode(", ", $selects)." from $table_name where $where";
			$result = $this->doQuery($sql, $vals);
		} else {
			$result = parent::select($table_name, $column_names, $where, $vals);
			if ($result) $this->processResultSet($result);
		}
		return $result;
	}
	/**
	 * execute a insert statement
	 * @param  string $table_name table name
	 * @param  array/object $data data to be inserted
	 * @return boolean/mixed true/false/lastInsertedId if possible
	 */
	public function insert ($table_name, $data) {
		$data = self::objectToArray($data);
		$columns = $this->getColumnNames($table_name);
		if (!$columns) {
			$this->lastError = "table with name $table_name does not exist";
			return false;
		}
		if (in_array($this->virtualColumnName, $columns)) {
			$flat = [];
			foreach ($data as $key => $value) {
				if (in_array($key, $columns)) {
					if (is_array($value) || is_object($value)) {
						$flat[$key] = json_encode($value);
					} else {
						$flat[$key] = $value;
					}
					unset($data[$key]);
				}
				// this is for the index columns, which would be completely unnecessary if native json is supported
				if (in_array("_".$key, $columns)) {
					if (is_array($value) || is_object($value)) {
						$flat["_".$key] = json_encode($value);
					} else {
						$flat["_".$key] = $value;
					}
				}
			}
			if (!empty($data)) {
				$flat[$this->virtualColumnName] = json_encode($data);
			}
			$data = $flat;
		}
		return parent::insert($table_name, $data);
	}

	/**
	 * execute a update statement
	 * @param  string $table_name table name
	 * @param  array/object $data data to be update to table row
	 * @param  array/string $where citeria to find the table row to be updated
	 * @param  array  $vals values to replace ? in the where clause if $where is string
	 * @return boolean if success true, else false
	 */
	public function update ($table_name, $data, $where, $vals = []) {
		$columns = $this->getColumnNames($table_name);
		if (!$columns) {
			$this->lastError = "table with name $table_name does not exist";
			return false;
		}
		$data = static::objectToArray($data);
		$vals = static::objectToArray($vals);
		$where = static::objectToArray($where);
		if (is_array($where)) {
			$clause = $this->getWhereClause($where, $table_name);
			$where = $clause["where"];
			$vals = $clause["values"];
		}

		if (in_array($this->virtualColumnName, $columns)) {
			// merged update
			$old = $this->select($table_name, "*", $where, $vals);
			if (count($old) == 0) {
				// record not found
				throw new BaseException("record to update is not found");
			} elseif (count($old) > 1) {
				throw new BaseException("multiple record update is not supported");
			} else {
				$old = $old[0];
				foreach ($data as $key => $value) {
					$old[$key] = $value; // can be set to null;
				}
				$data = $old;
			}

			$flat = [];
			foreach ($data as $key => $value) {
				if (in_array($key, $columns)) {
					if (is_array($value) || is_object($value)) {
						$flat[$key] = json_encode($value);
					} else {
						$flat[$key] = $value;
					}
					unset($data[$key]);
				}
				// this is for the index columns, which would be completely necessary if native json is supported
				if (in_array("_".$key, $columns)) {
					if (is_array($value) || is_object($value)) {
						$flat["_".$key] = json_encode($value);
					} else {
						$flat["_".$key] = $value;
					}
				}
			}
			if (!empty($data)) {
				$flat[$this->virtualColumnName] = json_encode($data);
			} else $flat[$this->virtualColumnName] = "{}";
			$data = $flat;
		}
		return parent::update($table_name, $data, $where, $vals);
	}

	/**
	 * execute a update statement, columns which are not in the given data will be set to null (if possible, can result SQL error)
	 * @param  string $table_name table name
	 * @param  array/object $data data to be update to table row
	 * @param  array/string $where citeria to find the table row to be updated
	 * @param  array  $vals values to replace ? in the where clause if $where is string
	 * @param array $keep the columns which should keep the original value instead of being set to null or updated
	 * @return boolean if success true, else false
	 */
	public function replace ($table_name, $data, $where, $vals = [], $keep = []) {
		$columns = $this->getColumnNames($table_name);
		if (!$columns) {
			$this->lastError = "table with name $table_name does not exist";
			return false;
		}
		$data = static::objectToArray($data);
		$vals = static::objectToArray($vals);
		$where = static::objectToArray($where);
		if (is_array($where)) {
			$clause = $this->getWhereClause($where, $table_name);
			$where = $clause["where"];
			$vals = $clause["values"];
		}

		if ($keep) {
			$skip = true;
			foreach ($keep as $key) {
				$skip = $skip && in_array($key, $columns);
			}
			if (!$skip) {
				// merged update
				$old = $this->select($table_name, "*", $where, $vals);
				if (count($old) == 0) {
					// record not found
					throw new BaseException("record to update is not found");
				} elseif (count($old) > 1) {
					throw new BaseException("multiple record update is not supported");
				} else {
					// handle keep, but no order is specified
					$old = $old[0];
					foreach ($old as $key => $value) {
						if (in_array($key, $keep) && !in_array($key, $columns)) {
							$data[$key] = $value;
						}
					}
				}
			}
		}

		if (in_array($this->virtualColumnName, $columns)) {
			$flat = [];
			foreach ($data as $key => $value) {
				if (in_array($key, $columns)) {
					if (is_array($value) || is_object($value)) {
						$flat[$key] = json_encode($value);
					} else {
						$flat[$key] = $value;
					}
					unset($data[$key]);
				}
				// this is for the index columns, which would be completely necessary if native json is supported
				if (in_array("_".$key, $columns)) {
					if (is_array($value) || is_object($value)) {
						$flat["_".$key] = json_encode($value);
					} else {
						$flat["_".$key] = $value;
					}
				}
			}
			if (!empty($data)) {
				$flat[$this->virtualColumnName] = json_encode($data);
			} else $flat[$this->virtualColumnName] = "{}";
			$data = $flat;
		}
		return parent::replace($table_name, $data, $where, $vals, $keep);
	}

	/**
	 * execute a SQL statement
	 * @param  string $sql  sql statement
	 * @param  array $vals values to replace ? in the statement
	 * @return array/boolean    
	 */
	public function doQuery ($sql, $vals = []) {
		foreach ($vals as &$v) {
			if (is_array($v) || is_object($v)) {
				$v = json_encode($v);
			}
		}
		
		$result = parent::doQuery($sql, $vals);
		if ($result && is_array($result)) $this->processResultSet($result);
		return $result;
	}
}
DocumentRecord::$VirtualColumnName = $GLOBALS["DOCUMENT_RECORD_SETTINGS"]["virtual_column_name"];
DocumentRecord::$NativeJSON = $GLOBALS["DOCUMENT_RECORD_SETTINGS"]["native_json_support"];
?>