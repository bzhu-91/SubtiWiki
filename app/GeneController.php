<?php
require_once ("ViewAdapters.php");

class GeneController extends Controller {
	/** get method */		
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

	/* offers table like structure */
	protected function query ($input, $accept) {
		if ($accept == HTML) {
			// exporter
		} elseif ($accept == JSON) {
			$query = $this->filter($input, "query", "has", ["Invalid query", 400, JSON]);
			// increment the count
			Statistics::increment("geneExport");
			$ids = $this->filter($input, "ids", "has");
			$queries = explode(";", $query);
			foreach ($queries as &$keypath) {
				$keypath = new KeyPath($keypath);
			}
			if ($ids) {
				$ids = explode(",", $ids);
				$genes = Gene::getAll("id in (".implode(",", array_pad([], count($ids), "?")).")", $ids);
			} else {
				$genes = Gene::getAll(1);
			}
			if ($genes) {
				$header = ["id", "locus", "title"];
				foreach ($queries as $keypath) {
					$header[] = (string) $keypath;
				}
				$table = [$header];
				foreach ($genes as $gene) {
					$row = [$gene->id, $gene->locus, $gene->title];
					foreach ($queries as $keypath) {
						$row[] = $keypath->get($gene);
					}
					$table[] = $row;
				}
				$this->respond($table, 200, JSON);
			} else {
				$this->error("not found", 404, JSON);
			}
		} else {
			$this->error("Unaccepted", 406, $accept);
		}
	}

	/** no input */
	protected function index ($accept) {
		$this->list([
			"page" => 1,
			"page_size" => 150,
		], $accept);
	}

	/** view gene */
	protected function view ($input, $accept) {
		$id = $this->filter($input, "id", "/^[0-9a-f]{40}$/i", ["Invalid id", 400, $accept]);
		$gene = Gene::get($id);
		if ($gene) {
			Utility::clean($gene);
			Utility::decodeLinkForView($gene);
			$gene->updateCount(); // start counter

			$data = MetaData::sort($gene);
			
			if (count($gene->names) > 1) {
				array_pop($gene->names);
				$gene->names = implode(", ", $gene->names);
			} else {
				unset($gene->names);
			}
			switch ($accept) {
				case HTML:
					$view = View::loadFile("layout1.tpl");
					$this->autoAdapters($gene, $view);
					$this->setAdapters($gene, $view);
					$view->set($data);
					$view->set([
						"title" => "<i>{$gene->title}</i>",
						"pageTitle" => $gene->title,
						"content" => "{{gene.view.tpl}}",
						"titleExtra" => "<div id=strain>{{:strain}}</div>",
						"vars" => [
							"geneId" => $gene->id,
							"geneTitle" => $gene->title,
							"genomeLength" => $GLOBALS["GENOME_LENGTH"],
						],
						"jsAfterContent" => [
							"libs/genome.canvas", 
							"libs/interactome", 
							"libs/simpleTree", 
							"contextBrowser", 
							"gene.view",
							"pathwaySearch"
						],
	 					"navlinks" => [
								["onclick" => "pathwaySearch(\"{{:id}}\")", "innerHTML" => "Pathway"],
								["href" => "interaction?gene={{:id}}", "innerHTML" => "Interaction"],
								["href" => "expression?gene={{:id}}", "innerHTML" => "Expression"],
								["href" => "genome?gene={{:id}}", "innerHTML" => "Genome"],
								["href" => "regulation?gene={{:id}}", "innerHTML" => "Regulation"],
								["href" => "regulon?id=protein:{{:id}}", "innerHTML" => "Regulon"],
								["href" => "history?target=gene&id={{:id}}", "innerHTML" => "History"]
						],

						"css" => ["gene.view"],
						"side" => "{{structure:pdb}}{{expression:expid}}{{gene__interaction.view.tpl}}{{gene__regulation.view.tpl}}",
					]);
					if ($gene->{"The protein"}) {
						if ($gene->{'The protein'}->Structure) {
							$view->set("pdb", $gene->{"The protein"}->getStructures()[0]);
						}
					}
					if ($gene->outlinks->bsupath) {
						$view->set("expid", $gene->outlinks->bsupath);
					}
					if (User::getCurrent()) {
						$view->set([
							"floatButtons" => [
								["href" => "gene/editor?id=$id", "icon" => "edit.svg"]
							],
						]);
					}
					$this->respond($view, 200, HTML);
				case HTML_PARTIAL:
					$this->error("Unacceptable, please use gene::summary", 406, HTML);
					break;
				case JSON:
					$this->respond($gene, 200, JSON);
					break;
			}
		} else $this->error("Page not found", 404, $accept);
	}

