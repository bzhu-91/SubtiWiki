<?php
require "ViewAdapters.php";

class GenomeController extends \Monkey\Controller {

	public function read ($input, $accept) {
		switch ($accept) {
			case HTML:
				$tables = Genome::codonTable();
				$view = \Monkey\View::loadFile("layout2.tpl");
				$view->set([
					"pageTitle" => "Genome Browser",
					"headerTitle" => "Genome Browser",
					"content" => "{{genome.read.tpl}}",
					"styles" => ["genome.read"],
					"vars" => [
						"genomeLength" => $GLOBALS["GENOME_LENGTH"],
						"organismName" => $GLOBALS["ORGANISM_NAME"],
						"strainName" => $GLOBALS["STRAIN_NAME"],
						"codonTable" => $tables["codonTable"],
						"startTable" => $tables["startTable"],
					],
					"jsAfterContent" => ["tabs","libs/genome.canvas", "contextBrowser", "genome.read"],
				]);
				$this->respond($view, 200, HTML);
				break;
			default:
				$this->error("Not acceptable", 406, HTML);
				break;
		}
	}

	public function create ($input, $accept) {
		$this->error("Forbidden", 403, $accept);
	}

	public function delete ($input, $accept) {
		$this->error("Forbidden", 403, $accept);
	}

	public function update ($input, $accept) {
		if ($accept == JSON) {
			$object = $this->filter($input, "object", "/^\{gene\|[0-9a-f]{40}\}$/i", ["object is required", 400, HTML]);
			$contextObject = Genome::get($object);
			if ($contextObject) {
				$start = $this->filter($input, "start", "/^\d+$/", ["Start should be a positive number", 400, JSON]);
				$stop = $this->filter($input, "stop", "/^\d+$/", ["Stop should be a positive number", 400, JSON]);
				$strand = $this->filter($input, "strand", "/^(0|1)$/", ["Strand should be 0 or 1", 400, JSON]);
				if ($start > $stop) {
					$contextObject->stop = $start;
					$contextObject->start = $stop;
				} else {
					$contextObject->start = $start;
					$contextObject->stop = $stop;
				}
				$contextObject->$strand = $strand;
				if ($contextObject->update()) {
					$this->respond(["message" => "Update successful"], 200, JSON);
				}  else {
					$this->error("Internal error", 500, JSON);
				}
			} else $this->error("Not found", 404, JSON);
		} else $this->error("Unaccepted", 406, $accept);
	}

