<?php
define("JSON", "JSON");
define("HTML", "HTML");
define("HTML_PARTIAL", "HTML_PARTIAL");
define("CSV", "CSV");

class Router {
	public static function getWebRoot() {
		$webroot = "/".trim(str_replace($_SERVER["DOCUMENT_ROOT"], "", dirname($_SERVER["PHP_SELF"])), "/");
		$GLOBALS["WEBROOT"] = $webroot;
		return $webroot;
	}

	public static function getCleanURL() {
		$webroot = self::getWebRoot();
		if (array_key_exists("REDIRECT_URL", $_SERVER)) {
			return str_replace($webroot, "", $_SERVER["REDIRECT_URL"]);
		} else {
			return ""; //in this case the request on index.php
		}
	}

	public static function getPutData() {
		$file = fopen("php://input", "r");
		$query_string = "";
		while ($str = fread($file, 1024)) {
			$query_string .= $str;
		}
		$put = [];
		parse_str($query_string, $put);
		return $put;
	}

	public static function getInput() {
		// gather data from post or get
		$input = [];
		foreach ($_GET as $key => $value) {
			$input[$key] = Utility::autocast($value);
		}
		foreach ($_POST as $key => $value) {
			$input[$key] = Utility::autocast($value);
		}
		foreach (self::getPutData() as $key => $value) {
			$input[$key] = Utility::autocast($value);
		}
		if (array_key_exists("data", $input)) {
			foreach ($input["data"] as $key => $value) {
				$input[$key] = Utility::autocast($value);
			}
			unset($input["data"]);
		}
		Utility::clean($input);
		return $input;
	}

	public static function getAccept() {
		$accept = strtolower($_SERVER['HTTP_ACCEPT']);
		// sort out accept
		// can be json /html /html_partial
		if ($accept == "application/json") {
			$accept = JSON;
			header("Content-type: application/json");
		} else if ($accept == "text/html_partial") {
			$accept = HTML_PARTIAL;
		} else if (strpos($accept, "text/html") !== false) {
			$accept = HTML;
			header("Content-type: text/html");
		} elseif ($accept == "text/csv") {
			$accept = CSV;
			header("Content-type: text/csv");
		} else $accept = HTML; // by default html
		return $accept;
	}

	public static function call($className = null, $methodName, $input, $accept, $method = null) {
		if ($className == null && function_exists($methodName)) {
			call_user_func_array($methodName, [$input, $accept, $method]);
		} else if ($className && $methodName) {
			if (class_exists($className)) {
				$instance = new $className();
				if (method_exists($instance, $methodName)) {
					$reflection = new ReflectionMethod($instance, $methodName);
					if ($reflection->isPublic()) {
						if ($method) $instance->$methodName($input, $accept, $method);
						else $instance->$methodName($input, $accept);
					} else throw new MethodNotPublicException("$className::$methodName is not public", 1);	
				} else throw new MethodNotPublicException("$className::$methodName does not exist", 1);
			} else throw new ClassNotFoundException("class $className does not exist", 1);
		} else throw new MethodNotFoundException("function $methodName does not exist", 1);
	}

	public static function route($settings = null) {
		$input = self::getInput();
		$url = self::getCleanURL();
		$accept = self::getAccept();

		if (array_key_exists("__accept", $input)) {
			if (preg_match("/^(JSON|HTML|HTML_PARTIAL|CSV)$/i", strtoupper($input["__accept"]))) {
				$accept = strtoupper($input["__accept"]);
				unset($input["__accept"]);
			}
		}

		if (array_key_exists("__method", $input)) {
			$method = strtoupper($input["__method"]);
			unset($input["__method"]);
		} else {
			$method = $_SERVER['REQUEST_METHOD'];
		}
		
		$routed = false;

		if ($settings) {
			foreach ($settings as $key => $value) {
				if (preg_match($key, $url)) {
					$routed = true;
					self::call($value[0], $value[1], $input, $accept, $method);
				}
			}
		}
		if (!$routed) {
			if ($url) {
				$segments = explode("/", trim($url, "/"));
				$controllerName = ucfirst($segments[0])."Controller";

				
				if (count($segments) == 1) { // routing based on http verb
					switch ($method) {
						case 'GET':
							self::call($controllerName, "read", $input, $accept);
							break;
						case 'PUT':
							self::call($controllerName, "update", $input, $accept);
							break;
						case 'POST':
							self::call($controllerName, "create", $input, $accept);
							break;
						case "DELETE":
							self::call($controllerName, "delete", $input, $accept);
							break;
						default:
							// option or head
							// can not handle
							http_response_code(400);
							break;
					}
				} else if (count($segments) == 2) {
					self::call($controllerName, $segments[1], $input, $accept, $method);
				}
			}
		}
	}
}