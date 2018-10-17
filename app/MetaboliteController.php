<?php
class MetaboliteController extends Controller {
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

	protected function view ($input, $accept) {
		if ($accept == JSON) {
			$id = $this->filter($input, "id", "is_numeric", ["metabolite id is required", 400, JSON]);
			$metabolite = Metabolite::get($id);
			if ($metabolite) {
				$this->respond($metabolite, 200, JSON);
			}  else {
				$this->error("Metabolite not found", 404, JSON);
			} 
		} else $this->error("Unaccepted", 400, $accept);
	}

	/** search function */
	protected function search ($input, $accept) {
		$keyword = $input["keyword"];
		$messages = [400 => "Keyword too short", 404 => "No results found"];
		$error = null;
		if (strlen($keyword) < 2) {
			$error = 400;
		} else {
			$mode = $this->filter($input, "mode", "has");
			switch($mode) {
				case "strict":
					$results = Metabolite::getAll("title like ? or synonym like ?", [$keyword, $keyword]);
				default:
					$results = Metabolite::getAll("title like ? or synonym like ?", ["%".$keyword."%", "%".$keyword."%"]);

			}
			if (!$results) {
				$error = 404;
			}
		}
		switch ($accept) {
			case HTML:
				$view = View::loadFile("layout1.tpl");
				$view->set([
					"pageTitle" => "Search: $keyword",
					"showFootNote" => "none"
				]);
				if ($error) {
					$view->set([
						"content" => $messages[$error],
						"title" => "Search: $keyword (0 result)"
					]);
				} else {
					$view->set([
						"title" => "Search: $keyword (".count($results)." results)",
						"content" => "{{metabolite.list.tpl}}",
						"metabolites" => $results,
						"vars" => [
							"max" => 0
						],
						"jsAfterContent" => ["all.list"]
					]);
				}
				$this->respond($view, 200, HTML);
				break;
			case HTML_PARTIAL:
				if ($error) {
					$this->respond("<p>No result</p>", 200, HTML);
				} else {
					$view = View::load("{{metaboliteTable:metabolites}}");
					$view->set("metabolites", $results);
					$this->respond($view, 200, HTML_PARTIAL);
				}
				break;
			case JSON:
				if ($error) {
					$this->error($messages[$error], $error, JSON);
				} else {
					$results = Utility::arrayColumns($results, ["id", "title", "synonym"]);
					Utility::decodeLinkForView($results);
					$this->respond($results, 200, JSON);
				}
				break;
		}
	}

	protected function index ($accept) {
		$this->list(["page" => 1, "page_size" => 150], $accept);
	}

	protected function list ($input, $accept) {
		$page = $this->filter($input, "page", "is_numeric", ["Invalid page number", 400, $accept]);
		$pageSize = $this->filter($input, "page_size", "is_numeric", ["Invalid page size", 400, $accept]);
		$metabolites = Metabolite::getAll("1 order by id limit ?,?", [$pageSize*($page-1), $pageSize]);
		switch ($accept) {
			case HTML:
				if ($metabolites) {
					$count = Metabolite::count();
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"title" => "All metabolites (page $page)",
						"content" => "{{metabolite.list.tpl}}",
						"metabolites" => $metabolites,
						"showFootNote" => "none",
						"jsAfterContent" => ["all.list"],
						"vars" => [
							"type" => "metabolite",
							"max" => ceil($count / $pageSize)
						],
					]);
					$this->respond($view, 200, HTML);
				} else $this->error("Not found", 404, HTML);
				break;
			case JSON:
				if ($metabolite) $this->respond(Utility::arrayColumns($metabolite, ["id", "title", "function"]), 200, JSON);
				else $this->error("Not found", 404, JSON);
				break;
		}
	}

	protected function query ($input, $accept) {
		if ($accept == HTML) {
			// exporter
		} elseif ($accept == JSON) {
			$ids = $this->filter($input, "ids", "has");
			$query = $this->filter($input, "query", "has", ["Invalid query", 400, JSON]);
			$queries = explode(";", $query);
			foreach ($queries as &$keypath) {
				$keypath = new KeyPath($keypath);
			}
			if ($ids) {
				$ids = explode(",", $ids);
				$metabolites = Metabolite::getAll("id in (".implode(",", array_pad([], count($ids), "?")).")", $ids);
			} else {
				$metabolites = Metabolite::getAll(1);
			}
			if ($metabolites) {
				$table = [];
				foreach ($metabolites as $metabolite) {
					$row = [$metabolite->id];
					foreach ($queries as $kp) {
						$row[] = $kp->get($metabolite);
					}
					$table[] = $row;
				}
				$this->respond($table, 200, JSON);
			} else {
				$this->error("not found", 404, JSON);
			}
			
		}
	}

	public function update ($input, $accept) {
		$id = $this->filter($input, "id", "is_numeric", ["Id is required", 400, $accept]);
		$title = $this->filter($input, "title", "has", ["Name is required", 400, $accept]);
		switch($accept) {
			default:
				$this->error("Unaccepted", 405, $accept);
				break;
			case JSON:
				$metabolite = Metabolite::withData($input);
				if ($metabolite->update()){
					$this->respond("okay", 200, JSON);
				} else {
					$this->respond("The metabolite with the name $title already exists", 500, JSON);
				}
		}
	}

	public function create ($input, $accept) {
		$id = $this->filter($input, "id", "is_numeric", ["Id is required", 400, $accept]);
		$title = $this->filter($input, "title", "has", ["Name is required", 400, $accept]);
		switch($accept) {
			default:
				$this->error("Unaccepted", 405, $accept);
				break;
			case JSON:
				$metabolite = Metabolite::withData($input);
				if ($metabolite->insert()){
					$this->respond("okay", 201, JSON);
				} else {
					$this->respond("The metabolite with the name $title already exists", 500, JSON);
				}
		}
	}

	public function delete ($input, $accept) {
		$id = $this->filter($input, "id", "is_numeric", ["Id is required", 400, $accept]);
		switch($accept) {
			default:
				$this->error("Unaccepted", 405, $accept);
				break;
			case JSON:
				$metabolite = Metabolite::withData($input);
				if ($metabolite->delete()){
					$this->respond("okay", 204, JSON);
				} else {
					$this->respond("This metabolite is still associated with a reaction. Deletion is restricted", 500, JSON);
				}
		}
	}

	public function editor ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		if ($accept == HTML && $method == "GET") {
			$metaboliteId = $this->filter($input, "id", "is_numeric");
			if ($metaboliteId) {
				$metabolite = Metabolite::get($metaboliteId);
				if(is_null($metabolite)) {
					$this->error("Page not found", 404, $accept);
				}
			}
			$view = View::loadFile("layout2.tpl");
			$view->set($metabolite);
			$view->set([
				"headerTitle" => "Edit metabolite",
				"pageTitle" => "Edit metabolite",
				"content" => "{{metabolite.editor.each.tpl}}",
				"showFootNote" => "none",
				"jsAfterContent" => ["all.list"],
				"vars" => [
					"type" => "metabolite",
					"max" => ceil($count / $pageSize)
				],
				"method" => is_null($metabolite) ? "post": "put",
				"jsAfterContent" => ["all.editor"]
			]);
			$this->respond($view, 200, HTML);
		}
	}
}
?>