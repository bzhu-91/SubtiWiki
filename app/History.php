<?php
class History extends \Monkey\Model {
	static $tableName = "History";
	static $primaryKeyName = "commit";

	public static function record ($obj, $operation){
		$className = get_class($obj);
		$record = null;
		if (is_subclass_of($obj, "Model")) {
			switch ($operation) {
				case "add":
					$record = clone $obj;
					break;
				case "remove":
				case "update":
					$record = $className::raw($obj->{$className::$primaryKeyName});
					break;
			}
		} else if ($obj instanceof Relationship || is_subclass_of($obj, "Relationship")) {
			switch ($operation) {
				case "add":
					$record = $obj;
					break;
				case "remove":
				case "update":
					$record = $obj->raw();
					break;
			}
		} else throw new \Monkey\BaseException("object is not an instance of Model or Relationship");

		if ($record) {
			if (is_subclass_of($record, "Model")) {
				$origin = lcfirst($className);
				$identifer = $record->{$className::$primaryKeyName};
			} else {
				$origin = lcfirst($record->getTableName());
				$identifer = $record->{$record->getPrimaryKeyName()};
			}
			$data = [
				"origin" => $origin,
				"identifier" => $identifer,
				"lastOperation" => $operation,
				"commit" => History::rand16(),
				"user" => User::getCurrent()->name,
				"record" => json_encode($record->getData()),
			];
			$conn = \Monkey\Application::$conn;
			return $conn->insert(self::$tableName, $data);
		} else return false;
	}

	private static function rand16() {
		$str = "";
		$table = "0123456789qwertzuiopasdfghjklyxcvbnmQWERTZUIOPASDFGHJKLYXCVBNM:$!-_{}[]()~.";
		for ($i=0; $i < 16; $i++) { 
			$str .= $table[rand(0, strlen($table)+1)];
		}
		return $str;
	}

	public static function getByFilter ($filters, $operations, $user = "", $page, $pageSize) {
		if (!$filters || !$operations ) {
			throw new BaseException("filters and operations are required");
		}
		$where0 = []; 
		foreach ($filters as $key => $value) {
			$where0[] = "`origin` like ?";
		}
		$vals0 = array_keys($filters);


		$where1 = [];
		foreach ($operations as $key => $value) {
			$where1[] = "`lastOperation` like ?";
		}
		$vals1 = array_keys($operations);

		$where = "(".implode(" or ", $where0).") and (".implode(" or ", $where1).")";
		$vals = array_merge($vals0, $vals1);

		if ($user && $user != "all") {
			$where .= " and user like ?";
			$vals[] = $user;
		}

		$where .= " order by time desc";

		$count = self::count($where, $vals);

		// add paging
		if ($page && $pageSize) {
			$where .= " limit ?,?";
			$vals[] = $pageSize*($page-1);
			$vals[] = $pageSize;
		}

		return [
			"records" => self::getAll($where, $vals),
			"count" => $count
		];
	}

	public static function findLastRevision ($target, $identifer) {
		$record = self::getAll("origin like ? and identifier like ? order by time desc limit 1", [$target, $identifer]);
		if ($record) {
			return $record[0];
		}
	}

	public static function parse ($str) {
		$current = \Monkey\Model::parse($str);
		if ($current) return $current;
		else {
			preg_match_all("/\{(\w+?)\|([^\[\]\|]+?)\}/i", $str, $matches);
			$className = ucfirst($matches[1][0]);
			if (!empty($matches)) {
				$record = self::findLastRevision($matches[1][0], $matches[2][0]);
				if ($record) {
					return $className::withData($record->record);
				}
			}
		}
	}
}
?>