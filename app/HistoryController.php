<?php
require_once("ViewAdapters.php");

class HistoryController extends \Monkey\Controller {
	public function read ($input, $accept) {
		if (!$input) {
			$this->index($accept);
		} elseif (array_key_exists("target", $input)) {
			$this->view($input, $accept);
		} elseif (array_key_exists("page", $input) || array_key_exists("user", $input)) {
			$this->list($input, $accept);
		} else $this->error("Invalid request", 400, $accept);
	}

	protected function index ($accept) {
		$this->list([
			"page" => 1,
			"page_size" => 100
		], $accept);
	}  

	protected function view ($input, $accept) {
		if ($accept == HTML) {
			$target = $this->filter($input, "target", "/\w/i", ["Invalid target", 400, HTML]);
			$id = $this->filter($input, "id", "has", ["Id is required", 400, HTML]);

			$className = ucfirst($target);

			$targetObject = $className::simpleGet($id);
			if (!$targetObject) {
				$lastRevision = History::findLastRevision($target, $id);
				$targetObject = $className::withData($lastRevision->record);
			}

			\Monkey\Utility::decodeLinkForView($targetObject->title);

			$records = History::getAll(["origin" => $target, "identifier" => $id]);
			usort($records, function ($a, $b) {
				return - strcmp($a->time, $b->time);
			});
			$view = \Monkey\View::loadFile("layout1.tpl");
			$view->set([
				"showFootNote" => "none",
				"records" => $records,
				"title" => "Edit history: $className: ".$targetObject->title,
				"pageTitle" => "Edit history: $className: ".$targetObject->title,
				"content" => "{{history:records}}{{jsvars:vars}}",
				"navlinks" => [
					["innerHTML" => $targetObject->title, "href" => $target."?id=".$id]
				],
				"jsAfterContent" => ["history.read"],
				"vars" => [
					"target" => $target,
					"id" => $id
				]
			]);
			$this->respond($view, 200, HTML);
		} else {
			// DO NOT SUPPORT NOW
		}
	}

	protected function list ($input, $accept) {
		if ($accept == HTML) {
			$page = $this->filter($input, "page", "is_numeric");
			if (is_null($page)) $page = 1;
			$pageSize = $this->filter($input, "page_size", "is_numeric");
			if (is_null($pageSize)) $pageSize = 100;
			$enableFilters = $this->filter($input, "filters", "/on/i");
			$user = $this->filter($input, "user", "/\w+/i");

			$message = "";
			$title = "Edits from all users";

			if ($enableFilters) {
				$filters = []; $operations = [];
				foreach ($input as $key => $value) {
					if (\Monkey\Utility::startsWith($key, "filter-")) {
						$filters[substr($key, 7)] = $value;
					}
					if (\Monkey\Utility::startsWith($key, "operation-")) {
						$operations[substr($key, 10)] = $value;
					}
				}
				if ($filters && $operations) {
					$result = History::getByFilter($filters, $operations, $user, $page, $pageSize);
					$count = $result["count"];
					$records = $result["records"];
				}
				if ($user && $user != "all") {
					$title = "Edits by: $user";
				}
			} elseif ($user && $user != "all") {
				$records = History::getAll("user = ? order by time desc limit ?,?", [$user, $pageSize*($page-1), $pageSize]);
				$count = History::count("user = ?", [$user]);
				$title = "Edits by: $user";
			} else {
				$records = History::getAll("1 order by time desc limit ?,?", [$pageSize*($page-1), $pageSize]);
				$count = History::count();
			}
			if ($records) {
				$table = [["Time", "Target", "User", "Operation"]];
				foreach ($records as $entry) {
					$table[] = $this->recordPresentation($entry);
				}
			} else {
				$message = "No result";
			}
			$view = \Monkey\View::loadFile("layout1.tpl");
			$view->set([
				"title" => $title,
				"pageTitle" => $title,
				"content" => "{{history.list.tpl}}",
				"css" => ["history.list"],
				"jsAfterContent" => ["all.list", "history.list"],
				"showFootNote" => "none",
				"page" => $page,
				"page_size" => $pageSize,
				"message" => $message,
				"history" => $table,
				"vars" => [
					"max" => ceil($count / $pageSize),
					"type" => "history",
					"currentInput" => $input
				]
			]);
			$this->respond($view, 200, HTML);
		} else {
			$this->error("Unaccepted", 405, $accept);
		}
	}

