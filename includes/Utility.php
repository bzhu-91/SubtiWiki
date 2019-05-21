<?php
class Utility {
	use UtilityExtra;
	
	public static function autocast($val) {
		if (is_bool($val)) {
			return (bool) $val;
		} else if (is_numeric($val)) {
			return (double) $val;
		} else if (is_string($val)) {
			if (strpos($val, "{") !== false || strpos($val, "[") !== false) {
				// could be json string
				$obj = json_decode($val, true);
				if ($obj) {
					return $obj;
				} else {
					return $val;
				}
			} elseif ($val === "true") {
				$val = true;
			} elseif ($val === "false") {
				$val = false;
			}
		} else if (is_object($val)) {
			return (array) $val;
		}
		return $val;
	}

	public static function decodeLinkForView (&$object) {
		$patterns = [];$callbacks = [];
		/*
			handle [[gene|6740108089F13116F200C15F35C2E7561E990FEB|test]] to [gene|6740108089F13116F200C15F35C2E7561E990FEB|test]
		*/
		$patterns[] = "/\[\[(\w+?)\|([^\]]+?)\|([^\]]+?)\]\]/";
		$callbacks[] = function ($matches) {
			$type = $matches[1];
			$id = $matches[2];
			$alias = $matches[3];
			return "[$type|{$id}|{$alias}]";
		};
		/*
			handle [[gene|dnaA]] to [gene|6740108089F13116F200C15F35C2E7561E990FEB|dnaA]
		*/
		$patterns[] = "/\[\[(\w+?)\|([^\]\|]*?)\]\]/";
		$callbacks[] = function ($matches) {
			$type = $matches[1];
			$class_name = ucfirst($type);
			$id = $matches[2];
			try {
				if (class_exists($class_name)) {
					$title = $class_name::simpleGet($id);
					if ($title) {
						return "[$type|{$title->id}|{$title->title}]";
					}
				}
			} catch (Exception $err) {
				// tolerrent CLassNotFoundException
			}
			
			return "[$type|search|$id]";
		};
		if (is_string($object)) {
			for ($i=0; $i < 2; $i++) { 
				$object = preg_replace_callback($patterns[$i], $callbacks[$i], $object);
			}
		} else if (is_array($object) || is_object($object)) {
			foreach ($object as $key => &$value) {
				static::decodeLinkForView($value);
			}
		}
	}

	public static function decodeLinkForEdit (&$object) {
		$patterns = [];$callbacks = [];
		/*
			handle [[gene|6740108089F13116F200C15F35C2E7561E990FEB|test]] to [[gene|dnaa|test]]
		*/
		$patterns[] = "/\[\[([^\[]+?)\|([^\]]+?)\|([^\]]+?)\]\]/";
		$callbacks[] = function ($matches) {
			$type = $matches[1];
			$class_name = ucfirst($type);
			$id = $matches[2];
			$alias = $matches[3];
			try {
				if (class_exists($class_name)) {
					$object = $class_name::simpleGet($id);
					if ($object) {
						return "[[$type|{$object->title}|{$alias}]]";
					}
				}
			} catch (Expcetion $exp) {
				// tolerrent CLassNotFoundException
			}
			
			return $matches[0];
		};
		/*
			handle [[gene|dnaA]] to [gene|6740108089F13116F200C15F35C2E7561E990FEB|dnaA]
		*/
		$patterns[] = "/\[\[([^\[\|]+?)\|([^\]\|]*?)\]\]/";
		$callbacks[] = function ($matches) {
			$type = $matches[1];
			$class_name = ucfirst($type);
			$id = $matches[2];
			try {
				if (class_exists($class_name)) {
					$object = $class_name::simpleGet($id);
					if ($object) {
						return "[[$type|{$object->title}]]";
					}
				}
			} catch (Exception $exp) {
				// tolerrent CLassNotFoundException
			}
			return $matches[0];
		};
		if (is_string($object)) {
			for ($i=0; $i < 2; $i++) { 
				$object = preg_replace_callback($patterns[$i], $callbacks[$i], $object);
			}
		} else if (is_array($object) || is_object($object)) {
			foreach ($object as $key => &$value) {
				static::decodeLinkForEdit($value);
			}
		}
	}

