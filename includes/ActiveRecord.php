<?php
// implement ORM pattern: ActiveRecord
// has select, insert, update, delete functions
class ActiveRecord extends DBBase {
	/**
	 * generate the where clause based on the input
	 * @param  array/object $array 
	 * @return array        "where": where clause, "values": values
	 */
	private function generateWhereClause ($array) {
		$where = [];
		foreach ($array as $key => $value) {
			if (is_numeric($value)) {
				$where[] = "`".$key."` = ?";
			} else {
				$where[] = "`".$key."` like ?";
			}
		}
		$where = implode(" and ", $where);
		$vals = array_values($array);
		return [
			"where" => $where,
			"values" => $vals
		];
	}
	/**
	 * select statement
	 * @param  string $table_name   table name
	 * @param  string/array $column_names column name to select
	 * @param  string/array $where        criteria to find the row
	 * @param  array  $values       values to replace ? in the where clause when $where is string
	 * @return result set or false            false when SQL error happened
	 */
	public function select ($table_name, $column_names = "*", $where, $values = []) {
		$where = self::objectToArray($where);
		$values = self::objectToArray($values);
		if ($column_names != "*") {
			foreach ($column_names as &$name) {
				$name = "`".$name."`";
			}
			$column_names = implode(", ", $column_names);
		}
		if (is_array($where)) {
			$clause = $this->generateWhereClause($where);
			$where = $clause["where"];
			$values = $clause["values"];
		}
		$sql = "select $column_names from `$table_name` where $where";
		return $this->doQuery($sql, $values);
	}
	
	/**
	 * insert statement
	 * @param  string $table_name table name
	 * @param  array/object $data       data to be insert
	 * @return boolean             true if successful, false otherwise
	 */
	public function insert ($table_name, $data) {
		$data = self::objectToArray($data);
		$column_names = $this->getColumnNames($table_name);
		if ($column_names) {
			$keys = [];
			$values = [];
			foreach ($column_names as $name) {
				if (array_key_exists($name, $data)) {
					$keys[] = "`".$name."`";
					$values[] = $data[$name];
				}
			}
			
			$sql = "insert into `$table_name` (".implode(", ", $keys).") values (".implode(", ", array_pad([], count($keys), "?")).")";
			$stmt = $this->doQuery($sql, $values);
			if ($stmt) {
				$lastInsertId = $this->lastInsertId();
				return $lastInsertId ? $lastInsertId : true;
			}
		} else {
			$this->lastError = "table with name $table_name does not exist";
			return false;
		}
	}

	/**
	 * update the table by the given data, extra keys are ignored
	 * @param  string $table_name name of the table
	 * @param  array/object $data       data of the table row
	 * @param  string/array $where      citeria to find the row to be updated
	 * @param  array  $vals       values to replace ? in the where clause if $where is string
	 * @return boolean             if success true, else false
	 */
	public function update ($table_name, $data, $where, $vals = []) {
		if ($where) {
			$columns = $this->getColumnNames($table_name);
			if ($columns) {
				$data = self::objectToArray($data);
				$vals = self::objectToArray($vals);
				$where = self::objectToArray($where);
				$updates = [];
				$values = [];
				if (is_array($where)) {
					$clause = $this->generateWhereClause($where);
					$where = $clause["where"];
					$data = $vals;
					$vals = $clause["values"];
				}
				foreach ($columns as $name) {
					if (array_key_exists($name, $data)) {
						$updates[] = "`".$name."` = ?";
						$values[]  = $data[$name];
					}
				}
				$sql = "update $table_name set ".implode(", ", $updates)." where $where";
				$values = array_merge($values, $vals);
				$stmt = $this->doQuery($sql, $values);
				return !($stmt == false);
			} else {
				$this->lastError = "table with name $table_name does not exist";
				return false;
			}
		} else {
			throw new Exception("Should provide where clause for update statement");
		}
	}

	public function replace ($table_name, $data, $where, $vals = [], $keep = []) {
		if ($where) {
			$columns = $this->getColumnNames($table_name);
			if ($columns) {
				// cast all objects to array
				$data = self::objectToArray($data);
				$vals = self::objectToArray($vals);
				$where = self::objectToArray($where);
				$updates = [];
				$values = [];
				// get the where clause
				if (is_array($where)) {
					$clause = $this->generateWhereClause($where);
					$where = $clause["where"];
					$data = $vals;
					$vals = $clause["values"];
				}

				foreach ($columns as $name) {
					if (!in_array($name, $keep)) {
						$updates[] = "`".$name."` = ?";
						$values[]  = $data[$name]; // complete replace, nullable
					}
				}
				$sql = "update $table_name set ".implode(", ", $updates)." where $where";
				$values = array_merge($values, $vals);
				$stmt = $this->doQuery($sql, $values);
				return !($stmt == false);
			} else {
				$this->lastError = "table with name $table_name does not exist";
				return false;
			}
		} else {
			throw new Exception("Should provide where clause for update statement");
		}
	}
	
	/**
	 * delete statement
	 * @param  string $table_name table name
	 * @param  string/array $where      criteria to find the row
	 * @param  array  $vals       values to replace ? in the where clause when $where is a string
	 * @return boolean            true if successful, false otherwise
	 */
	public function delete ($table_name, $where, $vals = []) {
		if ($where) {
			$where = self::objectToArray($where);
			if (is_array($where)) {
				$clause = $this->generateWhereClause($where);
				$where = $clause["where"];
				$vals = $clause["values"];
			}
			$sql = "delete from $table_name where $where";
			$result = $this->doQuery($sql, $vals);
			return !($result == false);
		} else {
			throw new Exception("Should provide where clause for delete statement");
		}
	}

	/**
	 * do the queries
	 * @param  string $sql  sql statement
	 * @param  array $vals values to replace ? in the statement
	 * @return array/boolean       
	 */
	public function doQuery ($sql, $vals = []) {
		$sql = trim($sql);
		$stmt = parent::doQuery($sql, $vals);
		if ($stmt) {
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ("select" == substr($sql, 0,6)) {
				return $result;
			} else {
				return $this->lastError == null;
			}
		} else return false;
	}
}
?>