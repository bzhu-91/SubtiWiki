<?php
class WikiController extends Controller {
    public function read ($input, $accept) {
        if ($input) {
            if (array_key_exists("id", $input) || array_key_exists("title", $input)) {
                $this->view($input, $accept);
            } elseif (array_key_exists("keyword", $input)) {
                $this->search($input, $accept);
            } elseif (array_key_exists("page", $input)) {
                $this->list($input, $accept);
            } else $this->error("Invalid arguments", 400, $accept);
        } else {
            $this->list([
                "page" => 1,
                "page_size" => 50,
            ], $accept);
        }
    }

    protected function list ($input, $accept) {
        $page = $this->filter($input, "page", "is_numeric", ["page number is required", 400, $accept]);
        $pageSize = $this->filter($input, "page_size", "is_numeric", ["page size is required", 400, $accept]);

        $articles = Wiki::getAll("1 order by title limit ?,?", [$pageSize*($page-1), $pageSize]);
		switch ($accept) {
			case HTML:
			$count = Wiki::count();
            $view = View::loadFile("layout1.tpl");
            $view->set([
                "title" => "All articles (page $page)",
                "content" => "{{wiki.list.tpl}}",
                "showFootNote" => "none",
                "jsAfterContent" => ["all.list"],
                "vars" => [
                    "type" => "wiki",
                    "max" => ceil($count / $pageSize),
                    "currentInput" => $input
                ],
            ]);
			if ($articles) {
                $view->set([
                    "data" => $articles,
                ]);
            } else {
                $view->set([
                    "messages" => ["No articles"],
                ]);
            }
            $this->respond($view, 200, HTML);
            break;
			case JSON:
				if ($articles) $this->respond(Utility::arrayColumns($articles, ["id", "title", "function"]), 200, JSON);
				else $this->error("Not found", 404, JSON);
				break;
		}
    }

    protected function search ($input, $accept) {
        $keyword = $this->filter($input, "keyword", "has", ["keyword is required", 400, $accept]);
        $mode = $this->filter($input, "mode", "has");

        switch ($mode) {
            case "blur":
                $articles = Wiki::getAll("article like ?", ["%".$keyword."%"]);
                break;
            case "exact":
            default:
                $articles = Wiki::getAll("title like ?", ["%".$keyword."%"]);
        }

        switch ($accept) {
            case HTML:  
                $view = View::loadFile("layout1.tpl");
                $view->set([
                    "title" => "Search for: $keyword",
                    "content" => "{{wiki.search.tpl}}",
                    "showFootNote" => "none",
                    "data" => $articles,
                    "message" => $articles ? "" : "No result"
                ]);
                $this->respond($view, 200, HTML);
                break;
            case HTML_PARTIAL:
                if ($articles) {
                    $view = View::load("{{wikiList:data}}");
                    $view->set("data", $articles);
                    $this->respond($view, 200, HTML);
                } else $this->error("No result", 404, HTML);
            case JSON:
                if ($articles) {
                    $data = Utility::arrayColumns($articles, ["id", "title"]);
                    $this->respond($data, 200, JSON);
                } else {
                    $this->error("Article not found", 404, JSON);
                }
        }
    }

    protected function view ($input, $accept) {
        if ($accept == HTML) {
            $id = $this->filter($input, "id", "is_numeric");
            $title = $this->filter($input, "title", "has");
            if (!$id && !$title) {
                $this->error("Article title and id is required", 400, $accept);
            } else if ($title) {
                $articles = Wiki::getAll(["title" => $title]);
                if ($articles) {
                    $article = $articles[0];
                }
            } else {
                $article = Wiki::get($id);
            }
            if ($article) {
                $article->updateCount();
                switch ($accept) {
                    case HTML:
                        $view = View::loadFile("layout1.tpl");
                        $view->set($article);
                        $view->set([
                            "pageTitle" => "{{:title}}",
                            "content" => "{{wiki.view.tpl}}",
                            "jsAfterContent" => ["wiki.view"],
                            "floatButtons" => [
                                ["href" => "wiki/editor?id={$article->id}", "icon" => "edit.svg"]
                            ]
                        ]);
                        $this->respond($view, 200, HTML);
                        break;
                    case HTML_PARTIAL:
                    case JSON:
                        $this->error("Unaccepted", 405, HTML);
                        break;
                }
            } elseif ($title) {
                $view = View::loadFile("layout1.tpl");
                $view->set($input);
                $view->set([
                    "content" => "This page does not exist. Would you like to <a href='wiki/editor?title={{:title}}'>create a page with the title: {{:title}}</a>?",
                    "showFootNote" => "none"
                ]);
                $this->respond($view, 200, HTML);
            } else {
                $this->error("Article not found", 404, HTML);
            }
        } else {
            $this->error("Unaccepted", 406, $accept);
        }
    }

