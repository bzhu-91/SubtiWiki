<?php
class BiologicalObject extends \Kiwi\Model {
	public $id;
	public $title;

	public function __construct($id) {
		$this->id = $id;
		$this->title = $id;
	}

	public static function simpleLookUp ($id) {
		return new BiologicalObject($id);
	}

	public static function simpleValidate ($title) {
		return new BiologicalObject($title);
	}

	public static function simpleGet ($id) {
		return new BiologicalObject($id);
	}
}