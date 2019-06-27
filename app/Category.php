<?php
/**
 * For functional categories in SubtiWiki
 */
class Category extends \Kiwi\Model {
	use \Kiwi\ReferenceCache;

	static $tableName = "Category";
	static $relationships = [
		"genes" => [
			"tableName" => "GeneCategory",
			"mapping" => [
				"gene" => "Gene",
				"category" => "Category"
			],
			"position" => 2
		]
	];

	/**
	 * get the parents, children, and siblings
	 */
	public function patch () {
		if ($this->id) {
			$this->fetchParentCategories();
			$this->fetchChildCategories();
			$this->fetchSiblingCategories();
		}
	}

	/**
	 * get the parent categories of a category
	 * set the property $this->_parent, $this->parents
	 */
	public function fetchParentCategories(){
		if (!property_exists($this, "parents")) {
			$this->parents = [];
			$allIds = [];
			$parentId = $this->getParentId($this->id);
			while($parentId != "SW"){
				$allIds[] = $parentId;
				$parentId = $this->getParentId($parentId);
			}
			if($allIds){
				foreach (array_reverse($allIds) as $id) {
					$this->parents[] = Category::get($id);
				}
				$this->_parent = $this->parents[count($this->parents) - 1];
			}
		}
	}

	/**
	 * calculate the id of the parent category
	 * @return string the id of the parent
	 */
	private function getParentId($id) {
		return rtrim(rtrim($id, "1234567890"), ".");
	}

	/**
	 * get the child categories of a category
	 * set the property $this->children
	 */
	public function fetchChildCategories() {
		if (!property_exists($this, "children")) {
			static::initLookupTable();

			$this->children = [];
			$regexp = "/^".$this->id."\.\d+$/i";
			foreach (static::$lookupTable as $key => $instance) {
				if (preg_match($regexp, $key)) {
					$this->children[] = $instance;
				}
			}
			usort($this->children, ["Category", "compare"]);
		}
		return $this->children;
	}

	/**
	 * get the sibling categories of a category
	 * set the property $this->siblings
	 */
	public function fetchSiblingCategories() {
		if (!property_exists($this, "siblings")) {
			static::initLookupTable();

			$parentId = rtrim(rtrim($this->id, "."), "09123456789");
			if ($parentId) {
				$regexp = "/^".$parentId."\d+$/i";
			} else {
				$regexp = "/^\d+$/i";
			}
			$this->siblings = [];
			foreach (static::$lookupTable as $key => $instance) {
				if (preg_match($regexp, $key) && $key !== $this->id) {
					$this->siblings[] = $instance;
				}
			}
			usort($this->siblings, ["Category", "compare"]);
		}
		return $this->siblings;
	}

	/**
	 * get the genes related to this category
	 * @return array relationship instances
	 * */
	public function getGenes () {
		$genes = $this->has("genes");
		if ($genes) {
			usort($genes, function($a,$b){
				return strcmp($a->gene->title, $b->gene->title);
			});
		}
		return $genes;
	}

	/**
	 * get the depth of the current category
	 * @return number depth of the current category
	 */
	public function getDepth() {
		if($this->id){
			return count(explode(".", $this->id)) - 1;
		}
	}

	/**
	 * get the count of all genes related to this category and sub-categories
	 * @return number/null count of all genes related to this category and all sub-categories
	 */
	public function countGenesAll() {
		if($this->id){
			$conn = \Kiwi\Application::$conn;
			$sql = "select count(gene) as count from ".static::$relationships["genes"]["tableName"]." where category like ?";
			$result = $conn->doQuery($sql, [$this->id."%"]);
			if ($result) {
				return $result[0]["count"];
			}
		}
	}

	/**
	 * compare function
	 * @param  Category $a category 1
	 * @param  Category $b category 2
	 * @return number -1/0/1, result of the comparison, -1 if category 1 < category 2, 0 if category 1 == category 2, 1 if category1 > category2
	 */
	public static function compare (Category $a, Category $b) {
		$a = $a; $b = $b;
		$arr = explode(".",$a->id);
		$brr = explode(".",$b->id);
		foreach ($arr as &$id){
			if(strlen($id) < 3){
				$id = str_pad($id, 3, "0", STR_PAD_LEFT);
			}
			
		}
		foreach ($brr as &$id){
			if(strlen($id) < 3){
				$id = str_pad($id, 3, "0", STR_PAD_LEFT);
			}
		}
		$A = join(".", $arr);
		$B= join(".", $brr);
		return strcasecmp($A, $B);
	}

	/**
	 * present the category (full path) in a linear way of links
	 * @return string linear presentation
	 */
	public function toLinearPresentation(){
		$this->fetchParentCategories();
		$links = [];
		foreach ($this->parents as $parent) {
			$links[] = $parent->toLinkMarkup(false);
		}
		$links[] = $this->toLinkMarkup(false);
		return implode(", ", $links);
	}

	/**
	 * @param boolean $includeID whether include id or not in the presentation
	 * @return string link markup for the category
	 */
	public function toLinkMarkup ($includeID = true) {
		if ($includeID) 
			return "[category|".$this->id."|".substr($this->id, 3).". ".$this->title."]";
		else 
			return "[category|".$this->id."|".$this->title."]";
	}

	/**
	 * add a child category to this category,
	 * @param Category $child the child category to be added
	 * @return boolean true if success, false if not
	 * @throws ConstraintViolatedException when title is duplicated
	 */
	public function addChildCategory (Category $child) {
		if ($this->id && $child->title) {
			if (Category::simpleValidate($child->title)) {
				throw new \Kiwi\ConstraintViolatedException("category with title ".$child->title." already exists", 1);
			} else {
				if ($this->has("genes")) {
					throw new \Kiwi\ConstraintViolatedException("Category ".$this->title." has genes assigned to it.", 1);
				} else {
					$this->fetchChildCategories();
					if ($this->children) {
						$child->id = $this->id.".".(count($this->children) + 1);
					} else {
						$child->id = $this->id.".1";
					}
					return $child->insert();
				}
			}
		}
		return false;
	}

