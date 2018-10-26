<?php
class View {
	private $template_str = "";
	private $rendered_str = "";
	private $rendered = false;
	private $data = [];
	private $key_consumption = [];
	public $restPrintingStyle = "HTML";

	private static $adapters = [];
	private $localAdapters = [];

	public $last_error = "";

	private static $default_load_dir = false;

	public static function __callStatic ($key, $args){
		if ("registerAdapter" == $key) {
			self::$adapters[$args[0]] = $args[1];
		}
		if ("setDefaultLoadDir" == $key) {
			self::$default_load_dir = $args[0];
		}
	}

	public function __call ($key, $args) {
		if ("registerAdapter" == $key) {
			if (is_array($args[0])) {
				foreach ($args[0] as $key => $value) {
					$this->localAdapters[$key] = $value;
				}
			} else $this->localAdapters[$args[0]] = $args[1];
		}
	}
	// @override
	public static function load($str){
		$tpl = new View();
		$tpl->rendered_str = $tpl->template_str = $str;
		return $tpl;
	}

	public static function loadFile($filename){
		global $ROOT;
		if (!realpath($filename)) {
			$filename = self::$default_load_dir.DIRECTORY_SEPARATOR.$filename;
		}
		$tpl = new View();
		if (file_exists($filename)) {
			ob_start();
			include "$filename";
			$tpl->rendered_str = $tpl->template_str = ob_get_clean();
		} else $last_error = "file $filename not found";

		return $tpl;
	}

	public function set ($a, $b = null){
		if ($b !== null) {
			$this->data[$a] = $b;
		} else if ($a) {
			foreach ($a as $key => $value) {
				$this->data[$key] = $value;
			}
		} else {
			$this->last_error = "error in input";
		}
	}

	private function bind (){
		$matches = [];

		// include other templates
		$pattern = "/{{([^:{}]+)?}}/";
		$matches = [];
		preg_match_all($pattern, $this->template_str, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$file = $match[1];
			if (!realpath($file)) {
				$file = self::$default_load_dir."/".$file;
			}
			if (file_exists($file)) {
				 $replacement = View::loadFile($file);
				 $replacement = $replacement->template_str; // load the raw str (php code executed)
			} else {
				$replacement = "";
			}
			$this->rendered_str = str_replace($match[0], $replacement, $this->rendered_str);
		}

		// string replacement
		$pattern = "/{{:([^\{\}:]+?)}}/";
		preg_match_all($pattern, $this->template_str, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$keys = $match[1];
			$keypath = new KeyPath($keys);
			$val = $keypath->get($this->data);
			if ($val !== null) {
				$replacement = $val;
				// consume the first level key
				$this->key_consumption[$keypath->first()] = true;
			} else $replacement = "";
			$this->rendered_str = str_replace($match[0], $replacement, $this->rendered_str);
		}


		// with adapters
		$pattern = "/{{([^\{\}:]+?)\:([^\{\}:]+?)}}/";
		preg_match_all($pattern, $this->template_str, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$type = $match[1];
			$keys = $match[2];
			// find adapter function
			$func = function(){return "";};
			if (array_key_exists($type, self::$adapters)) {
				$func = self::$adapters[$type];
			};
			if (array_key_exists($type, $this->localAdapters)) {
				$func = $this->localAdapters[$type];
			};
			// find value
			$keypath = new KeyPath($keys);
			$val = $keypath->get($this->data);

			if ($val !== null) {
				$this->key_consumption[$keypath->first()] = true;
			}
			$replacement = call_user_func_array($func, [$val]);
			$this->rendered_str = str_replace($match[0], $replacement, $this->rendered_str);
		}

		
	}

