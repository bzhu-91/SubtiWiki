<?php
require_once ("ViewAdapters.php");

class CategoryController extends \Monkey\Controller {

	/**
	 * correspond to get method
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public function read ($input, $accept) {
		if ($input) {
			if (array_key_exists("id", $input)) {
				$this->view($input, $accept);
			} else if (array_key_exists("gene", $input)) {
				$this->findForGene($input, $accept);
			} elseif (array_key_exists("keyword", $input)) {
				$this->search($input, $accept);
			}
		} else $this->index($accept);
		$this->error("Page not found", 404, $accept);
	}

	/**
	 * index page, when no input data is provided
	 * @param  enum $accept response content type
	 * @return none         
	 */
	protected function index ($accept) {
		$data = Category::getAll("1");
		usort($data, ["Category", "compare"]);
		Statistics::increment("categoryIndex");
		switch ($accept) {
			case JSON:
				// print only id and title
				$data = \Monkey\Utility::arrayColumns($data, ["id", "title"]);
				$this->respond($data, 200, JSON);
				break;
			case HTML_PARTIAL:
			case HTML:
				$view = \Monkey\View::loadFile("layout1.tpl");
				$view->set([
					"title" => "All categories",
					"pageTitle" => "All categories",
					"content" => "<div style='margin-left:-30px' rewrite='true'>{{categoryTree:data}}</div>",
					"data" => $data,
					"jsAfterContent" => ["pubmed", "category.index"],
					"navlinks" => [
							["href" => "/", "innerHTML" => "Home"],
							["href" => "pathway", "innerHTML" => "Pathway"],
							["href" => "interaction", "innerHTML" => "Interaction"],
							["href" => "expression", "innerHTML" => "Expression"],
							["href" => "genome", "innerHTML" => "Genome"],
							["href" => "regulation", "innerHTML" => "Regulation"],
					],
					"showFootNote" => "none"
				]);
				$this->respond($view);
			default:
				break;
		}
	}

