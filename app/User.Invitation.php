<?php
namespace User;

class Invitation {
	public $token;
	public $email;
	public $name;
	public $type;
	protected $fromWhom;

	static $tableName = "UserInvitation";

	/**
	 * create an instance with given data
	 * @param  array/object $data given data to be copied to the new instance
	 * @return User\Invitation       the invitation instance
	 */
	public static function withData ($data) {
		$instance = new Invitation();
		foreach ($data as $key => $value) {
			$instance->$key = $data;
		}
	}

	/**
	 * insert an invitation to the database
	 * @return boolean true if succeeded, false if failed
	 */
	public function insert () {
		$conn = \Application::$conn;
		$this->fromWhom = \User::getCurrent();
		return $conn->doQuery(
			"insert into ".static::$tableName." (token, email, name, type, fromWhom) values (?,?,?,?,?) on duplicate key update token = ?, name = ?, type = ?, fromWhom = ?", 
			[$this->token, $this->email, $this->name, $this->type, $this->fromWhom->name, $this->token, $this->name, $this->type, $this->fromWhom->name]);
	}

	/**
	 * validate an invitation token
	 * @param  string $token invitation token
	 * @return array        information about this invitation token
	 */
	public function get ($token) {
		$sql = "select * from ".static::$tableName." where token like ? && expired is null";
		$con = \Application::$conn;
		return $con->doQuery($sql, [$token]);
	}

	/**
	 * set this invitation expired
	 * @return boolean true if succeeded, false if failed
	 */
	public function expire () {
		$con = \Application::$conn;
		return $con->doQuery("update ".static::$tableName." set expired = 1 where token = ?", [$token]);
	}
}
?>