	protected function toRevisionLink ($object) {
		$className = get_class($object);
		$origin = lcfirst($className);
		$identifier = $object->{$className::$primaryKeyName};
		return "<a href='history?target=$origin&id=$identifier'>".$object->title."</a>";
	}

	protected function recordPresentation ($entry) {
		$presentation = $entry->lastOperation." ";
		switch ($entry->origin) {
			case 'gene':
				$gene = Gene::withData($entry->record);
				$presentation .= $this->toRevisionLink($gene);
				break;
			case 'category':
				$category = Category::withData($entry->record);
				$presentation .= $category->toLinkMarkup();
				break;
			case 'operon':
				$operon = Operon::withData($entry->record);
				$operon->title = preg_replace_callback("/\[\[gene\|([a-f0-9]{40})\]\]/i",function($match){
					$gene = Gene::simpleGet($match[1]);
					return "''".$gene->title."''";
				}, $operon->title);
				$presentation .= $this->toRevisionLink($operon);
				break;
			case 'reaction':
				// need to include reaction first
				$reaction = Reaction::withData($entry->record);
				$presentation .= "R{$reaction->id}";
				break;
			case 'regulon':
				$regulon = Regulon::withData($entry->record);
				$regulon->title = $regulon->getTitle();
				$presentation .= $this->toRevisionLink($regulon);
				break;
			case "pathway":
				$presentation .= "<a href='pathway?id={$entry->record->id}'>{$entry->record->title}</a>";
				break;
			case "wiki":
				$presentation .= "<a href='wiki?id={$entry->record->id}'>{$entry->record->title}</a>";
				break;
			case "metabolite":
				$metabolite = Metabolite::withData($entry->record);
				$presentation .= $metabolite->title;
				break;
			case 'complex':
				$complex = Complex::withData($entry->record);
				$presentation .= $this->toRevisionLink($complex);
				break;
			case 'geneCategory':
				if (is_string($entry->record->gene)) {
					$gene = Gene::simpleGet($entry->record->gene);
				} else {
					$gene = Gene::simpleGet($entry->record->gene->id);
				}
				if (is_string($entry->record->category)) {
					$category = Category::simpleGet($entry->record->category);
				} else {
					$category = Category::simpleGet($entry->record->category->id);
				}
				if (!$gene) {
					// in case gene was deleted
					$lastRevision = History::findLastRevision("gene", $entry->record->gene);
					$gene = Gene::withData($lastRevision->record);
				}
				if (!$category) {
					$lastRevision = History::findLastRevision("category", $entry->record->category);
					$category = Category::withData($lastRevision->record);
				}
				if ($entry->lastOperation == "remove") {
					$presentation .= $gene->toLinkMarkup()." from ".$category->toLinkMarkup();
				} elseif ($entry->lastOperation == "add") {
					$presentation .= $gene->toLinkMarkup()." to ".$category->toLinkMarkup();
				}
				break;
			case 'interaction':
				if (is_string($entry->record->prot1)) {
					$prot1 = Gene::simpleGet($entry->record->prot1);
					$prot2 = Gene::simpleGet($entry->record->prot2);
				} else {
					$prot1 = Gene::simpleGet($entry->record->prot1->id);
					$prot2 = Gene::simpleGet($entry->record->prot2->id);
				}
				if (!$prot1) {
					// in case gene was deleted
					$lastRevision = History::findLastRevision("gene", $entry->record->prot1);
					$prot1 = Gene::withData($lastRevision->record);
				}
				if (!$prot2) {
					// in case gene was deleted
					$lastRevision = History::findLastRevision("gene", $entry->record->prot2);
					$prot2 = Gene::withData($lastRevision->record);
				}
				$presentation .= $this->toRevisionLink($prot1)."-".$this->toRevisionLink($prot2);
				break;
			case 'paralogousProtein':
				if (is_string($entry->record->prot1)) {
					$prot1 = Gene::simpleGet($entry->record->prot1);
					$prot2 = Gene::simpleGet($entry->record->prot2);
				} else {
					$prot1 = Gene::simpleGet($entry->record->prot1->id);
					$prot2 = Gene::simpleGet($entry->record->prot2->id);
				}
				if (!$prot1) {
					// in case gene was deleted
					$lastRevision = History::findLastRevision("gene", $entry->record->prot1);
					$prot1 = Gene::withData($lastRevision->record);
				}
				if (!$prot2) {
					// in case gene was deleted
					$lastRevision = History::findLastRevision("gene", $entry->record->prot2);
					$prot2 = Gene::withData($lastRevision->record);
				}
				$presentation .= $this->toRevisionLink($prot1)."-".$this->toRevisionLink($prot2);
			case 'regulation':
				$regulator = History::parse($entry->record->regulator);
				$regulated = History::parse($entry->record->regulated);
				if ($regulated && $regulator) {
					$presentation .= "regulation: ".$this->toRevisionLink($regulator)." → ".$regulated->toLinkMarkup();
				} else {
					$presentation .= "regulation: Error with record. Please contact admin";
				}
				break;
			case "complexMember":
				$complex = Complex::simpleGet($entry->record->complex);
				if ($complex == null) {
					$lastRevision = History::findLastRevision("complex", $entry->record->complex);
					$complex = Complex::withData($lastRevision);
				}
				$member = History::parse($entry->record->member);
				if ($member) {
					$predicate = $entry->lastOperation == "add" ? " to " : " from ";
					$presentation .= $this->toRevisionLink($member).$predicate.$this->toRevisionLink($complex);
				} else {
					$presentation .= "Error with record. Please contact admin";
				}
				break;
			case 'reactionCatalyst':
				$reaction = Reaction::simpleGet($entry->record->reaction);
				if ($reaction == null) {
					$lastRevision = History::findLastRevision("reaction", $entry->record->reaction);
					$reaction = Reaction::withData($lastRevision);
				}
				$catalyst = Model::parse($entry->record->catalyst);
				if ($catalyst) {
					$predicate = $entry->lastOperation == "add" ? " to " : " from ";
					$presentation .= $this->toRevisionLink($catalyst).$predicate."<a href='history?target=reaction&id={$reaction->id}'>R{$reaction->id}: {$reaction->equation}</a>";
				}
				break;
			default:
				break;
			
		}

		return [
			$entry->time,
			$entry->origin,
			$entry->user,
			$presentation
		];
	}

