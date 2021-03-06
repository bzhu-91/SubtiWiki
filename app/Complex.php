<?php
/**
 * For protein-protein, protein-ligand, RNA-Protein, complexes..
 */
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

    public static function findByMember ($member) {
        $sql = "select distinct complex from `".self::$tableName."` where member like ? ";
        $searchResult = \Kiwi\Application::$conn->doQuery($sql, [$member]);
        if ($searchResult) {
            $result = [];
            foreach($searchResult as $row) {
                $result[] = self::get($row["complex"]);
            }
            return $result;
        }
    }

    /**
     * insert to database, History recorded
     * @return boolean whether insertion is successful or not
     */
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

    /**
     * update to database, History recorded
     * @return boolean whether update is successful or not
     */
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

    /**
     * delete an instance, foreign key to ReactionCatalyst checked
     * @return boolean whether update is successful or not
     */
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

    /**
     * add member to a complex
     * @param mixed $member the member
     * @param number $coefficient the coefficient
     * @param string $modification the modification of the member, can be "P" or "Met"
     * @return boolean whether the operation is successful
     */
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

    /**
     * update the coefficient
     * @param mixed $member the member
     * @param number $coefficient the coefficient to be update
     * @return boolean whether the operation is successful
     */
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

    /**
     * remove a member
     * @param mixed $member the member
     * @return boolean whether the operation is successful
     */
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