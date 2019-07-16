<?php
namespace Kiwi;

/**
 * This class is a wrap-around of the PDO class. Magic function __call is implement to "inherit" all methods from PDO
 */
class DBBase {
	public $lastError;
	public $last_warning;
	protected $dbh;
	protected $db_struct = [];

	/**
	 * construct exactly like PDO, as PDO is final, wrap it around
	 * @param string $dsn  dsn
	 * @param string $user user name
	 * @param string $pass password
	 */
	function __construct($dsn, $user, $pass){
		$this->dbh = new \PDO($dsn, $user, $pass);
		$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}
	
	/**
	 * hack to inherite all PDO methods
	 * @param  string $method name of the method
	 * @param  array $args   arguments
	 * @return mixed         
	 */
	public function __call($method, $args) {
		if (isset($this->$method)) {
			$this->last_warning = null;
			$this->lastError = null;
			$func = $this->$method;
			return call_user_func_array($func, $args);
		} else {
			$str = [];
			for ($i=0; $i < count($args); $i++) { 
				$str[] = '$args['.$i."]";
			}
			$str = join(",", $str);
			return eval('return $this->dbh->'.$method.'('.$str.');');
		}
	}

	/**
	 * execute the sql statement
	 * @param  string $sql  sql statement
	 * @param  array $vals values to replace ? in the where clause
	 * @return mixed PDOStatement if SQL excution is successful, false if not
	 */
	public function doQuery($sql, $vals = []){
		try {
			$this->lastError = null;
			$stmt = $this->dbh->prepare($sql);
			for ($i=0; $i < count($vals); $i++) {
				$value = $vals[$i];
				if (is_null($value)) {
					$stmt->bindValue($i + 1, $value, \PDO::PARAM_NULL);
				} else if (is_string($value)) {
	 				$stmt->bindValue($i + 1, $value, \PDO::PARAM_STR);
	 			} else {
	 				$stmt->bindValue($i + 1, $value, \PDO::PARAM_INT);
	 			}
			}
			$stmt->execute();
			return $stmt;
		} catch (\Exception $e) {
			// Log::debug($e->getMessage());
			$this->lastError = $sql."; --".$e->getMessage();
			return false;
		}
	}

	/**
	 * get the columns name of the given table
	 * @param  string $table_name table name
	 * @return array array of column names
	 */
	public function getColumnNames ($table_name) {
		if (!array_key_exists($table_name, $this->db_struct)) {
			$sql = "desc `$table_name`";
			$stmt = $this->query($sql);
			if ($stmt) {
				$columns = [];
				foreach ($stmt as $row) {
					$columns[] = $row["Field"];
				}
				$this->db_struct[$table_name] = $columns;
				return $columns;
			}
		} else {
			return $this->db_struct[$table_name];
		}
	}

	/**
	 * execute a transaction
	 * @param function $func the function
	 * @return boolean
	 */
	public function transaction ($func) {
		try {
			$this->dbh->beginTransaction(); // if already in transaction, exception can be thrown
		} catch (\Exception $e) {
			return false;
		}
		if ($func()) {
			$this->dbh->commit();
			return true;
		} else {
			$this->dbh->rollback();
			return false;
		}
	}
	
	/**
	 * parse object to array, for other data types will simple return the input
	 * @param  mixed $input input
	 * @return mixed        
	 */
	protected static function objectToArray ($input) {
		if (is_object($input)) {
			return get_object_vars($input);
		}
		return $input;
	}
}
?>