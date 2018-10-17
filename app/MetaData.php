<?php
class MetaData extends Model {
	static $tableName = "MetaData";
	static $primaryKeyName = "className";

	public static function insertKeyValuePair (&$object, $keypath, $value) {
		if (is_string($keypath)) $keypath = new KeyPath($keypath);
		// find possible previous one
		$className = get_class($object);
		$meta = self::get($className);
		if ($meta) {
			$scheme = $meta->scheme;
			$previous = []; 
			foreach($scheme as &$each) {
				$each->path = new KeyPath($each->path);
				if ($each->path->equalsTo($keypath)) {
					break;
				} else {
					array_unshift($previous, $each->path);
				}
			}
			
			$inserted = false;
			foreach($previous as $path) {
				if (Utility::hasKeypath($object, $path)) {
					Utility::insertAfter($object, $keypath, $value, $path);
					$inserted = true;
					break;
				}
			}
			if (!$inserted) {
				Utility::setValueFromKeypath($object, $keypath, $value);
			}
		}
	}


	// find the scheme of the given object
	public static function track ($object) {
		// get the tablename
		$className = get_class($object);
		$structure = self::deflate($object);
		$meta = self::get($className);
		if ($meta) {
			$current = $meta->scheme;
			if ($current) {
				$merged = self::align($current, $structure);
				if (self::inversionTest($merged)) {
					if (count($merged) != count($structure)) {
						$meta->scheme = $merged;
						return $meta->update();	
					}
					return true;	
				} else {
					return false;
				}
			} else {
				$meta->scheme = $structure;
				// send an email to the admin
				return $meta->update();
			}
		} else {
			$meta = new MetaData();
			$meta->className = $className;
			$meta->scheme = $structure;
			return $meta->insert();			
		}
	}

	public static function analyse ($className) {
		$allObjects = $className::getAll(1);
		$meta = self::get($className);
		$anomalies = [];
		if ($meta && $meta->scheme) {
			$count = count($meta->scheme);
			foreach ($allObjects as $object) {
				$structure = self::deflate($object);
				foreach ($structure as $each) {
					if (!self::hasPath($each, $meta->scheme)) {
						$anomalies[] = $object;
						Log::debug($each);
						continue;
					}
				}
			}
		}
		return $anomalies;
	}

	public static function hasPath ($path, $structure) {
		foreach ($structure as $each) {
			if (self::pathEqual($each->path, $path->path)) {
				return true;
			}
		}
		return false;
	}

	// sort out the object according to the scheme
	// @return array or null
	public static function sort ($object) {
		// need to add failsafe here
		// get the tablename
		$className = get_class($object);
		$meta = self::get($className);
		$sorted = [];

		if ($meta) {
			$template = array_column($meta->scheme, "path");
			foreach ($template as $path) {
				$keypath = new KeyPath($path);
				$val = $keypath->get($object);
				if (!is_null($val)) {
					$sorted[(string) $keypath] = $val;
				}
			}
			return Utility::inflate($sorted);
		}
	}

	// fill in all the keys according to the scheme
	public static function fill ($object, $placeholder = "") {
		// get the tablename
		$className = get_class($object);
		$meta = self::get($className);
		$sorted = [];

		if ($meta) {
			foreach ($meta->scheme as $entry) {
				$keypath = new KeyPath($entry->path);
				$val = $keypath->get($object);
				if (!is_null($val)) {
					$sorted[(string) $keypath] = $val;
				} elseif ($entry->default) {
					// use default if it is defined
					$sorted[(string) $keypath] = $entry->default;
				} elseif ($entry->type == "b" || $entry->type == "ab") {
					$sorted[(string) $keypath] = [$placeholder];
				} else {
					$sorted[(string) $keypath] = $placeholder;
				}
			}
			return Utility::inflate($sorted, true); // use strict mode
		}	
	}