	public function context ($input, $accept, $method) {
		if ($method != "GET") {
			$this->error("Unaccepted method", 405, $accept);
		}
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
				break;
			case JSON:
				$span = $this->filter($input, "span", "is_numeric");
				if (!$span) {
					$span = 6000;
				}
				$geneId = $this->filter($input, "gene", "/^[a-f0-9]{40}$/i");
				$position = $this->filter($input, "position", "is_numeric");
				if ($geneId) {
					$data = Genome::findContextByGene($geneId, $span)				;
				} elseif ($position || $position === 0) {
					if ($span * 2 > $GLOBALS['GENOME_LENGTH']) {
						$l = 0;
						$r = $GLOBALS['GENOME_LENGTH'];
					} else {
						$l = (($position - $span) < 0) ? $position - ($span - $GLOBALS['GENOME_LENGTH']) : $position - $span;
						$r = (($position + $span) > $GLOBALS['GENOME_LENGTH']) ? $position + ($span - $GLOBALS['GENOME_LENGTH']) : $position + $span;
					}
					$data = Genome::findContextBySpan($l , $r);
				} else $this->error("Bad request", 400, JSON);
				if ($data) {
					$this->respond($data, 200, JSON);
				} else {
					$this->error("Data not found", 404, JSON);
				}
				break;
		}
	}

	public function sequence ($input, $accept, $method) {
		if ($method != "GET") {
			$this->error("Unaccepted method", 405, $accept);
		}
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
				break;
			case JSON:
				$geneId = $this->filter($input, "gene", "/^[a-f0-9]{40}$/i");
				$position = $this->filter($input, "position", "/^\d+_\d+_\d$/i");
				if ($geneId) {
					$sequence = Genome::findSequenceByGene($geneId);
				} elseif ($position) {
					$span = explode("_", trim($position));
					$s = (int) $span[0];
					$e = (int) $span[1];
					$strand = (int) $span[2];
					if ($s >= 1 && $s <= $GLOBALS['GENOME_LENGTH']) {
						if ($e >= 1 && $e <= $GLOBALS['GENOME_LENGTH']) {
							if ($strand == 0 || $strand == 1) {
								if ($s < $e) {
									$seq = Genome::findSequenceByLocation($s, $e, $strand);
									if ($seq) {
										$this->respond(["sequence" => $seq], 200, JSON);
									} else {
										$this->error("Data not found", 404, JSON);
									}
								} elseif ($s > $e) {
									$part1 = Genome::findSequenceByLocation($s, $GLOBALS["GENOME_LENGTH"], $strand);
									$part2 = Genome::findSequenceByLocation(1, $e, $strand);
									if ($strand == 0) {
										$this->respond(["sequence" => $part2.$part1], 200, JSON);
									} else {
										$this->respond(["sequence" => $part1.$part2], 200, JSON);
									}
								} else $this->respond(["sequence" => ""], 200, JSON);
							} else $this->error(400, "Invalid strand, should be 0 or 1", JSON);
						} else $this->error(400, "Invalid start point", JSON);
					} else $this->error(400, "Invalid end point", JSON);
				} else $this->error(400, "Invalid input", JSON);
				break;
		}
	}

	public function importer ($input, $accept, $method) {
		if ($accept != HTML) {
			$this->error("Unaccepted", 406, $accept);
		}
		$errors = [];
		if ($method == "POST") {
			$tableName = Genome::$tableName;
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

				if ($mode == "replace") {
					// remove table content
					if (!$conn->doQuery("delete from `$tableName`")) {
						$errors[] = "Internal error, can not delete from table, replace is not successful";
					}
				}

				if (empty($errors)) {
					// gene import
					if (in_array("locus", $header)) { 
						// check table structure
						$tableOkay = true;
						$required = ["start", "stop", "strand"];
						foreach($required as $key) {
							if (!in_array($key, $header)) {
								$tableOkay = false;
								$errors[] = "Required column missing: $key";
							}
						}
						
						if ($tableOkay) {
							foreach($table as $i => $row) {
								if (count($row) == count($header)) {
									$row = array_combine($header, $row);
									$genes = Gene::getAll(["locus" => $row["locus"]]);
									if ($genes) {
										$gene = $genes[0];
										$row["object"] = (string) $gene;
										if ($row["start"] > $row["stop"]) {
											\Monkey\Utility::swap($row["start"], $row["stop"]); // swap, keep start < stop
										}
										if(!$conn->insert(Genome::$tableName, $row)) {
											$errors[] = "Error in line ".($i+2).": ".$conn->lastError;
										}
									} else $errors[] = "Error in line ".($i+2).": gene with locus ".$row["locus"]." not found";
								} else $errors[] = "Error in line ".($i+2).": row has missing or extra cell.";
							} 
						}
					} elseif (in_array("object", $header)){
						// check table structure
						$tableOkay = true;
						$required = ["position", "strand"];
						foreach($required as $key) {
							if (!in_array($key, $header)) {
								$tableOkay = false;
								$errors[] = "Required column missing: $key";
							}
						}
						if ($tableOkay) {
							foreach($table as $i => $row) {
								if (count($row) == count($header)) {
									$row = array_combine($header, $row);
									$row["start"] = $row["stop"] = $row["position"];
									if(!$conn->insert(Genome::$tableName, $row)) {
										$errors[] = "Error in line ".($i+2).": ".$conn->lastError;
									}
								} else $errors[] = "Error in line ".($i+2).": row has missing or extra cell.";
							}
						}
					} else $errors[] = "Required column missing: locus or object";
				}
			}
			if (empty($errors)) $errors[] = "Import successfull";
		}

		$view = \Monkey\View::loadFile("layout1.tpl");
		$view->set([
			"title" => "Import genomic context data",
			"pageTitle" => "Import genomic context data",
			"content" => "{{genome.importer.tpl}}",
			"showFootNote" => "none",
			"errors" => $errors
		]);
		$this->respond($view, 200, HTML);
	}

	public function editor ($input, $accept, $method) {
		if ($method == "GET") {
			if ($accept == HTML || $accept == HTML_PARTIAL) {
				$object = $this->filter($input, "object", "has", ["object is required", 400, HTML]);
				$contextObject = Genome::get($object);
				if ($contextObject) {
					if ($accept == HTML) {
						$view = \Monkey\View::loadFile("layout1.tpl");
						$view->set([
							"jsAfterContent" => ["all.editor"],
							"title" => "Edit genomic context",
							"pageTitle" => "Edit genomic context"
						]);
					} else {
						$view = \Monkey\View::loadFile("genome.context.editor.tpl");
					}
					$view->set($contextObject);
					$this->respond($view, 200, HTML);
				} else $this->error("Not found", 404, $accept);
			} else $this->error("Unaccepted", 406, $accept);
		} else $this->error("Unaccepted method", 405, $accept);
	}
}
?>