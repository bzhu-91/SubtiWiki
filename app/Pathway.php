<?php
class Pathway extends Model {
    static $tableName = "Pathway";

    public function insert () {
        $conn = Application::$conn;
        if ($this->title) {
            $conn->beginTransaction();
            if (parent::insert() && History::record($this, "add")) {
                $conn->commit();
                return true;
            } else {
                $conn->rollback();
                return false;
            }
        }
    }
}
?>