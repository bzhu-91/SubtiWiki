<?php
namespace Kiwi;
/**
 * Tools
 */
class Utility {
	use UtilityExtra;
	
	/**
	 * try to cast the value to types
	 * @param mixed $val
	 * @return mixed casted value
	 */
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

	/**
	 * replace [[gene|6740108089F13116F200C15F35C2E7561E990FEB]] with [gene|6740108089F13116F200C15F35C2E7561E990FEB|test]
	 * @param mixed $object the input
	 */
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

	/**
	 * replace [[gene|6740108089F13116F200C15F35C2E7561E990FEB]] with [[gene|dnaA]]
	 * @param mixed $object the input
	 */
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

	/**
	 * replace [[gene|6740108089F13116F200C15F35C2E7561E990FEB]] with [[gene|6740108089F13116F200C15F35C2E7561E990FEB]]
	 * @param mixed $object the input
	 */
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
					$object = $model->simpleValidate($title);
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
					$object = $model->simpleValidate($title);
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
	
	/**
	 * return the array of objects where each object in the array with given keys
	 * @param array $arr the array of objects
	 * @param array $keys the keys
	 * @return array the array of object only with the given keys
	 */
	public static function arrayColumns ($arr, $keys) {
		return array_map(function($each) use ($keys) {
			$row = [];
			foreach ($keys as $key) {
				$row[$key] = is_object($each) ? $each->{$key} : $each[$key];
			}
			return $row;
		}, $arr);
	}

	/**
	 * patch of array_merge, because array_merge can return null if one of the args is null
	 * @param array/null $arrs...
	 * @return array the merged array
	 */
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

	/**
	 * create a tree presentation of the object.
	 * Example:
	 * [
	 * 		0 => [
	 * 			"name" => "Chris",
	 * 			"gender" => "M"
	 * 		]
	 * ] will become
	 * 		
	 * [
	 * 		"0->name" => "Chris",
	 * 		"0->gender" => "M"
	 * ]
	 * 
	 */
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
	/**
	 * inflate an object from a tree presentation
	 * Example:
	 * 		
	 * [
	 * 		"0->name" => "Chris",
	 * 		"0->gender" => "M"
	 * ] will become
	 * [
	 * 		0 => [
	 * 			"name" => "Chris",
	 * 			"gender" => "M"
	 * 		]
	 * ] will become
	 * 
	 * @param array $intree the tree presentation of an object
	 * @param boolean $strict whether use strict mode or not
	 * @param string $errorMode error mode, default ignore
	 */
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

	/**
	* unset keys of
	*	1. empty array
	*	2. empty object
	*	3. empty string
	*	4. null	
	* @param object/array $object the object to be cleaned
	*/
	public static function clean (&$object = null) {
		$intree = self::deflate($object);
		$diff = array_filter($intree, function($val){
			return ($val === null || (is_string($val) && $val === "")); // only empty string, string with white characters will be kept
		});
		// clean null values or empty string
		if ($diff) {
			foreach ($diff as $keypath => $value) {
				$keypath = new \Kiwi\KeyPath($keypath);
				$keypath->unset($object);
			}
		}
		// clean empty arr and obj
		$result = self::findEmpty($object);
		if ($result) {
			foreach ($result as $keypath) {
				$keypath = new \Kiwi\KeyPath($keypath);
				$keypath->unset($object);
			}
		}
	}

	/**
	 * find the empty object or array
	 * @param object/array $object the object to be searched with
	 * @param array $keypath
	 * @return array array of \Kiwi\KeyPaths
	 */
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

	/**
	 * deep search of a certain value
	 * @param object/array $object the object to search with
	 * @param mixed $val the value to search
	 * @param array $keypath used for recursion
	 * @return array array of \Kiwi\KeyPaths
	 */
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

	/**
	 * deep filter of the object
	 * @param object/array $object the object to search with
	 * @param function $func comparison function, takes $keypath:array and $value:mixed as parameter
	 * @param array $keypath used for recursion
	 * @return array array of \Kiwi\KeyPaths
	 */
	public static function deepFilter ($object, $func, $keypath = array()) {
		$all = [];
		foreach ($object as $key => $value) {
			$keypath[] = $key;
			if ($func($keypath, $value)) {
				// end case
				$keypath_string =  implode("->", $keypath);
				$all[] = $keypath_string;
			} else if (is_array($value) || is_object($value)) {
				// recursion
				$all = array_merge($all, self::deepFilter($value, $val, $keypath));
			}
			array_pop($keypath);
		}
		return $all;
	}

	/**
	 * deep walk of the object
	 * @param object/array $object the object to search with
	 * @param function $func function applied to each \Kiwi\KeyPath, takes $keypath:array and $value:mixed as parameter
	 * @param array $keypath used for recursion
	 */
	public static function deepWalk (&$object, $func, $keypath = array()) {
		$all = [];
		foreach ($object as $key => &$value) {
			$keypath[] = $key;
			$func($keypath, $value);
			if (is_array($value) || is_object($value)) {
				// recursion
				self::deepWalk($value, $func, $keypath);
			}
			array_pop($keypath);
		}
	}

	/** 
	 * slimiar to String.startsWith in javascript 
	 * @param string $haystack
	 * @param string $needle the prefix
	 * @return boolean
	 * */
	public static function startsWith($haystack, $needle) {
	     $length = strlen($needle);
	     return (substr($haystack, 0, $length) === $needle);
	}

