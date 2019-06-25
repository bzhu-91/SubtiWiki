<?php
class Object extends \Kiwi\Model {
	public $id;
	public $title;

	public function __construct($id) {
		$this->id = $id;
		$this->title = $id;
	}

	public static function simpleLookUp ($id) {
		return new Object($id);
	}

	public static function simpleValidate ($title) {
		return new Object($title);
	}

	public static function simpleGet ($id) {
		return new Object($id);
	}
}