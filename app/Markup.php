<?php
namespace Monkey;
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
			try {
				if (class_exists($className) && is_subclass_of($className, "\Monkey\Model")) {
					return $className::simpleGet($matches[2][0]);
				}
			} catch (Exception $e) {
				$object = new Object();
				$object->title = $matches[2][0];
				$object->id = $matches[2][0];
				$object->type = $matches[1][0];
				return $object;
			}
		}
	}
}
?>