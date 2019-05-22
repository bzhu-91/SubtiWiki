<?php
require_once ("ViewAdapters.php");

class OperonController extends Controller {
	public function read ($input, $accept) {
		if ($input) {
			if (array_key_exists("id", $input)) {
				$this->view($input, $accept);
			} elseif (array_key_exists("gene", $input)) {
				$this->search($input, $accept);
			} elseif (array_key_exists("page", $input)) {
				$this->list($input, $accept);
			}
		} else {
			$this->index($accept);
		}
		$this->error("Page not found", 404, $accept);
	}

	protected function list ($input, $accept) {
		$page = $this->filter($input, "page", "is_numeric", ["Invalid page number", 400, $accept]);
		$pageSize = $this->filter($input, "page_size", "is_numeric", ["Invalid page size", 400, $accept]);
		$operons = Operon::getAll("1 limit ?,?", [$pageSize*($page-1), $pageSize]);
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				if ($operons) {
					$count = Operon::count();
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"title" => "All operons (page $page)",
						"content" => "{{all.list.tpl}}",
						"data" => $operons,
						"jsAfterContent" => ["all.list"],
						"showFootNote" => "none",
						"vars" => [
							"max" => ceil($count/$pageSize),
							"type" => "operon",
							"currentInput" => $input,
						],
					]);
					if ($page == 1) $view->set("previous", "operon");
					else $view->set("previous", "operon?page=".($page-1)."&page_size=$pageSize");
					if ($page >= $count/$pageSize) $view->set("next", "operon");
					else $view->set("next", "operon?page=".($page+1)."&page_size=$pageSize");

