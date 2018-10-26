<?php
require_once ("ViewAdapters.php");

class ExpressionController extends Controller {
	public function read ($input, $accept){
		$geneId = $this->filter($input, "gene", "/^[a-f0-9]{40}$/i");
		$condition = $this->filter($input, "condition", "/^\d+$/i");
		$alternative = $this->filter($input, "alternative", "is_bool");
		$geneIds = $this->filter($input, "genes", "/^([a-f0-9]{40},?)+$/i");
		$range = $this->filter($input, "range", "/^-?\d+_-?\d+_(0|1)$/i");
		$sampling = $this->filter($input, "sampling", "^\d+$");
		
		if ($accept == HTML) {
			$view = View::loadFile("layout2.tpl");
			$view->set([
				"pageTitle" => "Expression Browser",
				"headerTitle" => "Expression Browser",
				"content" => "{{expression.read.tpl}}",
				"jsAfterContent" => [
					"libs/view",
					"libs/echarts.common.min",
					"libs/genome.canvas",
					"libs/papaparse",
					"contextBrowser",
					"contextExpressionBrowser",
					"expression.read"
				],
				"styles" => ["expression.read"],
				"vars" => [
					"genomeLength" => $GLOBALS["GENOME_LENGTH"]
				]
			]);
			if ($alternative) {
				$view->set("jsAfterContent", ["expression.read", "expression.read.alternative"]);
			}
			if ($geneId) {
				$gene = Gene::get($geneId);
				$view->set([
					"bsupath" => $gene->outlinks->bsupath,
				]);
			}
			$this->respond($view, 200, HTML);
		} elseif ($accept == HTML_PARTIAL) {
			$this->error("Unaccepted", 406, HTML);
		} else {
			$data = null; $csvData = null;
			if ($geneId !== null) {
				$data = Expression::get($geneId);
				if ($data) {
					$csvData = [["id", "value"]];
					foreach($data as $key => $value) {
						$csvData[] = [$key, $value];
					}
				}
			} elseif ($range !== null && $condition !== null) {
				// the tilling array data
				list($start, $stop, $strand) = explode("_", $range);
				if ($sampling === NULL) $sampling = 1;
				// get the real start/stop
				$start = (int) $start;
				$stop = (int) $stop;
				$strand = (int) $strand;

				$start = $start % $GLOBALS["GENOME_LENGTH"];
				$stop = $stop % $GLOBALS["GENOME_LENGTH"];
				if ($start < 0) {
					$start += $GLOBALS["GENOME_LENGTH"];
				}
				if ($stop < 0) {
					$stop += $GLOBALS["GENOME_LENGTH"];
				}

				if ($start > $stop) {
					$range1 = Expression::getByRange($condition, 1, $stop, $strand, $sampling);
					$range2 = Expression::getByRange($condition, $start, $GLOBALS["GENOME_LENGTH"], $strand, $sampling);
					$data= array_merge($range1, $range2);
				} else {
					$data = Expression::getByRange($condition, $start, $stop, $strand, $sampling);
				}
				if ($data) {
					array_unshift($data, ["position", "value"]);
					$csvData = $data;
				};
			} elseif ($condition != null) {
				if ($geneIds) {
					$geneIds = explode(",", $geneIds);
				} else $geneIds = [];
				$data = Expression::getByCondition($condition, $geneIds);
				if ($data) {
					$csvData = [["condition", "value"]];
					foreach($data as $key => $value) {
						$csvData[] = [$key, $value];
					}
				}
			}
			

			if ($data) {
				if ($accept == JSON) {
					$this->respond($data, 200, JSON);
				} elseif($accept == CSV) {
					$delimiter = $input["delimiter"] ? $input["delimiter"] : ",";
					$this->respond(Utility::encodeCSV($csvData, $delimiter, null), 200, CSV);
				} else {
					$this->error("Unaccepted", 406, $accept);
				}
			} else $this->error("Data not found", 404, $accept);
		}
	}

