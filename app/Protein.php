<?php
class Protein extends Gene {
	static $tableName = "Gene";
	static $lookupTable = [];
	static $validateTable = [];

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
		],
		"reaction" => [
			"tableName" => "ReactionCatalyst",
			"mapping" => [
				"reaction" => "Reaction",
				"catalyst" => "mixed"
			],
			"position" => 2
		]
	];

	public static function withData ($data) {
		\Kiwi\Utility::clean($data);
		\Kiwi\Utility::toObject($data);
		$protein = new Protein();
		foreach ($data as $key => $value) {
			$protein->{$key} = $value;
		}
		if ($protein->title) {
			$protein->title = ucfirst($protein->title);
		}
		return $protein;
	}

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

	public function fetchDomains () {
		if ($this->id) {
			$sql = "select * from ProteinDomain where protein like ?";
			$result = \Kiwi\Application::$conn->doQuery($sql, [$this->id]);
			if ($result) {
				foreach ($result as &$row) {
					$row = (object) $row;
				}
				return $result;
			}
		}
	}

	public function patch () {
		$results = \Kiwi\Utility::deepSearch($this, "[[this]]");
		foreach ($results as $keypath) {
			$data = null;
			$keypath = new \Kiwi\KeyPath($keypath);
			if (\Kiwi\Utility::startsWith($keypath, "paralogous protein")) {
				$data = $this->fetchParalogues();
			} elseif ($keypath == "domains") {
				$data = $this->fetchDomains();
			}
			if ($data == null) {
				$keypath->unset($this);
			} else {
				$keypath->set($this, $data);
			}
		}
		\Kiwi\Utility::clean($this); 
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
			\Kiwi\Utility::decodeLinkForView($row);
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
			$conn = \Kiwi\Application::$conn;
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
			$conn = \Kiwi\Application::$conn;
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