    public function update ($input, $accept) {
        UserController::authenticate(1, $accept);
        if ($accept == JSON) {
            $id = $this->filter($input, "id", "is_numeric", ["Id is required to edit an article", 400, $accept]);
            $title = $this->filter($input, "title", "has", ["title is required", 400, $accept]);
            $article = Wiki::get($id);
            if ($article) {
                $article->title = $title;
                $article->article = $input["article"];
                $article->lastAuthor = User::getCurrent()->name;
                if ($article->update()){
                    $this->respond(["uri" => "wiki?id=$id"], 200, JSON);
                } else {
                    $this->error("Article with the same title already exitst", 500, JSON);
                }
            } else $this->error("Article not found", 404, JSON);
        } else $this->error("Unaccepted", 405, $accept);
    }

    public function delete ($input, $accept) {
        UserController::authenticate(1, $accept);
        if ($accept == JSON) {
            $id = $this->filter($input, "id", "is_numeric", ["id is required for deleting an article", 400, $accept]);
            $article = Wiki::get($id);
            if ($article) {
                if ($article->delete()) {
                    $this->respond(null, 204, JSON);
                } else {
                    $this->error("Deletion is not successful, please contact admin", 500, JSON);
                }
            } else {
                $this->respond(null, 204, JSON);
            }
        }
    }

    public function create ($input, $accept) {
        UserController::authenticate(1, $accept);
        if ($accept == JSON) {
            $title = $this->filter($input, "title", "has", ["title is required to create an article", 400, $accept]);
            $article = $this->filter($input, "article", "has");
    
            $wiki = new Wiki;
            $wiki->title = $title;
            $wiki->article = $article;
            $wiki->lastAuthor = User::getCurrent()->name;

            if ($wiki->insert()){
                $this->respond(["uri" => "wiki?id=".$wiki->id], 201, JSON);
            } else {
                $this->error("The article with same title already exists", 500, JSON);
            }
        } else $this->error("Unaccepted", 405, $accept);
    }

    public function editor ($input, $accept, $method) {
        UserController::authenticate(1, $accept);
        if ($accept == HTML && $method == "GET") {
            $id = $this->filter($input, "id", "is_numeric");
            $view = View::loadFile("layout2.tpl");
            if (!array_key_exists("id", $input)) {
                $method = "post";
                $pageTitle = "Create article";
            } elseif ($id) {
                $article = Wiki::get($id);
                if ($article) {
                    $method = "put";
                    $pageTitle = "Edit article";
                    $view->set($article);
                } else {
                    $this->error("Article not found", 404, HTML);
                }
            } else {
                $this->error("Invalide request", 400, $accept);
            }

            $view->set([
                "pageTitle" => $pageTitle,
                "headerTitle" => $pageTitle,
                "content" => "{{wiki.editor.tpl}}",
                "method" => $method,
                "jsAfterContent" => ["libs/quill.min", "wiki.editor", "all.editor"],
                "styles" => ["quill.snow"],
            ]);
            
            if ($article && $article->article) {
                $view->set("vars", [
                    "article" => $article->article
                ]);
            } else {
                if (array_key_exists("title", $input)) {
                    $view->set("title", $input["title"]); // pre-fill the title
                }
            }
            $this->respond($view, 200, HTML);
        } else $this->error("Unaccepted", 406, $accept);
    }
}
?>