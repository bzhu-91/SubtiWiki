<?php
require_once ("ViewAdapters.php");

/**
 * Provide operations on Expression (Expression browser)
 * RESTful API summary:
 * - GET: /expression
 * - GET: /expression?gene=:geneId
 * - GET: /expression?range=:range&condition=:conditionId[&sampling=:sampling]
 * - GET: /expression?condition=:conditionId[&genes=:geneIds]
 * 
 * 
 */
class ExpressionController extends \Kiwi\Controller {
	/**
	 * multiple APIs.
	 * 
	 * API: expression browser
	 * URL: /expression
	 * Method: GET
	 * Success Response:
	 * - code: 200, accept: HTML, Content: a html page
	 * Error response:
	 * - code: 400, accept: -, Content: {message: "Bad request"}
	 * - code: 406, accept:HTML_partial, Content: {message: "Unaccepted"}
	 * 
	 * API: show expression data of a gene
	 * URL: /expression?gene=:geneId
	 * URL Params: geneId=[sha1 hash string]
	 * Method: GET
	 * Success Response:
	 * - code: 200, accept: HTML, Content: a html page
	 * - code: 200, accept: JSON, Content: expression level, {1: 12.3}
	 * Error Response:
	 * - code: 400, accept: JSON/CSV, Content: {message: "Bad request"}
	 * - code: 404, accept: JSON/CSV, Content: {message: "Data not found"}
	 * - code: 406, accept: HTML_PARTIAL, Content: {message: "Unaccepted"}
	 * 
	 * API: show expression data of a range
	 * URL: /expression?range=:range&condition=:conditionId[&sampling=:sampling]
	 * Method: GET
	 * URL Params: range=[string] in the format of start_stop_strand, where strand is 1 or 0; conditionId=[int]; sampling=[int], default 1
	 * Success Response:
	 * - code: 200, accept: JSON, content: {45543: 12.3, 45544: 1.32, ...}
	 * - code: 200, accept: CSV, content: csv with columns "position", "value"
	 * Error Response:
	 * - code: 404, accept: JSON/CSV, Content: {message: "Data not found"}
	 * - code: 406, accept: HTML_PARTIAL, Content: {message: "Unaccepted"}
	 * 
	 * API: show expression level of a certain conditions
	 * URL: /expression?condition=:conditionId[&genes=:geneIds]
	 * Method: GET
	 * URL Params: geneIds=[geneIds, seperated by comma], conditionId=[int, id of the condition]
	 * Success Response:
	 * - code: 200, accept: JSON, content: {xxxxxxxxxxxx: 12.2, xxxxxxxxxxxxx: 123.212}
	 * - code: 200, accept: CSV, content: csv file with columns "gene", "value"
	 * Error Response:
	 * - code: 404, accept: JSON/CSV, Content: {message: "Data not found"}
	 * - code: 406, accept: HTML_PARTIAL, Content: {message: "Unaccepted"}
	 */
	public function read ($input, $accept){
		$geneId = $this->filter($input, "gene", "/^[a-f0-9]{40}$/i");
		$condition = $this->filter($input, "condition", "/^\d+$/i");
		$alternative = $this->filter($input, "alternative", "is_bool");
		$geneIds = $this->filter($input, "genes", "/^([a-f0-9]{40},?)+$/i");
		$range = $this->filter($input, "range", "/^-?\d+_-?\d+_(0|1)$/i");
		$sampling = $this->filter($input, "sampling", "^\d+$");
		
		if ($accept == HTML) {
			$view = \Kiwi\View::loadFile("layout2.tpl");
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
					$csvData = [["gene", "value"]];
					foreach($data as $key => $value) {
						$csvData[] = [$key, $value];
					}
				}
			} else {
				$this->error("Bad request", 400, $accept);
			}
			

