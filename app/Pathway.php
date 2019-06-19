<?php
class Pathway extends \Monkey\Model {
    static $tableName = "Pathway";

    public function update () {
        $conn = \Monkey\Application::$conn;
        if ($this->id) {
            $conn->beginTransaction();
            if (History::record($this, "update") && parent::update()) {
                $conn->commit();
                return true;
            } else {
                $conn->rollback();
                return false;
            }
        }
    }

    public function insert () {
        $conn = \Monkey\Application::$conn;
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

    /**
     * @param Array $reactions the reactions
     * @return true/false 
     */
    public function setReactions ($reactions) {
        $conn = \Monkey\Application::$conn;
        if ($this->id && $reactions) {
            $conn->beginTransaction();
            $sql = "delete from ReactionPathway where pathway = ?";
            if ($conn->doQuery($sql, [$this->id])) {
                $vals = [];
                $placeholders = array_fill(0, count($reactions), "(?,?)");
                $sql = "insert into ReactionPathway (pathway, reaction) values ";
                foreach($reactions as $reaction) {
                    $vals[] = $this->id;
                    $vals[] = $reaction->id;
                }
                $sql .= implode(",", $placeholders);
                if ($conn->doQuery($sql, $vals)) {
                    $conn->commit();
                    return true;
                }
            }
            $conn->rollback();
            return false;
        }
    }
}
?>