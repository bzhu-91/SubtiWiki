<?php 
require_once("ViewAdapters.php");

class InteractionController extends \Monkey\Controller {
	public function read ($input, $accept) {
		$geneId = $this->filter($input, "gene", "/^[a-f0-9]{40}$/i");
		$radius = $this->filter($input, "radius", "is_numeric");
		if ($radius === null) {
			$radius = 5;
		}
		$wholeGraph = Interaction::getWholeGraph($sigA);
		$subgraph = $wholeGraph->subgraph($geneId, $radius);
		if ($subgraph) {
			$data = $subgraph->toJSON();
			foreach ($data["nodes"] as $key => &$gene) {
				$gene = Gene::simpleGet($gene->id);
			}
			$data["nodes"] = \Monkey\Utility::arrayColumns($data["nodes"], ["id", "title"]);
			\Monkey\Utility::decodeLinkForView($data["edges"]);
		}
		switch ($accept) {
			case HTML:
				$view = \Monkey\View::loadFile("layout2.tpl");
				$view->set([
					"pageTitle" => "Interaction Browser",
					"headerTitle" => "Interaction Browser",
					"content" => "{{interaction.read.tpl}}",
					"jsAfterContent" => ["libs/vis-network.min", "libs/jscolor","interaction.read"],
					"styles" => ["browser", "vis-network.min"],
				]);
				if ($data) {
					$view->set([
						"message" => "loading",
						"vars" => [
							"rawData" => $data,
							"datasetDisplayMode" => $GLOBALS["DATASET_DISPLAY_MODE"],
							"conditions" => Expression::getConditions()
						]
					]);
				} else {
					$view->set([
						"message" => "Data not found"
					]);
				}
				$this->respond($view, 200, HTML);
				break;
			case JSON:
				if ($data) $this->respond($data, 200, JSON);
				else $this->error("Data not found", 404, JSON);
				break;
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
				break;
		}
	}

	public function list ($input, $accept) {
		$page = $this->filter($input, "page", "is_numeric");
		if ($page === null) $input["page"] = $page = 1;
		$pageSize = $this->filter($input, "page_size", "is_numeric");
		if ($pageSize === null) $input["page_size"] = $pageSize = 50;
		$proto = Protein::hasPrototype("interaction");
		$all = $proto->getAll("1 order by id limit ?,?", [$pageSize*($page-1), $pageSize]);
		switch ($accept) {
			case HTML:
			$count = $proto->count();
			$view = \Monkey\View::loadFile("layout1.tpl");
			$view->set([
				"title" => "All interactions (page $page)",
				"content" => "{{interaction.list.tpl}}",
				"showFootNote" => "none",
				"vars" => [
					"currentInput" => $input,
					"type" => "interaction",
					"max" => ceil($count / $pageSize)
				],
				"jsAfterContent" => ["all.list"],
			]);
			if ($all) {
				$view->set([
					"data" => $all,
					
				]);
			} else {
				$view->set([
					"messages" => ["No regulation"]
				]);
			}
			$this->respond($view, 200, HTML);
			break;
			case JSON:
				foreach($all as &$interaction) {
					$interaction->prot1 = \Monkey\Utility::arrayColumns($interaction->prot1, ["id", "title", "locus"]);
					$interaction->prot2 = \Monkey\Utility::arrayColumns($interaction->prot2, ["id", "title", "locus"]);
				}
				if ($all) $this->respond($all, 200, JSON);
				else $this->error("Not found", 404, JSON);
				break;
		}
	}

