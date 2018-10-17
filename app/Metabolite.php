<?php
class Metabolite extends Model {
	static $tableName = "Metabolite";
	static $primaryKeyName = "id";

	public function update () {
		if ($this->id) {
			$conn = Application::$conn;
			$conn->beginTransaction();
			if (History::record($this, "update") && parent::update()) {
				$conn->commit();
				return true;
			} else {
				$conn->rollback();
				return false;
			}
		}
	}

	public function insert () {
		$conn = Application::$conn;
		$conn->beginTransaction();
		if (($id = parent::insert()) && History::record($this, "add")) {
			$conn->commit();
			$this->id = $id;
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}
}
?>