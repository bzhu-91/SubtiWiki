<?php
class Interaction extends \Monkey\Relationship {
	static $tableName = "Interaction";
	protected $_tableName = "Interaction";
	protected $_ordered = false;
	protected $_col1 = "prot1";
	protected $_col2 = "prot2";
	protected $_class1 = "Protein";
	protected $_class2 = "Protein";
	protected $_primaryKeyName = "id";
	protected $_name = "interaction";

	public function __construct () {}

	public static function withData ($data) {
		$ins = new Interaction();
		foreach ($data as $key => $value) {
			if ($key[0] !== "_") {
				$ins->$key = $value;
			}
		}
		return $ins;
	}

	public function insert () {
		$conn = \Monkey\Application::$conn;
		if (strcmp($this->prot1, $this->prot2) > 0) {
			\Monkey\Utility::swap($this->prot1, $this->prot2);
		}
		$conn->beginTransaction();
		$this->id = parent::insert();
		if ($this->id && History::record($this, "add")) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	public function update () {
		$conn = \Monkey\Application::$conn;
		$conn->beginTransaction();
		if (parent::update() && History::record($this, "update")) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	public function delete () {
		$conn = \Monkey\Application::$conn;
		$conn->beginTransaction();
		$this->id = parent::delete();
		if ($this->id && History::record($this, "remove")) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}
	
	public static function getWholeGraph () {
		$conn = \Monkey\Application::$conn;
		$sql = "select prot1 as `from`, prot2 as `to`, data from ".static::$tableName.";";
		$result = $conn->doQuery($sql);
		foreach ($result as &$row) {
			$row["from"] = (object) [
				"id" => $row["from"]
			];
			$row["to"] = (object) [
				"id" => $row["to"]
			];
		}
		return new Graph ($result);
	}
}
?>