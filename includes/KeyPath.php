<?php
class KeyPath implements Iterator {
	private $segments = [];
	private $length = 0;
	private static $globalDelimiter = "->";
	private $delimiter;

	public static function setGlobalDelimiter ($delimiter) {
		self::$globalDelimiter = $delimiter;
	}

	public function setDelimiter ($delimiter) {
		$this->delimiter = $delimiter;
	}

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
			$this->segments = $arg;
		} elseif (is_string($arg)) {
			$this->segments = explode($this->delimiter, $arg);
		} else throw new BaseException("Invalid input");

		
		$this->length = count($this->segments);
	}

	public function __toString() {
		return implode($this->delimiter, $this->segments);
	}

	public function toString ($delimiter) {
		return implode($delimiter, $this->segments);
	}

	public function length() {
		return $this->length;
	}

	public function pop () {
		$ins = clone $this;
		array_pop($ins->segments);
		$ins->length--;
		return $ins;
	}

	public function push ($key) {
		$ins = clone $this;
		array_push($ins->segments, $key);
		$ins->length++;
		return $ins;
	}

	public function shift () {
		$ins = clone $this;
		array_shift($ins->segments);
		$ins->length--;
		return $ins;
	}

	public function unshift ($key) {
		$ins = clone $this;
		array_unshift($ins->segments, $key);
		$ins->length++;
		return $ins;
	}

	public function get ($object) {
		$value = (array) $object;
		foreach ($this->segments as $key) {
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
	
	public function toArray () {
		return $this->segments;
	}

	public function segmentAt($i) {
		return $this->segments[$i];
	}

	public function last () {
		return $this->segments[$this->length-1];
	}

	public function first () {
		return $this->segments[0];
	}

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

	/** Iterator */
	public function rewind () {
		reset($this->segments);
	}

	public function current () {
		return current($this->segments);
	}

	public function key () {
		return key($this->segments);
	}

	public function next () {
		return next($this->segments);
	}

	public function valid () {
		$key = $this->key();
		return $key !== null && $key !== false;
	}

	public function equalsTo (KeyPath $keypath) {
		return strtolower((string) $this) == strtolower((string) $keypath);
	}
}
?>