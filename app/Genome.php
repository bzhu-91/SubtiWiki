<?php
class Genome extends \Monkey\Model {
	static $tableName = "GenomicContext";
	static $fileName = "res/genome.txt";
	static $primaryKeyName = "object"; // Not exactly the primary key in the table, but can be used to update gene coordinates

	public static function findContextByGene ($id, $span) {
		if ($id) {
			$gene = self::getAll("object like ?", ["%{$id}%"]);
			if ($gene) {
				$gene = $gene[0];
				$r = $gene->stop; $l = $gene->start;
				if ((abs($r - $l) + 2 * $span) > $GLOBALS["GENOME_LENGTH"]) {
					$l = 0;
					$r = $GLOBALS["GENOME_LENGTH"];
				} else {
					$r += (($r + $span) > $GLOBALS["GENOME_LENGTH"]) ?  ($span - $GLOBALS["GENOME_LENGTH"]) : $span;
					$l -= (($l - $span) < 0) ?( $span - $GLOBALS["GENOME_LENGTH"] ): $span;
				}
				return self::findContextBySpan($l, $r);
			}
		}
		return false;
	}

	public static function findContextBySpan ($l, $r) {
		$conn = \Monkey\Application::$conn;
		$range = [];
		if ($l > $r) {
			$range[] = [$l, $GLOBALS["GENOME_LENGTH"]];
			$range[] = [0, $r];
		} else {
			$range[] = [$l, $r];
		}
		$where = [];
		$vals = [];
		foreach ($range as $a) {
			// (start < r) && (start >= l || stop > l)
			array_push($where, "(start < ? ) && (start >= ? || stop > ?)");
			$vals[] = $a[1]; // r
			$vals[] = $a[0]; // l
			$vals[] = $a[0]; // l
		}
		$where = join(" || ", $where);

		$els = $conn->select(self::$tableName, ['start','stop','strand','object'], $where, $vals);
		if($els) {
			foreach ($els as &$el) {
				$object = \Monkey\Model::parse($el["object"]);
				if ($object) {
					$object->start = $el["start"];
					$object->stop = $el["stop"];
					$object->strand = $el["strand"];
					$object->type = lcfirst(get_class($object));
					$el = $object;
				} else {
					$el["title"] = $el["type"] = $el["object"];
					unset($el["object"]);
				}
			}
			return $els;
		}
		return false;
	}

	public static function findSequenceByLocation ($s, $e, $strand) {
		$file = fopen(self::$fileName,'r');
		if(fseek($file, $s - 1) == 0){
			$seq = fread($file, ($e - $s + 1));
			if (!$strand) {
				$seq = str_split($seq);
				foreach ($seq as &$base) {
					switch ($base) {
						case 'A':
							$base = 'T';
							break;
						case 'T':
							$base = 'A';
							break;
						case 'C':
							$base = 'G';
							break;
						case 'G':
							$base = 'C';
							break;
					}
				}
				$seq = strrev(join("", $seq));
			}
			return $seq;
		} else {
			return false;
		}
	}

	public static function findSequenceByGene ($geneId) {
		$gene = static::getAll("object like ?", ["{gene|$geneId}"]);
		if ($gene) {
			$gene = $gene[0];
			return self::findSequenceByLocation($gene->start, $gene->stop, $gene->strand);
		}
	}

	public static function codonTable () {
		$codonString = trim($GLOBALS["CODON_TABLE"]);
		$strings = explode("\n", $codonString);
		if (count($strings) != 5) {
			return false;
		}
		foreach($strings as &$str) {
			$str = trim($str);
			if (strlen($str) != 64) {
				\Monkey\Log::debug(strlen($str));
				return false;
			}
		}
		$A = $strings[0];
		$B = $strings[1];
		$C = $strings[2];
		$P = $strings[3];
		$S = $strings[4];
		$codonTable = [];
		$startTable = [];
		for ($i = 0; $i < 64; $i++) {
			$path = new \Monkey\KeyPath([$A[$i], $B[$i], $C[$i]]);
			$path->set($codonTable, $P[$i]);
			$path->set($startTable, $S[$i]);
		}
		return [
			"codonTable" => $codonTable,
			"startTable" => $startTable
		];
	}
}

if ($GLOBALS["GENOME_FILE_NAME"]) {
	Genome::$fileName = $GLOBALS["GENOME_FILE_NAME"];
}
?>
