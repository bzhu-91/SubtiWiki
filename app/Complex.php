<?php
class Complex extends \Kiwi\Model {
    static $tableName = "Complex";

    static $relationships = [
        "member" => [
            "tableName" => "ComplexMember",
            "mapping" => [
                "complex" => "Complex",
                "member" => "mixed"
            ],
            "position" => 1
        ],
        "reaction" => [
            "tableName" => "ReactionCatalyst",
            "mapping" => [
                "reaction" => "Reaction",
                "catalyst" => "mixed"
            ],
            "position" => 2
        ]
    ];

    public function insert () {
        $conn = \Kiwi\Application::$conn;
        $conn->beginTransaction();
        if (parent::insert() && History::record($this, "add")){
            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    }

    public function update () {
        $conn = \Kiwi\Application::$conn;
        $conn->beginTransaction();
        if (History::record($this, "update") && parent::update()){
            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    }

    public function delete  () {
        // if is associated with a reaction
        if ($this->has("reaction")) {
            return false;
        }
        $conn = \Kiwi\Application::$conn;
        $conn->beginTransaction();
        if (History::record($this, "remove") && parent::delete()){
            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    }

    public function addMember ($member, $coefficient, $modification) {
        if ($this->id) {
            $hasMember = $this->hasPrototype("member");
            $hasMember->complex = $this;
            $hasMember->coefficient = $coefficient;
            $hasMember->modification = $modification;
            $hasMember->member = $member;
            $conn = \Kiwi\Application::$conn;
            $conn->beginTransaction();
            if ($hasMember->insert() && History::record($hasMember, "add")) {
                $conn->commit();
                return true;
            } else {
                $conn->rollback();
                return false;
            }
        }
    }

    public function updateMember ($member, $coefficient) {
        if ($this->id) {
            $members = $this->has("member");
            $row = array_values(array_filter($members, function($each) use ($member){
                return (string) $member == (string) $each->member;
            }));
            if ($row) {
                $hasMember = $row[0];
                $hasMember->coefficient = $coefficient;
                $conn = \Kiwi\Application::$conn;
                $conn->beginTransaction();
                if (History::record($hasMember, "update") && $hasMember->update()) {
                    $conn->commit();
                    return true;
                } else {
                    $conn->rollback();
                    return false;
                }
            }
        }
    }

    public function removeMember ($member) {
        if ($this->id) {
            $members = $this->has("member");
            $row = array_values(array_filter($members, function($each) use ($member){
                return (string) $member == (string) $each->member;
            }));
            if ($row) {
                $hasMember = $row[0];
                $conn = \Kiwi\Application::$conn;
                $conn->beginTransaction();
                if (History::record($hasMember, "remove") && $hasMember->delete()) {
                    $conn->commit();
                    return true;
                } else {
                    $conn->rollback();
                    return false;
                }
            }
        }
    }
}