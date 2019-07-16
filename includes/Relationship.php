<?php
namespace Kiwi;

/**
 * This class implements a relationship. This class is prototype based
 */
class Relationship {
	use RelationshipExtra;
	use Markup;
	
	protected $_tableName;
	protected $_ordered;
	protected $_col1;
	protected $_col2;
	protected $_class1;
	protected $_class2;
	protected $_primaryKeyName;
	protected $_name;


	/**
	 * create a prototype for a relationship
	 * @param string  $tableName table name
	 * @param array  $mapping definition of table, colname => classname
	 * @param string  $primaryKeyName col name of the primary key, default id
	 * @param boolean $ordered if relationship is ordered
	 * @throws BaseException when parameters are not correct
	 */
	function __construct ($tableName, $mapping, $name = "", $primaryKeyName = "id", $ordered = true) {
		if ($tableName && $mapping) {
			$this->_tableName = $tableName;
			$this->_ordered = $ordered === null ? true: $ordered;
			$this->_name = $name === null ? "" : $name;
			$this->_primaryKeyName = $primaryKeyName === null ? "id" : $primaryKeyName;
			$i = 0;
			foreach ($mapping as $key => $value) {
				$this->$key = null;
				if ($i == 0) {
					$this->_col1 = $key;
					$this->_class1 = $value;
				} else {
					$this->_col2 = $key;
					$this->_class2 = $value;
				}
				$i++;
			}
		} else throw new BaseException("tablename and mapping can not be empty");
	}

	/**
	 * count the row numbers according to a search criteria (optional)
	 * @param string/array $where search criteria
	 * @param array $vals values to replace the ? in the SQL
	 * @return int/null the count
	 */
	public function count ($where = 1, $vals = []) {
		$conn = Application::$conn;
		if ($this->_tableName) {
			$result = $conn->doQuery("select count(`".$this->_primaryKeyName."`) as c from `".$this->_tableName."` where ".$where, $vals);
			if ($result) {
				return $result[0]["c"];
			}
		}
	}

	/**
	 * get all relationship instances according to a search criteria (optional), Both partners of the relationship will be instantialized with the simpleLookUp function.
	 * @param string/array $where search criteria
	 * @param array $vals the values to replace ? in the where clause
	 * @return array array of relationship instances
	 */
	public function getAll ($where = 1, $vals = []) {
		$conn = Application::$conn;
		if ($this->_tableName) {
			$result = $conn->doQuery("select * from `".$this->_tableName."` where ".$where, $vals);
			foreach ($result as &$row) {
				if ($this->_class2 != "mixed") {
					$row[$this->_col2] = method_exists($this->_class2, "simpleLookUp") ? $this->_class2::simpleLookUp($row[$this->_col2]) : $this->_class2::simpleGet($row[$this->_col2]);
				} else $row[$this->_col2] = Model::parse($row[$this->_col2]);
				if ($this->_class1 != "mixed") {
					$row[$this->_col1] = method_exists($this->_class1, "simpleLookUp") ? $this->_class1::simpleLookUp($row[$this->_col1]) : $this->_class1::simpleGet($row[$this->_col1]);
				} else $row[$this->_col1] = Model::parse($row[$this->_col1]);
				$row = $this->clone($row);
			}
			return $result;
		}
	}

	/**
	 * get a clone with the given data
	 * @param  array/object $data data to be copied to the new clone
	 * @return Relationship       
	 */
	public function clone ($data) {
		$instance = clone $this;
		foreach ($data as $key => $value) {
			$instance->$key = $value;
		}
		return $instance;
	}

	/**
	 * get the table name of the relationship
	 * @return string/null table name
	 */
	public function getTableName () {
		return $this->_tableName;
	} 

	/**
	 * get the primary key name of the relationship
	 * @return string/null primary key name
	 */
	public function getPrimaryKeyName () {
		return $this->_primaryKeyName;
	}

