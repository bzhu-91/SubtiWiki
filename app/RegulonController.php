<?php
require_once("ViewAdapters.php");

class RegulonController extends Controller {
	public function read ($input, $accept) {
		if ($input) {
			if (array_key_exists("id", $input)) {
				$this->view($input, $accept);
			} else if (array_key_exists("page", $input)) {
				$this->list($input, $accept);
			}
		} else $this->index($accept);
	}

	protected function index ($accept) {
		$this->list(["page" => 1, "page_size" => 150], $accept);
	}

	protected function view ($input, $accept) {
		$id = $this->filter($input,"id", "/^(protein|riboswitch)\:.+$/i", ["Invalide regulon id", 400, $accept]);
		$regulon = Regulon::get($id);
		Utility::decodeLinkForView($regulon);
		if ($regulon) {
			$regulon->updateCount();
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"pageTitle" => $regulon->getTitle(),
						"title" => $regulon->getTitle(),
						"content" => "{{::rest}}",
						"floatButtons" => [
							["href" => "regulon/editor?id=$id", "icon" => "edit.svg"],
						],
						"navlinks" => [
							["href" => "regulon", "innerHTML" => "Regulon list"]
						],
						"genes in this regulon" => $regulon->getGenes()
					]);
					$view->set($regulon);
					$this->respond($view, 200, HTML);
					break;
				case JSON:
					$this->respond($regulon, 200, JSON);
					break;
			}
		} else $this->error("Not found", 404, $accept);
	}

	protected function list ($input, $accept) {
		$page = $this->filter($input, "page", "is_numeric", ["Invalid page number", 400, $accept]);
		$pageSize = $this->filter($input, "page_size", "is_numeric", ["Invalid page size", 400, $accept]);
		$all = Regulon::getAll();
		$count = count($all);
		$regulons = array_slice($all, ($page-1)*$pageSize, $pageSize);
		Statistics::increment("regulonIndex");
		switch ($accept) {
			case HTML:
			$view = View::loadFile("layout1.tpl");
			$view->set([
				"title" => "All regulons (page $page)",
				"content" => "{{all.list.tpl}}",
				
				"showFootNote" => "none",
				"jsAfterContent" => ["all.list"],
				"vars" => [
					"max" => ceil($count/$pageSize),
					"type" => "regulon",
					"currentInput" => $input
				]
			]);
			if ($regulons) {
				$view->set([
					"data" => $regulons,
				]);
			} else {
				$view->set([
					"messages" => ["No regulons"],
				]);
			}
			$this->respond($view, 200, HTML);
			break;
			case JSON:
			if ($regulons) $this->respond(Utility::arrayColumns($regulons, ["id", "title"]), 200, JSON);
			else $this->error("Not found", 404, JSON);
			break;
		}
	}

	public function update ($input, $accept) {
		UserController::authenticate(1, $accept);
		$id = $this->filter($input, "id", "/^(protein|riboswitch)\:[^\[\]\|]+$/i", ["Invalide id", 400, $accept]);
		$regulon = Regulon::withData($input);
		Utility::encodeLink($regulon);
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Unacceptable", 405, HTML);
				break;
			case JSON:
				$regulon->lastAuthor = User::getCurrent()->name;
				if ($regulon->update()) {
					$this->respond(["uri" => "regulon?id=".$regulon->id], 200, JSON);
				} else {
					$this->error("An unexpected error has happened, please contact admin", 500, JSON);
				}
			default:
				
				break;
		}
	}

	public function delete ($input, $accept) {
		$this->error("Fobidden", 403, $accept);
	}

	public function create ($input, $accept) {
		UserController::authenticate(1, $accept);
		$id = $this->filter($input, "id", "/^(protein|riboswitch)\:[^\[\]\|]+$/i", ["Invalide id", 400, $accept]);
		$regulon = Regulon::withData($input);
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Unacceptable", 405, HTML);
				break;
			case JSON:
				$regulon->lastAuthor = User::getCurrent()->name;
				if ($regulon->insert()) {
					$this->respond(["uri" => "regulon?id=".$regulon->processId()], 200, JSON);
				} else $this->error($regulon->getRegulator()->title." is not a regulator", 500, JSON);
		}
	}

	public function editor ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		if ($method == "GET") {
			$id = $this->filter($input,"id", "/^(protein|riboswitch)\:.+$/i", ["Invalide regulon id", 400, $accept]);
			$regulon = Regulon::get($id);
			if (!$regulon) {
				$this->error(" is not a regulator", 500, $accept);
			}
			switch ($accept) {
				case HTML:
					$view = View::loadFile("layout2.tpl");
					if ($regulon) {
						$view->set([
							"pageTitle" => "Edit: ".$regulon->getTitle(),
							"headerTitle" => "Edit: ".$regulon->getTitle(),
							"content" => "{{regulon.editor.tpl}}",
							"jsAfterContent" => ["libs/monkey"],
							"mode" => "redirect"
						]);
					}
					break;
				case HTML_PARTIAL:
					$view = View::loadFile("regulon.editor.tpl");
					$view->set("mode", "replace");
					break;
				default:
					$this->error("Unacceptable", 405, $accept);
					break;
			}
			$view->set([
				"method" => $regulon->isNew() ? "post" : "put",
			]);
			$view->set($regulon);
			$view->restPrintingStyle = "monkey";
			$this->respond($view, 200, HTML);
		} else $this->error("Unacceptable method", 406, $accept);
	}
}
?>