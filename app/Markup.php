<?php

trait Markup {
	public function toLinkMarkup () {
		return "[".lcfirst(get_called_class())."|".$this->id."|".$this->title."]";
	}

	public function toEditMarkup () {
		return "[[".lcfirst(get_called_class())."|".$this->title."]";
	}

	public function toObjectMarkup() {
		return "{".lcfirst(get_called_class())."|".$this->id."}";
	}

	/**
	 * get the instance from the object makrup
	 * @param  string $str object markup, like {type|id}
	 * @return Model|null      the reference object
	 */
	public static function parse($str) {
		$matches = [];

		preg_match_all("/\{(\w+?)\|([^\[\]\|]+?)\}/i", $str, $matches);
		if (!empty($matches)) {
			$className = ucfirst($matches[1][0]);
			if ($className == "object") {
				$obj = new stdClass;
				$obj->id = $matches[2][0];
				$obj->title = $matches[2][0];
				return $obj;
			}
			if (class_exists($className) && is_subclass_of($className, "Model")) {
				return $className::simpleGet($matches[2][0]);
			}
		}
	}
}
?>