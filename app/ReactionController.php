<?php
require_once("ViewAdapters.php");

class ReactionController extends Controller {
	
    public function read ($input, $accept) {
        if ($input) {
			if (array_key_exists("id", $input)) {
				$this->view($input, $accept);
			} elseif (array_key_exists("keyword", $input)) {
				$this->search($input, $accept);
			} elseif (array_key_exists("query", $input)) {
				$this->query($input, $accept);
			} elseif (array_key_exists("page", $input)) {
				$this->list($input, $accept);
			}
		} else $this->index($accept);
	}
	
	protected function index ($accept) {
		$this->list([
			"page" => 1,
			"page_size" => 50,
		], $accept);
	}

	protected function view ($input, $accept) {
		if ($accept == JSON) {
			$id = $this->filter($input, "id", "is_numeric", ["reaction id is required", 400, $accept]);
			$reaction = Reaction::get($id);
			if ($reaction) {
				$reaction->reactants = array_column($reaction->hasReactants(), "metabolite");
				$reaction->products = array_column($reaction->hasProducts(), "metabolite");
				$reaction->catalysts = array_column($reaction->has("catalyst"), "catalyst");
				foreach($reaction->catalysts as &$catalyst) {
					if (get_class($catalyst) == "Complex") {
						$catalyst->members = array_column($catalyst->has("member"), "member");
						foreach($catalyst->members as &$member) {
							$member->type = lcfirst(get_class($member));
						}
						$catalyst->type="complex";
					} else {
						$catalyst->type="protein";
					}
				}
				$this->respond($reaction, 200, JSON);
			} else $this->error("Reaction not found", 404, JSON);
		} else $this->error("Unaccepted", 400, $accept);
	}