	private function printValueHTML (KeyPath $keypath, $obj) {
		if (is_array($obj)) {
			$obj = json_decode(json_encode($obj));
		}
		$str = "";
		foreach ($obj as $key => $value) {
			if ($key[0] === "_" || $value === null || $value === "" || json_encode($value) === "[]" || json_encode($value) === "{}") {
				continue;
			}
			$keypath = $keypath->push($key);
			$showKey = !is_numeric($key) && !empty($key);

			// find adapters
			$keypathStr = (string) $keypath;
			$func = false;

			// incase the object is inside the array
			// adapter name like : Expression and Regulation->Operons->each
			// will apply to all elements in this array
			
			if (is_numeric($key)) {
				$adapterName = (string) $keypath->pop()->push("each");
			} else {
				$adapterName = (string) $keypath;
			}

			// search adapter by name in 
			if (array_key_exists($adapterName, self::$adapters)) {
				$func = self::$adapters[$adapterName];
			}
			
			if (array_key_exists($adapterName, $this->localAdapters)) {
				$func = $this->localAdapters[$adapterName];
			}

			if ($func) {
				$str .= $func($value);
			} else {
				// ======== if no adapter is defined =============
				if (is_object($value)) {
					if ($showKey) $str .= "<div class='m_key'>".$key."</div>";
					$str .= "<div class='m_object'>".$this->printValueHTML($keypath, $value)."</div>";
				} else if (is_array($value)) {
					if ($showKey) $str .= "<div class='m_key'>".$key."</div>";
					$str .= "<div class='m_array'>".$this->printValueHTML($keypath, $value)."</div>";
				} else if (is_bool($value)) {
					if ($showKey) {
						$str .= "<div class='m_block'>";
						$str .= "<div class='m_key m_inline'>".$key."</div>";
						$str .= "<div class='m_value m_inline'>".($value ? "yes" : "no")."</div>";
						$str .= "</div>";
					} else {
						$str .= "<div class='m_value'>".($value ? "yes" : "no")."</div>";
					}
				} else {
					if ($showKey) {
						$str .= "<div class='m_block'>";
						$str .= "<div class='m_key m_inline'>".$key."</div>";
						$str .= "<div class='m_value m_inline'>".$value."</div>";
						$str .= "</div>";
					} else {
						$str .= "<div class='m_value'>".$value."</div>";
					}
				}
			}
			$keypath = $keypath->pop();
		}
		return $str;
	}

	private function printValueMonkdey (KeyPath $keypath, $obj) {
		$str = "";
		foreach ($obj as $key => $value) {
			if ($key[0] === "_" || $value === null || $value === "" || json_encode($value) === "[]" || json_encode($value) === "{}") {
				continue;
			}
			$keypath = $keypath->push($key);
			$showKey = !is_numeric($key) && !empty($key);
			$indent = $keypath->length();
			if (is_object($value)) {
				if ($showKey) {
					if ($indent == 1) $str .= "\n";
					$str .= str_repeat("*", $indent)." $key\n";
				}
				$str .= trim($this->printValueMonkdey($keypath, $value));
			} else if (is_array($value)) {
				if ($showKey) {
					if ($indent == 1) $str .= "\n";
					$str .= str_repeat("*", $indent)." $key\n";
				}
				$str .= trim($this->printValueMonkdey($keypath, $value));
			} else if (is_bool($value)) {
				if ($showKey) {
					$str .= str_repeat("*", $indent)." $key: ";
				}
				$str .= ($value ? "yes" : "no");
			} else {
				if ($showKey) {
					$str .= str_repeat("*", $indent)." $key: ";
				}
				$str .= $value;
			}
			$str .= "\n";
			$keypath = $keypath->pop();
		}
		return $str;
	}

	private function printRest () {
		$rest = [];
		foreach ($this->key_consumption as $key => $value) {
			if (!$value) {
				$rest[$key] = $this->data[$key];
			}
		}

		if (strpos($this->rendered_str, "{{::rest}}") !== false) {
			if ($this->restPrintingStyle == "HTML") {
				$rendered = $this->printValueHTML(new KeyPath(), $rest);
			} else {
				$rendered = $this->printValueMonkdey(new KeyPath(), $rest);
			}
			$this->rendered_str = str_replace("{{::rest}}", trim($rendered), $this->rendered_str);
		}
	}

	public function isClean (){
		return !(preg_match("/{{:([^\{\}:]+?)}}/", $this->rendered_str) || preg_match("/{{([^\{\}:]+?)\:([^\{\}:]+?)}}/", $this->rendered_str) || preg_match("/{{([^:{}]+)?}}/", $this->rendered_str));
	}

	public function generate ($recursive = false, $restPrinting = false){
		// all keys are not consumed yet
		foreach ($this->data as $key => $value) {
			$this->key_consumption[$key] = false;
		}
		if ($recursive) {
			while (!$this->isClean()) {
				$this->template_str = $this->rendered_str;
				$this->bind();
			}
		} else $this->bind();

		
		if ($restPrinting) {
			$this->printRest();
		} else {
			$this->rendered_str = str_replace("{{::rest}}", "", $this->rendered_str);
		}

		// redo the cleaning incase there are markups in the rest part.
		if ($recursive) {
			while (!$this->isClean()) {
				$this->template_str = $this->rendered_str;
				$this->bind();
			}
		} else $this->bind();
		return $this->rendered_str;
	}
}
?>