	/**
	 * delete this category, calling the removeChildCategory of the parent
	 * @return boolean true if okay, false if failed
	 */
	public function delete () {
		if ($this->_parent) {
			return $this->_parent->removeChildCategory($this);
		}
		return false;
	}
	/**
	 * remove a child category, this will cause the category tree to change and ids
	 * @param  Category $child the child to be removed
	 * @return boolean true if deletion/renaming is successful, false when not
	 */
	public function removeChildCategory (Category $child) {
		if ($this->id && $child->id) {
			$this->fetchChildCategories();
			if ($this->children) {
				$oldIds = array_column($this->children, "id");
				$index = array_search($child->id, $oldIds);
				if ($index !== false) {
					// other children which is has id > $child->id need to be updated
					$changeList = array_slice($oldIds, $index+1);

					// use transactions to keep everything in order
					$conn = \Kiwi\Application::$conn;
					$conn->beginTransaction();
					History::record($child, "remove");
					// try remove child
					if ($conn->doQuery("delete from `".static::$tableName."` where id like ?", [$child->id])) {
						if ($changeList) {
							$sql = "update `".static::$tableName."` set id = CASE ";
							for ($i=0; $i < count($changeList); $i++) { 
								if($i==0) {
									$new_id = $child->id;
								} else{
									$new_id = $changeList[$i-1];
								}
								$sql .= "WHEN id = '".$changeList[$i]."' THEN '".$new_id."' ";
							}
							$sql .= "ELSE id END where id in (";
							foreach ($changeList as &$id) {
								$id = "'$id'";
							}
							$sql .= implode(",", $changeList).")";
							if ($conn->exec($sql)) {
								$conn->commit();
								return true;
							} else {
								$conn->rollback();
							}
						} else {
							$conn->commit();
							return true;
						}
					} else {
						$conn->rollback();
					}
				}
			}
		}
		return false;
	}

	/**
	 * update the category, include history functions and duplication check
	 * @return boolean true if success, false if not
	 * @throws ConstraintViolatedException when title conflicts
	 */
	public function update () {
		if ($this->id) {
			$old = Category::get($this->id);
			// check name changes
			// title has no unique key
			if ($old->title !== $this->title && Category::simpleValidate($this->title)) {
				throw new ConstraintViolatedException("Category with name ".$this->title." already exists.", 1);
			}
			$conn = \Kiwi\Application::$conn;
			$conn->beginTransaction();

			// update the duplicated categories
			if (property_exists($old, "equalTo")) {
				$dup = Category::get($old->equalTo);
				$dup->title = $this->title;
				foreach ($this as $key => $value) {
					if ($key[0] !== "_" && $key !== "id" && $key !== "title") {
						$dup->$key = $value;
					}
				}
				if (History::record($dup, "update") && History::record($this, "update") && $dup->simpleUpdate() && $this->simpleUpdate()) {
					$conn->commit();
					return true;
				} else {
					$conn->rollback();
				}
			} else {
				if ($this->simpleUpdate() && History::record($this, "update")) {
					$conn->commit();
					return true;
				} else {
					$conn->rollback();
					return false;
				}
			}
		}
		return false;
	}

	/** update without considering the duplication. */
	private function simpleUpdate() {
		return parent::replace(["id", "lastUpdate", "equalTo"]);
	}

	/** add gene without considering duplicated category. No transaction is used.
	 * @param Gene $gene the gene
	 * @param object/array $data extra data related to this relationship
	 * @return boolean
	*/
	private function simpleAddGene($gene, $data) {
		$r = $this->hasPrototype("genes");
		$r->category = $this;
		$r->gene = $gene;
		foreach ($data as $key => $value) {
			$r->$key = $value;
		}
		return $r->insert() && History::record($r, "add");
	}

	/**
	 * assign gene to the category.
	 * @param Gene $gene gene to be added
	 * @param array/object $data the extra data
	 * @return  boolean true if successful, false if failed
	 */
	public function addGene (Gene $gene, $data) {
		$conn = \Kiwi\Application::$conn;
		$conn->beginTransaction();

		$result = $this->simpleAddGene($gene, $data);

		if ($this->equalTo) {
			$duplication = Category::get($this->equalTo);
			$result = $result && $duplication->simpleAddGene($gene, $data);
		}
		if ($result) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}

	/** remove a gene without checking the duplicated category.
	 * @param Gene $gene the gene
	 * @return boolean the result of operation
	*/
	private function simpleRemoveGene (Gene $gene) {
		$genes = $this->getGenes();
		// find the relationship
		$rows = array_filter($genes, function ($row) use ($gene) {
			return $row->gene->id == $gene->id;
		});

		if ($rows) {
			$row = array_values($rows)[0];
			return History::record($row, "remove") && $row->delete();
		}
		return true;
	}

	/**
	 * remove gene, duplicated gene considered
	 * @param  Gene $gene gene to be removed
	 * @return boolean true if succesful, false if otherwise
	 */
	public function removeGene (Gene $gene) {
		$conn = \Kiwi\Application::$conn;
		$conn->beginTransaction();

		$result = $this->simpleRemoveGene($gene);

		// handle duplications
		if (property_exists($this, "equalTo")) {
			$duplication = Category::get($this->equalTo);
			$result = $result && $duplication->simpleRemoveGene($gene);
		}
		
		if ($result) {
			$conn->commit();
			return true;
		} else {
			$conn->rollback();
			return false;
		}
	}
}
?>