	// from title to id
	public static function encodeLink (&$object) {
		$patterns = [];$callbacks = [];
		/*
			handle [[gene|dnaA|test]] to [[gene|6740108089F13116F200C15F35C2E7561E990FEB|test]]
		*/
		$patterns[] = "/\[\[([^\[]+?)\|([^\]]+?)\|([^\]]+?)\]\]/";
		$callbacks[] = function ($matches) {
			$type = $matches[1];
			$class_name = ucfirst($type);
			$title = $matches[2];
			$alias = $matches[3];
			if (class_exists($class_name)) {
				$model = new $class_name();
				if ($model) {
					$object = $model->getRefWithTitle($title);
					if ($object) {
						return "[[$type|{$object->id}|{$alias}]]";
					}
				}
			}
			return $matches[0];
		};
		/*
			handle [[gene|dnaA]] to [[gene|6740108089F13116F200C15F35C2E7561E990FEB]]
		*/
		$patterns[] = "/\[\[([^\[\|]+?)\|([^\]\|]*?)\]\]/";
		$callbacks[] = function ($matches) {
			$type = $matches[1];
			$class_name = ucfirst($type);
			$title = $matches[2];
			if (class_exists($class_name)) {
				$model = new $class_name();
				if ($model) {
					$object = $model->getRefWithTitle($title);
					if ($object) {
						return "[[$type|{$object->id}]]";
					}
				}
			}
			return $matches[0];
		};
		if (is_string($object)) {
			for ($i=0; $i < 2; $i++) { 
				$object = preg_replace_callback($patterns[$i], $callbacks[$i], $object);
			}
		} else if (is_array($object) || is_object($object)) {
			foreach ($object as $key => &$value) {
				static::encodeLink($value);
			}
		}
	}
	
	public static function getValueFromKeypath ($object, $keypath) {
		if (is_string($keypath)) $keypath = new KeyPath($keypath);
		$value = (array) $object;
		foreach ($keypath as $key) {
			if (array_key_exists($key, $value)) {
				$value = $value[$key];
				// cast if is object
				if (is_object($value)) {
					$value = (array) $value;
				}
			} else {
				return null;
			}
		}
		return $value;
	}

	public static function hasKeypath ($object, $keypath) {
		if (is_string($keypath)) $keypath = new KeyPath($keypath);
		$value = (array) $object;
		foreach ($keypath as $key) {
			if (array_key_exists($key, $value)) {
				$value = $value[$key];
				// cast if is object
				if (is_object($value)) {
					$value = (array) $value;
				}
			} else {
				return false;
			}
		}
		return true;
	}
	public static function setValueFromKeypath (&$object, $keypath, $val) {
		if (!$keypath) return false;
		$segments = is_array($keypath) ? $keypath : explode('->', $keypath);
		$last = array_pop($segments);
		$cur =& $object;
		foreach ($segments as $segment) {
			if (is_object($cur)) {
				if (!isset($cur->{$segment})) $cur->{$segment} = [];
				$cur =& $cur->{$segment};
			} else if (is_array($cur)) {
				if (!isset($cur[$segment])) $cur[$segment] = [];
				$cur =& $cur[$segment];
			}
		}
		if (is_object($cur)) {
			$cur->{$last} = $val;
		} else if (is_array($cur)) {
			$cur[$last] = $val;
		}
	}
	public static function unsetValueFromKeypath(&$object, $keypath) {
		if (!$keypath) return false;
		$segments = is_array($keypath) ? $keypath : explode('->', $keypath);
		$last = array_pop($segments);
		$cur =& $object;
		foreach ($segments as $segment) {
			if (is_object($cur)) {
				if (!isset($cur->{$segment})) return;
				$cur =& $cur->{$segment};
			} else if (is_array($cur)) {
				if (!isset($cur[$segment])) return;
				$cur =& $cur[$segment];
			}
		}
		if (is_object($cur)) {
			unset($cur->{$last});
		} else if (is_array($cur)) {
			unset($cur[$last]);
		}
	}
	public static function arrayColumns ($arr, $keys) {
		return array_map(function($each) use ($keys) {
			$row = [];
			foreach ($keys as $key) {
				$row[$key] = is_object($each) ? $each->{$key} : $each[$key];
			}
			return $row;
		}, $arr);
	}
	// always return []
	// array_merge can return null if one of the args is null
	public static function arrayMerge () {
		$arrs = func_get_args();
		if (count($arrs)) {
			$all = [];
			foreach ($arrs as $arr) {
				if ($arr) {
					$all = array_merge($all, $arr);
				}
			}
			return $all;
		}
		return [];
	}

	public static function deflate ($object, $keypath = []) {
		$arr = [];
		foreach ($object as $key => $value) {
			$keypath[] = $key;
			if (is_object($value) || is_array($value)) {
				$arr = array_merge($arr, self::deflate($value, $keypath));
			} else {
				$arr[implode("->", $keypath)] = $value;
			}
			array_pop($keypath);
		}
		return $arr;
	}

