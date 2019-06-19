<?php
require_once ("ViewAdapters.php");

class RegulationController extends \Monkey\Controller {

	public function read ($input, $accept) {
		$geneId = $this->filter($input, "gene", "/^[a-f0-9]{40}$/i");
		$sigA = $this->filter($input, "sigA", "is_bool");
		$radius = $this->filter($input, "radius", "is_numeric");
		if ($sigA === null) {
			$sigA = false;
		}
		if ($radius === null) {
			$radius = 5;
		}
		$wholeGraph = Regulation::getWholeGraph($sigA);
		$subgraph = $wholeGraph->subgraph($geneId, $radius, true);
		if ($subgraph) {
			$data = $subgraph->toJSON();
			foreach ($data["nodes"] as $key => &$node) {
				if (!$node->title) {
					$node = Gene::simpleGet($node->id);
				}
			}
			$data["nodes"] = \Monkey\Utility::arrayColumns($data["nodes"], ["id", "title"]);
		}
		switch ($accept) {
			case HTML:
				$view = \Monkey\View::loadFile("layout2.tpl");
				$view->set([
					"pageTitle" => "Regulation Browser",
					"headerTitle" => "Regulation Browser",
					"content" => "{{regulation.read.tpl}}",
					"jsAfterContent" => ["libs/vis-network.min", "libs/jscolor","regulation.read"],
					"styles" => ["browser", "vis-network.min"],
					"sigA" => $sigA ? "checked" : "",
				]);
				if ($data) {
					$view->set([
						"vars" => [
							"rawData" => $data,
							"conditions" => Expression::getConditions(),
							"datasetDisplayMode" => $GLOBALS["DATASET_DISPLAY_MODE"]
						],
						"message" => "loading"
					]);
				} else {
					$view->set([
						"message" => "Data not found"
					]);
				}
				$this->respond($view, 200, HTML);
				break;
			case JSON:
				if ($data) {
					$this->respond($data, 200, JSON);
				} else $this->error("Gene not found", 404, JSON);
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
		$proto = Protein::hasPrototype("regulation");
		$all = $proto->getAll("1 order by id limit ?,?", [$pageSize*($page-1), $pageSize]);
		switch ($accept) {
			case HTML:
			$count = $proto->count();
			$view = \Monkey\View::loadFile("layout1.tpl");
			$view->set([
				"title" => "All regulations (page $page)",
				"content" => "{{regulation.list.tpl}}",
				"showFootNote" => "none",
				"vars" => [
					"currentInput" => $input,
					"type" => "regulation",
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
	
	// required: 
	public function create ($input, $accept) {
		UserController::authenticate(1, $accept);
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
				break;
			case JSON:
				$regulator = $this->filter($input, "regulator", "has", ["Invalid regulator", 400, JSON]);
				$type = $this->filter($input, "type", "/^(protein)|(riboswitch)$/i", ["Regulator type is required", 400, JSON]);

				$regulated = $this->filter($input, "regulated", "/^\{(gene|operon)\|[0-9a-f]{40}+\}$/i", ["Invalid regulated object", 400, JSON]);
				$mode = $this->filter($input, "mode", "has", ["Mode is required", 400, JSON]);

				$regulator = Model::parse("{".$type."|".$regulator."}");
				$regulated = Model::parse($regulated);


				if ($regulator === null) {
					$this->error("Regulator can not be parsed", 404, JSON);
				}
				if ($regulated === null) {
					$this->error("Regulated object can not be parsed", 404, JSON);
				}

				$regulation = new Regulation($regulator, $mode, $regulated, str_replace("[pubmed|]", "", $input["description"]));
				if ($regulation->insert()) {
					$this->respond(["uri" => $_SERVER['HTTP_REFERER']], 201, JSON);
				} else {
					$this->error("This regulation already exists.", 400, JSON);
				}
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
				$regulation = new Regulation();
				$regulation->id = $id;
				if ($regulation->delete()) {
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
				$mode = $this->filter($input, "mode", "has", ["Mode is required", 400, JSON]);

				$regulation = (new Regulation())->getWithId($id);
				$regulation->mode = $mode;
				$regulation->description = $input["description"];

				if ($regulation->update()) {
					$this->respond(null, 200, JSON);
				} else {
					$this->error(\Monkey\Application::$conn->lastError, 500, JSON);
				}
		}
	}

	// three modes
	// editor?gene=:geneId
	// editor?id=:regulationId
	// editor?regulator=:regulatorId
	public function editor ($input, $accept, $method) {
		if ($accept != HTML_PARTIAL) {
			$this->error("Unaccepted", 405, $accept);
		}
		if ($method != "GET") {
			$this->error("Unaccepted method", 406, $accept);
		}
		$id = $this->filter($input, "id", "is_numeric");
		$geneId = $this->filter($input, "gene", "/[a-f0-9]{40}/i");
		$regulatorId = $this->filter($input, "regulator", "has");

		$regulations = [];
		if ($id) {
			$regulation = (new Regulation)->getWithId($id);
			if ($regulation) {
				$regulations[] = $regulation;
			} elseif ($accept == HTML) {
				$this->error("Regulation not found", 404, HTML);
			}
		} elseif ($geneId) {
			$gene = Gene::simpleGet($geneId);
			if (is_null($gene) && $accept == HTML) {
				$this->error("Gene not found", 404, HTML);
			}
			$regulations = (new Regulation)->get(null, $gene);
		} elseif ($regulatorId) {
			$regulations = Regulation::getByRegulator($regulatorId);
		} elseif (empty($input)) {
			$view = \Monkey\View::loadFile("regulation.blank.tpl");
			$this->respond($view, 200, HTML);
		} else {
			$this->error("Invalid input", 400, $accept);
		}

		if ($regulations) {
			$content = "";
			foreach ($regulations as $regulation) {
				$view = \Monkey\View::loadFile("regulation.editor.tpl");
				$view->set($regulation);
				$view->set("type", get_class($regulation->regulator));
				$content .= $view->generate(1,1);
			}
			$this->respond($content, 200, HTML);
		} else {
			$this->error("", 404, HTML);
		}
	}

	public function exporter ($input, $accept, $mehod) {
		if ($mehod == "GET") {
			$all = Regulation::export(true);
			Statistics::increment("regulationExport");
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
				case CSV:
					$csv = [["regulator locus", "regulator name", "mode", "gene locus", "gene name"]];
					foreach ($all as $row){
						$csv[] = [
							$row->regulator->locus,
							$row->regulator->title,
							$row->mode,
							$row->regulated->locus,
							$row->regulated->title,
						];
					}	
					$this->respond(\Monkey\Utility::encodeCSV($csv), 200, CSV);
					break;
				case JSON:
					$json = [];
					foreach ($all as $row) {
						$json[] = [
							"regulator" => [
								"locus" => $row->regulator->locus,
								"name" => $row->regulator->title,
							],
							"mode" => $row->mode,
							"gene" => [
								"locus" => $row->regulated->locus,
								"name" => $row->regulator->title,
							]
						];
					}
					$this->respond($json, 200, JSON);
					break;
			}
		} else $this->error("Unaccpeted method", 406, $accept);
	}

	public function cache ($input, $accept, $method) {
		if ($accept == JSON) {
			$target = $this->filter($input, "target", "/^[0-9a-f]{40}$/i", ["Target is required", 400, JSON]);
			$radius = $this->filter($input, "radius", "/^\d$/", ["Radius is required", 400, JSON]);
			if ($method == "POST") {
					$content = $this->filter($input, "content");
					$conn = \Monkey\Application::$conn;
					if ($input["chunkNr"]) {
						$re = $conn->doQuery("insert into RegulationNetworkCache (target, radius, content) values (?,?,?) on duplicate key update content = JSON_MERGE_PRESERVE(content, ?)", [$target, $radius, $content, $content]);
					} else {
						$re = $conn->doQuery("insert into RegulationNetworkCache (target, radius, content) values (?,?,?) on duplicate key update content = ?", [$target, $radius, $content, $content]);
					}
					if ($re) {
						$this->respond(["message" => "okay"], 200, JSON);
					} else {
						$this->error("Internal error", 500, JSON);
					}
			} elseif ($method == "GET") {
				$conn = \Monkey\Application::$conn;
				$result = $conn->doQuery("select content from RegulationNetworkCache where target = ? and radius = ?", [$target, $radius]);
				if ($result) {
					$this->respond($result[0]["content"], 200, JSON);
				} elseif ($radius >= 3) {
					// for high radius subnetworks , use cache from other networks with the similar radius;
					$result = $conn->doQuery("select content from RegulationNetworkCache where and radius = ?", [$radius]);
					if ($result) {
						$this->respond($result[0]["content"], 200, JSON);
					} else {
						$this->error("Not found", 404, JSON);
					}
				} else {
					$this->error("Not found", 404, JSON);
				}
			} else $this->error("Unaccepted methods", 405, $accept);
		} else $this->error("Not accepted", 406, $accept);
	}
}
?>