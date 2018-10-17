<?php
class Protein extends Model {
	use Markup;

	static $tableName = "Gene";

	static $relationships = [
		"paralogues" => [
			"tableName" => "ParalogousProtein",
			"mapping" => [
				"prot1" => "Protein",
				"prot2" => "Protein"
			],
			"ordered" => false,
			"position" => 1
		],
		"interaction" => [
			"tableName" => "Interaction",
			"mapping" => [
				"prot1" => "Protein",
				"prot2" => "Protein"
			],
			"ordered" => false,
		],
		"regulation" => [
			"tableName" => "Regulation",
			"mapping" => [
				"regulator" => "mixed",
				"regulated" => "mixed"
			],
			"position" => 1
		]
	];

	public function fetchParalogues () {
		if ($this->id) {
			$relationships = $this->has("paralogues");
			if ($relationships) {
				$list1 = [];
				$list2 = [];
				foreach ($relationships as $row) {
					$prot = $row->prot1->id === $this->id ? $row->prot2 : $row->prot1;
					if (property_exists($row, "description")) {
						$list2[] = $prot->toLinkMarkup().", ".$row->description;
					} else {
						$list1[] = $prot->toLinkMarkup();
					}
				}
				array_unshift($list2, implode(", ", $list1));
				return $list2;
			}
		}
	}

	public function patch () {
		$results = Utility::deepSearch($this, "[[this]]");
		foreach ($results as $keypath) {
			$data = null;
			if (Utility::startsWith($keypath, "Paralogous protein")) {
				$data = $this->fetchParalogues();
			}
			if ($data == null) {
				Utility::unsetValueFromKeyPath($this, $keypath);
			} else {
				Utility::setValueFromKeyPath($this, $keypath, $data);
			}
		}
		Utility::clean($this); 
	}

	public static function getRefWithId($id) {
		$gene = Gene::getRefWithId($id);
		if ($gene) {
			$gene->title = ucfirst($gene->title);
			return Protein::withData($gene);
		}
	}

	public static function getRefWithTitle($id) {
		$gene = Gene::getRefWithId($id);
		if ($gene) {
			$gene->title = ucfirst($gene->title);
			return Protein::withData($gene);
		}
	}

	public function getStructures () {
		$s = $this->Structure;
		$matches = array();
		preg_match_all("/\[PDB\|(.+?)\]/i", $s[0], $matches);
		return $matches[1];
	}

	public function getInteractions () {
		$data = $this->has("interaction");
		foreach ($data as &$row) {
			$row->from = $row->prot1;
			$row->to = $row->prot2;
			unset($row->prot1);
			unset($row->prot2);
			Utility::decodeLinkForView($row);
		}
		return new Graph($data, false);
	}

	public function addParalogue (Protein $prot, $data = null) {
		$paralogue = self::hasPrototype("paralogues");
		if ($this->id && $prot) {
			$paralogue->prot1 = $this;
			$paralogue->prot2 = $prot;
			if ($data) {
				foreach($data as $key => $value) {
					$paralogue->{$key} = $value;
				}
			}
			$paralogue->lastAuthor = User::getCurrent()->name;
			$conn = Application::$conn;
			$conn->beginTransaction();
			if ($paralogue->insert() && History::record($paralogue, "add")){
				$conn->commit();
				return $paralogue;
			} else {
				$conn->rollback();
				return false;
			}
		}
	}

	public function updateParalogue (Protein $prot, $data = null) {
		$paralogue = self::hasPrototype("paralogues");
		if ($this->id && $prot) {
			$paralogue->prot1 = $this;
			$paralogue->prot2 = $prot;
			if ($data) {
				foreach($data as $key => $value) {
					$paralogue->{$key} = $value;
				}
			}
			$paralogue->lastAuthor = User::getCurrent()->name;
			$conn = Application::$conn;
			$conn->beginTransaction();
			if (History::record($paralogue, "update") && $paralogue->replace(["id", "prot1", "prot2"])){
				$conn->commit();
				return $paralogue;
			} else {
				$conn->rollback();
				return false;
			}
		}
	}
}
?>