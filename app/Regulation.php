<?php
class Regulation extends Relationship {

	protected $_tableName = "Regulation";
	protected $_ordered = true;
	protected $_col1 = "regulator";
	protected $_col2 = "regulated";
	protected $_class1 = "mixed";
	protected $_class2 = "mixed";
	protected $_primaryKeyName = "id";
	protected $_name = "regulation";

	function __construct ($regulator = null, $mode = null, $regulated = null, $description = "") {
		if (func_num_args()) {
			$this->regulator = $regulator;
			$this->regulated = $regulated;
			$this->mode = $mode;
			$this->description = $description;
		}
	}

	public static function withData ($data) {
		$ins = new Regulation();
		foreach ($data as $key => $value) {
			if ($key[0] !== "_") {
				$ins->$key = $value;
			}
		}
		return $ins;
	}

	public static function getByRegulator ($regulatorId) {
		if ($regulatorId) {
			$regulator = Model::parse("{".str_replace("|", ":", $regulatorId)."}");
			if ($regulator) {
				return (new Regulation())->get($regulator, null);
			}
		}
	}

	public function insert () {
		$conn = Application::$conn;
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
		$conn = Application::$conn;
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
		$conn = Application::$conn;
		$conn->beginTransaction();
		if (History::record($this, "remove") && parent::delete()) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	public static function getWholeGraph ($sigA = false) {
		$conn = Application::$conn;
		if ($sigA) {
			$sql = "select regulator as `from`, mode, description, gene as `to` from MaterialViewGeneRegulation join Regulation on Regulation.id = MaterialViewGeneRegulation.regulation";
		} else {
			$sql = "select regulator as `from`, mode, description, gene as `to` from MaterialViewGeneRegulation join Regulation on Regulation.id = MaterialViewGeneRegulation.regulation where regulator not like '{protein|360F48D576DE950DF79C1A2677B7A35A8D8CC30C}'";
		}
		$result = $conn->doQuery($sql);

		foreach ($result as &$row) {
			if (strlen($row["from"]) == 50) {
				$row["from"] = (object) [
					"id" => substr($row["from"], 9, 40)
				];
			} else {
				$row["from"] = Model::parse($row["from"]);
			}
			$row["to"] = (object) [
				"id" => $row["to"]
			];
		}
		return new Graph ($result, true);
	}

	public static function export () {
		$conn = Application::$conn;
		$sql = "select regulator, mode, gene from MaterialViewGeneRegulation join Regulation on Regulation.id = MaterialViewGeneRegulation.regulation";
		$result = $conn->doQuery($sql);

		foreach ($result as &$row) {
			$row = self::withData($row);
			$row->regulator = Model::parse($row->regulator);
			$row->regulated = Gene::simpleGet($row->regulated);
		}
		return $result;
	}
}
?>