	public function create ($input, $accept) {
		UserController::authenticate(1, $accept);
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
				break;
			case JSON:
				$prot1 = $this->filter($input, "prot1", "/^[a-f0-9]{40}$/i", ["Invalid prot1", 400, JSON]);
				$prot2 = $this->filter($input, "prot2", "/^[0-9a-f]{40}$/i", ["Invalid prot2", 400, JSON]);

				$prot1 = Protein::simpleGet($prot1);
				$prot2 = Protein::simpleGet($prot2);

				if ($prot1 === null) {
					$this->error("prot1 can not be parsed", 404, JSON);
				}
				if ($prot2 === null) {
					$this->error("prot2 can not be parsed", 404, JSON);
				}

				
				$input["prot1"] = $prot1;
				$input["prot2"] = $prot2;
				
				$interaction = Interaction::withData($input);
				
				if ($interaction->insert()) {
					$this->respond(["uri" => "interaction/editor?id=".$interaction->id], 201, JSON);
				} else {
					$this->error("This interaction already exists.", 400, JSON);
				}
				break;
		}
	}

	public function delete ($input, $accept) {
		UserController::authenticate(2, $accept);
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
				break;
			case JSON:
				$id = $this->filter($input, "id", "is_numeric", ["Invalid id", 400, JSON]);
				$interaction = new Interaction();
				$interaction->id = $id;
				if ($interaction->delete()) {
					$this->respond(null, 204, JSON);
				} else {
					$this->error("An unexpected error has happened.", 500, JSON);
				}
		}
	}

	public function update ($input, $accept) {
		UserController::authenticate(1, $accept);
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
				break;
			case JSON:
				$id = $this->filter($input, "id", "is_numeric", ["Invalid id", 400, JSON]);
				$interaction = Interaction::withData($input);
				\Monkey\Utility::encodeLink($interaction);
				if ($interaction->update()) {
					$this->respond(null, 200, JSON);
				} else {
					$this->error("Operon with the same genes already exists", 500, JSON);
				}
		}
	}

	public function editor ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		if ($method != "GET") {
			$this->error("Unaccepted method", 405, $accept);
		}
		if ($accept == JSON) {
			$this->error("Unaccepted", 406, JSON);
		} else {
			$interactions = [];
			$id = $this->filter($input, "id", "is_numeric");
			$proteinId = $this->filter($input, "protein", "/^[a-f0-9]{40}$/i");
			if ($id) {
				$interaction = (new Interaction())->getWithId($id);
				if ($interaction) {
					$interactions[] = $interaction;
				}
			} elseif ($proteinId) {
				$protein = Protein::simpleGet($proteinId);
				$interactions = $protein->has("interaction");
			} else {
				// if no argument is given, simply give the blank page
				if ($accept == HTML_PARTIAL) {
					$view = \Monkey\View::loadFile("interaction.editor.blank.tpl");
					$view->set("updateMode", "replace");
					$this->respond($view, 200, HTML_PARTIAL);
				} else {
					$view = \Monkey\View::loadFile("layout2.tpl");
					$view->set([
						"pageTitle" => "Add interaction:",
						"headerTitle" => "Add interaction:",
						"updateMode" => "redirect",
						"content" => "{{interaction.editor.blank.tpl}}",
						"jsAfterContent" => ["all.editor", "interaction.editor","libs/monkey"]
					]);
					$this->respond($view, 200, HTML);
				}
			}
			if ($interactions) {
				$content = "";
				foreach ($interactions as $interaction) {
					\Monkey\Utility::decodeLinkForEdit($interaction);
					$segment = \Monkey\View::loadFile("interaction.editor.tpl");
					$segment->set($interaction);
					$segment->restPrintingStyle = "monkey";
					$segment->set([
						"delMode" => $accept == HTML ? "redirect" : "remove",
					]);
					$content .= $segment->generate(1,1);
				}
				if ($accept == HTML_PARTIAL) {
					$this->respond($content, 200, HTML_PARTIAL);
				} else {
					$view = \Monkey\View::loadFile("layout2.tpl");
					$view->set([
						"pageTitle" => "Edit interaction:",
						"headerTitle" => "Edit interaction:",
						"content" => $content,
						"jsAfterContent" => ["all.editor", "interaction.editor","libs/monkey"]
					]);
					$this->respond($view, 200, HTML);
				}
			} else {
				if ($accept == HTML) {
					$this->error("Interactions not found", 404, HTML);
				} else {
					$this->error("", 404, HTML_PARTIAL);
				}
			}
		}
	}

	public function exporter ($input, $accept, $mehod) {
		if ($mehod == "GET") {
			Statistics::increment("interactionExport");
			$all = (new Interaction())->getAll();
			
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
				case CSV:
					$csv = [["protein 1 locus", "protein 1 name", "protein 2 locus", "protein 2 name"]];
					foreach ($all as $row){
						$csv[] = [
							$row->prot1->locus,
							$row->prot1->title,
							$row->prot2->locus,
							$row->prot2->title
						];
					}	
					$this->respond(\Monkey\Utility::encodeCSV($csv), 200, CSV);
					break;
				case JSON:
					$json = [];
					foreach ($all as $row) {
						$json[] = [
							"prot1" => [
								"locus" => $row->prot1->locus,
								"name" => $row->prot1->title,
							],
							"prot2" => [
								"locus" => $row->prot2->locus,
								"name" => $row->prot1->title,
							]
						];
					}
					$this->respond($json, 200, JSON);
					break;
			}
		} else $this->error("Unaccpeted method", 406, $accept);
	}

	public function importer ($input, $accept, $method) {
		if ($accept != HTML) {
			$this->error("Unaccepted", 406, $accept);
		}
		UserController::authenticate(3, HTML);
		$errors = [];
		if ($method == "POST") {
			// validate user input
			$tableName = Interaction::$tableName;
			$mode = $this->filter($input, "mode", "/^(replace)|(append)$/i");
			$conn = \Monkey\Application::$conn;
			$cols = $conn->getColumnNames($tableName);
			if (!$cols) $errors[] = "Table $tableName not found, please import the database structure please";
			if (!$mode) $errors[] = "Mode is required";
			if (!isset($_FILES["file"])) $errors[] = "No file uploaded";
			if ($_FILES["file"]["size"] > 1048576 * 2) $errors[] = "File too large, max. 2MB is accepted.";

			if (empty($errors)) {
				// get file content as csv
				$fileContent = file_get_contents($_FILES["file"]["tmp_name"]);
				$fileContent = str_replace("\r", "", $fileContent);
				$table = explode("\n", $fileContent);
				foreach($table as &$row) {
					$row = explode("\t", $row);
				}
				$header = array_shift($table);
				$conn = \Monkey\Application::$conn;

				// check headers
				$required = ["prot1", "prot2"];
				foreach($required as $key) {
					if (!in_array($key,$header)) $errors[] = "$key column is required";
				}

				if (empty($errors)) {
					if ($mode == "replace") {
						// remove table content
						if (!$conn->doQuery("delete from `$tableName`")) {
							$errors[] = "Internal error, can not delete from table, replace is not successful";
						}
					}
					if (empty($errors)) {
						foreach($table as $i => $row) {
							if (count($header) == count($row)) {
								$row = array_combine($header, $row);
								$genes = Gene::getAll(["locus" => $row["prot1"]]);
								if ($genes) {
									$row["prot1"] = $genes[0]->id;
								} else {
									$errors[] = "Error in line ".($i+2).": gene with locus ".$row["prot1"]." is not found";
									continue;
								}
								$genes = Gene::getAll(["locus" => $row["prot2"]]);
								if ($genes) {
									$row["prot2"] = $genes[0]->id;
								} else {
									$errors[] = "Error in line ".($i+2).": gene with locus ".$row["prot1"]." is not found";
									continue;
								}
								$interaction = Protein::hasPrototype("interaction")->clone($row);
								$interaction->lastAuthor = User::getCurrent()->name;
								if (!$interaction->insert()) {
									$errors[] = "Error in line ".($i+2).": ".$conn->lastError;
								} else {
									$errors[] = "okay";
								}
							}
						}
					}
				}
			}
			if (empty($errors)) {
				$errors[] = "Import successful";
			}
		}
		$view = \Monkey\View::loadFile("layout1.tpl");
		$view->set([
			"title" => "Importer for interaction",
			"pageTitle" => "Importer for interaction",
			"content" => "{{interaction.importer.tpl}}",
			"showFootNote" => "none",
			"errors" => $errors
		]);
		$this->respond($view, 200, HTML);
	}
}
?>