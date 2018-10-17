<?php
class Wiki extends Model {
    static $tableName = "Wiki";

    public function insert () {
        if ($this->title) {
            $conn = Application::$conn;
            $conn->beginTransaction();
            if (parent::insert() && History::record($this, "add")){
                $conn->commit();
                return true;
            } else {
                $conn->rollback();
                return false;
            }
        }
    }
    
    public function update () {
        if ($this->id && $this->title) {
            $conn = Application::$conn;
            $conn->beginTransaction();
            if (History::record($this, "update") && parent::update()){
                $conn->commit();
                return true;
            } else {
                $conn->rollback();
                return false;
            }
        }
    }

    public function delete () {
        if ($this->id && $this->title) {
            $conn = Application::$conn;
            $conn->beginTransaction();
            if (History::record($this, "remove") && parent::delete()){
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