	/**
	 * load template which is defined as gene__{keypath}.view.tpl to the corresponding keypath
	 * @param  Gene       $gene    gene
	 * @param  View       $view    view
	 * @param  KeyPath|null $keypath current keypath, for recursive call
	 * @return none		
	 */
	protected function autoAdapters ($gene, $view, KeyPath $keypath = null) {
		if ($keypath === null) {
			// use __ for delimiter incase windows
			$keypath = new KeyPath();
		}
		// get all the defined templates
		foreach ($gene as $key => $value) {
			$keypath = $keypath->push($key);
			$filename = "gene__".$keypath->toString("__").".view.tpl";
			if (file_exists(stream_resolve_include_path($filename))) {
				$view->registerAdapter((string) $keypath, function() use ($filename) {
					return "{{".$filename."}}";
				});
			} else if (is_object($value) || is_array($value)) {
				// recursively find all the templates avabile
				$this->autoAdapters($value, $view, $keypath);
			}
			$keypath = $keypath->pop();
		}
	}

	/**
	 * set view adapters only for gene.view
	 * @param none $view
	 */
	private function setAdapters ($gene, $view) {
		$view->registerAdapter("structure", function($data){
			if ($data) return "<h3>Structure</h3><p><a href='http://www.rcsb.org/structure/{$data}' target='_blank'>				<img id='structure' src='http://www.rcsb.org/pdb/images/{$data}_bio_r_500.jpg' style='width: 98%' />			</a></p>";
		});

		$view->registerAdapter("expression", function($data){
			if ($data) return "<h3>Expression</h3><p><a href='./expression?gene={{:id}}' target='_blank'><img src='http://genome.jouy.inra.fr/seb/images/details/{{:expid}}.png' style='width: 98%' /></a></p>";
		});

		$view->registerAdapter("Expression and Regulation->Operons->each", function($each) {
			$segment = View::loadFile("operon.view.tpl");
			$each->button = '<a href="operon?id={{:id}}" class="button" style="float:right;">Open in new tab</a>';
			$operon = Operon::withData($each);
			$data = MetaData::sort($operon);
			$segment->set($data);
			return "<div class='box'>".$segment->generate(true, true)."</div>";
		});

		$view->registerAdapter("The protein->id", function(){
			return "";
		});

		$view->registerAdapter("The protein->title", function(){
			return "";
		});

		$view->registerAdapter("mw", function($data){
			if ($data) {
				return "<div class='m_block'><div class='m_key m_inline'>Molecular weight</div><div class='m_value m_inline'>".number_format($data, 2)." kDa</div></div>";
			}
		});
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
			switch ($input["mode"]) {
				case "blur":
					$results = Gene::getAll(["data" => "%{$keyword}%"]);
					break;
				case "locus":
					$results = Gene::getAll(["locus" => "%{$keyword}%"]);
					break;
				case "title":
					$results = Gene::getAll("title like ? or _synonyms like ?", [$keyword, "%{$keyword}%"]);
					break;
				case "id":
					$results = Gene::getAll(["id" => $keyword]);
					break;
				default:
					$results = Gene::getAll("title like ? or _synonyms like ? or _locus like ? or id like ?", [$keyword,"%{$keyword}%", "%{$keyword}%", $keyword]);
			}	
			if (!$results) {
				$error = 404;
			}
		}
		switch ($accept) {
			case HTML:
				if (count($results) == 1) {
					header("Location: ".$GLOBALS["WEBROOT"]."/gene?id=".$results[0]->id);
				} else {
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
							"content" => "{{geneTable:genes}}",
							"genes" => $results,
						]);
					}
					$this->respond($view, 200, HTML);
				}

				break;
			case HTML_PARTIAL:
				if ($error) {
					$this->respond("<p>No result</p>", 200, HTML);
				} else {
					$view = View::load("{{geneTable:genes}}");
					$view->set("genes", $results);
					$this->respond($view, 200, HTML_PARTIAL);
				}
				break;
			case JSON:
				if ($error) {
					$this->error($messages[$error], $error, JSON);
				} else {
					$results = Utility::arrayColumns($results, ["id", "title", "function"]);
					Utility::decodeLinkForView($results);
					$this->respond($results, 200, JSON);
				}
				break;
		}
	}

	protected function list ($input, $accept) {
		$page = $this->filter($input, "page", "is_numeric", ["Invalid page number", 400, $accept]);
		$pageSize = $this->filter($input, "page_size", "is_numeric", ["Invalid page size", 400, $accept]);
		$genes = Gene::getAll("1 order by title limit ?,?", [$pageSize*($page-1), $pageSize]);
		switch ($accept) {
			case HTML:
			$count = Gene::count();
			if ($genes) {
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"title" => "All genes (page $page)",
						"content" => "{{all.list.tpl}}",
						"data" => $genes,
						"showFootNote" => "none",
						"jsAfterContent" => ["all.list"],
						"vars" => [
							"currentInput" => $input,
							"type" => "gene",
							"max" => ceil($count / $pageSize)
						],
					]);
					$this->respond($view, 200, HTML);
				} else $this->error("Not found", 404, HTML);
				break;
			case JSON:
				if ($genes) $this->respond(Utility::arrayColumns($genes, ["id", "title", "function"]), 200, JSON);
				else $this->error("Not found", 404, JSON);
				break;
		}
	}

	public function update ($input, $accept) {
		UserController::authenticate(1, $accept);
		if ($accept == JSON) {
			$id = $this->filter($input, "id", "/^[a-f0-9]{40}$/i", ["Invalid id", 400, $accept]);
			$old = Gene::raw($id);
			if ($old) {
				$new = Gene::withData($input);
				if ($new->title != $old->title) {
					// handle name change in gene
					if ($new->synonyms) {
						$new->synonyms = array_map("trim", explode(",", $new->synonyms));
						if (!in_array($old->title, $new->synonyms)) {
							$new->synonyms[] = $old->title;
						}
						if (in_array($new->title, $new->synonyms)) {
							$new->synonyms = array_unique(array_values(array_filter($new->synonyms, 
								function($a) use ($new) {
									return $a !== $new->title;
								}
							)));
						}
						$new->synonyms = implode(", ", $new->synonyms);
					} else {
						// MetaData::inserKeyValuePair: insert the key-value pair according to the scheme
						MetaData::insertKeyValuePair($new, "synonyms", $old->title);						
					}
				}
				Utility::encodeLink($new);
				MetaData::insertKeyValuePair($new, "lastUpdate", date("Y-m-d H:i:s"));	
				MetaData::insertKeyValuePair($new, "lastAuthor", User::getCurrent()->name);	
				// track the meta data change
				if ($new->replace()) {
					$this->respond(["uri" => "gene?id=".$new->id], 200, $accept);
				} else {
					$this->error("Internal error, please contact admin", 500, $accept);
				}
			} else $tihs->error("Gene not found, maybe it is deleted by other user", 404, $accept);
		} else $this->error("Unacceptable", 405, $accept);
	}

	public function create ($input, $accept) {
		UserController::authenticate(2, $accept);
		if ($accept == JSON) {
			$gene = Gene::withData($input);
			if ($gene->insert()) {
				$this->respond(["uri" => "gene?id=".$gene->id], 201, JSON);
			} else $this->error("Internal error", 500, JSON);
		} else $this->error("Unacceptable", 405, $accept);
	}

	public function blast ($input, $accept, $method) {
		if ($method == "GET") {
			$id = $this->filter($input, "id", "/[a-f0-9]{40}/i", ["Invalid id", 400, $accept]);
			$type = $this->filter($input, "type", "/^(dna|protein)$/i", ["Invalid type", 400, $accept]);
			$gene = Gene::simpleGet($id);
			if ($gene) {
				$gene->fetchSequences();
				if ($type == "dna") {
					header("Location: http://blast.ncbi.nlm.nih.gov/Blast.cgi?PROGRAM=blastn&PAGE_TYPE=BlastSearch&LINK_LOC=blasthome&QUERY={$gene->DNA}");
					return;
				} else if ($type == "protein") {
					header("Location: http://blast.ncbi.nlm.nih.gov/Blast.cgi?&PROGRAM=blastp&PAGE_TYPE=BlastSearch&LINK_LOC=blasthome&QUERY={$gene->aminos}");
					return;
				}
			}
		}
	}

	// requires geneId and admin password
	public function delete ($input, $accept) {
		UserController::authenticate(2, $accept);
		if ($accept == JSON) {
			$id = $this->filter($input, "id", "/[a-f0-9]{40}/i", ["Invalid id", 400, $accept]);
			$password = $this->filter($input, "password", "/".md5($GLOBALS["ADMIN_PASSWORD"])."/", ["Invalid admin password", 403, $accept]);
			$gene = Gene::simpleGet($id);
			if ($gene) {
				if ($gene->delete()) {
					$this->respond(null, 204, JSON);
				} else $this->error("Internal error", 500, JSON);
			} else $this->error("Gene not found", 404, JSON);
		} else $this->error("Unacceptable", 405, $accept);
	}

	public function random ($input, $accept) {
		$row = rand(1, Gene::count());
		$gene = Gene::getAll("1 limit ?,1", [$row])[0];
		switch ($accept) {
			case HTML:
				header("Location: ".$GLOBALS['WEBROOT']."/gene?id=".$gene->id);
				break;
			case JSON:
				$this->respond(Utility::arrayColumns($gene, ["id", "title", "function"]), 200, JSON);
				break;
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
		}
	}

	public function summary ($input, $accept, $method) {
		$id = $this->filter($input, "id", "/^[0-9a-f]{40}$/i", ["Invalid gene id", 400, $accept]);
		switch($method){
			case 'POST':
			case 'DELETE':
			case 'PUT':
			case 'PATCH':
				$this->error("Unacceptable method", 405, $accept);
		}
		$gene = Gene::raw($id);
		if($gene) {
			$summary = [];
			foreach ($gene as $key => $value) {
				if (!is_object($value) && !is_array($value) && $key != "genomicContext" && $value !== "[[this]]" || $key == "outlinks") {
					$summary[$key] = $value;
				}
			}
			$summary = (object) $summary;
			Utility::decodeLinkForView($summary);
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
					$view = View::loadFile("gene.summary.tpl");
					$this->autoAdapters($summary, $view);
					$view->set($summary);
					$this->respond($view, 200, HTML);
					break;
				case JSON:
					$this->respond($summary, 200, JSON);
					break;
			}
		} else $this->error("Gene not found", 404, $accept);
	}

	public function editor ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		if ($method != "GET") {
			$this->error("Unacceptable method", 406, $accept);
		}
		if ($accept != HTML) {
			$this->error("Unacceptable", 405, $accept);
		}
		$id = $this->filter($input, "id", "/^[a-f0-9]{40}$/i");
		if ($id) {
			$gene = Gene::raw($id);
			if ($gene) {
				Utility::decodeLinkForEdit($gene);
				$data = MetaData::fill($gene, "insert text here");
				$view = View::loadFile("layout2.tpl");
				$view->set([
					"pageTitle" => "Edit: ".$gene->title,
					"headerTitle" => "Edit: ".$gene->title,
					"method" => "put",
					"content" => "{{gene.editor.tpl}}",
					"styles" => ["all.editor","gene.editor"],
					"regulated" => $gene->toObjectMarkup(),
					"vars" => [
						"geneId" => $gene->id,
						"geneTitle" => $gene->title,
						"showDelBtn" => User::getCurrent()->privilege > 1
					],
					"jsAfterContent" => [
						"libs/monkey", 
						"tabs", 
						"gene.template",
						"all.editor", 
						"gene.editor", 
						"operon.editor", 
						"regulation.editor",
						"interaction.editor",
						"paralogue.editor",
						"category.selector"
					],
					"floatButtons" => [
						["icon" => "eye-outline.svg", "href" => "gene?id={$gene->id}"],
						["icon" => "top.svg", "href" => "javascript:window.scrollTo(0,0)"],
					]
				]);
				$view->restPrintingStyle = "monkey";
				$view->set($data);
				$this->respond($view, 200, HTML);
			} else $this->error("Not found", 404, $accept);
		}
	}

	public function migrate ($input) {
		$password = $this->filter($input, "password", "/^bzhu2018__$/i", ["Not found", 404, $accept]);
		$genes = Gene::getAll(1);
		foreach ($genes as $gene) {
			if ($gene->names) {
				Utility::insertAfter($gene, "synonyms", implode(", ", $gene->names), "names");
				unset($gene->names);
				$gene->replace(true);
				Log::debug($gene->title);
			}
		}
	}

	public function exporter ($input, $accept, $method) {
		if ($accept == HTML) {
			if ($method == "GET") {
				$view = View::loadFile("layout1.tpl");
				$view->set([
					"title" => "Gene export wizard",
					"content" => "<div id='exporter'></div>{{jsvars:vars}}",
					"vars" => [
						"scheme" => MetaData::get("Gene")->scheme
					],
					"jsAfterContent" => ["all.exporter"],
					"showFootNote" => "none",
					"navlinks" => [
						["innerHTML" => "Exports", "href" => "exports"],
					],
				]);	
				$this->respond($view, 200, HTML);
			} else $this->error("Unaccepted method", 406, $accept);
		} else $this->error("Unacceptable", 405, $accept);
	}


	/**
	 * importer function, provides an interface for data import and handles the data import
	 * @layout layout1.tpl
	 * @layout gene.importer.tpl
	 * @accept HTML
	 * @method POST/GET
	 */
	public function importer ($input, $accept, $method) {
		if ($accept != HTML) $this->error("Unaccepted", 406, $accept);
		UserController::authenticate(3, $accept);
		$errors = [];
		if ($method == "POST") {
			$tableName = Gene::$tableName;
			$mode = $this->filter($input, "mode", "/^(replace)|(patch)$/i");
			$type = $this->filter($input, "type", "/^(scalar)|(array)$/i");
			// check the existence of the table
			$conn = Application::$conn;
			$cols = $conn->getColumnNames($tableName);
			if (!$cols) $errors[] = "Table $tableName not found, please import the database structure please";
			if (!$mode) $errors[] = "Mode is required";
			if ($mode == "patch" && !$type) $errors[] = "Type is required for patch mode";
			if (!isset($_FILES["file"])) $errors[] = "No file uploaded";
			if ($_FILES["file"]["size"] > 1048576 * 2) $errors[] = "File too large, max. 2MB is accepted.";
			// no errors with the input data
			if (empty($errors)) {
				// get file content as csv
				$fileContent = file_get_contents($_FILES["file"]["tmp_name"]);
				$fileContent = str_replace("\r", "", $fileContent);
				$table = explode("\n", $fileContent);
				foreach($table as &$row) {
					$row = explode("\t", $row);
				}
				$header = array_shift($table);
				// now working on the data
				if ($mode == "replace") {
					if (in_array("locus", $header)) { // locus is required to ensure furture update
						if ($conn->doQuery("delete from `$tableName`")) {
							$hashId = !in_array("id", $header); // need to create hash id
							foreach($table as $i => &$row) {
								if (count($row) != count($header)) {
									foreach($row as &$field) {
										if ($field == "null") $field = null; // only need to handle nulls
									}
									$row = array_combine($header, $row);
									$row["lastAuthor"] = User::getCurrent()->name;
									$row = Utility::inflate($row);
									if ($hashId) $row["id"] = sha1(json_encode($row));
									$gene = Gene::withData($row);
									$gene->lastAuthor = User::getCurrent()->name;
									if (!$gene->insert()){
										$errors[] = "Error in line ".($i+1).": ".$conn->lastError;
									}
								} else {
									$errors[] = "Error in line ".($i+1).": row has missing or extra cells.";
								}
							}
						} else $errors[] = "Constraints violated, replace is not possible.";
					} else $errors[] = "locus column is needed for replace mode";
				} else {
					if (count($header) == 2) {
						if ($header[0] == "locus") {
							$dict = []; // locus => value
							foreach ($table as $i => $row) {
								if (count($row) == 2) {
									$dict[$row[0]] = $row[1];
								} else $errors[] = "Error on line ".($i+2).": line does not have 2 fields, line is ignored";
							}
							$allGenes = Gene::getAll(1);
							foreach($allGenes as $gene) {
								if (array_key_exists($gene->locus, $dict)) {
									if ($tpye == "array") $val = explode(";", $dict[$gene->locus]);
									else $val = $dict[$gene->locus];
									if (Utility::setValueFromKeypath($gene, $keypath, $val)) {
										$gene->lastAuthor = User::getCurrent()->name;
										if (!$gene->update()) $errors[] = "Update of gene with the locus {$gene->locus} is not successful.";
									} else $errors[] = "Update of gene with the locus {$gene->locus} is not successful, merge of the data failed.";
								}
							}
						} else $errors[] = "For patch mode, the first column of the uploaded file should be the locus";
					} else $errors[] = "For patch mode, max. the upload file should have only two columns";
				}
			}
			if (empty($errors)) $errors[] = "Import successful";
		}
		$view = View::loadFile("layout1.tpl");
		$view->set([
			"title" => "Importer for Gene table",
			"pageTitle" => "Importer for Gene table",
			"content" => "{{gene.importer.tpl}}",
			"showFootNote" => "none",
			"errors" => $errors
		]);
		$this->respond($view, 200, HTML);
	}
}
?>