	/** 
	 * slimiar to String.endsWith in javascript 
	 * @param string $haystack
	 * @param string $needle the suffix
	 * @return boolean
	 * */
	public static function endsWith($haystack, $needle) {
	    $length = strlen($needle);
	    return $length === 0 || 
	    (substr($haystack, -$length) === $needle);
	}

	/**
	 * take all key-value pairs in an object where all keys starts with the prefix. The prefix is removed in the result.
	 * Example:
	 * [
	 * 		"user_name" => "Chris",
	 * 		"user_gender" => "M",
	 * 		"company_name" => "COM",
	 * 		"company_id" => 42
	 * ] spliced with prefix "user_" will result
	 * [
	 * 		"name" => "Chris",
	 * 		"gender" => "M"
	 * ]
	 * @param object/array $object the input object
	 * @param string $prefix the prefix of the keys
	 * @return array/object type dependent on the input obj, the spliced key-value pairs, and prefix is removed
	 */
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

	/**
	 * insert after a key, no recursion
	 * @param array/object $var the object/array
	 * @param string/number $key the key
	 * @param mixed $value the value to be inserted
	 * @param string/number $after after this key the given key will be inserted
	 */
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
			throw new BaseException("The \Kiwi\KeyPath $after is not found");
		}
	}

	/**
	 * insert after a \Kiwi\KeyPath, recursion used
	 * @param array/object $var the object/array
	 * @param string/number $key the \Kiwi\KeyPath
	 * @param mixed $value the value to be inserted
	 * @param string/number $after after this key the given key will be inserted
	 */
	public static function insertAfter(&$var, $key, $value, $after){
		$kp = null;
		if (is_object($after) && $after instanceof \Kiwi\KeyPath) {
			if ($after->length() == 1) {
				self::insertAfterSimple($var, $key, $value, $after->first());
			} else $kp = $after;
		} elseif (is_null($after)) {

		} elseif (is_string($after)) {
			// simple key
			if (strpos($after, "->") === false) {
				self::insertAfterSimple($var, $key, $value, $after);
			} else {
				$kp = new \Kiwi\KeyPath($after);
			}
		} elseif (is_array($after)) {
			$kp = new \Kiwi\KeyPath($after);
		} elseif (is_numeric($after)) {
			self::insertAfterSimple($var, $keypath, $value, $after);
		}

		if ($kp) {
			$last = $kp->last();
			$poped = $kp->pop();
			$object = $poped->get($var);
			if (is_array($object) || is_object($object)) {
				self::insertAfterSimple($object, $key, $value, $last);
				self::setValueFromKeyPath($var, $poped, $object);
			} else {
				throw new BaseException("\Kiwi\KeyPath $poped does not refer to an object nor an array", 1);
			}
		}
		
	}

	/**
	 * insert before a key, no recursion
	 * @param array/object $var the object/array
	 * @param string/number $key the key
	 * @param mixed $value the value to be inserted
	 * @param string/number $after before this key the given key will be inserted
	 */
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
			throw new BaseException("The \Kiwi\KeyPath $after is not found");
		}
	}

	/**
	 * insert before a \Kiwi\KeyPath, recursion used
	 * @param array/object $var the object/array
	 * @param string/number $key the \Kiwi\KeyPath
	 * @param mixed $value the value to be inserted
	 * @param string/number $after before this key the given key will be inserted
	 */
	public static function insertBefore(&$var, $key, $value, $after){
		$kp = null;
		if (is_string($after)) {
			// simple key
			if (strpos($after, "->") === false) {
				self::insertBeforeSimple($var, $key, $value, $after);
			} else {
				$kp = new \Kiwi\KeyPath($after);
			}
		} elseif (is_array($after)) {
			$kp = new \Kiwi\KeyPath($after);
		} elseif (is_numeric($after)) {
			self::insertBeforeSimple($var, $keypath, $value, $after);
		}

		if ($kp) {
			$last = $kp->last();
			$poped = $kp->pop();
			$object = $poped->get($var);
			if (is_array($object) || is_object($object)) {
				self::insertBeforeSimple($object, $key, $value, $last);
				self::setValueFromKeyPath($var, $poped, $object);
			} else {
				throw new BaseException("\Kiwi\KeyPath $poped does not refer to an object nor an array", 1);
			}
		}
	}

	/**
	 * insert a \Kiwi\KeyPath-value pair at the beginning of the object/array
	 * @param array/object $var the object/array
	 * @param string/number $key the \Kiwi\KeyPath
	 * @param mixed $value the value to be inserted
	 */
	public static function unshift (&$var, $key, $value) {
		$first = null;
		foreach ($var as $k => $v) {
			$first = $k;
			break;
		}
		self::insertBefore($var, $key, $value, $first);
	}

	/**
	 * swap two variables
	 * @param mixed $a the variable
	 * @param mixed $b the variable
	 */
	public static function swap (&$a, &$b) {
		$tmp = $a;
		$a = $b;
		$b = $tmp;
	}
	
	/**
	 * determine if a given string a valid email address or not
	 * @param string $email the email address to be tested
	 * @return boolean
	 */
	public static function validateEmailAddress($email){
		$emailIsValid = FALSE;
		if (!empty($email)) {
			$domain = ltrim(stristr($email, '@'), '@') . '.';
			$user   = stristr($email, '@', TRUE);
			if (
				!empty($user) &&
				!empty($domain) &&
				checkdnsrr($domain)
				) {
					$emailIsValid = TRUE;
				}
		}
		return $emailIsValid;
	}
}
?>