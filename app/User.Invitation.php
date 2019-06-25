<?php
namespace User;

class Invitation extends \Kiwi\Model{
	protected $fromWhom;
	static $tableName = "UserInvitation";
	static $primaryKeyName = "email";

	/**
	 * insert an invitation to the database
	 * @return boolean true if succeeded, false if failed
	 */
	public function insert () {
		$conn = \Kiwi\Application::$conn;
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
	static public function getByToken ($token) {
		$sql = "select * from ".static::$tableName." where token like ? && expired is null";
		$con = \Kiwi\Application::$conn;
		$result = $con->doQuery($sql, [$token]);
		if ($result) return self::withData($result[0]);
	}

	/**
	 * set this invitation expired
	 * @return boolean true if succeeded, false if failed
	 */
	public function expire () {
		$con = \Kiwi\Application::$conn;
		return $con->doQuery("update ".static::$tableName." set expired = 1 where token = ?", [$token]);
	}
}
?>