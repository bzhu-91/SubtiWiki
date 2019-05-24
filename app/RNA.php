<?php
/**
 * Abstraction
 */
class RNA extends DNA {
	public $type = "RNA";
	public function toObjectMarkup() {
		return "{RNA|".$this->id."}";
	}
	public function __toString(){
		return "{RNA|".$this->id."}";
	}
}
?>