	protected function search ($input, $accept) {
		$keyword = $input["keyword"];
		$messages = [400 => "Keyword too short", 404 => "No results found"];
		$error = null;
		if (strlen($keyword) < 2) {
			$error = 400;
		} else {
			$results = Category::getAll("title like ? ", ["%{$keyword}%"]);
			if (!$results) {
				$error = 404;
			}
		}
		switch ($accept) {
			case HTML:
				if (count($results) == 1) {
					header("Location: ".$GLOBALS["WEBROOT"]."/category?id=".$results[0]->id);
				} else {
					$view = \Monkey\View::loadFile("layout1.tpl");
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
							"genes" => $results,
						]);
					}
					$this->respond($view, 200, HTML);
				}
				break;
			case JSON:
				if ($error) {
					$this->error($messages[$error], $error, JSON);
				} else {
					$results = \Monkey\Utility::arrayColumns($results, ["id", "title"]);
					\Monkey\Utility::decodeLinkForView($results);
					$this->respond($results, 200, JSON);
				}
				break;
		}
	}

	protected function findForGene ($input, $accept) {
		$geneId = $this->filter($input, "gene", "/^[a-f0-9]{40}$/i", ["Invalid gene id", 400, $accept]);
		$gene = Gene::simpleGet($geneId);
		$data = $gene->has("categories");
		switch ($accept) {
			case HTML:
			$this->error("Unaccepted", 406, $accept);
			break;
			case HTML_PARTIAL:
			if ($data) {
				$str = "";
				foreach ($data as $row) {
					$str .= "<p>".$row->category->toLinearPresentation()."</p>";
				}
				$this->respond($str, 200, HTML);
			} else $this->respond("", 404, HTML);
			break;
			case JSON:
			if ($data) {
				foreach ($data as &$row) {
					$row = $row->toLinearPresentation();
				}
				$this->respond($data, 200, JSON);
			}
			break;
		}
	}

	/**
	 * view page, when id is given
	 * @param  array $input  client-side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	protected function view ($input, $accept) {
		$id = $this->filter($input, "id", "/^SW(\.\d+)*$/i", ["Page not found", 404, $accept]);
		// the virutal root node
		if ($id === "SW") {
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
					header("Location: ".$GLOBALS["WEBROOT"]."/category");
					break;
				case JSON:
					$children = Category::getAll("id regexp ?", ["^[[:digit:]]+\.$"]);
					$children = \Monkey\Utility::arrayColumns($children, ["id", "title"]);
					$data = [
						"children" => $children,
						"self" => ["id" => ".", "title" => "All categories"]
					];
					$this->respond($data, 200, JSON);
			}
		} else if (($category = Category::get($id))) {
			\Monkey\Utility::decodeLinkForView($category);
			$category->updateCount();
			switch ($accept) {
				case JSON:
					$this->respond($category, 200, JSON);
					break;
				case HTML:
				case HTML_PARTIAL:
					$view = \Monkey\View::loadFile("layout1.tpl");
					$genes = $category->getGenes();
					if ($category->children && $category->countGenesAll() < 200) {
						$view->set("recursion", $this->createChildCategorySegment($category->children));
						unset($category->children);
					}
					$view->set($category);
					$view->set([
						"self" => [$category],
						"pageTitle" => "Category: ".$category->title,
						"title" => "Category: ".$category->title,
						"content" => "{{category.view.tpl}}",
						"side" => "<h3>Sibling categories</h3>{{categoryList:siblings}}",
						"navlinks" => [
							["href" => "/", "innerHTML" => "Home"],
							["href" => "pathway", "innerHTML" => "Pathway"],
							["href" => "interaction", "innerHTML" => "Interaction"],
							["href" => "expression", "innerHTML" => "Expression"],
							["href" => "genome", "innerHTML" => "Genome"],
							["href" => "regulation", "innerHTML" => "Regulation"],
						],
						"genes" => $genes
					]);
					if (User::getCurrent()) {
						$view->set([
							"floatButtons" => [
								["href" => "category/editor?id=$id", "icon" => "edit.svg"]
							]
						]);
					}
					$this->respond($view, 200, HTML);
			}
		} else $this->error("Category with id $id not found", 404, $accept);
	}

	// recursive function to load all child categories with genes displayed
	/**
	 * recursive functino to load all child categories with genes displayed
	 * @param  [Category] $children array of categories
	 * @return String           the html presentation of those child categories
	 */
	private function createChildCategorySegment($children) {
		$str = "";
		$segTpl = "{{categoryTree:self}}{{:children}}<div style='margin-left:{{:depth}}px'>{{relationGeneTable:genes}}{{:rest}}</div>";
		foreach ($children as $child) {
			$child->patch();
			$genes = $child->getGenes();
			$seg = \Monkey\View::load($segTpl);
			$seg->set([
				"self" => [$child],
				"depth" => $child->getDepth() * 30,
				"genes" => $genes
			]);
			// recursive handle the children
			if ($subs = $child->children)  {
				$seg->set("children", $this->createChildCategorySegment($subs));
			} else{
				$genes = $child->genes;
				usort($genes, function($a, $b) {
					return strcmp($a->gene->title, $b->gene->title);
				});
				$seg->set("genes", $genes);
			}
			$str .= $seg->generate(1,1);
		}
		return $str;
	}

	public function update ($input, $accept) {
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Unacceptable", 406, HTML);
				break;
			case JSON:
				UserController::authenticate(1, JSON);
				$id = $this->filter($input, "id", "/^SW(\.\d+)+$/i", ["Invalid category id", 400, JSON]);
				$category = Category::withData($input);
				$category->lastAuthor = User::getCurrent()->name;
				try {
					$result = $category->update();
					if ($result) {
						$this->respond(["uri" => "category?id=".$category->id], 200, JSON);
					} else $this->error("An unexpected error has happened. Update is not successful.", 500, JSON);
				} catch (BaseException $e) {
					$this->error($e->getMessage(), 500, JSON);
				}
				break;
		}
	}

	/**
	 * create a new category under the parent category
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public function create ($input, $accept) {
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Unacceptable", 406, HTML);
				break;
			case JSON:
				UserController::authenticate(2, JSON);
				$title = $this->filter($input, "title", "is_string", ["Invalid title", 400, JSON]);
				$parentId = $this->filter($input, "parentId", "/^SW(\.\d+)*$/i", ["Invalid parent category id", 400, JSON]);
				$parent = Category::get($parentId);
				$child = Category::withData([
					"title" => $title,
					"lastAuthor" => User::getCurrent()->name,
				]);
				if ($parent) {
					if (!$parent->genes) {
						try {
							$result = $parent->addChildCategory($child);
							if ($result) $this->respond(["uri" => "category/editor?id=".$child->id], 201, JSON);
							else $this->error("An unexpected error has happened. New category is not saved.", 500, JSON);
						} catch (BaseException $e) {
							$this->error($e->getMessage(), 500, JSON);
						}
					} else $this->error("New category cannot be created because the parent category has genes assigned to it", 500, JSON);
				} else $this->error("Parent category not found", 404, JSON);
				break;
		}
	}

	/**
	 * remove a category which has no gene or children
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public function delete ($input, $accept) {
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
 				$this->error("Unacceptable", 406, HTML);
				break;
			case JSON:
				UserController::authenticate(2, JSON);
				$id = $this->filter($input, "id", "/^SW(\.\d+)+$/i", ["Invalid category id", 400, JSON]);
				$category = Category::get($id);
				if ($category) {
					if ($category->genes) {
						$this->error("This category can not be removed because there are genes assigned to it", 500, JSON);
					} elseif ($category->children) {
						$this->error("This category can not be removed because it has subcategories", 500, JSON);
					} elseif ($category->delete()){
						$this->respond(null, 204, JSON);
					} else {
						$this->error("An unexpected error has happened. Deletion is not successful", 500, JSON);
					}
				} else $this->respond(null, 204, JSON);
		}
	}

	/**
	 * provide editor of the category
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public function editor ($input, $accept) {
		UserController::authenticate(1, $accept);
		$user = User::getCurrent();
		switch ($accept) {
			case HTML:
				$id = $this->filter($input, "id", "/^SW(\.\d+)*$/i", ["Page not found", 404, $accept]);
				$category = Category::raw($id);
				if ($category) {
					\Monkey\Utility::decodeLinkForEdit($category);
					$genes = $category->has("genes");
					$children = $category->fetchChildCategories();
					$parents = $category->fetchParentCategories();

					$depth = $category->getDepth() + 1;

					if (!property_exists($category, "description")) {
						$category->description = ["insert text here"];
					}
					if (!property_exists($category, "reference")) {
						$category->reference = ["insert text here"];
					}
					$view = \Monkey\View::loadFile("layout2.tpl");
					$view->restPrintingStyle = "monkey";
					$view->set($category);
					$view->set([
						"pageTitle" => "Edit category",
						"headerTitle" => "Edit category",
						"content" => "{{category.editor.tpl}}",
						"genes" => $category->has("genes"),
						"self" => [$category],
						"jsAfterContent" => ["all.editor", "category.editor", "libs/monkey"],
						"showDelBtn" => $user->privilege >= 2 ? "auto" : "none"
					]);

					if (empty($category->genes)) {
						$view->set([
							"addNewButton" => "<button class='addBtn button' target='category' style='margin-left:".($depth*30)."px'>Add new category here</button>",
						]);
					}
					if (empty($genes) && empty($category->children)) {
						$view->set("new", "true");
						$view->set("genes", true);
					} else {
						$view->set("new", "false");
					}
					$this->respond($view, 200, HTML);
				} else $this->error("Category not found", 404, $accept);
				break;
			case HTML_PARTIAL:
				// the category selector in the gene's editing page
				$geneId = $this->filter($input, "gene", "/[a-f0-9]{40}/i", ["gene id is required", 400, HTML_PARTIAL]);
				$gene = Gene::simpleGet($geneId);
				if ($gene) {
					$inCategories = $gene->has("categories");
					$view = \Monkey\View::load("{{relationCategoryEdit:data}}");
					$view->set("data", $inCategories);
					$this->respond($view, 200, HTML);
				} else {
					$this->error("Gene not found", 404, HTML_PARTIAL);
				}
				break;
			case JSON:
				$this->error("Unacceptable", 406, JSON);
				break;
		}
	}

	/**
	 * assign gene to category
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public function assignment ($input, $accept, $method) {
		UserController::authenticate(1, $accept);
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Unacceptable", 406, HTML);
				break;
			case JSON:
				switch ($method) {
					case 'DELETE':
						$geneId = $this->filter($input, "gene", "/^[0-9a-f]{40}$/i", ["Invalid gene id", 400, JSON]);
						$categoryId = $this->filter($input, "category", "/^SW(\.\d+)+$/i", ["Invalid category id", 400, JSON]);
						$category = Category::get($categoryId);
						$gene = Gene::get($geneId);
						if ($category) {
							if ($gene) {
								if ($category->removeGene($gene)) {
									$this->respond(null, 204, JSON);
								} else {
									$this->error("An unexpected error has happened.", 500, JSON);
								}
							} else $this->error("Gene not found", 404, JSON);
						} else $this->error("Category not found", 404, JSON);
						break;
					case 'POST':
						$geneId = $this->filter($input, "gene", "/^[0-9a-f]{40}$/i", ["Invalid gene id", 400, JSON]);
						$categoryId = $this->filter($input, "category", "/^SW(\.\d+)+$/i", ["Invalid category id", 400, JSON]);
						$category = Category::get($categoryId);
						$gene = Gene::get($geneId);
						if ($category && $gene) {
							$data = [
								"lastAuthor" => User::getCurrent()->name
							];
							if ($category->addGene($gene, $data)) {
								$this->respond(null, 201, JSON);
							} else {
								$this->error("Internal error", 500, JSON);
							}
						} else $this->error("Gene or category not found.", 500, JSON);
						
						break;	
					default:
						$this->error("Unaccepted method", 405, JSON);
						break;
				}
		}
	}

	public function exporter ($input, $accept, $method) {
		if ($method == "GET") {
			Statistics::increment("categoryExport");
			$all = Category::getAll(1);
			switch ($accept) {
				case HTML:
				case CSV:
				case HTML_PARTIAL:
					$csv = [["id", "category"]];
					foreach ($all as $row) {
						$csv[] = [
							$row->id,
							$row->title,
						];
					}
					$this->respond(\Monkey\Utility::encodeCSV($csv), 200, CSV);
					break;
				case JSON:
					$json = \Monkey\Utility::arrayColumns($all, ["id", "title"]);
					$this->respond($json, 200, JSON);
					break;
			}
		} else $this->error("Unaccepted method", 406, $accept);
	}

	public function assignmentExporter ($input, $accept, $method) {
		if ($method == "GET") {
			Statistics::increment("geneCategoryExport");
			$assignment = Gene::hasPrototype("categories");
			$all = $assignment->getAll();
			switch ($accept) {
				case HTML:
				case CSV:
				case HTML_PARTIAL:
					$csv = [["category id", "category", "gene locus", "gene name"]];
					foreach ($all as $row) {
						$csv[] = [
							$row->category->id,
							$row->category->title,
							$row->gene->locus,
							$row->gene->name
						];
					}
					$this->respond(\Monkey\Utility::encodeCSV($csv), 200, CSV);
					break;
				case JSON:
					$json = [];
					foreach ($all as $row) {
						$json[] = [
							"gene" => [
								"locus" => $row->gene->locus,
								"title" => $row->gene->title,
							],
							"category" => [
								"id" => $row->category->id,
								"title" => $row->category->title
							]
						];
					}
					$this->respond($json, 200, JSON);
					break;
			}
		} else $this->error("Unaccepted method", 406, $accept);
	}
}
?>