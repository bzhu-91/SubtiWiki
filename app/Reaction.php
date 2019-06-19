<?php
class Reaction extends \Monkey\Model {
    static $tableName = "Reaction";
    static $relationships = [
        "metabolite" => [
            "tableName" => "ReactionMetabolite",
            "mapping" => [
                "reaction" => "Reaction",
                "metabolite" => "mixed"
            ],
            "position" => 1
        ],
        "catalyst" => [
            "tableName" => "ReactionCatalyst",
            "mapping" => [
                "reaction" => "Reaction",
                "catalyst" => "mixed"
            ],
            "position" => 1
        ],
        "pathway" => [
            "tableName" => "ReactionPathway",
            "mapping" => [
                "reaction" => "Reaction",
                "pathway" => "Pathway"
            ],
            "position" => 1
        ]
    ];

    private $fromEquation = false;

    // call this->update
    public function updateEquation () {
        if($this->id) {
            $metabolites = self::has("metabolite", true);
            if($metabolites) {
                $left = $this->hasReactants();
                $right = $this->hasProducts();
                usort($left, function($a,$b){
                    return $a->id - $b->id;
                });
                usort($right, function($a,$b){
                    return $a->id - $b->id;
                });
                $equation = "";
                $lhs = [];
                foreach($left as $hasMetabolite) {
                    $name = "";
                    if ($hasMetabolite->coefficient > 1) {
                        $name = $hasMetabolite->coefficient." ".$hasMetabolite->metabolite->title;
                    } else {
                        $name = $hasMetabolite->metabolite->title;
                    }
                    if ($hasMetabolite->modification) {
                        $name .= "<sup>".$hasMetabolite->modification."</sup>";
                    }
                    $lhs[] = $name;
                }
                $rhs = [];
                foreach($right as $hasMetabolite) {
                    $name = "";
                    if ($hasMetabolite->coefficient > 1) {
                        $name = $hasMetabolite->coefficient." ".$hasMetabolite->metabolite->title;
                    } else {
                        $name = $hasMetabolite->metabolite->title;
                    }
                    if ($hasMetabolite->modification) {
                        $name .= "<sup>".$hasMetabolite->modification."</sup>";
                    }
                    $rhs[] = $name;
                }
                $equal = " ⟹ ";
                if ($this->reversible) $equal = " ⟺ ";
                $equation = implode(" + ", $lhs).$equal.implode(" + ", $rhs);

                $this->equation = $equation;
                return parent::update();
            } else {
                $this->equation = "";
                return parent::update();
            }
        }
    }

    public function hasReactants () {
        if ($this->id) {
            $metabolites = $this->has("metabolite");
            return array_values(array_filter($metabolites, function ($each) {
                return $each->side == "L";
            }));
        }
    }

    public function hasProducts () {
        if ($this->id) {
            $metabolites = $this->has("metabolite");
            return array_values(array_filter($metabolites, function ($each) {
                return $each->side == "R";
            }));
        }
    }

