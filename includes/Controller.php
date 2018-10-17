<?php
abstract class Controller {
	abstract function read ($input, $contentType);
	abstract function create ($input, $contentType);
	abstract function update ($input, $contentType);
	abstract function delete ($input, $contentType);

	/**
	 * create a response, application will stop after this funciton is called
	 * @param  mixed  $body        body of the response, can be string/object/array or View instance
	 * @param  integer $status      http status code
	 * @param  HTML/JSON/HTML_PARTIAL  $contentType content type of the response
	 * @param  array/object  $headers     extra headers
	 * @return none               
	 */
	public function respond ($body, $status = 200, $contentType = HTML, $headers = NULL) {
		http_response_code($status);
		if ($headers) {
			foreach ($headers as $str) {
				header($str);
			}
		}
		switch ($contentType) {
			case HTML:
				header("Content-type: text/html");
				break;
			case HTML_PARTIAL:
				header("Content-type: text/html_partial");
				break;
			case JSON:
				header("Content-type: application/json");
				break;
			case CSV:
				header("Content-type: text/csv");
				break;
		}
		if ($body === null) {
			echo "null";
		} elseif (is_object($body) && (is_subclass_of($body, "View") || $body instanceof View)) {
			echo $body->generate(1,1);
		} elseif (is_array($body) || is_object($body)) {
			echo json_encode($body);
		} elseif (is_bool($body)) {
			echo $body ? "true" : "false";
		} else {
			echo $body;
		}
		Application::stop();
	}

	/**
	 * create an error response, application will stop after this function is called
	 * @param  string  $message     error message
	 * @param  integer $status      http status code, by default 404
	 * @param  HTML/HTML_PARTIAL/JSON  $contentType content type of the response
	 * @param  array/object  $headers     extra headers
	 * @return none               
	 */
	public function error ($message, $status = 404, $contentType = HTML, $headers = null) {
		http_response_code($status);
		if ($headers) {
			foreach ($headers as $str) {
				header($str);
			}
		}
		switch ($contentType) {
			case HTML:
				header("Content-type: text/html");
				$view = View::loadFile("Error.php");
				$view->set([
					"title" => "Error $status",
					"content" => $message,
				]);
				echo $view->generate(1,1);
				break;
			case HTML_PARTIAL:
				header("Content-type: text/html_partial");
				echo "Error $status: $message";
				break;
			case JSON:
				header("Content-type: application/json");
				echo json_encode(["message" => $message]);
				break;
		}
		Application::stop();
	}

	/**
	 * filter user input, if error response is given, will call $this->error and end the application
	 * @param  array $input         input from client
	 * @param  string $keypath       keypath
	 * @param  string $requirement   requirement of the value, "has" / regexp
	 * @param  array $errorResponse arguments for error response
	 * @return none                
	 */
	public function filter ($input, $keypath, $requirement = null, $errorResponse = null) {
		$val = Utility::getValueFromKeypath($input, $keypath);
		if ($val !== null) {
			if ($requirement) {
				if ($requirement[0] == "/") { // is regexp
					if (preg_match($requirement, (string) $val)) {
						return $val;
					} 
				} elseif ($requirement == "has") {
					return $val;
				} elseif ($requirement == "is_numeric" || $requirement == "is_string" || $requirement == "is_object" || $requirement == "is_array" || $requirement == "is_bool") {
					if (call_user_func($requirement, $val)) {
						return $val;
					}
				} elseif ($requirement == "is_email") {
					if (Utility::validateEmail($val)) {
						return $val;
					}
				}
			} else return $val;
		}
		// if filter failed
		if ($errorResponse) {
			call_user_func_array([$this, "error"], $errorResponse);
		}
	}
}
?>