	public function comparison ($input, $accept, $method) {
		if ($accept == HTML && $method == "GET") {
			$commits = $this->filter($input, "commits", "is_string", ["commits are required", 400, HTML]);
			$commits = explode(" ", $commits);
			if ($commits[0] != "current" && $commits[1] != "current") {
				$meta0 = History::get($commits[0]);
				$meta1 = History::get($commits[1]);
				
				\Monkey\Utility::decodeLinkForEdit($meta0->record);
				\Monkey\Utility::decodeLinkForEdit($meta1->record);

				if (get_class($meta1->origin) != get_class($meta0->origin)) {
					$this->error("Page not found", 404, HTML);
				}

			} elseif ($commits[0] != "current" || $commits[1] != "current") {
				$meta1 = History::get($commits[0] == "current" ? $commits[1] : $commits[0]);
				\Monkey\Utility::decodeLinkForEdit($meta1->record);

				$className = ucfirst($meta1->origin);
				$targetObject = $className::raw($meta1->identifier);
				if (!$targetObject) {
					// target was deleted already
					$targetObject = new stdClass;
				}

				\Monkey\Utility::decodeLinkForEdit($targetObject);
				$meta0 = (object) [
					"commit" => "current",
					"time" => "current",
					"record" => $targetObject
				];
			} else {
				$this->error("Invalid commits", 400, HTML);
			}

			if ($meta0 && $meta1) {
				$view = \Monkey\View::loadFile("layout1.tpl");
				$view->set([
					"title" => "Comparison",
					"pageTitle" => "Comparison",
					"content" => "{{history.comparison.tpl}}",
					"vars" => [
						"entry0" => $meta0,
						"entry1" => $meta1,
						"ignores" => ["lastUpdate", "lastAuthor", "count", "id"]
					],
					"jsAfterContent" => ["history.comparison"],
					"showFootNote" => "none",
					"css" => ["history.comparison"]
				]);
				$this->respond($view, 200, HTML);
			} else {
				$this->error("Page not found", 404, HTML);
			}

		} else $this->error("Invalid request", 406, $accept);
	}

	public function create ($input, $accept) {
		$this->error("Not allowed", 403, $accept);
	}

	public function delete ($input, $accept) {
		$this->error("Not allowed", 403, $accept);
	}

	public function update ($input, $accept) {
		$this->error("Not allowed", 403, $accept);
	}
}
?>