	protected function list ($input, $accept) {
		$page = $this->filter($input, "page", "is_numeric", ["Invalid page number", 400, $accept]);
		$pageSize = $this->filter($input, "page_size", "/(\d+)|(max)/i", ["Invalid page size", 400, $accept]);
		if ($pageSize == "max") {
			$input["page_size"] = $pageSize = Reaction::count();
		}
		$reactions = Reaction::getAll("1 order by id limit ?,?", [$pageSize*($page-1), $pageSize]);
		switch ($accept) {
			case HTML:
				if ($reactions) {
					$count = Reaction::count();
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"title" => "All reactions (page $page)",
						"pageTitle" => "All reactions (page $page)",
						"content" => "{{reaction.list.tpl}}",
						"data" => $reactions,
						"showFootNote" => "none",
						"jsAfterContent" => ["all.list"],
						"vars" => [
							"type" => "reaction",
							"max" => ceil($count / $pageSize),
							"currentInput" => $input
						],
					]);
					$this->respond($view, 200, HTML);
				} else $this->error("Not found", 404, HTML);
				break;
			case JSON:
				if ($reactions) $this->respond(Utility::arrayColumns($reactions, ["id", "equation"]), 200, JSON);
				else $this->error("Not found", 404, JSON);
				break;
		}
	}

	public function update ($input, $accept) {
		if ($accept == JSON) {
			UserController::authenticate(1, $accept);
			$id = $this->filter($input, "id", "/^\d+$/i", ["Id is required", 400, JSON]);
			$reversible = $this->filter($input, "reversible", "/^(on)|(off)$/i", ["Reversible is required", 400, JSON]);
			$novel = $this->filter($input, "novel", "/^(on)|(off)$/i", ["Novel is required", 400, JSON]);

			$input["reversible"] = $input["reversible"] == "on" ? 1 : 0;
			$input["novel"] = $input["novel"] == "on" ? 1 : 0;
			$reaction = Reaction::withData($input);
			if ($reaction->update()) {
				$this->respond(["uri" => "reaction/editor?id=".$reaction->id], 200, JSON);
			} else {
				$this->error("An internal error has happened, please contact admin.", 500, JSON);
			}
		} else $this->error("Unaccepted", 406, $accept);
	}

	public function create ($input, $accept) {
		if ($accept == JSON) {
			UserController::authenticate(1, $accept);
			$reversible = $this->filter($input, "reversible", "/^(on)|(off)$/i", ["Reversible is required", 400, JSON]);
			$novel = $this->filter($input, "novel", "/^(on)|(off)$/i", ["Novel is required", 400, JSON]);

			$input["reversible"] = $input["reversible"] == "on" ? 1 : 0;
			$input["novel"] = $input["novel"] == "on" ? 1 : 0;
			$reaction = Reaction::withData($input);
			if ($reaction->insert()) {
				$this->respond(["uri" => "reaction/editor?id=".$reaction->id], 200, JSON);
			} else {
				$this->error("An internal error has happened, please contact admin.", 500, JSON);
			}
		} else $this->error("Unaccepted", 406, $accept);
	}
	
	public function delete ($input, $accept) {
		if ($accept == JSON) {
			UserController::authenticate(1, $accept);
			$id = $this->filter($input, "id", "/^\d+$/", ["Id is required", 400, JSON]);
			$reaction = Reaction::get($id);
			if ($reaction === null || $reaction->delete()) {
				$this->respond(null, 204, JSON);
			} else {
				$this->error("Delete is not successful", 500, JSON);
			}
		}
	}

	public function editor ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		$id = $this->filter($input, "id", "is_numeric");
		$pathway = $this->filter($input, "pathway", "is_numeric");
		if($id) {
			$reaction = Reaction::get($id);
			if(is_null($reaction)) $this->error("Reaction not found", 404, $accept);
		}
		if($accept == HTML && $method == "GET") {
			if ($reaction) {
				$hasReactancts = $reaction->hasReactants();
				$hasProducts = $reaction->hasProducts();
				$hasCatalyst = $reaction->has("catalyst"); 
			}
			$view = View::loadFile("layout2.tpl");
			$view->set($reaction);
			$view->set([
				"pageTitle" => "Edit reaction:",
				"headerTitle" => "Edit reaction",
				"content" => "{{reaction.editor.tpl}}",
				"method" => $reaction ? "put": "post",
				"message" => $reaction ? "" : "To add products/reactants, please submit this form first",
				"reactantsEditor" => $reaction ? "{{reaction.reactants.tpl}}" : "",	
				"productsEditor" => $reaction ? "{{reaction.products.tpl}}" : "",
				"catalystsEditor" => $reaction ? "{{reaction.catalysts.tpl}}" : "",
				"reactants" => $hasReactancts,
				"products" => $hasProducts,
				"catalysts" => $hasCatalyst,
				"jsAfterContent" => ["all.editor"],
				"styles" => ["all.editor"],
				"checkReversible" => ($reaction && $reaction->reversible) ? "checked" : "",
				"checkNovel" => ($reaction && $reaction->novel) ? "checked" : ""
				
			]);
			if (!$reaction) {
				$view->set([
					"equation" => "<i>This is automatically generated</i>",
					"vars" => [
						"pathway" => $pathway
					]
				]);
			} else {
				$view->set([
					"vars" => $reaction->getData()
				]);
			}
			$this->respond($view,200,HTML);
		} else $this->error("Unaccepted", 405, $accept);
	}
	
	public function metabolite ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		if($accept != JSON) {
			$this->error("Unaccpeted", 405, $accept);
		}
		$reaction = $this->filter($input, "reaction", "is_numeric", ["Reaction id is required", 400, JSON]);
		$reaction = Reaction::get($reaction);
		if(is_null($reaction)) $this->error("Reaction not found", 404, JSON);
		switch($method) {
			case 'POST':
				$metaboliteTitle = $this->filter($input, "metabolite", "has", ["Metabolite is required", 400, JSON]);
				$side = $this->filter($input, "side", "/^L|R$/i", ["Metabolite type (product or reactanct) is required", 400, JSON]);
				$metabolite = Metabolite::getAll(["title" => $metaboliteTitle]);
				if ($metabolite) {
					$metabolite = $metabolite[0];
				} else {
					$metabolite = new Metabolite;
					$metabolite->title = $metaboliteTitle;
					$metabolite->insert();
					if (!$metabolite->id) {
						$this->error("An internal error has happened, please contact admin.", 500, JSON);
					}
				}
				if ($reaction->addMetabolite($metabolite, $side, $input["coefficient"] ? $input["coefficient"]: 1)){
					$this->respond(["uri" => "reaction/editor?id=$reaction->id"], 201, JSON);
				} else {
					$this->error("An internal error has happened, please contact admin", 500, JSON);
				}
				break;
			case 'PUT':
				$metabolite = $this->filter($input, "metabolite", "is_numeric", ["Metabolite is required", 400, JSON]);
				$coefficient = $this->filter($input, "coefficient", "is_numeric", ["Coefficient is required", 400, JSON]);
				$reaction = Reaction::get($reaction);
				$metabolite = Metabolite::get($metabolite);
				if ($metabolite) {
					if ($reaction->updateMetabolite($metabolite, $coefficient)) {
						$this->respond(["uri" => "reaction/editor?id=$reaction->id"], 200, JSON);
					} else $this->error("Internal error", 500, JSON);
				} else $this->error("Metabolite not found", 404, JSON);
				break;
			case 'DELETE':
				// delete metabolite here
				$metabolite = $this->filter($input, "metabolite", "is_numeric", ["Metabolite is required", 400, JSON]);
				$metabolite = Metabolite::get($metabolite);
				if ($metabolite) {
					if ($reaction->removeMetabolite($metabolite, $coefficient)) {
						$this->respond(["uri" => "reaction/editor?id=$reaction->id"], 200, JSON);
					} else $this->error("Internal error", 500, JSON);
				} else $this->error("Metabolite not found", 404, JSON);
				break;
		}
	}

	public function catalyst ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		if($accept != JSON) {
			$this->error("Unaccpeted", 405, $accept);
		}
		$reaction = $this->filter($input, "reaction", "is_numeric", ["Reaction id is required", 400, JSON]);
		$reaction = Reaction::get($reaction);
		if(is_null($reaction)) $this->error("Reaction not found", 404, JSON);
		switch($method) {
			case 'POST':
				// add metabolite here
				$type = $this->filter($input, "type", "/(protein)|(complex)/i", ["Type of the catalyst is required", 400, JSON]);
				$title = $this->filter($input, "title", "has", ["Name of the catalyst is required", 400, JSON]);
				switch($type) {
					case "protein":
						$protein = Protein::getAll(["title" => $title]);
						if ($protein) {
							$protein = $protein[0];
							if ($reaction->addCatalyst($protein)){
								$this->respond(["uri" => "reaction/editor?id={$reaction->id}"], 201, JSON);
							} else {
								$this->error("An internal error has happened, please contact admin", 500, JSON);
							}
						}  else $this->error("Protein $title is not found", 404, JSON);
						break;
					case "complex":
						$complex = Complex::getAll(["title" => $title]);
						if ($complex){
							$complex = $complex[0];
						} else {
							$complex = new Complex;
							$complex->title = $title;
							$complex->insert();
							if (is_null($complex->id)){
								$this->error("An internal error has happened, please contact admin", 500, JSON);
							}
						}
						if ($reaction->addCatalyst($complex)){
							$this->respond(["uri" => "reaction/editor?id={$reaction->id}"], 201, JSON);
						} else {
							$this->error("An internal error has happened, please contact admin", 500, JSON);
						}
						break;
				}
				break;
			case 'DELETE':
				// delete metabolite here
				$catalyst = $this->filter($input, "catalyst", "/\{(protein)|(complex)\|[^\{\}\|]+?\}/i", ["Catalyst is required", 400, JSON]);
				$catalyst = Model::parse($catalyst);
				if ($catalyst){
					if ($reaction->removeCatalyst($catalyst)) {
						$this->respond(["uri" => "reaction/editor?id={$reaction->id}"], 200, JSON);
					} else {
						$this->error("An internal error has happened, please contact admin", 500, JSON);
					}
				} else {
					$this->error("The given catalyst is not found", 404, JSON);
				}
				break;
		}
	}
}
?>