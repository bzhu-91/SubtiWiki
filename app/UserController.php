<?php
require_once("ViewAdapters.php");

// TODO: change password function

class UserController extends Controller {
	/**
	 * get method
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public function read ($input, $accept) {
		if ($input) {
			if (array_key_exists("name", $input)) {
				$this->view($input, $accept);
			} elseif (array_key_exists("keyword", $input)) {
				$this->search($input, $accept);
			} elseif (array_key_exists("page", $input)) {
				$this->list($input, $accept);
			}
		} else {
			$this->index($accept);
		}
	}

	/**
	 * user
	 * @param  enum $accept response content type
	 * @return none         
	 */
	protected function index ($accept) {
		$this->list([
			"page" => 1,
			"page_size" => 150
		], $accept);
	}

	/**
	 * list the users
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	protected function list ($input, $accept) {
		$page = $this->filter($input, "page", "is_numeric", ["Invalid input", 400, $accept]);
		$pageSize = $this->filter($input, "page_size", "is_numeric", ["Invalid input", 400, $accept]);

		$users = User::getAll("1 limit ?,?", [($page-1)*$pageSize, $pageSize]);
		switch ($accept) {
			case JSON:
				$this->respond(Utility::arrayColumns($users, ["name", "description"]), 200, JSON);
				break;
			case HTML:
			case HTML_PARTIAL:
				$view = View::loadFile("layout1.tpl");
				$view->set([
					"title" => "Users: page $page",
					"data" => $users,
					"content" => "{{user.search.tpl}}{{all.list.tpl}}",
					"showFootNote" => "none",
					"jsAfterContent" => ["all.list"],
					"vars" => [
						"type" => "user",
						"max" => ceil(User::count() / $pageSize),
						"currentInput" => $input
					]
				]);
				$this->respond($view, 200, HTML);
		}
	}

	/**
	 * search for user
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	protected function search ($input, $accept) {
		$keyword = $this->filter($input, "keyword", "/[0-9a-zöüä]{2,}/i");

		if ($keyword) {
			$users = User::getAll("CONVERT(name USING utf8) like ?", ["%".$keyword."%"]);

			switch ($accept) {
				case JSON:
					$this->respond(Utility::arrayColumns($users, ["name", "description"]), 200, JSON);
					break;
				case HTML:
				case HTML_PARTIAL:
					$view = View::loadFile("layout1.tpl");
					if (!$users) {
						$view->set("message", "Not found");
					}
					$view->set([
						"title" => "Users: search for <i>$keyword</i>",
						"pageTitle" => "Users: search",
						"content" => "{{user.search.tpl}}",
						"users" => $users,
						"showFootNote" => "none",
						"navlinks" => [
							["href" => "user", "innerHTML" => "User list"]
						],
					]);
					$this->respond($view, 200, HTML);
			}
		} else {
			switch ($accept) {
				case JSON:
					$this->error("Invalid input", 400, JSON);
					break;
				case HTML:
				case HTML_PARTIAL:
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"title" => "Users: search for <i>".$input["keyword"]."</i>",
						"pageTitle" => "Users: search",
						"content" => "{{user.search.tpl}}",
						"users" => $users,
						"message" => "keyword too short or contains unaccepted characters",
						"showFootNote" => "none",
						"navlinks" => [
							["innerHTML" => "User list", "href" => "user"]
						]
					]);
					$this->respond($view, 200, HTML);
			}
		}
	}

	/**
	 * provide the profile page of the user
	 * @param  array $input  input from client
	 * @param  enum $accept content type of the response
	 * @return none         
	 */
	protected function view ($input, $accept) {
		$name = $this->filter($input, "name", "has", ["User not found", 404, $accept]);
		$user = User::get($name);
		if ($user === null) $this->error("User not found", 404, $accept);
		switch ($accept) {
			case JSON:
				$this->respond(["name" => $user->name, "description" => $user->description], 200, JSON);
				break;
			case HTML:
			case HTML_PARTIAL:
				$view = View::loadFile("layout1.tpl");
				$view->set($user);
				$view->set([
					"title" => "User: {{:name}}",
					"pageTitle" => "User: {{:name}}",
					"content" => "{{user.view.tpl}}",
					"showFootNote" => "none",
					"titleExtra" => '<a href="history?user={{:name}}">Edits</a>',
					"navlinks" => [
						["innerHTML" => "User list", "href" => "user"]
					],
				]);
				$currentUser = User::getCurrent();
				// weird, this gives a E_NOTICE
				if ($currentUser !== null && $currentUser->name == $user->name || $currentUser->privilege > 1) {
					$view->set("floatButtons", [
						["href" => "user/editor?name=$name", "icon" => "edit.svg"]
					]);
				}
				$this->respond($view, 200, HTML);
		}
	}

