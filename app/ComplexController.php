<?php
require_once("ViewAdapters.php");

/**
 * Provides operations on Complexes.
 * 
 * RESTful API summary:
 * 
 */
class ComplexController extends Controller {

    /**
     * API: GET:*
     */
    public function read ($input, $accept) {
        if ($input) {
            if (array_key_exists("id", $input)) {
                $this->view($input, $accept);
            } elseif (array_key_exists("keyword", $input)) {
                $this->search($input, $accept);
            } elseif (array_key_exists("page", $input)) {
                $this->list($input, $accept);
            } else {
                $this->error("bad request", 400, $accept);
            }
        } else {
            $this->list([
                "page" => 1,
                "page_size" => 150
            ], $accept);
        }
    }

    protected function search ($input, $accept) {
        $keyword = $this->filter($input,"keyword", "/^.{2,}$/i");
        if ($accept == JSON) {
            if ($keyword) {
                $data = Complex::getAll("title like ?", ["%".$keyword."%"]);
                if ($data ){
                    $this->respond($data, 200 ,JSON);
                } else {
                    $this->error("Not found", 404, JSON);
                }
            } else {
                $this->error("Keyword is required and should belonger than 2 characters", 400, $accept);
            }
        }
    }

    protected function list ($input, $accept) {
        $page = $this->filter($input, "page", "/^\d+$/", ["Page number is required", 400, $accept]);
        $pageSize = $this->filter($input, "page_size", "/^\d+$/", ["Page size is required", 400, $accept]);

        $all = Complex::getAll("1 limit ?,?", [$pageSize*($page-1), $pageSize]);

        switch ($accept) {
            case HTML:
            case HTML_PARTIAL:
                $count = Complex::count();
                $view = View::loadFile("layout1.tpl");
                $view->set([
                    "content" => "{{all.list.tpl}}",
                    "jsAfterContent" => ["all.list"],
                    "title" => "All complexes (page $page)",
                    "pageTitle" => "All complexes (page $page)",
                    "showFootNote" => "none",
                    "vars" => [
                        "type" => "complex",
                        "currentInput" => $input,
                        "max" => $count ? ceil($count / $pageSize):0
                    ]
                ]);
                if ($all) {
                    $view->set([
                        "data" => $all,
                    ]);
                    $this->respond($view, 200, HTML);
                } else {
                    $view->set([
                        "messages" => [
                            "No complex found"
                        ],
                    ]);
                    $this->respond($view, 200, HTML);
                }
                break;
            case JSON:
                if ($all) {
                    $this->respond($all, 200, JSON);
                } else {
                    $this->error("Not found", 404, JSON);
                }
                break;
            case CSV:
                if ($all) {
                    $csvData = [["id", "title"]];
                    foreach($all as $complex) {
                        $csvData[] = [
                            $complex->id,
                            $complex->title
                        ];
                    }
                    $this->respond($csvData, 200 , CSV);
                }  else {
                    $this->error("Not found", 404, CSV);
                }

        }

    }

    protected function view ($input, $accept) {
        $id = $this->filter($input, "id", "/^\d+$/i", ["Id is required", 400, $accept]);
        $complex = Complex::get($id);
        if ($complex) {
            switch ($accept) {
                case HTML:
                case HTML_PARTIAL:
                    $view = View::loadFile("layout1.tpl");
                    $view->set($complex);
                    $view->set([
                        "pageTitle" => "Complex: ".$complex->title,
                        "members" => $complex->has("member"),
                        "content" => "{{complex.view.tpl}}"
                    ]);
                    if (User::getCurrent()) {
                        $view->set("floatButtons", [
                            ["href" => "complex/editor?id=$id", "icon" => "edit.svg"]
                        ]);
                    }
                    $this->respond($view, 200, HTML);
                    break;
                case JSON:
                    $this->respond($complex, 200, JSON);
                    break;
                case CSV:
                    $this->respond("Unaccepted", 406, CSV);
                    break;
            }
        } else $this->error("Not found", 404, $accept);
    }

    public function update ($input, $accept) {
        $id = $this->filter($input, "id", "/^\d+$/", ["Id is required", 400, $accept]);
        if ($accept == JSON) {
            $complex = Complex::withData($input);
            if ($complex->update()) {
                $this->respond(null, 200, JSON);
            } else {
                $this->error("Internal error", 500, JSON);
            }
        } else {
            $this->error("Unaccepted", 406, $accept);
        }
    }

    public function delete ($input, $accept) {
        $id = $this->filter($input, "id", "/^\d+$/", ["Id is required", 400, $accept]);
        if ($accept == JSON) {
            $complex = Complex::get($id);
            if ($complex == null || $complex->delete()) {
                $this->respond(null, 204, JSON);
            } else {
                $this->error("Internal error.", 500, JSON);
            }
        } else {
            $this->error("Unaccepted", 406, $accept);
        }
    }

    public function create ($input, $accept) {
        $title = $this->filter($input, "title", "has", ["Title is required", 400, $accept]);
        if ($accept == JSON) {
            $complex = Complex::withData($input);
            if ($complex->insert()) {
                $this->respond(["uri" => "complex/editor?id={$complex->id}"], 201, JSON);
            } else {
                $this->error("Complex with the same title already exists.", 500, JSON);
            }
        } else {
            $this->error("Unaccepted", 406, $accept);
        }
    }

