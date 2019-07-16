<?php
/**
 * The class for wiki, this is a replacement of the mediawiki engine.
 */
class Wiki extends \Kiwi\Model {
    static $tableName = "Wiki";
    /**
     * the insert function, tracks the version changes.
     * @return boolean the result of the insertion
     */
    public function insert () {
        if ($this->title) {
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
    }
    
    /**
     * the udpate function, tracks the version changes
     * @return boolean the result of the update
     */
    public function update () {
        if ($this->id && $this->title) {
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
    }

    /**
     * the delete function, tracks the version changes
     * @return boolean the result of the update
     */
    public function delete () {
        if ($this->id && $this->title) {
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
    }

    /**
     * create a string in the format of [wiki|the_title_of_the_wiki_page]
     * @return string the markup string
     */
    public function toLinkMarkup () {
        if ($this->title) {
            return "[wiki|".$this->title."]";
        }
    }
}
?>