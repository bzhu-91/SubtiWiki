<?php
require_once ("ViewAdapters.php");
class PubmedController extends \Kiwi\Controller {
	public function read ($input, $accept) {
		if ($input) {
			if (array_key_exists("ids", $input)) {
				$this->view($input, $accept);
			} else if (array_key_exists("keyword", $input)) {
				$this->search($input, $accept);
			} else if (array_key_exists("page", $input)) {
				$this->list($input, $accept);
			}
		} else $this->index($accept);
	}

	protected function index ($accept) {
		$this->list(["page" => 1, "page_size" => 30], $accept);
	}

	protected function view ($input, $accept) {
		// accept ",", space, tab, ";" as delimiter
		$ids = $this->filter($input, "ids", "/^(\s*\d+\s*(\s|,|;)?)+$/i", ["invalide pmids", 400, $accept]);
		$ids = preg_replace("/\s+/i", ",", $ids);
		$ids = preg_replace("/;/i", ",", $ids);
		$ids = explode(",", $ids);
		sort($ids);
		$reports = [];
		foreach ($ids as $id) {
			if (($id = trim($id))) {
				$pubmed = Pubmed::get(trim($id));
				if ($pubmed) {
					$reports[] = $pubmed->report;
				} else {
					$pubmed = Pubmed::download($id);
					if ($pubmed) {
						$reports[] = $pubmed->report;
					} else $reports[] = "Error: citation with PMID: $id can not be downloaded.";
				}
			}
		}
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->respond(implode("", $reports), 200, HTML);
				break;
			case JSON:
				$this->respond($reports, 200, JSON);
				break;
		}
	}


	protected function search ($input, $accept) {
		if (JSON == $accept) {
			$this->error("Unacceptable", 405, $accept);
		}
		$keyword = $this->filter($input, "keyword", "/^[\w\- äüöß]{3,}$/i");
		if ($keyword === null) {
			$content = "Error: keyword too short or contains unacceptable characters";
		} else {
			$citations = Pubmed::getAll("report like ?", ["%{$keyword}%"]);
			if ($citations) {
				usort($citations, function($a, $b){
					return $a->id - $b->id;
				});
				$content = "";
				foreach ($citations as $each) {
					$content .= $each->report;
				}
			} else {
				$content = "Not found";
			}
		}
		$view = \Kiwi\View::loadFile("layout1.tpl");
		$view->set([
			"result" => $content, 
			"content" => "{{pubmed.search.tpl}}", 
			"keyword" => $keyword, 
			"title" => "Search result for: $keyword", 
			"pageTitle" => "Search for paper",
			"showFootNote" => "none",
		]);
		$this->respond($view, 200, HTML);
	}

	protected function list ($input, $accept) {
		$page = $this->filter($input, "page", "is_numeric", ["Invalid page number", 400, $accept]);
		$pageSize = $this->filter($input, "page_size", "is_numeric", ["Invalid page size", 400, $accept]);
		$citations = Pubmed::getAll("1 order by id limit ?,?", [$pageSize*($page-1), $pageSize]);

		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				if ($citations) {
					$count = Pubmed::count();
					$reports = "";
					foreach ($citations as $citation) {
						$reports .= $citation->report;
					}
					$view = \Kiwi\View::loadFile("layout1.tpl");
					$view->set([
						"reports" => $reports, 
						"content" => "{{pubmed.list.tpl}}", 
						"title" => "All citations (page $page)", 
						"pageTitle" => "All citations (page $page)",
						"showFootNote" => "none",
					]);
					if ($page == 1) $view->set("previous", "pubmed");
					else $view->set("previous", "pubmed?page=".($page-1)."&page_size=$pageSize");
					if ($page >= $count/$pageSize) $view->set("next", "pubmed");
					else $view->set("next", "pubmed?page=".($page+1)."&page_size=$pageSize");
					$this->respond($view, 200, HTML);
				} else $this->error("Not found", 404, HTML);
				break;
			case JSON:
				if ($genes) $this->respond($citations, 200, JSON);
				else $this->error("Not found", 404, JSON);
				break;
		}
	}

	public function create ($input, $accept) {
		$this->error("Forbidden", 403, $accept);
	}
	public function update ($input, $accept) {
		$this->error("Forbidden", 403, $accept);
	}
	public function delete ($input, $accept) {
		$this->error("Forbidden", 403, $accept);
	}
}
?>