	public function create ($input, $accept) {
		/*
			the upload file size is restricted by the php.ini
			by default is 2MB
			however, this method is implemented in the way that much larger (~30MB) can be handled
		*/
		$title = $this->filter($input, "title", "has");
		$_SESSION["forImporter"] = [];
		if ($title === null) {
			$_SESSION["forImporter"]["errorMsg"] = "Title of the dataset is required";
			header("Location: ".$GLOBALS['WEBRROT']."expression/importer");
			return;
		}
		$type = $this->filter($input, "type", "has");
		if ($type === null) {
			$_SESSION["forImporter"]["errorMsg"] = "Type of the dataset is required";
			header("Location: ".$GLOBALS['WEBRROT']."expression/importer");
			return;
		}
		
		if ($_FILES["dataset"] && $_FILES["dataset"]["size"] > 0) {
			
			/* 
			the tilling array data is very huge and is usually over the capacity of the server (memory per process too small)
			hence, the file will be chopped into chunks of 400 rows, and insert chunk by chunk
			*/
			$file = fopen($_FILES["dataset"]["tmp_name"], "r");
			if ($file) {
				$row = fgetcsv($file, 0, "\t");
				$row = fgetcsv($file, 0, "\t");
				$rowNr = 0; // row number, exclude the header;
				$values = [];
	
				// start import
	
				if (!Expression::startImport($title, $input["description"], $type)) {
					$_SESSION["forImporter"]["errorMsg"] = "Data set with the same name and type already exists";
					header("Location: ".$GLOBALS['WEBRROT']."expression/importer");
					return;
				}
	
				while ($row) {
					if (count($row) != 1 || $row[0] !== null) { // when the $row was not an empty row
						if (count($row) != 2) {
							// cell count error
							$_SESSION["forImporter"]["errorMsg"] = "File format error. Error at row ".($rowNr+1);
							header("Location: ".$GLOBALS['WEBRROT']."expression/importer");
							return;
						}
						// trim off the white characters
						// in windows, new line is \r\n, is_numeric will not work if the number string has a leading or tailing \t\r\n
						array_walk($row, function(&$each){
							$each = trim($each);
						});
	
						if (!is_numeric($row[1])) {
							// not a number
							$_SESSION["forImporter"]["errorMsg"] = "File format error, values are not numbers at row ".($rowNr+1);
							header("Location: ".$GLOBALS['WEBRROT']."expression/importer");
							return;
						}
	
						$values[$row[0]] = (double) $row[1];

						
						if ($rowNr % 400 == 0 && $rowNr != 0) { // chunk size = 400
							if (!Expression::importData(null, $values)) {
								// something wrong, mostly possile error: violation of the unique constraint
								$_SESSION["forImporter"]["errorMsg"] = "Duplicated row exists in the uploaded file";
								header("Location: ".$GLOBALS['WEBRROT']."expression/importer");
								return;
							}
							$values = []; // clear the chunk
						}
					}
					$rowNr++; // 
					$row = fgetcsv($file, 0, "\t");
				}
	
				// wrap up the remaining data in $values
				if (!empty($values)) {
					if (!Expression::importData(null, $values)) {
						// last batch successful
						// something wrong, mostly possile error: violation of the unique constraint
						$_SESSION["forImporter"]["errorMsg"] = "Duplicated row exists in the uploaded file";
						header("Location: ".$GLOBALS['WEBRROT']."expression/importer");
						return;
					}
				}
	
				// okay done, now end import
				Expression::endImport();
				$_SESSION["forImporter"]["msg"] = "Import successful";
				header("Location: ".$GLOBALS['WEBRROT']."expression/importer");
				return;
			} else {
				$_SESSION["forImporter"]["errorMsg"] = "Can not open the file";
			}

			
		} else {
			$_SESSION["forImporter"]["errorMsg"] = "No file is uploaded or file is empty";
			header("Location: ".$GLOBALS['WEBRROT']."expression/importer");
			return;
		}
	}

	/**
	 * @usergroup 3
	 */
	public function delete ($input, $accept) {
		if ($accept == JSON) {
			UserController::authenticate(3, $accept);
			$id = $this->filter($input, "id", "/^\d+$/", ["Id is required", 400, $accept]);
			$condition = Expression::getCondition($id);
			if ($condition == null || Expression::deleteCondition($condition)) {
				$this->respond(null, 204, JSON);
			} else {
				$this->error("An internal error has happened: ".Application::$conn->lastError, 500, JSON);
			}
		} else $this->error("Unaccepted", 406, $accept);
	}

	/**
	 * @usergroup 3
	 */
	public function update ($input, $accept) {
		if ($accept == JSON) {
			UserController::authenticate(3, $accept);
			$id = $this->filter($input, "id", "/^\d+$/", ["Id is required", 400, $accept]);
			$condition = Expression::getCondition($id);
			if ($condition) {
				$con = ExperimentalCondition::withData($input);
				if ($con->replace()) {
					$this->respond(["uri" => "expression/viewer?id=$id"], 200, JSON);
				} else {
					$this->error("An internal error has happened: ".Application::$conn->lastError, 500, JSON);
				}
			} else $this->error("Not found", 404, JSON);
		} else $this->error("Unaccepted", 406, $accept);
	}

	public function condition ($input, $accept, $method) {
		switch ($method) {
			case "GET":
				if ($accept == JSON) {
					// get all conditions
					$data = Expression::getConditions();
					$this->respond($data, 200, JSON);
				} else {
					$this->error("Unacceptable", 406, HTML);
				} 
				break;
			default:
				$this->error("Method not allowed", 405, $accept);
				break;
		}
	}

