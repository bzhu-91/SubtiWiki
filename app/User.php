<?php
class User extends \Monkey\Model {

	use \Monkey\ReferenceCache;

	static $tableName = "User";
	static $primaryKeyName = "name"; // by default is "id"

	protected $token;
	protected $registration;
	protected $password;


	public static function initLookupTable () {
		// if use self::lookupTable, this will be shared between all classes which use this trait
		// if use get_called_class()::lookupTable, each class will has its own copy
		$className = get_called_class();
		if (!$className::$lookupTable) {
			$con = \Monkey\Application::$conn;
			$className = get_called_class();
			if ($con && static::$tableName) {
				$result = $con->select(static::$tableName, ["name", "id", "token", "registration"], "1");
				$keys = array_column($result, "name");
				foreach ($keys as &$key) {
					$key = strtolower($key);
				}
				foreach ($result as &$row) {
					$row = $className::withData($row);
				}
				$className::$lookupTable = array_combine($keys, $result);
			}
		}
	}

	public function patch () {}

	/**
	 * user login, create session
	 * @param  string $password md5 hashed password
	 * @return boolean           true if success, false if not
	 */
	public function login($password) {
		$sql = "select id from ".self::$tableName." where password = concat(':B:',substr(password, 4,8),':',md5(concat(substr(password,4,8),'-',?))) and name = ?";
		if ($this->id) {
			$conn = \Monkey\Application::$conn;
			$result = $conn->doQuery($sql, [$password, $this->name]);
			if ($result) {
				$_SESSION["name"] = $this->name;
				$_SESSION["id"] = $this->id;
				$_SESSION["token"] = $this->token;
				return true;
			}
		}
		return false;
	}

	/**
	 * insert a new user to database
	 * @return number/boolean lastInsertId if there is one, otherwise true if success, false if failed
	 */
	public function insert () {
		$this->name = ucfirst($this->name);
		$this->createUserToken();
		$this->encryptPassword();
		// no history
		return parent::insert();
	}

	/**
	 * get the current user
	 * @return User/null current user if there is one
	 */
	public static function getCurrent() {
		if (array_key_exists("name", $_SESSION)) {
			return User::get($_SESSION["name"]);
		}
	}

	/**
	 * generate random 32 bit user token
	 * set $this->token
	 * @return none 
	 */
	protected function createUserToken () {
		$token = "";
		$pool = "0123456789abcde";
		for($i = 0; $i < 32; $i ++) {
			$randNum = rand(0,14);
			$token = $token.$pool[$randNum];
		}
		$this->token = $token;
	}

	/**
	 * create salt and encrypt the raw password, set $this->password
	 * @return [type] [description]
	 */
	protected function encryptPassword () {
		$salt = "";
		$pool = "0123456789abced";
		for($i = 0; $i < 8; $i ++ ){
			$randNum = rand(0,14);
			$salt = $salt.$pool[$randNum];
		}
		$this->password = ":B:".$salt.":".md5($salt. "-".$this->_rawPassword);
	}

	/**
	 * log out funciton ,destroys the session
	 * @return none 
	 */
	public function logout () {
		session_destroy();
	}

	public function toLinkMarkup () {
		return "[user|".$this->name."|".$this->name."]";
	}

	public function update () {
		$conn = \Monkey\Application::$conn;
		if ($this->name) {
			$old = self::get($this->name);
			if ($this->email && $this->email != $old->email) {
				// email change, change the token as well
				$validation = User\Invitation::get($old->email);
				if ($validation) {
					$conn->beginTransaction();
					$sql = "update ".User\Invitation::$tableName." set email = ? where email = ?";
					if ($conn->doQuery($sql, [$this->email, $validation->email]) && parent::update()) {
						$conn->commit();
						return true;
					} else {
						$conn->rollback();
						return false;
					}
				}
			}
			if ($this->_rawPassword) {
				$this->encryptPassword();
			}
			return parent::update();
		}
	}
}
?>