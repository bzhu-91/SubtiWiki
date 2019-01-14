<?php
/**
 * abstraction
 */
class DNA extends Model {
	public $subtype;
	public $title;
	public $id;
	public $type = "DNA";

	public static function get ($id) {
		$instance = new DNA;
		if (Utility::startsWith($id, "gene")) {
			$gene = Gene::simpleGet(substr($id, 5));
			Log::debug($gene);
			if ($gene === null) return null;
			$instance->id = $id;
			$instance->subtype = "gene";
			$instance->title = $gene->title;
			return $instance;
		} elseif (Utility::startsWith($id, "operon")) {
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
}
?>