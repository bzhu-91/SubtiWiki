<?php
class Pubmed extends \Monkey\Model {
	static $tableName ="Pubmed";

	public function downloadData () {
		if ($this->id) {
			$url = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&id=".$this->id;
			// download
			$re = fopen($url,"r");
			$data = "";
			do {
				$buffer = fread($re, 512);
				$data .= $buffer;
			} while($buffer);
			$pubmed = new DOMDocument();
			$pubmed->loadXML($data);
			// find title
			$xpath = new DOMXPath($pubmed);
			$findList = ["Title", "FullJournalName", "SO"];
			$resultList = [];
			foreach ($findList as $attribute) {
				$query = "//DocSum/Item[@Name='".$attribute."']";
				$entries = $xpath->query($query);
				$item = $entries->item(0);
				$resultList[$attribute] = $item->nodeValue;
			}
			$query = "//DocSum/Item[@Name='AuthorList']/Item[@Name='Author']";
			$entries = $xpath->query($query);
			$resultList["Authors"] = [];
			for ($i=0; $i < $entries->length; $i++) { 
				$item = $entries->item($i);
				$resultList["Authors"][] = $item->nodeValue;
			}
			$resultList["Authors"] = implode(", ", $resultList["Authors"]);
			$authors = $resultList["Authors"];
			$title = $resultList["Title"];
			$journal = $resultList["FullJournalName"].". ".$resultList["SO"];
			$journal .= ". PMID: ".$this->id;
			$journal = str_replace(";", "; ", $journal);
			$this->report = "<div class='pubmed' id='".$this->id."'><span class='pubmed_author'>$authors</span><span class='pubmed_title'>$title</span><span class='pubmed_journal'>$journal</span></div>";
		}
	}

	public static function download ($id) {
		$ins = new Pubmed();
		$ins->id = $id;
		$ins->downloadData();
		if ($ins->report && $ins->id != 0) {
			$ins->insert();
			return $ins;
		}
	}
}
?>