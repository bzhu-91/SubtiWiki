<?php
class Metabolite extends \Kiwi\Model {
	static $tableName = "Metabolite";
	static $primaryKeyName = "id";
	static $relationships = [
		"reaction" => [
			"tableName" => "ReactionMetabolite",
			"mapping" => [
				"metabolite" => "mixed",
				"reaction" => "Reaction",
			],
			"position" => 1,
		]
	];
	public function update () {
		if ($this->id) {
			$conn = \Kiwi\Application::$conn;
			$conn->beginTransaction();
			$result = true;
			// need to update the equations of related reactions
			$hasReactions = $this->has("reaction");
			foreach($hasReactions as $hasReaction) {
				$result = $result && $hasReaction->reaction->updateEquation();
			}
			$result = $result && History::record($this, "update") && parent::update();
			if ($result) {
				$conn->commit();
				return true;
			} else {
				$conn->rollback();
				return false;
			}
		}
	}

	public function insert () {
		$conn = \Kiwi\Application::$conn;
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