	// if strict mode, then numberic key and string key are not allowed to be in the same associti
	public static function inflate ($intree, $strict = false, $errorMode = "ignore") {
		$object = [];
		foreach ($intree as $keypath => $val) {
			$keypath = explode("->", $keypath);
			$lastKey = array_pop($keypath);
			$cur = &$object;
			foreach ($keypath as $key) {
				$cur = &$cur[$key];
			}
			if ($strict) {
				if (!$cur || (self::isAssociateArray($cur) && !is_numeric($lastKey))) {
					$cur[$lastKey] = $val;
				} else if ($errorMode == "exception") {
					throw new BaseException("Type conflict in the input");
				}
			} else {
				$cur[$lastKey] = $val;
			}
		}
		return $object;
	}

	// unset keys of
	// 1. empty array
	// 2. empty object
	// 3. empty string
	// 4. null
	public static function clean (&$object = null) {
		$intree = self::deflate($object);
		$diff = array_filter($intree, function($val){
			return ($val === null || (is_string($val) && $val === "")); // only empty string, string with white characters will be kept
		});
		// clean null values or empty string
		if ($diff) {
			foreach ($diff as $keypath => $value) {
				self::unsetValueFromKeypath($object, $keypath);
			}
		}
		// clean empty arr and obj
		$result = self::findEmpty($object);
		if ($result) {
			foreach ($result as $keypath) {
				self::unsetValueFromKeypath($object, $keypath);
			}
		}
	}

	public static function findEmpty ($object, $keypath = []) {
		$all = [];
		foreach ($object as $key => $value) {
			$keypath[] = $key;
			if (json_encode($value) == "{}" or json_encode($value) == "[]") {
				// end case
				$keypath_string =  implode("->", $keypath);
				$all[] = $keypath_string;
			} else if (is_array($value) || is_object($value)) {
				// recursion
				$all = array_merge($all, self::findEmpty($value, $keypath));
			}
			array_pop($keypath);
		}
		return $all;
	}

	public static function LCS ($s1, $s2) {
		$l = min(strlen($s1),strlen($s2));
		$s = "";
		for ($i=0; $i < $l; $i++) { 
			if ($s1[$i] == $s2[$i]) {
				$s .= $s1[$i];
			} else {
				return $s;
			}
		}
	}

	// search for scalar value
	public static function deepSearch ($object, $val, $keypath = array()) {
		$all = [];
		foreach ($object as $key => $value) {
			$keypath[] = $key;
			if ($val == $value) {
				// end case
				$keypath_string =  implode("->", $keypath);
				$all[] = $keypath_string;
			} else if (is_array($value) || is_object($value)) {
				// recursion
				$all = array_merge($all, self::deepSearch($value, $val, $keypath));
			}
			array_pop($keypath);
		}
		return $all;
	}

	public static function startsWith($haystack, $needle) {
	     $length = strlen($needle);
	     return (substr($haystack, 0, $length) === $needle);
	}

	public static function endsWith($haystack, $needle) {
	    $length = strlen($needle);
	    return $length === 0 || 
	    (substr($haystack, -$length) === $needle);
	}

	public static function arraySpliceByPrefix (&$obj, $prefix) {
		$is_object = is_object($obj);
		$cp = (array) $obj;
		$arr = [];
		foreach ($cp as $key => $value) {
			if (self::startsWith($key, $prefix)) {
				$arr[str_replace($prefix, "", $key)] = $value;
				if ($is_object) unset($obj->{$key});
				else unset($obj[$key]);
			}
		}
		if ($is_object) {
			return (object) $arr;
		} else {
			return $arr;
		}
	}

	private static function insertAfterSimple (&$var, $key, $value, $after){
		$found = false;
		if (is_object($var)) {
			$copy = clone $var;
			foreach ($var as $k => $v) {
				if (!is_callable([$var, $k])) {
					unset($var->{$k});
				}
			}
			foreach($copy as $k => $v){
				if (!is_callable([$copy, $k])) {
					if ($k != $key)	$var->{$k} = $v;
					if ($after == $k) {
						$found = true;
						$var->{$key} = $value;
				   }
				}
			}
		} elseif (is_array($var)) {
			$copy = $var;
			foreach ($var as $k => $v) {
				if (!is_callable([$var, $k])) {
					unset($var[$k]);
				}
			}
			foreach($copy as $k => $v){
				if (!is_callable([$copy, $k])) {
					if ($k != $key) $var[$k] = $v;
					if ($after == $k){
						$var[$key] = $value;
						$found = true;
				   }
				}
			}
		} else {
			throw new BaseException("the first argument is not an array or an object");
		}

		if (!$found) {
			throw new BaseException("The keypath $after is not found");
		}
	}