	public function importer ($input, $accept, $method) {
		UserController::authenticate(3, $accept);
		if ($method == "GET" && $accept == HTML) {
			$view = View::loadFile("layout2.tpl");
			$view->set($input);
			$view->set($_SESSION["forImporter"]);
			unset($_SESSION["forImporter"]);
			$view->set([
				"title" => "Import expression data",
				"pageTitle" => "Import expression data",
				"headerTitle" => "Import expression data",
				"content" => "{{expression.importer.tpl}}",
				"jsAfterContent" => ["all.editor"],
				"vars" => [
					"type" => $input["type"],
					"types" => Expression::getAllConditionTypes()
				],
				"showError" => $input["errorMsg"] ? "block" : "none",
				"showMsg" => $input["msg"] ? "block" : "none",
				"showFootNote" => "none"
			]);
			$this->respond($view, 200, HTML);
		} else {
			$this->error("Unaccepted", 405, $accept);
		}
	}

	public function list ($input, $accept, $method) {
		if ($accept == HTML && $method == "GET") {
			$page = $this->filter($input, "page", "/^\d+$/");
			$pageSize = $this->filter($input, "page_size", "/^\d+$/");
			if ($page === null) $input["page"] = $page = 1;
			if ($pageSize === null) $input["page_size"] = $pageSize = 20;

			$all = Expression::getConditions();
			$count = count($all);
			$max = ceil ($count / $pageSize);
			if ($page > $max) {
				$this->error("Not found", 404, HTML);
			}

			$currentPage = array_slice($all, $pageSize*($page-1), $pageSize);
			$view = View::loadFile("layout1.tpl");
			$view->set([
				"showFootNote" => "none",
				"pageTitle" => "All expression data sets (page $page)",
				"title" => "All expression data sets (page $page)",
				"content" => "{{expression.list.tpl}}",
				"datasets" => $currentPage,
				"jsAfterContent" => ["all.list"],
				"vars" => [
					"type" => "expression/list",
					"currentInput" => $input,
					"max" => $max
				]
			]);
			$this->respond($view, 200, HTML);
		} else $this->error("Not available", 404, $accept);
	}

	public function viewer ($input, $accept, $method) {
		if ($accept == HTML && $method == "GET") {
			$id = $this->filter($input, "id", "is_numeric", ["Not found", 404, HTML]);
			$dataSet = Expression::getCondition($id);
			if ($dataSet->category != "position-based" ){
				$rawData = Expression::getByCondition($id);
				$i = 0; $data = [["locus", "value"]];
				foreach($rawData as $key => $value) {
					$locus = Gene::get($key)->locus; // safe, as there is foreign key constraint
					$data[] = [$locus, $value];
					if ($i > 20) break;
					$i++;
				}
			} else {
				$raw1 = Expression::getByRange($id, 1, 20, 1, 1);
				$raw2 = Expression::getByRange($id, 1, 20, 1, 1);
				$data = [];
				for ($i = 0; $i < 20; $i++) {
					$data[] = [$raw1[$i][0], $raw1[$i][1], $raw2[$i][1]];
				}
				array_unshift($data, ["position", "+ strand", "- strand"]);
			}
			$view = View::loadFile("layout1.tpl");
			$view->set($dataSet);
			$view->set([
				"pageTitle" => "Data set: ".$dataSet->title,
				"showFootNote" => "none",
				"content" => "{{expression.viewer.tpl}}",
				"pubmed" => $dataSet->pubmed ? "[pubmed|{$dataSet->pubmed}]" : "",
				"data" => Utility::encodeCSV($data,",\t",null),
				"navlinks" => [
					["href" => "expression/list", "innerHTML" => "All data sets"],
				],
				"fullDataLink" => $dataSet->category == "gene-based" ? "expression?condition={{:id}}&__accept=CSV": "",
				"floatButtons" => [
					["icon" => "edit.svg", "href" => "expression/editor?id=$id"]
				]
			]);
			$this->respond($view, 200, HTML);
		} else $this->error("Not available", 404, $accept);
	}

	public function editor ($input, $accept, $method) {
		if ($accept == HTML && $method == "GET") {
			UserController::authenticate(3, HTML);
			$id = $this->filter($input, "id", "/^\d+$/", ["Id is required", 400, JSON]);
			$dataSet = Expression::getCondition($id);
			if ($dataSet) {
				$view = View::loadFile("layout2.tpl");
				$view->set($dataSet);
				$view->set([
					"headerTitle" => "Update expression data set",
					"pageTitle" => "Update expression data set",
					"content" => "{{expression.editor.tpl}}",
					"vars" => [
						"type" => $dataSet->type,
						"types" => Expression::getAllConditionTypes()
					],
					"showError" => $input["errorMsg"] ? "block" : "none",
					"showMsg" => $input["msg"] ? "block" : "none",
					"jsAfterContent" => ["all.editor"]
				]);
				$this->respond($view, 200, HTML);
			} else {
				$this->error("Not found", 404, $accept);
			}
		} else $this->error("Not available", 404, $accept);
	}
}
?>