	// deflate the object, different from Utility::deflate, multidimentional array is not allowed
	public static function deflate ($object, $keypath = []) {
		$result = [];
		foreach ($object as $key => $value) {
			if ($key[0] != "_") {
				$keypath[] = $key;
				if (is_object($value)) {
					$result = array_merge($result, self::deflate($value, $keypath));
				} elseif (is_array($value)) {
					if (self::arrayDimention($value)) {
						$result[] = (object) [
							"path"=> $keypath,
							"type"=> "b"
						];
					} else throw new BaseException("multidimentional array found");
				} else {
					$result[] = (object) [
						"path"=> $keypath,
						"type"=> "b"
					];
				}
				array_pop($keypath);	
			}
		}
		return $result;
	}

	// find out if array is a non-assosiative, single-dimentional array or not
	public static function arrayDimention ($array) {
		// test if is the array of scalar type or array of objects or arrays
		foreach ($array as $key => $value) {
			if (is_array($value) || is_object($value)) {
				return false;
			}
		}
		return true;
	}

	// case insensitive, space insensitive comparison of the key paths
	// arguments are two arrays
	public static function pathEqual ($a, $b) {
		// case imsensitive comparision
		foreach ($a as &$k) {
			$k = preg_replace("/\s+/", " ", $k);
			$k = strtolower($k);
			$k = trim($k);
		}
		foreach ($b as &$k) {
			$k = preg_replace("/\s+/", " ", $k);
			$k = strtolower($k);
			$k = trim($k);
		}
		return implode("->", $a) === implode("->", $b);
	}

	// Needlemann-Wunsch algorithm
	// use extremly high mismatch penalty to avoid mismatch completely
	// $a is used as global template
	public static function align ($a, $b) {
		$rowCount = count($b) + 1;
		$colCount = count($a) + 1;
		$scores = array_pad([], $rowCount, array_pad([], $colCount, 0));
		$directions = array_pad([], $rowCount, array_pad([], $colCount, "stop"));
		// fill the scores
		// first row and column
		for ($i=0; $i < $rowCount; $i++) { 
			$scores[$i][0] = -$i;
		}
		for ($i=0; $i < $colCount; $i++) { 
			$scores[0][$i] = -$i;
		}

		// match: 10, mismatch: -100, gap: 0
		for ($i=1; $i < $rowCount; $i++) { 
			for ($j=1; $j < $colCount; $j++) { 
				$isMatch = self::pathEqual($a[$j-1]->path, $b[$i-1]->path);
				$diagonal = $scores[$i-1][$j-1] + ($isMatch ? 10 : -10000);
				$left = $scores[$i][$j-1];
				$top = $scores[$i-1][$j];
				$max = max($diagonal, $left, $top);
				if ($diagonal == $max) {
					$directions[$i][$j] = "diagonal";
				} elseif ($left == $max) {
					$directions[$i][$j] = "left";
				} else {
					$directions[$i][$j] = "top";
				}
				$scores[$i][$j] = $max;
			}
		}
		// backtrace
		$alignment = [];
		$i = $rowCount-1; $j = $colCount-1;
		while ($i >= 0 && $j >= 0) {
			switch ($directions[$i][$j]) {
				case 'diagonal':
					// is match
					array_unshift($alignment, $a[$j-1]);
					$i -= 1;
					$j -= 1;
					break;
				case 'left':
					array_unshift($alignment, $a[$j-1]);
					$j -= 1;
					break;
				case 'top':
					array_unshift($alignment, $b[$i-1]);
					$i -= 1;
					break;
				case 'stop':
					break 2;
			}
		}
		return $alignment;
	}

	// for debug use
	public static function printMatrix($m, $rowCount, $colCount) {
		$str = "<table>";
		for ($i=0; $i < $rowCount; $i++) {
			$str .= "<tr>"; 
			for ($j=0; $j < $colCount; $j++) { 
				$str .= "<td>".$m[$i][$j]."</td>";
			}
			$str .= "</tr>";
		}
		$str .= "</table>";
		echo $str;
	}

	// TODO: but how should we recover from failed inversion test?
	public static function inversionTest ($alignment) {
		// if the same key entry exists in the $alignment, then an inversion is detected
		$count = count($alignment);
		for ($i=0; $i < $count; $i++) { 
			for ($j=$i+1; $j < $count; $j++) { 
				$a = $alignment[$i];
				$b = $alignment[$j];
				if (self::pathEqual($a->path, $b->path)) {
					return false;
				}
			}
		}
		return true;	
	}
}
?>