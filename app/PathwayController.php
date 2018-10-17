<?php
require_once ("ViewAdapters.php");

class PathwayController extends Controller {
	public function read ($input, $accept) {
		$id = $this->filter($input, "id", "is_numeric");
		$proteinId = $this->filter($input, "protein", "/^[a-f0-9]{40}$/");
		if ($accept == HTML) {
			if (is_null($id)) {
				$id = 1;
			}
			$pathway = Pathway::get($id);
			$view = View::loadFile("layout2.tpl");
			$view->set($pathway);
			$view->set([
				"pageTitle" => "Pathway browser",
				"headerTitle" => "Pathway browser",
				"content" => "{{pathway.read.tpl}}",
				"jsAfterContent" => ["libs/view","libs/pathway","pathway.read"],
				"styles" => ["browser", "pathway"],
				"vars" => [
					"pathwayId" => $id
				],
			]);
			$this->respond($view, 200, HTML);
		} elseif ($accept == JSON) {
			if ($id) {
				$pathway = Pathway::get($id);
				// get all the reactions
				$all = Reaction::getAll(["pathway" => $id]);
				$pathway->reactions = $all;
				$this->respond($pathway, 200, JSON);
			} elseif ($proteinId) {
				// find all reactions
				// get all pathways in reactions
				$protein = Protein::get($proteinId);
				if ($protein) {
					$reactions = $protein->has("reaction");
					$allPathways = [];
					foreach($reactions as $reaction) {
						$pathways = array_column($reaction->has("pathway"), "pathway");
						foreach($pathways as $pathway) {
							$allPathways[$pathway->id] = $pathway;
						}
					}
					if ($allPathways) {
						$this->respond(array_values($allPathways), 200, JSON);
					} else $this->error("Pathway not found", 404, JSON);
				} else {
					$this->error("Gene not found", 404, JSON);
				}
			} else {
				$allPathways = Pathway::getAll(1);
				$this->respond($allPathways, 200, JSON);
			}
		}
	}

	public function editor ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		if ($accept == HTML && $method == "GET") {
			$id = $this->filter($input, "id", "is_numeric");
			// by default $id is one
			if (is_null($id)){
				$id = 1;
			}
			$pathway = Pathway::get($id);
			$view = View::loadFile("layout4.tpl");
			$view->set($pathway);
			$view->set([
				"headerTitle" => "Edit pathway",
				"content" => "{{pathway.editor.tpl}}{{jsvars:vars}}",
				"vars" => [
					"pathwayId" => $id,
				],
				"jsAfterContent" => ["libs/view", "libs/pathway","all.editor", "pathway.editor"],
				"styles" => ["pathway","pathway.editor"]
			]);
			$this->respond($view, 200, HTML);
		}
	}

	public function update ($input, $accept) {
		if ($accept == JSON) {
			$id = $this->filter($input, "id", "is_numeric", ["pathway id is required", 400, JSON]);
			$pathway = Pathway::get($id);
			if ($pathway) {
				if (array_key_exists("title", $input)) {
					$pathway->title = $input["title"];
				}
				if (array_key_exists("map", $input)) {
					$pathway->map = $input["map"];
				}
				if ($pathway->update()) {
					$this->respond(null, 200, JSON);
				} else {
					$this->error("Pathway with the same title already exists", 500, JSON);
				}
			}
		} else {
			$this->error("Unaccepted", 405, JSON);
		}
	}

	public function delete ($input, $accept) {}

	public function create ($input, $accept) {
		if ($accept == JSON) {
			$title = $this->filter($input, "title", "has", ["pathway name is required", 400, JSON]);
			$pathway = new Pathway;
			$pathway->title  = $title;
			if ($pathway->insert()) {
				$this->respond(null, 201, JSON);
			} else {
				$this->respond("This pathway already exists", 500, JSON);
			}
		} else {
			$this->error("Unaccepted", 400, $accept);
		}
	}

	// TODO: pathway importer ( from kegg)
}
?>