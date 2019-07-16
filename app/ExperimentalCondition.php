<?php
/**
 * For the experiemental conditions for Omics data.
 */
class ExperimentalCondition extends \Kiwi\Model{
	static $tableName = "DataSet";
	static $types = [
		"transcript level (fold change)",
		"transcript level (expression index)",
		"transcript level (raw intensity)",
		"protein level (copies per cell)",
		"protein level (existence)",
		"tilling array"
	];

	/**
	 * get the category of the condition.
	 * @return string either "position-based" or "gene-based"
	 */
	public function getCategory () {
		if ($this->type) {
			if ($this->type == "tilling array") {
				return "position-based";
			} else {
				return "gene-based";
			}
		}
	}
}
?>