<?php
class ProteinController extends GeneController {
	/**
	 * provides editor for paralogues proteins
	 * protein/paralogueEditor?id=:id
	 * protein/paralogueEditor?protein=:proteinId
	 */
	public function paralogueEditor ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		if ($method != "GET") {
			$this->error("Unaccepted method", 405, $accept);
		}
		if ($accept == JSON) {
			$this->error("Unaccepted", 406, JSON);
		} else {
			$paralogues = [];
			$id = $this->filter($input, "id", "is_numeric");
			$proteinId = $this->filter($input, "protein", "/^[a-f0-9]{40}$/i");
			if ($id) {
				$paralogue = Gene::hasPrototype("paralogues")->getWithId($id);
				if ($paralogue) {
					$paralogues[] = $paralogue;
				}
			} elseif ($proteinId) {
				$protein = Protein::simpleGet($proteinId);
				$paralogues = $protein->has("paralogues");
			} else {
				if ($accept == HTML_PARTIAL) {
					$view = \Monkey\View::loadFile("paralogue.editor.blank.tpl");
					$view->set("updateMode", "replace");
					$this->respond($view, 200, HTML_PARTIAL);
				} else {
					$view = \Monkey\View::loadFile("layout2.tpl");
					$view->set([
						"pageTitle" => "Add paralogue:",
						"headerTitle" => "Add paralogue:",
						"updateMode" => "redirect",
						"content" => "{{paralogue.editor.blank.tpl}}",
						"jsAfterContent" => ["all.editor", "paralogue.editor","libs/monkey"]
					]);
					$this->respond($view, 200, HTML);
				}
			}
			if ($paralogues) {
				$content = "";
				foreach ($paralogues as $paralogue) {
					\Monkey\Utility::decodeLinkForEdit($paralogue);
					$view = \Monkey\View::loadFile("paralogue.editor.tpl");
					$view->set($paralogue);
					$view->restPrintingStyle = "monkey";
					$view->set([
						"delMode" => $accept == HTML ? "redirect" : "remove",
					]);
					$content .= $view->generate(1,1);
				}
				if ($accept == HTML_PARTIAL) {
					$this->respond($content, 200, HTML_PARTIAL);
				} else {
					$view = \Monkey\View::loadFile("layout2.tpl");
					$view->set([
						"pageTitle" => "Edit paralogue:",
						"headerTitle" => "Edit paralogue:",
						"content" => $content,
						"jsAfterContent" => ["all.editor", "paralogue.editor","libs/monkey"]
					]);
					$this->respond($view, 200, HTML);
				}
			} else {
				if ($accept == HTML) {
					$this->error("paralogues not found", 404, HTML);
				} else {
					$this->error("", 404, HTML_PARTIAL);
				}
			}
		}
	}
	
	public function paralogue ($input, $accept, $method) {
		if ($accept != JSON) {
			$this->error("Unaccepted", 405, $accept);
		}
		switch ($method) {
			case "GET":
				$this->error("Unaccepted method", 406, $accept);
				break;
			case "POST":
				$prot1 = $this->filter($input, "prot1", "/^[a-f0-9]{40}$/i", ["Invalid protein 1", 400, $accept]);
				$prot2 = $this->filter($input, "prot2", "/^[a-f0-9]{40}$/i", ["Invalid protein 2", 400, $accept]);
				if ($prot1 == $prot2) {
					$this->error("Protein 1 and Protein 2 should not be the same", 400, JSON);
				}
				$prot1 = Protein::simpleGet($prot1);
				if (!$prot1) {
					$this->error("Protein 1 not found", 404, JSON);
				}
				$prot2 = Protein::simpleGet($prot2);
				if (!$prot2) {
					$this->error("Protein 2 not found", 404, JSON);
				}
				$data = [];
				foreach($input as $key => $value) {
					if ($key != "prot1" && $key != "prot2") {
						$data[$key] = $value;
					}
				}
				if (($paralogue = $prot1->addParalogue($prot2, $data))) {
					$this->respond(["uri" => "protein/paralogueEditor?id=".$paralogue->id], 201, JSON);
				} else $this->error("Internal error. Operation is not successful.", 500, JSON);
				break;
			case "PUT":
				$prot1 = $this->filter($input, "prot1", "/^[a-f0-9]{40}$/i", ["Invalid protein 1", 400, $accept]);
				$prot2 = $this->filter($input, "prot2", "/^[a-f0-9]{40}$/i", ["Invalid protein 2", 400, $accept]);
				if ($prot1 == $prot2) {
					$this->error("Protein 1 and Protein 2 should not be the same", 400, JSON);
				}
				$prot1 = Protein::simpleGet($prot1);
				if (!$prot1) {
					$this->error("Protein 1 not found", 404, JSON);
				}
				$prot2 = Protein::simpleGet($prot2);
				if (!$prot2) {
					$this->error("Protein 2 not found", 404, JSON);
				}
				$data = [];
				foreach($input as $key => $value) {
					if ($key != "prot1" && $key != "prot2") {
						$data[$key] = $value;
					}
				}
				if (($paralogue = $prot1->updateParalogue($prot2, $data))) {
					$this->respond(["uri" => "protein/paralogueEditor?id=".$paralogue->id], 201, JSON);
				} else $this->error("Internal error. Operation is not successful.", 500, JSON);
				break;
			case "DELETE":
				$id = $this->filter($input, "id", "is_numeric", ["Invalid id", 400, $accept]);
				$paralogue = Gene::hasPrototype("paralogues");
				$paralogue->id = $id;
				if (\Monkey\Application::$conn->transaction(function() use ($paralogue) {
					return History::record($paralogue, "remove") && $paralogue->delete();
				})) {
					$this->respond(null, 204, JSON);
				} else {
					$this->error("Internal error. Deletion is not successful", 500, JSON);
				}
				break;
		}
	}

	public function paralogueImporter ($input, $accept, $method) {
		if ($accept != HTML) {
			$this->error("Unaccepted", 406, $accept);
		}
		UserController::authenticate(3, HTML);
		$errors = [];
		if ($method == "POST") {
			// validate user input
			$tableName = Protein::hasPrototype("paralogues")->tableName;
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
								$paralogue = Protein::hasPrototype("paralogues")->clone($row);
								$paralogue->lastAuthor = User::getCurrent()->name;
								if (!$paralogue->insert()) {
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
			"title" => "Importer for paralogous proteins",
			"pageTitle" => "Importer for paralogous proteins",
			"content" => "{{interaction.importer.tpl}}",
			"showFootNote" => "none",
			"errors" => $errors
		]);
		$this->respond($view, 200, HTML);
	}
}
?>