    public function editor ($input, $accept, $method) {
        if ($accept == HTML && $method == "GET") {
            UserController::authenticate(1, HTML);
            $id = $this->filter($input, "id", "is_numeric");
            if ($id) {
                $complex = Complex::get($id);
                if (is_null($complex)) {
                    $this->error("Page not found", 404, HTML);
                }
            }
            $view = View::loadFile("layout2.tpl");
            if ($complex) {
                $hasMembers = $complex->has("member");
                foreach($hasMembers as &$hasMember) {
                    if (!property_exists($hasMember->member, "type")) {
                        $hasMember->member->type = lcfirst(get_class($hasMember->member));
                    }
                }
                $view->set([
                    "pageTitle" => "Edit complex",
                    "headerTitle" => "Edit complex",
                    "method" => "put",
                    "members" => $hasMembers,
                    "showDelForm" => User::getCurrent()->privilege > 2 ? "block" : "none",
                    "showMembers" => "block",
                    "floatButtons" => [
                        ["href" => "complex?id=$id", "icon" => "eye-outline.svg"]
                    ]
                ]);
                $view->set($complex);
            } else {
                $view->set([
                    "pageTitle" => "Create complex",
                    "headerTitle" => "Create complex",
                    "method" => "post",
                    "showMembers" => "none",
                    "message" => "<p>Hint: to add complex members, please submit this form first.</p>"
                ]);
            }
            $view->set([
                "content" => "{{complex.editor.tpl}}",
                "styles" => ["all.editor"],
                "jsAfterContent" => ["complex.editor","all.editor"],
                
            ]);
            $this->respond($view, 200, HTML);
        } else $this->error("Unaccepted", 405, $accept);
    }

    public function member ($input, $accept, $method) {
        if ($accept != JSON) {
            $this->error("Unaccepted", 400, $accept);
        }
        $complex = $this->filter($input, "complex", "is_numeric", ["Complex id is required", 400, JSON]);
        $complex = Complex::get($complex);
        if (is_null($complex)) $this->error("Complex not found", 404, JSON);

        $coefficient = array_key_exists("coefficient", $input) ? $input ["coefficient"] : 1; // by default 1
        switch ($method) {
            case "POST":
                $type = $this->filter($input, "type", "/(protein)|(metabolite|DNA|RNA)/i", ["Type is required for the complex member", 400, JSON]);
                $identifier = $this->filter($input, "member", "has", ["Member is required for the complex member", 400, JSON]);
                switch($type) {
					case "DNA":
					case "RNA":
						$member = $type::get($identifier);
						if ($member === null) $this->error("Gene or operon not found", 404, JSON);
						break;
					case "protein":
						$protein = Protein::get($identifier);
						if ($protein === null) $this->error("Protein is not found", 404, JSON);
						$member = $protein;
						break;
					case "complex":
						if ($input["member_validated"]) {
							$complex = Complex::get($identifier);
							if ($complex == null) $this->error("Complex not found", 404, JSON);
						} else {
							$complex = new Complex;
							$complex->title = $identifier;
							$complex->insert();
							if (!$complex->id) {
								$this->error("An internal error has happened, please contact admin.", 500, JSON);
							}
						}
						$member = $complex;
						break;
					case "metabolite":
						if ($input["member_validated"]) {
							$member = Metabolite::get($identifier);
							if ($member == null) $this->error("Metabolite not found", 404, JSON);
						} else {
							$member = Metabolite::getAll("title like ? or synonym regexp ?", [$identifier, $identifier."(,|$)"]);
							if ($member) {
								$member = $member[0];
							} else {
								$member = new Metabolite;
								$member->title = $identifier;
								$member->insert();
								if (!$member->id) {
									$this->error("An internal error has happened, please contact admin.", 500, JSON);
								}
							}
						}
						break;
				}
                if ($complex->addMember($member, $coefficient, $input["modification"])) {
                    $this->respond(["uri" => "complex/editor?id={$complex->id}"], 201, JSON);
                } else {
                    $this->error("An internal error has happened, please contact admin.", 500, JSON);
                }
                break;
            case "PUT":
                $member = $this->filter($input, "member", "has", ["Complex member is required", 400, JSON]);
                $member = Model::parse($member);
                if ($member) {
                    if ($complex->updateMember($member, $coefficient)) {
                        $this->respond(["uri" => "complex/editor?id={$complex->id}"], 200, JSON);
                    } else {
                        $this->error("An internal error has happened, please contact admin", 500, JSON);
                    }
                } else {
                    $this->errror("Complex member not found", 404, JSON);
                }
                break;
            case "DELETE":
                $member = $this->filter($input, "member", "has", ["Complex member is required", 400, JSON]);
                $member = Model::parse($member);
                if ($member) {
                    if ($complex->removeMember($member)) {
                        $this->respond(["uri" => "complex/editor?id={$complex->id}"], 200, JSON);
                    } else {
                        $this->error("An internal error has happened, please contact admin", 500, JSON);
                    }
                } else {
                    $this->errror("Complex member not found", 404, JSON);
                }
                break;
        }
    }
}
?>