<?php
require_once("ViewAdapters.php");

/**
 * Provides operations on Complexes.
 * 
 * RESTful API summary:
 * GET:/complex?keyword=:keyword
 * GET:/complex?page=:pageNumber&page_size=:pageSize
 * GET:/complex?id=:id
 * PUT:/complex?id=:id
 * POST:/complex
 * DELETE:/complex?id=:id
 * POST:/complex/member?complex=:complexId&member=:member
 * PUT:/complex/member?complex=:complexId&member=:member
 * DELETE:/complex/member?complex=:complexId&member=:member
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

    /**
    * API: search for a certain complex.
    * API: search for a certain complex
    * URL: /complex?keyword=:keyword
    * Method: GET
    * URL Params: keyword=[string]
    * Success Reponse: 
    * - code: 200, accept: JSON, content: [{id:xxx, title:xxx}, ...]
    * Error response: 
    * - code: 404, accept: JSON, content: {message: "Not found"}
    * - code: 400, accept: JSON, content: {message: xxxx}
    **/
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

    /**
     * API: list all complexes, include paging.
     * API: list all complexes, include paging
     * URL: /complex?page=:pageNumber&page_size=:pageSize
     * Method: GET,
     * URL Params: pageNumber=[int], pageSize=[int]
     * Success Response:
     * - code 200, accept: HTML
     * - code 200, accept: JSON, content: [{id:xxx, title: xxxx}, ...]
     * - code 200, accept: CSV, content: csv file with the columns "id", "title"
     * Error Response:
     * - code 404, accept: JSON/CSV
    **/
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

    /**
     * API: get the details of a complex.
     * API: get the details of a complex
     * URL: /complex?id=:id
     * Method: GET
     * URL Params: id=[int]
     * Success Response:
     * - code: 200, accepet: HTML
     * - code: 200, accepet: JSON, content: {id:xxx, title:xxx}
     * Error Reponse:
     * - code 404, accept: -
     * - code 406, accept: CSV
     */
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

    /**
     * API: update the data of a complex.
     * API: update the data of a complex
     * URL: /complex?id=:id
     * Method: PUT
     * URL Params: id=[int]
     * Data Params: {title: xxxx}
     * Success Response:
     * - code:200, accept:JSON,content: null
     * Error Response:
     * - code: 500, accept:JSON
     * - code: 406, accept:!JSON
     */
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

    /**
     * API: remove a comlpex.
     * API: remove a comlpex
     * URL: /complex?id=:id
     * Method: DELETE
     * URL Params: id=[int]
     * Success Response:
     * - code: 204, accept: JSON, content: null
     * Error Response:
     * - code: 500, accept: JSON, content: {message: "Internal errror"}
     * - code: 406, accept: !JSON
     */
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


    /**
     * API: create a complex.
     * API: create a complex
     * URL: /complex
     * Method: POST
     * Data Params: {title: xxxx}
     * Success Response:
     * - code: 201, accept:JSON, content:{uri:"complex/editor?id=:newid"}
     * Error Response:
     * - code:500
     * - code:406
     */
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

    /**
     * API: provides a editor page.
     * API: provides a editor page
     * URL: /complex/editor[?id=:id]
     * Method: GET
     * URL Params: id=[int]
     * Success Response:
     * - code:200, accept:HTML
     * - code 405
     */
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

    /**
     * Multiple APIs related to complex member
     * API: add member
     * URL: /complex/member?complex=:complexId&member=:member
     * Method: POST
     * URL Params: complexId=[int]; member=[string, {DNA:xxxxxxx} or {RNA:xxxxxxx} or {protein|xxxxxx} or {metabolite|xxxxxxxxx}]
     * Success response:
     * - code:201, accept:JSON, content: {uri:complex/editor?id=:id}
     * Error response:
     * - code:400, accept:JSON, content: {message:"Complex id is required"}
     * - code:404, accept:JSON, content: {message:"Complex not found"}
     * - code:406, accept:!JSON, content: {message: "Unacccept"}
     * - code:500, accept:JSON, content: {message:"An internal error has happened, please contact admin."}
     * 
     * API: remove member
     * URL: /complex/member?complex=:complexId&member=:member
     * Method: DELETE
     * URL Params: complexId=[int]; member=[string, {DNA:xxxxxxx} or {RNA:xxxxxxx} or {protein|xxxxxx} or {metabolite|xxxxxxxxx}]
     * Success response:
     * - code:204, accept:JSON
     * Error response:
     * - code:400, accept:JSON, content: {message:"Complex id is required"}
     * - code:404, accept:JSON, content: {message:"Complex not found"}
     * - code:406, accept:!JSON, content: {message: "Unacccept"}
     * - code:500, accept:JSON, content: {message:"An internal error has happened, please contact admin."}
     * 
     * API: update coefficient of member
     * URL: /complex/member?complex=:complexId&member=:member
     * Method: DELETE
     * URL Params: complexId=[int]; member=[string, {DNA:xxxxxxx} or {RNA:xxxxxxx} or {protein|xxxxxx} or {metabolite|xxxxxxxxx}]
     * Success response:
     * - code:200, accept:JSON, content: {url:"complex/editor?id={$complex->id}"}
     * Error response:
     * - code:400, accept:JSON, content: {message:"Complex id is required"}
     * - code:404, accept:JSON, content: {message:"Complex not found"}
     * - code:406, accept:!JSON, content: {message: "Unacccept"}
     * - code:500, accept:JSON, content: {message:"An internal error has happened, please contact admin."}
     */
    public function member ($input, $accept, $method) {
        if ($accept != JSON) {
            $this->error("Unaccepted", 406, $accept);
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
                        $this->respond(["uri" => "complex/editor?id={$complex->id}"], 204, JSON);
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