					$this->respond($view, 200, HTML);
				} else $this->error("Not found", 404, HTML);

				break;
			case JSON:
				if ($operons) $this->respond(Utility::arrayColumns($operons, ["id", "title"]), 200, JSON);
				else $this->error("Not found", 404, JSON);
				break;
		}
	}

	protected function view ($input, $accept) {
		$id = $this->filter($input, "id", "/^[0-9a-f]{40}$/i", ["Invalid id", 400, $accept]);
		$operon = Operon::get($id);
		if ($operon) {
			Utility::decodeLinkForView($operon);
			$data = MetaData::sort($operon);
			$operon->updateCount();
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
					$view = View::loadFile("layout1.tpl");
					$view->set($data);
					$view->set([
						"pageTitle" => "Operon",
						"content" => "{{operon.view.tpl}}",
	 					"navlinks" => [],
					]);
					if (User::getCurrent()) {
						$view->set("floatButtons", [
							["href" => "operon/editor?id=$id", "icon" => "edit.svg"]
						]);
					}
					$this->respond($view, 200, HTML);
					break;
				case JSON:
					$this->respond($operon, 200, JSON);
					break;
			}
		} else $this->error("Page not found", 404, $accept);
	} 

	protected function index ($accept) {
		$this->list([
			"page" => 1,
			"page_size" => 150 
		], $accept);
	} 

	public function update ($input, $accept) {
		UserController::authenticate(1, $accept);
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
				break;
			case JSON:
				$id = $this->filter($input, "id", "/^[a-f0-9]{40}$/i", ["Invalid id", 400, JSON]);
				$title = $this->filter($input, "title", "has", ["Title is required", 400, JSON]);
				$genes = $this->filter($input, "genes", "has", ["Genes is required", 400, JSON]);
				$operon = Operon::withData($input);
				$operon->lastAuthor = User::getCurrent()->name;
				try {
					$result = $operon->replace();
					if ($result) {
						$this->respond(["uri" => "operon?id=$id"], 200, JSON);
					} else {
						$this->respond("Internal error, update is not successful", 500, JSON);
					}
				} catch (BaseException $e) {
					$this->error($e->getMessage(), 500, JSON);
				}
		}
	}

	protected function search ($input, $accept) {
		if ($accept == JSON) {
			$geneId = $this->filter($input, "gene", "/[a-f0-9]{40}/i", ["Gene is required", 400, $accept]);
			$gene = Gene::simpleGet($geneId);
			if ($gene) {
				$operons = $gene->has("operons");
				if ($operons) {
					$data = array_column($operons, "operon");
					Utility::decodeLinkForView($data);
					$this->respond($data, 200, JSON);
				} else {
					$this->error("Not found", 404, JSON);
				}
			} else $this->error("Gene not found", 404, JSON);
			
		} else {
			$this->error("Unacceptable", 406, $accept);
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
				$id = $this->filter($input, "id", "/^[a-f0-9]{40}$/i", ["Invalid id", 400, JSON]);
				$operon = new Operon();
				$operon->id = $id;
				if ($operon->delete()) {
					$this->respond(null, 204, JSON);
				} else $this->error("An unexpected error has happened.", 500, JSON);
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
				$title = $this->filter($input, "title", "has", ["Title is required", 400, JSON]);
				$genes = $this->filter($input, "genes", "has", ["Genes is required", 400, JSON]);
				$operon = Operon::withData($input);
				$operon->lastAuthor = User::getCurrent()->name;
				try {
					if ($operon->insert()) {
						$this->respond(["uri" => "operon/editor?id={$operon->id}"], 201, JSON);
					} else $this->error("This operon already exists", 400, JSON);
				} catch (BaseException $e) {
					$this->error($e->getMessage(), 400, JSON);
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
			$id = $this->filter($input, "id", "/^[a-f0-9]{40}$/i");
			$geneId = $this->filter($input, "gene", "/^[a-f0-9]{40}$/i");
			$operons = [];
			if ($id) {
				$operon = Operon::raw($id);
				if ($operon) {
					$operons[] = $operon;
				}
			} elseif ($geneId) {
				$gene = Gene::simpleGet($geneId);
				if ($gene) {
					foreach ($gene->has("operons") as $row) {
						$operon = Operon::raw($row->operon->id);
						$operons[] = $operon;
					}
				}
			} else {
				if ($accept == HTML_PARTIAL) {
					$view = View::loadFile("operon.editor.blank.tpl");
					$view->set("updateMode", "replace");
					$this->respond($view, 200, HTML_PARTIAL);
				} else {
					$view = View::loadFile("layout2.tpl");
					$view->set([
						"pageTitle" => "Add Operon:",
						"headerTitle" => "Add Operon:",
						"content" => "{{operon.editor.blank.tpl}}",
						"updateMode" => "redirect",
						"jsAfterContent" => ["libs/monkey", "all.editor", "operon.editor", "regulation.editor"],
						"styles" => ["all.editor"]
					]);
					$this->respond($view, 200, HTML);
				}
			}
			if ($operons) {
				Utility::decodeLinkForEdit($operons);
				$content = "";
				foreach ($operons as $operon) {
					$data = MetaData::fill($operon, "insert text here");
					$view = View::loadFile("operon.editor.tpl");
					$view->restPrintingStyle = "edit";
					$view->set($data);
					$regulations = $operon->has("regulation");
					$view->set([
						"regulations" => $regulations,
						"delMode" => $accept == HTML ? "redirect" : "remove",
						"updateMode" => $accept == HTML ? "redirect" : "alert",
						"regulated" => $operon->toObjectMarkup(),
					]);
					$view->set("regulations", $regulations);
					$content .= $view->generate(1,1);
				}

				if ($accept == HTML_PARTIAL) {
					$this->respond($content, 200, HTML);
				} else {
					$view = View::loadFile("layout2.tpl");
					$view->set([
						"pageTitle" => "Edit: Operon",
						"headerTitle" => "Edit: Operon",
						"content" => $content."{{jsvars:vars}}",
						"jsAfterContent" => ["operon.editor", "libs/monkey", "all.editor"],
						"vars" => [
							"showDelBtn" => User::getCurrent()->privilege > 1
						],
						"styles" => ["all.editor"]
					]);
					$this->respond($view, 200, HTML);
				}
			} else {
				if ($accept == HTML) {
					$this->error("Not found", 404, HTML);
				} else {
					$this->error("", 404, HTML);
				}
			}
		}
	}

	public function exporter ($input, $accept, $mehod) {
		if ($mehod == "GET") {
			$all = Operon::getAll();
			Utility::decodeLinkForEdit($all);
			Statistics::increment("operonExport");
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
				case CSV:
					$csv = [["operon", "genes"]];
					foreach ($all as $row){
						$csv[] = [
							$row->title,
							$row->genes,
						];
					}	
					$this->respond(Utility::encodeCSV($csv), 200, CSV);
					break;
				case JSON:
					$json = Utility::arrayColumns($all, ["title", "genes"]);
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
			$tableName = Operon::$tableName;
			$mode = $this->filter($input, "mode", "/^(replace)|(append)$/i");
			$conn = Application::$conn;
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
				$conn = Application::$conn;

				// check headers
				$required = ["genes"];
				foreach($required as $key) {
					if (!in_array($key,$header)) $errors[] = "$key column is required";
				}

				if (empty($errors)) {
					if ($mode == "replace") {
						// remove table content
						if (!$conn->doQuery("delete from `$tableName`")) {
							$errors[] = "Internal error, can not delete from table, replace is not successful";
						} else {
							$meta = MetaData::get("Operon");
							if ($meta && !$meta->delete()) {
								$errors[] = "Internal error, can not clear the template, replace is not successful";
							}
						}
					}
					if (empty($errors)) {
						foreach($table as $i => $row) {
							if (count($header) == count($row)) {
								$row = array_combine($header, $row);
								$genes = explode("-", $row["genes"]);
								array_walk($genes, function (&$each){
									$each = "[[gene|".trim($each)."]]";
								});
								if (count($genes) >= 2) {
									$row["title"] = $genes[0]."->".$genes[count($genes)-1];
								} else {
									$row["title"] = $genes[0];
								}
								$row["genes"]= implode("-", $genes);
								$operon = Operon::withData($row);
								$operon->lastAuthor = User::getCurrent()->name;
								try {
									if (!$operon->insert()) {
										$errors[] = "Error in line ".($i+2).": ".$conn->lastError;
									}
								} catch (Exception $e) {
									$errors[] = "Error in line ".($i+2).": ".$e;
								}
							} else $errors[] = "Error in line ".($i+2).": row has missing or extra fields";
						}
					}
				}
			}
			if (empty($errors)) {
				$errors[] = "Import successful";
			}
		}
		$view = View::loadFile("layout1.tpl");
		$view->set([
			"title" => "Importer for operon",
			"pageTitle" => "Importer for operon",
			"content" => "{{operon.importer.tpl}}",
			"showFootNote" => "none",
			"errors" => $errors
		]);
		$this->respond($view, 200, HTML);
	}
}
?>