    public function update () {
        $conn = \Monkey\Application::$conn;
        $conn->beginTransaction();
        if (History::record($this, "update") && parent::update()) {
            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    }

    public function insert () {
        $conn = \Monkey\Application::$conn;
        $conn->beginTransaction();
        if (parent::insert() && History::record($this, "add")) {
            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    }

    public static function searchByCatalyst($keyword, $page, $pageSize) {
        $sql = "select Reaction.id, Reaction.equation from Reaction join ReactionCatalyst on Reaction.id = ReactionCatalyst.reaction join Gene on ReactionCatalyst.Catalyst like concat('{protein|', Gene.id, '}') where Gene.title like ? or Gene._synonyms like ?";
        $vals = ["%{$keyword}%", "%{$keyword}%"];
        if ($page && $pageSize) {
            $sql .= " limit ?,?";
            $vals[] = $pageSize*($page-1);
            $vals[] = $pageSize;
        }
        $reGene = \Monkey\Application::$conn->doQuery($sql, $vals);

        $sql = "select Reaction.id, Reaction.equation from Reaction join ReactionCatalyst on Reaction.id = ReactionCatalyst.reaction join Complex on ReactionCatalyst.catalyst like concat('{complex|', Complex.id, '}') join ComplexMember on ComplexMember.complex = Complex.id join Gene on ComplexMember.member like concat('{protein|', Gene.id, '}') where Gene.title like ? or Gene._synonyms like ?";
        $vals = ["%{$keyword}%", "%{$keyword}%"];
        if ($page && $pageSize) {
            $sql .= " limit ?,?";
            $vals[] = $pageSize*($page-1);
            $vals[] = $pageSize;
        }
        $reComplex = \Monkey\Application::$conn->doQuery($sql, $vals);
        $re = array_merge($reGene, $reComplex);
        foreach($re as &$reaction){
            $reaction = Reaction::withData($reaction);
        }
        return $re;
    }
    
    public function addMetabolite ($metabolite, $side, $coefficient = 1, $modification) {
        # check duplicate
        if($this->id) {
            $data = [
                "reaction" => $this,
                "metabolite" => $metabolite,
                "side" => $side,
                "coefficient" => $coefficient,
                "modification" => $modification
            ];
            $prototype = self::hasPrototype("metabolite");
            $hasMetabolite = $prototype->clone($data);
            $conn = \Monkey\Application::$conn;
            $conn->beginTransaction();
            if ($hasMetabolite->insert() && History::record($this, "update") && $this->updateEquation() ) {
                $conn->commit();
                return true;
            } else {
                $conn->rollback();
                return false;
            }
        }
    }

    public function updateMetabolite ($hasMetabolite, $coefficient, $modification) {
        if ($this->id) {
            $hasMetabolite = self::hasPrototype("metabolite")->getWithId($hasMetabolite);
            if ($hasMetabolite) {
                $hasMetabolite->coefficient = $coefficient;
                $hasMetabolite->modification = $modification;
                $conn = \Monkey\Application::$conn;
                $conn->beginTransaction();
                if($hasMetabolite->update() && History::record($this, "update") && $this->updateEquation() ) {
                    $conn->commit();
                    return true;
                } else {
                    $conn->rollback();
                    return false;
                }
            } else return false;
        }
    }

    public function removeMetabolite ($hasMetabolite) {
        if ($this->id) {
            $hasMetabolite = self::hasPrototype("metabolite")->getWithId($hasMetabolite);
            if ($hasMetabolite) {
                $conn = \Monkey\Application::$conn;
                $conn->beginTransaction();
                if($hasMetabolite->delete() && History::record($this, "update") && $this->updateEquation() ) {
                    $conn->commit();
                    return true;
                } else {
                    $conn->rollback();
                    return false;
                }
            } else return true;
        }
    }

    public function addCatalyst ($catalyst, $novel, $modification) {
        # check duplicate
        if($this->id) {
            $data = [
                "reaction" => $this,
                "catalyst" => $catalyst,
                "novel" => $novel,
                "modification" => $modification
            ];
            $prototype = self::hasPrototype("catalyst");
            $hasCatalyst = $prototype->clone($data);
            $conn = \Monkey\Application::$conn;
            $conn->beginTransaction();
            if ($hasCatalyst->insert() && History::record($hasCatalyst, "add")) {
                $conn->commit();
                return true;
            } else {
                $conn->rollback();
                return false;
            }
        }
    }

    public function removeCatalyst ($hasCatalyst) {
        if ($this->id) {
            $hasCatalyst = self::hasPrototype("catalyst")->getWithId($hasCatalyst);
            if ($hasCatalyst) {
                $conn = \Monkey\Application::$conn;
                $conn->beginTransaction();
                if(History::record($hasCatalyst, "remove") && $hasCatalyst->delete()) {
                    $conn->commit();
                    return true;
                } else {
                    $conn->rollback();
                    return false;
                }
            } else return true;
        }
    }

    public static function fromEquation ($equation) {
        // not sure should include this function or not.
        // it might be difficult for user to input the reaction in the proper format.
        
        /*
        $reaction = new Reaction;
        $reaction->equation = $equation;
        $reaction->insert();

        $sides = explode(" = ", $reaction->equation);
		$left = explode(" + ", $sides[0]);
		$right = explode(" + ", $sides[1]);
		foreach($left as $title){
			$title = trim($title);
			$matches = [];
			$coefficient = 1;
			if(preg_match("/^(\d+) (.+)$/i", $title, $matches)) {
				$coefficient = $matches[1];
				$title = $matches[2];
			}
			// try to find the metaboltie
			$metabolite = \Metabolite::getAll(["title" => $title]);
			if($metabolite){
				$metabolite = $metabolite[0];
			} else {
				$metabolite = new \Metabolite();
				$metabolite->title = $title;
				$metabolite->insert();
			}
			$data = [
				"reaction" => $reaction,
				"coefficient" => $coefficient,
				"metabolite" => $metabolite,
				"side" => "L"
			];
			$hasMetabolite = \Reaction::hasPrototype("metabolite");
			$hasMetabolite = $hasMetabolite->clone($data);
			\Log::debug($hasMetabolite);
			$re = $hasMetabolite->insert();
		}
		foreach($right as $title){
			$title = trim($title);
			$matches = [];
			$coefficient = 1;
			if(preg_match("/^(\d+) (.+)$/i", $title, $matches)) {
				$coefficient = $matches[1];
				$title = $matches[2];
			}
			// try to find the metaboltie
			$metabolite = \Metabolite::getAll(["title" => $title]);
			if($metabolite){
				$metabolite = $metabolite[0];
			} else {
				$metabolite = new \Metabolite();
				$metabolite->title = $title;
				$metabolite->insert();
			}
			$data = [
				"reaction" => $reaction,
				"coefficient" => $coefficient,
				"metabolite" => $metabolite,
				"side" => "R"
			];
			$hasMetabolite = \Reaction::hasPrototype("metabolite");
			$hasMetabolite = $hasMetabolite->clone($data);
			$hasMetabolite->insert();
		}
        */
    }
}  
?>