	/**
	 * provide editor for description, only respond to html
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public function editor ($input, $accept, $method) {
		if ($method !== "GET") $this->error("Unaccepted method", 405, $accept);
		switch ($accept) {
			case JSON:
				$this->error("Unaccepted method", 405, JSON);
				break;
			case HTML:
			case HTML_PARTIAL:
				$name = $this->filter($input, "name", "is_string", ["Resource not found", 404, HTML]);
				$token = $this->filter($input, "token", "/^[a-f0-9]{32}$/");
				$user = User::get($name);
				$currentUser = User::getCurrent();
				if ($token) {
					// change password
					$validation = User\Invitation::getByToken($token);
					if ($validation && $validation->email == $user->email) {
						$view = View::loadFile("layout1.tpl");
						$view->set($user);
						$view->set([
							"title" => "Change password: {$user->name}",
							"pageTitle" => "Change password: {$user->name}",
							"content" => "{{user.changePassword.tpl}}",
							"showFootNote" => "none",
							"token" => $token,
						]);
						$this->respond($view, 200, HTML);
					} else {
						$this->error("This link has expired", 403, HTML);
					}
				} else {
					if ($currentUser && $currentUser->privilege >= 2 || $currentUser->name === $user->name) {
						$view = View::loadFile("layout1.tpl");
						$view->set($user);
						$view->set([
							"pageTitle" => "Eidt: $user->name",
							"title" => "Eidt: $user->name",
							"content" => "{{user.editor.tpl}}",
							"jsAfterContent" => ["Editor","all.editor"],
							"showFootNote" => "none"
						]);
						$this->respond($view, 200, HTML);
					} else $this->error("Permission error, your user group is not allowed of this operation", 403, HTML);
				}
		}
	}

	public function password ($input, $accept, $method) {
		if ($method == "POST") {
			if ($accept !== JSON) $this->error("Unaccepted", 406, $accept);
			$name = $this->filter($input, "name", "has", ["user name is required", 400, JSON]);
			$user = User::get($name);
			if ($user === null) {
				$this->error("User not found", 404, JSON);
			}
			$currentUser = User::getCurrent();
			$shouldSendEmail = !($currentUser && $currentUser->name === $user->name);

			// create token;
			$validation = User\Invitation::get($user->email);
			if ($validation == null) {
				$validation = new User\Invitation;
				$validation->email = $user->email;
				$validation->type = $user->privilege > 1 ? "admin" : "normal";
				$validation->name = $user->realName;
				$validation->token = md5($user->name.date("Y-m-d H:i:s"));
				if (!$validation->insert()) $this->error("Can not create validation token, please contact admin", 500, JSON);
			} else {
				$validation->expired = null;
				$validation->token = md5($user->name.date("Y-m-d H:i:s"));
				if (!$validation->update()) $this->error("Can not create validation token, please contact admin", 500, JSON);
			}
			if ($shouldSendEmail) {
				// send email here
				$link = "http://".$_SERVER['HTTP_HOST'].$GLOBALS["WEBROOT"]."/user/editor?name=".$user->name."&token=".$validation->token;
				$body = "To reset the password, please follow the link: <a href='$link'>$link</a>";
				if (Utility::sendEmail($user->email, $user->name, "Reset password for your ".$GLOBALS["SITE_NAME"]." account", $body)) {
					$this->respond(["message" => "An email has been sent to your registration email address with a link for password reset."], 200, JSON);
				} else {
					$this->respond("Can not send the email, please contact admin.", 500, JSON);
				}
			} else {
				$this->respond(["uri" => "user/editor?name=$name&token={$validation->token}"], 200, JSON);
			}
		} elseif ($method == "GET") {
			if ($accept !== HTML) $this->error("Unaccepted", 406, $accept);
			$view = View::loadFile("layout1.tpl");
			$view->set([
				"title" => "Reset password",
				"pageTitle" => "Reset password",
				"content" => "{{user.password.tpl}}",
				"showFootNote" => "none",
				"jsAfterContent" => ["all.editor"]
			]);
			$this->respond($view, 200, HTML);
		}
	}

	/**
	 * provide login form and process login
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public function login ($input, $accept, $method) {
		switch ($method) {
			case 'GET':
				switch ($accept) {
					case HTML:
						$view = View::loadFile("layout3.tpl");
						$view->set([
							"content" => "{{user.login.tpl}}",
							"showFootNote" => "none",
						]);
						$this->respond($view, 200, HTML);
						break;
					case HTML_PARTIAL:
						$view = View::loadFile("user.login.tpl");
						$this->respond($view, 200, HTML);
						break;
					case JSON:
						$this->respond("Not Acceptable", 406, JSON);
						break;
				}
				break;
			case 'POST':
				// only response JSON
				$name = $this->filter($input, "name", "is_string", ["Invalid user name", 400, JSON]);
				$password = $this->filter($input, "password", "/[0-9a-f]{32}/i", ["Invalid password", 400, JSON]);

				$name = ucfirst($name);
				$user = User::get($name);

				if ($user && $user->login($password)) {
					$this->respond(["message" => "successful"], 200, JSON);
				} else {
					$this->error("User name or password incorrect", 401, JSON);
				}

				break;
			default:
				$this->error("Unaccepted method", 405, JSON);
		}
	}

	public function logout ($input, $accept) {
		$user = User::getCurrent();
		if ($user) $user->logout();
		$this->respond(["message" => "successful"], 200, JSON);
	}

	/**
	 * can only update description
	 * @param  [type] $input  [description]
	 * @param  [type] $accept [description]
	 * @return [type]         [description]
	 */
	public function update ($input, $accept) {
		switch($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Not Acceptable", 406, HTML);
				break;
			case JSON:
				$name = $this->filter($input, "name", "is_string", ["No name specified"], 400, JSON);
				$user = User::get($name);
				if ($user === null) {
					$this->error("User not found", 404, JSON);
				}
				$currentUser = User::getCurrent();

				$privilege = $this->filter($input, "privilege", "/1|2|3/i");
				$token = $this->filter($input, "token", "/^[a-f0-9]{32}$/");
				$password = $this->filter($input, "password", "/^[a-f0-9]{32}$/");
				if ($token) {
					// change password
					$validation = User\Invitation::getByToken($token);
					if ($validation && $validation->email == $user->email) {
						$user->_rawPassword = $password;
						if ($user->update()) {
							$validation->expire();
							$this->respond(["message" => "Password changed"], 200, JSON);
						} else {
							$this->error("Password cannot be changed, please contact admin.", 500, JSON);
						}
					} else {
						$this->error("This link has expired", 403, HTML);
					}
				} elseif ($privilege && $currentUser->privilege >= 2) {
					$user->privilege = $privilege;
					if ($user->update()) {
						$this->respond(["uri" => "user?name=$name"], 200, JSON);
					} else $this->error("Unexpected error", 500, JSON);
				} elseif ($currentUser && $currentUser->privilege >= 2 || $user->name = $currentUser->name) {
					$user->description = $input["description"];
					$user->realName = $input["realName"];
					if ($input["email"] && Utility::validateEmailAddressAddressAddressAddressAddressAddress($input["email"])) {
						// if email is to updated, update the possible token as well;
						$user->email = $input["email"];
					}
					if ($user->update()) {
						$this->respond(["uri" => "user?name=$name"], 200, JSON);
					} else $this->error("Unexpected error, please contact admin", 500, JSON);
				} else $this->error("Permission denied", 403, JSON);
		}
	}

