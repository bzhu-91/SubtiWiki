<?php
/**
 * This class implements a key path for highly-nested data
 */
class KeyPath implements Iterator {
	private $keys = [];
	private $length = 0;
	private static $globalDelimiter = "->";
	private $delimiter;

	/**
	 * set the delimiter for all instances
	 * @param string $delimiter the delimiter of the key path
	 * @return void
	 */
	public static function setGlobalDelimiter ($delimiter) {
		self::$globalDelimiter = $delimiter;
	}

	/**
	 * set the delimiter for this instance
	 * @param string $delimiter the delimiter of the key path
	 * @return void
	 */
	public function setDelimiter ($delimiter) {
		$this->delimiter = $delimiter;
	}

	/**
	 * constructor
	 * @param array/string keypath
	 * @param string @delimiter delimiter of this instance, if not given, the global delimiter is used
	 */
	public function __construct ($arg = null, $delimiter = null) {
		if ($delimiter) {
			$this->delimiter = $delimiter;
		} else {
			$this->delimiter = self::$globalDelimiter;
		}

		if ($arg === null) {
			$arg = [];
		}
		if (is_array($arg)) {
			$this->keys = $arg;
		} elseif (is_string($arg)) {
			$this->keys = explode($this->delimiter, $arg);
		} elseif ($arg instanceof KeyPath) {
			// copy constructor
			$this->keys = $arg->keys;
			$this->delimiter = $arg->delimiter;
			$this->length = count($this->keys);
		} else throw new BaseException("Invalid input");
		$this->length = count($this->keys);
	}

	/**
	 * to string
	 * @return string key path in string presentation
	 */
	public function __toString() {
		return implode($this->delimiter, $this->keys);
	}

	/**
	 * to string
	 * @return string key path in string presentation
	 */
	public function toString ($delimiter) {
		return implode($delimiter, $this->keys);
	}

	/**
	 * get the length of the keypath
	 * @return int length of the keypath
	 */
	public function length() {
		return $this->length;
	}

	/**
	 * remove the last key from the key path
	 * @return KeyPath the new keypath without the last key
	 */
	public function pop () {
		$ins = clone $this;
		array_pop($ins->keys);
		$ins->length--;
		return $ins;
	}

	/**
	 * append a new key to the keypath
	 * @return KeyPath the new keypath with the new key appended
	 */
	public function push ($key) {
		$ins = clone $this;
		array_push($ins->keys, $key);
		$ins->length++;
		return $ins;
	}

	/**
	 * remove the first key from the keypath
	 * @return KeyPath the new keypath without the first key
	 */
	public function shift () {
		$ins = clone $this;
		array_shift($ins->keys);
		$ins->length--;
		return $ins;
	}

	/**
	 * prepend a new key to the keypath
	 * @return KeyPath the new keypath with the new key prepended
	 */
	public function unshift ($key) {
		$ins = clone $this;
		array_unshift($ins->keys, $key);
		$ins->length++;
		return $ins;
	}

	/**
	 * get the value of the keypath from an object
	 * @param object/array $object the object or array
	 * @return mixed the value of the keypath from the give object
	 */
	public function get ($object) {
		$value = (array) $object;
		foreach ($this->keys as $key) {
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

	/**
	 * test if the key path exists in the gibe object
	 * @param object/array $object
	 * @param boolean
	 */
	public function test ($object) {
		$value = (array) $object;
		foreach ($this->keys as $key) {
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
	
	/**
	 * get the keypath in array presentation
	 * @return array the array presentation
	 */
	public function toArray () {
		return $this->keys;
	}

	/**
	 * get the i-th key of the keypath
	 * @param int index
	 * @return mixed the key
	 */
	public function segmentAt($i) {
		return $this->keys[$i];
	}

	/**
	 * get the last key of the keypath
	 * @return mixed the last key
	 */
	public function last () {
		return $this->keys[$this->length-1];
	}

	/**
	 * get the first key of the keypath
	 * @return mixed the first key
	 */
	public function first () {
		return $this->keys[0];
	}

	/**
	 * set the value of the keypath for an object or array
	 * @param object/array the object to be processed
	 * @param mixed val the value to be set
	 */
	public function set (&$object, $val) {
		$last = $this->last();
		$cur = &$object;
		foreach ($this->pop() as $segment) {
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

	/**
	 * unset the keypath of an object or array
	 * @param object/array the object to be processed
	 */
	public function unset (&$object) {
		$last = $this->last();
		$cur =& $object;
		foreach ($this->pop() as $segment) {
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

	public function rewind () {
		reset($this->keys);
	}

	public function current () {
		return current($this->keys);
	}

	public function key () {
		return key($this->keys);
	}

	public function next () {
		return next($this->keys);
	}
	
	public function valid () {
		$key = $this->key();
		return $key !== null && $key !== false;
	}

	/**
	 * equals to, case insensitive comparison of the keypaths
	 * @param KeyPath $keypath the keypath to be compared with
	 * @return boolean
	 */
	public function equalsTo (KeyPath $keypath) {
		return strtolower((string) $this) == strtolower((string) $keypath);
	}
}
?>