	/**
	 * find the relationship by the entitiy, for ordered only
	 * @param  Model/null $e1 instance 1
	 * @param  Model/null $e2 instance 2
	 * @return array of Relationship or null  
	 */
	protected function simpleGet (Model $e1 = null, Model $e2 = null) {
		$conn = Application::$conn;
		$vals = [];
		if ($e1) {
			if ($this->_class1 == "mixed" || get_class($e1) == $this->_class1 || is_subclass_of($e1, $this->_class1)) {
				$vals[$this->_col1] = $this->_class1 == "mixed" ? (string) $e1 : $e1->{$this->_class1::$primaryKeyName};
			} else throw new BaseException("the argument is not an instance of {$this->_class1}", 1);
			
		}

		if ($e2) {
			if ($this->_class2 == "mixed" || get_class($e2) == $this->_class2 || is_subclass_of($e2, $this->_class2)) {
				$vals[$this->_col2] = $this->_class2 == "mixed" ? (string) $e2 : $e2->{$this->_class2::$primaryKeyName};
			} else throw new BaseException("the argument is not an instance of {$this->_class2}", 1);
		}

		if ($vals) {
			$result = $conn->select($this->_tableName, "*", $vals);
			if ($result) {
				foreach ($result as &$row) {
					if ($e1) {
						$row[$this->_col1] = $e1;
						if ($this->_class2 != "mixed") $row[$this->_col2] = method_exists($this->_class2, "simpleGet") ? $this->_class2::simpleGet($row[$this->_col2]) : $this->_class2::get($row[$this->_col2]);
						else $row[$this->_col2] = Model::parse($row[$this->_col2]);
					}
					if ($e2) {
						$row[$this->_col2] = $e2;
						if ($this->_class1 != "mixed") $row[$this->_col1] = method_exists($this->_class1, "simpleGet") ? $this->_class1::simpleGet($row[$this->_col1]) : $this->_class1::get($row[$this->_col1]);
						else $row[$this->_col1] = Model::parse($row[$this->_col1]);
					}
					$row = $this->clone($row);
				}
			}
			return $result;
		} else throw new BaseException("No argument given",1);
		
	}

	/**
	 * find the relationship by the entitiy
	 * @param  Model $e1 instance 1, @nullable
	 * @param  Model $e2 instance 2, @nullable
	 * @return array of Relationship or null  
	 */
	public function get (Model $e1 = null, Model $e2 = null) {
		if ($this->_ordered) {
			return $this->simpleGet($e1, $e2);
		} else {
			$resultSet1 = $this->simpleGet($e1, $e2);
			$resultSet2 = $this->simpleGet($e2, $e1);
			$merged = \Kiwi\Utility::arrayMerge($resultSet1, $resultSet2);
			$hash = [];
			foreach ($merged as $each) {
				$hash[$each->id] = $each;
			}
			return array_values($hash);
		}
	}

	/**
	 * return the raw table row
	 * @return array/null 	array if found, null if not
	 */
	public function raw () {
		$conn = Application::$conn;
		$result = $conn->select($this->_tableName, "*", [$this->_primaryKeyName => $this->id]);
		if ($result && count($result) == 1) {
			return $this->clone($result[0]);
		}
	}

	/**
	 * get the relationship by the id
	 * @param  string/number $id id
	 * @return Relationship     the found instance
	 */
	public function getWithId ($id) {
		$conn = Application::$conn;
		$result = $conn->select($this->_tableName, "*", [$this->_primaryKeyName => $id]);
		if ($result) {
			$row = $result[0];
			if ($this->_class2 != "mixed") {
				$row[$this->_col2] = method_exists($this->_class2, "simpleLookUp") ? $this->_class2::simpleLookUp($row[$this->_col2]) : $this->_class2::simpleGet($row[$this->_col2]);
			} else {
				$row[$this->_col2] = Model::parse($row[$this->_col2]);
			}
			if ($this->_class1 != "mixed") {
				$row[$this->_col1] = method_exists($this->_class1, "simpleLookUp") ? $this->_class1::simpleLookUp($row[$this->_col1]) : $this->_class1::simpleGet($row[$this->_col1]);
			} else {
				$row[$this->_col1] = Model::parse($row[$this->_col1]);
			}
			return $this->clone($row);
		}
	}


	/**
	 * insert the relationship
	 * @return boolean true if successful, false if otherwise
	 */
	public function insert () {
		$conn = Application::$conn;
		if ($this->{$this->_col1} && $this->{$this->_col2}) {
			$data = $this->getData();
			$result = $conn->insert($this->_tableName, $data);
			if (is_numeric($result)) {
				$this->{$this->_primaryKeyName} = $result;
			}
			return $result;
		} else throw new BaseException($this->_col1." or ".$this->_col2." not set", 1);
	}

