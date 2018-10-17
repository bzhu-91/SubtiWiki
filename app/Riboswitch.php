<?php
class Riboswitch extends Gene {
	public $id;
	public $title;

	static $relationships = [
		"regulation" => [
			"tableName" => "Regulation",
			"mapping" => [
				"regulator" => "mixed",
				"regulated" => "mixed"
			],
			"position" => 1
		]
	];

	public function __construct($id) {
		$this->id = $id;
		$this->title = $id;
	}

	public static function getRefWithId ($id) {
		return new Riboswitch($id);
	}

	public static function getRefWithTitle ($title) {
		return new Riboswitch($title);
	}
}

?>