<?php
class Object extends Model {
	public $id;
	public $title;

	public function __construct($id) {
		$this->id = $id;
		$this->title = $id;
	}

	public static function getRefWithId ($id) {
		return new Object($id);
	}

	public static function getRefWithTitle ($title) {
		return new Object($title);
	}

	public static function simpleGet ($id) {
		return new Object($id);
	}
}