	/**
	 * insert the key-value pair after a certain keypath
	 * @param $var {object/array} the object where the key-value pair inserts into
	 * @param $key {string} the key
	 * @param $value {mixed} the value
	 * @param $after {string/array/KeyPath} the keypath after which the key-value pair is inserted
	 */

	public static function insertAfter(&$var, $key, $value, $after){
		$kp = null;
		if (is_object($after) && $after instanceof KeyPath) {
			if ($after->length() == 1) {
				self::insertAfterSimple($var, $key, $value, $after->first());
			} else $kp = $after;
		} elseif (is_null($after)) {

		} elseif (is_string($after)) {
			// simple key
			if (strpos($after, "->") === false) {
				self::insertAfterSimple($var, $key, $value, $after);
			} else {
				$kp = new KeyPath($after);
			}
		} elseif (is_array($after)) {
			$kp = new KeyPath($after);
		} elseif (is_numeric($after)) {
			self::insertAfterSimple($var, $keypath, $value, $after);
		}

		if ($kp) {
			$last = $kp->last();
			$poped = $kp->pop();
			$object = $poped->get($var);
			if (is_array($object) || is_object($object)) {
				self::insertAfterSimple($object, $key, $value, $last);
				self::setValueFromKeypath($var, $poped, $object);
			} else {
				throw new BaseException("keypath $poped does not refer to an object nor an array", 1);
			}
		}
		
	}

	private static function insertBeforeSimple (&$var, $key, $value, $after){
		$found = false;
		if (is_object($var)) {
			$copy = clone $var;
			foreach ($var as $k => $v) {
				if (!is_callable([$var, $k])) {
					unset($var->{$k});
				}
			}
			foreach($copy as $k => $v){
				if (!is_callable([$copy, $k])) {
					if ($after == $k){
						$found = true;
						$var->{$key} = $value;
				 	}
					$var->{$k} = $v;
				}
			}
		} elseif (is_array($var)) {
			$copy = $var;
			foreach ($var as $k => $v) {
				if (!is_callable([$var, $k])) {
					unset($var[$k]);
				}
			}
			foreach($copy as $k => $v){
				if (!is_callable([$copy, $k])) {
					if ($after == $k){
						$var[$key] = $value;
						$found = true;
				   	}
					$var[$k] = $v;
				}
			}
		} else {
			throw new BaseException("the first argument is not an array or an object");
		}

		if (!$found) {
			throw new BaseException("The keypath $after is not found");
		}
	}

	public static function insertBefore(&$var, $key, $value, $after){
		$kp = null;
		if (is_string($after)) {
			// simple key
			if (strpos($after, "->") === false) {
				self::insertBeforeSimple($var, $key, $value, $after);
			} else {
				$kp = new KeyPath($after);
			}
		} elseif (is_array($after)) {
			$kp = new KeyPath($after);
		} elseif (is_numeric($after)) {
			self::insertBeforeSimple($var, $keypath, $value, $after);
		}

		if ($kp) {
			$last = $kp->last();
			$poped = $kp->pop();
			$object = $poped->get($var);
			if (is_array($object) || is_object($object)) {
				self::insertBeforeSimple($object, $key, $value, $last);
				self::setValueFromKeypath($var, $poped, $object);
			} else {
				throw new BaseException("keypath $poped does not refer to an object nor an array", 1);
			}
		}
	}

	public static function unshift (&$var, $key, $value) {
		$first = null;
		foreach ($var as $k => $v) {
			$first = $k;
			break;
		}
		self::insertBefore($var, $key, $value, $first);
	}

	public static function swap (&$a, &$b) {
		$tmp = $a;
		$a = $b;
		$b = $tmp;
	}
	
	public static function validateEmail($email)
	{
	    // SET INITIAL RETURN VARIABLES

	        $emailIsValid = FALSE;

	    // MAKE SURE AN EMPTY STRING WASN'T PASSED

	        if (!empty($email))
	        {
	            // GET EMAIL PARTS

	                $domain = ltrim(stristr($email, '@'), '@') . '.';
	                $user   = stristr($email, '@', TRUE);

	            // VALIDATE EMAIL ADDRESS

	                if
	                (
	                    !empty($user) &&
	                    !empty($domain) &&
	                    checkdnsrr($domain)
	                )
	                {$emailIsValid = TRUE;}
	        }

	    // RETURN RESULT

	        return $emailIsValid;
	}
}
?>