	/**
	 * not supported
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none
	 */
	public function delete ($input, $accept) {
		$this->error("Permission error", 403, $accept);
	}

	public function create ($input, $accept) {
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$this->error("Not acceptable", 406, HTML);
				break;
			case JSON:
				$name = $this->filter($input, "name", "is_string", ["Invalid user name", 400, JSON]);
				$rawPassword = $this->filter($input, "password", "/^[0-9a-f]{32}$/i", ["Invalid password", 400, JSON]);
				$realName = $input["realName"];
				
				if ($GLOBALS['OPEN_REGISTRATION']) {
					$email = $this->filter($input, "email", "is_email", ["Invalid email address", 400, JSON]);
					$new = User::withData([
						"name" => $name,
						"realName" => $realName,
						"_rawPassword" => $rawPassword,
						"email" => $email,
						"privilege" => 1,
						"registration" => date("Y-m-d H:i:s")
					]);
					if ($new->insert()) {
						$this->respond(["message" => "okay"], 201, JSON);
					} else {
						$this->error("User name is not available", 400, JSON);
					}
				} else {
					$token = $this->filter($input, "invitation", "/^[0-9a-f]{32}$/i", ["Invalid invitation token", 400, JSON]);
					$invitation = User\Invitation::getByToken($token);
					if ($invitation) {
						$new = User::withData([
							"name" => $name,
							"realName" => $realName,
							"_rawPassword" => $rawPassword,
							"email" => $invitation->email,
							"privilege" => $invitation->type == "admin" ? 2 : 1,
						]);
						if ($new->insert()) {
							$invitation->expire();
							$this->respond(["message" => "okay"], 201, JSON);
						} else {
							$this->error("User name is not available", 400, JSON);
						}
					} else $this->error("Invitation token is not valid", 500, JSON);
				}
				break;
		}
	}

	/**
	 * authenticate user login, will redirect login interface
	 * @param  int $group  user group
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public static function authenticate ($group, $accept = HTML) {
		$currentUser = User::getCurrent();
		$controller = new UserController();
		if ($currentUser && $currentUser->privilege >= $group) {
			return true;
		} else {
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
					$controller->login([], $accept, "GET");
					break;
				case JSON:
					$controller->error("Permission error.", 403, JSON);
					break;
			}
		}
	}

	/**
	 * procide the interface for user invitation
	 * @param  array $input  client side input
	 * @param  enum $accept response content type
	 * @return none         
	 */
	public function invitation ($input, $accept) {
		switch ($_SERVER["REQUEST_METHOD"]) {
			case 'GET':
				UserController::authenticate(2);
				// provides the interface
				$view = View::loadFile("layout1.tpl");
				if($GLOBALS["OPEN_REGISTRATION"]) {
					$view->set([
						"title" => "Invite user",
						"pageTitle" => "Invite user",
						"content" => "Open registration is enabled.",
						"showFootNote" => "none",
					]);
				} else {
					$view->set([
						"title" => "Invite user",
						"pageTitle" => "Invite user",
						"content" => "{{user.invitation.tpl}}",
						"showFootNote" => "none",
						"jsAfterContent" =>["user.invitation"]
					]);
				}
				$this->respond($view, 200, HTML);
			case 'POST':
				switch ($accept) {
					case JSON:
						UserController::authenticate(2, JSON);
						$name = $this->filter($input, "name", "is_string", ["Name is required.", 400, JSON]);
						$email = $this->filter($input, "email", "is_email", ["Invalid email address", 400, JSON]);
						$token = $this->filter($input, "token", "/^[0-9a-f]{32}$/i", ["Invalid token", 400, JSON]);
						$type = $this->filter($input, "type", "is_string", ["Type is required", 400, JSON]);
						$body = $this->filter($input, "body", "is_string");
						$sendEmail = $this->filter($input, "sendEmail", "is_bool");
						if ($sendEmail === null) {
							$sendEmail = true;
						}
						if ($type !== "admin") {
							$type = "normal";
						}
						$invitation = new User\Invitation();
						$invitation->name = $name;
						$invitation->email = $email;
						$invitation->token = $token;
						$invitation->type = $type;

						if ($invitation->insert()) {
							if ($sendEmail) {
								Utility::sendEmail($email, $name, "Your SubtiWiki invitation token", $body);
								$this->respond(["message" => "Invitation email sent"], 201, JSON);
							} else $this->respond(["message" => "Invitation token saved."], 201, JSON);
						} else $this->error("The same invitation token is already saved", 500, JSON);
						break;
					default:
						$this->error("Not acceptable", 406, HTML);
						break;
				}
		}
	}

	public function registration ($input, $accept) {
		switch ($accept) {
			case HTML:
			case HTML_PARTIAL:
				$view = View::loadFile("layout1.tpl");
				$view->set([
					"title" => "User: registration",
					"pageTitle" => "User: registration",
					"showFootNote" => "none",
					"jsAfterContent" => ["user.registration"]
				]);
				if ($GLOBALS["OPEN_REGISTRATION"]) {
					$view->set("content", "{{user.registration.open.tpl}}");
				} else {
					$view->set("content", "{{user.registration.tpl}}");
				}
				$this->respond($view, 200, HTML);
				break;
			
			default:
				$this->error("Not Acceptable", 406, JSON);
				break;
		}
	}

}
?>