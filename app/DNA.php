<?php
/**
 * abstraction
 */
class DNA extends \Kiwi\Model {
	public $subtype;
	public $title;
	public $id;
	public $type = "DNA";

	public static function get ($id) {
		$className = get_called_class();
		$instance = new $className;
		if (\Kiwi\Utility::startsWith($id, "gene")) {
			$gene = Gene::simpleGet(substr($id, 5));
			if ($gene === null) return null;
			$instance->id = $id;
			$instance->subtype = "gene";
			$instance->title = $gene->title;
			return $instance;
		} elseif (\Kiwi\Utility::startsWith($id, "operon")) {
			$operon = Operon::get(substr($id, 7));
			if ($operon === null) return null;
			$instance->id = $id;
			$instance->subtype = "operon";
			$title = preg_replace_callback("/\[\[gene\|([a-f0-9]{40})\]\]/i",function($match){
				$gene = Gene::simpleGet($match[1]);
				return $gene->title;
			}, $operon->genes);
			$instance->title = $title;
			return $instance;
		}
	}
	public static function simpleGet ($id) {
		return static::get($id);
	}
	public function toObjectMarkup() {
		return "{DNA|".$this->id."}";
	}
	public function __toString(){
		return "{DNA|".$this->id."}";
	}
}
?>