			if ($data) {
				if ($accept == JSON) {
					$this->respond($data, 200, JSON);
				} elseif($accept == CSV) {
					$delimiter = $input["delimiter"] ? $input["delimiter"] : ",";
					$this->respond(\Kiwi\Utility::encodeCSV($csvData, $delimiter, null), 200, CSV);
				} else {
					$this->error("Unaccepted", 406, $accept);
				}
			} else $this->error("Data not found", 404, $accept);
		}
	}

	/**
	 * API: import a dataset.
	 * API: import a dataset
	 * URL: /expression
	 * Method: POST
	 * Data Params: {
	 * 		file: uploaded_file, 
	 * 		description: the_description_of_a_dataset, 
	 *		type: type_of_dataset}
	 * Success Response: redirect
	 * Error Response: redirect
	 **/
	public function create ($input, $accept) {
		if ($accept == JSON) {
			$title = $this->filter($input, "title", "has");
			$conditionId = $this->filter($input, "condition", "/^\d+$/");
			if ($conditionId) {
				$condition = ExperimentalCondition::get($conditionId);
				if ($condition) {
					if ($_FILES["file"] && $_FILES["file"]["size"] > 0) {
						/*  the tilling array data is very huge and is usually over the capacity of the server (memory per process too small)
						hence, the file will be chopped into chunks of 400 rows, and insert chunk by chunk */
						$file = fopen($_FILES["file"]["tmp_name"], "r");
						if ($file) {
							$row = fgetcsv($file, 0, "\t");
							$row = fgetcsv($file, 0, "\t");
							$rowNr = 0; // row number, exclude the header;
							$values = [];
							// start import
							if (Expression::startImport($condition)) {
								while ($row) {
									if (count($row) != 1 || $row[0] !== null) { // when the $row was not an empty row
										if (count($row) == 2) {
											// trim off the white characters
											// in windows, new line is \r\n, is_numeric will not work if the number string has a leading or tailing \t\r\n
											array_walk($row, function(&$each){
												$each = trim($each);
											});
											// check the second cell is a number or not
											if (is_numeric($row[1])) {
												$values[$row[0]] = (double) $row[1];
											} else {
												$this->error("File format error, values are not numbers at row ".($rowNr+1),400,JSON);
											}
											// process chunks
											if ($rowNr % 400 == 0 && $rowNr != 0) { // chunk size = 400
												if (Expression::importData($values)) {
													$values = []; // clear the chunk
												} else {
													$this->error("Duplicated row exists in the uploaded file", 400, JSON);
												}
											}
										} else {
											// cell count error
											$this->error("File format error. Error at row ".($rowNr+1), 400, JSON);
										}
										
									}
									$rowNr++; // 
									$row = fgetcsv($file, 0, "\t");
								}
								// wrap up the remaining data in $values
								if (!empty($values)) {
									if (!Expression::importData($values)) {
										$this->error("Duplicated row exists in the uploaded file", 400, JSON);
									}
								}
								// okay done, now end import
								Expression::endImport();
								$this->respond(["message" => "Upload okay"], 200, JSON);
							} else $this->error("An internal error has happened", 500, JSON);
						} else {
							$this->error("File cannot be accessed", 500, JSON);
						}
					} else {
						$this->error("No file is uploaded", 400, JSON);
					}
				} else {
					$this->error("Dataset with the id is not found", 400, JSON);
				}
			} elseif ($title) {
				// create condition
				$type = $this->filter($input, "type", "has", ["Dataset type is required", 400, JSON]);
				$condition = ExperimentalCondition::withData($input);
				if ($condition->insert()) {
					$this->respond(["uri" => "expression/editor?id=".$condition->id], 201, JSON);
				} else {
					$this->error("Duplicated data set with the name $title", 500, JSON);
				}
			} else {
				$this->error("Condition id or title is required", 400, JSON);
			}
		} else $this->error("Unaccepted", 400, $accept);
	}

	/**
	 * API: remove a dataset.
	 * API: remove a dataset
	 * URL: /expression?id=:conditionId
	 * URL Params: conditionId=[int]
	 * Method: DELETE
	 * Success Response:
	 * - code: 204, accept: JSON, content: null
	 * Error Response:
	 * - code: 406, accept: - , content: {message: "Unaccepted"}
	 * - code: 500, accept: JSON, content: {message: "An internal error has happened"}
	 **/
	public function delete ($input, $accept) {
		if ($accept == JSON) {
			UserController::authenticate(3, $accept);
			$id = $this->filter($input, "id", "/^\d+$/", ["Id is required", 400, $accept]);
			$condition = Expression::getCondition($id);
			if ($condition == null || Expression::deleteCondition($condition)) {
				$this->respond(null, 204, JSON);
			} else {
				$this->error("An internal error has happened: ".\Kiwi\Application::$conn->lastError, 500, JSON);
			}
		} else $this->error("Unaccepted", 406, $accept);
	}

	/**
	 * API: update the informataion of a dataset.
	 * API: update the informataion of a dataset
	 * URL: /expression?id=:conditionId
	 * URL Params: conditionId=[int]
	 * Method: PUT
	 * Success Response:
	 * - code: 204, accept: JSON, content: null
	 * Error Response:
	 * - code: 406, accept: - , content: {message: "Unaccepted"}
	 * - code: 404, accept: JSON , content: {message: "Not found"}
	 * - code: 500, accept: JSON, content: {message: "An internal error has happened: " + lasterror}
	 **/
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
					$this->error("An internal error has happened: ".\Kiwi\Application::$conn->lastError, 500, JSON);
				}
			} else $this->error("Not found", 404, JSON);
		} else $this->error("Unaccepted", 406, $accept);
	}

	/**
	 * API: get all conditions.
	 * API: get all conditions
	 * URL: /expression/condition
	 * Success Response:
	 * - code: 200, accept: JSON, content: [{id: 1, title: "heat treatment"}, ...]
	 * Error Response:
	 * - code: 406, accept: - 
	 * - code: 405, accept: -
	 */
	public function condition ($input, $accept, $method) {
		switch ($method) {
			case "GET":
				if ($accept == JSON) {
					// get all conditions
					$data = Expression::getConditions();
					$this->respond($data, 200, JSON);
				} else {
					$this->error("Unacceptable", 406, $accept);
				} 
				break;
			default:
				$this->error("Method not allowed", 405, $accept);
				break;
		}
	}

	/**
	 * API: create a importer page.
	 * API: create a importer page
	 * URL: /expression/importer
	 * Method: GET
	 * Success Response:
	 * - code: 200, accept: HTML
	 * Error Response:
	 * - code: 405, accept: - 
	 */
	public function importer ($input, $accept, $method) {
		UserController::authenticate(3, $accept);
		if ($method == "GET" && $accept == HTML) {
			$view = \Kiwi\View::loadFile("layout2.tpl");
			$view->set([
				"pageTitle" => "Import expression data",
				"headerTitle" => "Import expression data",
				"content" => "{{expression.importer.tpl}}",
				"jsAfterContent" => ["all.editor"],
				"vars" => [
					"type" => $input["type"],
					"types" => Expression::getAllConditionTypes()
				],
				"showFootNote" => "none"
			]);
			$this->respond($view, 200, HTML);
		} else {
			$this->error("Unaccepted", 405, $accept);
		}
	}

	/**
	 * API: list all datasets.
	 * API: list all datasets
	 * URL: /expression/list
	 * Method: GET
	 * Success Response:
	 * - code: 200, accept: HTML
	 * Error Response:
	 * - code: 405, accept: -
	 */
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
			$view = \Kiwi\View::loadFile("layout1.tpl");
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
		} else $this->error("Not available", 405, $accept);
	}

	/**
	 * API: view the dataset.
	 * API: view the dataset
	 * URL: /expression/viewer
	 * Method: GET
	 * Success Response:
	 * - code: 200, accept: HTML
	 * Error Response:
	 * - code: 405, accept: !HTML
	 */
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
			$view = \Kiwi\View::loadFile("layout1.tpl");
			$view->set($dataSet);
			$view->set([
				"pageTitle" => "Data set: ".$dataSet->title,
				"showFootNote" => "none",
				"content" => "{{expression.viewer.tpl}}",
				"pubmed" => $dataSet->pubmed ? "[pubmed|{$dataSet->pubmed}]" : "",
				"data" => \Kiwi\Utility::encodeCSV($data,",\t",null),
				"navlinks" => [
					["href" => "expression/list", "innerHTML" => "All data sets"],
				],
				"fullDataLink" => $dataSet->category == "gene-based" ? "expression?condition={{:id}}&__accept=CSV": "",
				"floatButtons" => [
					["icon" => "edit.svg", "href" => "expression/editor?id=$id"]
				]
			]);
			$this->respond($view, 200, HTML);
		} else $this->error("Not available", 405, $accept);
	}

	/**
	 * API: edit the dataset.
	 * API: edit the dataset
	 * URL: /expression/editor
	 * Method: GET
	 * Success Response:
	 * - code: 200, accept: HTML
	 * Error Response:
	 * - code: 405, accept: !HTML
	 * - code: 404, accept: HTML
	 */
	public function editor ($input, $accept, $method) {
		if ($accept == HTML && $method == "GET") {
			UserController::authenticate(3, HTML);
			$id = $this->filter($input, "id", "/^\d+$/", ["Id is required", 400, JSON]);
			$dataSet = Expression::getCondition($id);
			if ($dataSet) {
				$view = \Kiwi\View::loadFile("layout2.tpl");
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