	/**
	 * update the relationship, prefer to use id
	 * @return boolean true if successful, false if otherwise
	 */
	public function update () {
		$conn = Application::$conn;
		$data = $this->getData();
		// update by id
		if ($this->_primaryKeyName) {
			$id = [$this->_primaryKeyName => $this->{$this->_primaryKeyName}];
		} else if ($this->{$this->_col1} && $this->{$this->_col2}) {
			// update by col1 and col2
			$id = [];
			$id[$this->_col1] = $this->_class1 == "mixed" ? $id[$this->_col1]->toObjectMarkup() : $id[$this->_col1]->id;
			$id[$this->_col2] = $this->_class2 == "mixed" ? $id[$this->_col2]->toObjectMarkup() : $id[$this->_col2]->id;
		} else throw new BaseException("no update criteria defined", 1);

		return $conn->update($this->_tableName, $data, $id);
	}

	/**
	 * update the relationship, prefer to use id, use replace instead of update
	 * @param array $ignores the columns which should be ignored in this update
	 * @return boolean true if successful, false if otherwise
	 */
	public function replace ($ignores) {
		$conn = Application::$conn;
		$data = $this->getData();
		// update by id
		if ($this->_primaryKeyName) {
			$id = [$this->_primaryKeyName => $this->{$this->_primaryKeyName}];
		} else if ($this->{$this->_col1} && $this->{$this->_col2}) {
			// update by col1 and col2
			$id = [];
			$id[$this->_col1] = $this->_class1 == "mixed" ? $id[$this->_col1]->toObjectMarkup() : $id[$this->_col1]->id;
			$id[$this->_col2] = $this->_class2 == "mixed" ? $id[$this->_col2]->toObjectMarkup() : $id[$this->_col2]->id;
		} else throw new BaseException("no update criteria defined", 1);

		return $conn->replace($this->_tableName, $data, $id, null, $ignores);
	}


	/**
	 * delete the relationship, prefer to use id
	 * @return boolean true if successful, false if not
	 */
	public function delete () {
		$conn = Application::$conn;

		// update by id
		if ($this->_primaryKeyName) {
			$id = [$this->_primaryKeyName => $this->{$this->_primaryKeyName}];
		} else if ($this->{$this->_col1} && $this->{$this->_col2}) {
			// update by col1 and col2
			$id = [];
			$id[$this->_col1] = $this->_class1 == "mixed" ? $id[$this->_col1]->toObjectMarkup() : $id[$this->_col1]->id;
			$id[$this->_col2] = $this->_class2 == "mixed" ? $id[$this->_col2]->toObjectMarkup() : $id[$this->_col2]->id;
		} else if ($this->{$this->_col1} && $this->{$this->_col2} == "*") {
			$id = [];
			$id[$this->_col1] = $this->_class1 == "mixed" ? $id[$this->_col1]->toObjectMarkup() : $id[$this->_col1]->id;
		} else if ($this->{$this->_col2} && $this->{$this->_col1} == "*") {
			$id = [];
			$id[$this->_col2] = $this->_class2 == "mixed" ? $id[$this->_col2]->toObjectMarkup() : $id[$this->_col2]->id;
		} else throw new BaseException("no update criteria defined", 1);

		return $conn->delete($this->_tableName, $id);
	}

	/**
	 * create a string presentation of this relationship
	 * @return string the string presentation
	 */
	public function toObjectMarkup() {
		return "{".lcfirst($this->_tableName)."|".$this->id."}";
	}

	/**
	 * create a string presentation of this relationship
	 * @return string the string presentation
	 */
	public function __toString() {
		return $this->toObjectMarkup();
	}

	/**
	 * get the key-value pairs of this instance and save then in an array
	 * @return array data
	 */
	public function getData () {
		if ($this->{$this->_col1} && $this->{$this->_col2}) {
			$data = [];
			foreach ($this as $key => $value) {
				if ($key[0] != "_") {
					$data[$key] = $value;
				}
			}
			if (is_object($data[$this->_col1])) {
				$data[$this->_col1] = $this->_class1 == "mixed" ? $data[$this->_col1]->toObjectMarkup() : $data[$this->_col1]->{$this->_class1::$primaryKeyName};
			}
			if (is_object($data[$this->_col2])) {
				$data[$this->_col2] = $this->_class2 == "mixed" ? $data[$this->_col2]->toObjectMarkup() : $data[$this->_col2]->{$this->_class2::$primaryKeyName};
			}
			if ($this->_ordered === false && $data[$this->_col1] > $data[$this->_col2]) {
				\Kiwi\Utility::swap($data[$this->_col1], $data[$this->_col2]);
				\Kiwi\Utility::swap($this->{$this->_col1}, $this->{$this->_col2});
			}
			return $data;
		}
	}
}
?>