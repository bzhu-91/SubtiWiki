<?php
class Node {
	public $id;
	public static function withData ($data) {
		$ins = new Node();
		foreach ($data as $key => $value) {
			if ($key[0] !== "_") {
				$ins->$key = $value;
			}
		}
		return $ins;
	}

	public function __toString () {
		return (string) $id;
	}
}

class Edge {
	public $from;
	public $to;
	protected $_directed = false;

	public static function withData ($data, $directed = false) {
		$ins = new Edge();
		$ins->_directed = $directed;
		foreach ($data as $key => $value) {
			if ($key[0] !== "_") {
				$ins->$key = $value;
			}
		}
		return $ins;
	}

	public function __toString () {
		if ($_directed) {
			return ((string) $this->from)."->".((string) $this->to);
		} else {
			if (strcmp((string) $this->from, (string) $this->to)) {
				return ((string) $this->from)."-".((string) $this->to);
			} else {
				return ((string) $this->to)."-".((string) $this->from);
			}
		}
	}

	public function toJSON () {
		$data = get_object_vars($this);
		foreach ($data as $key => $value) {
			if ($key[0] === "_"	) {
				unset($data[$key]);
			}
		}
		return $data;
	}
}

class Graph {
	protected $map = [];
	protected $nodes = [];
	protected $edges = [];
	protected $directed = false;
	// for induced subgraphs
	protected $center;
	protected $radius;
	protected $distances;

	/**
	 * constructor
	 * @param array   $data     [["from" => Model, "to" => Model]]
	 * @param boolean $directed graph is directed or not
	 */
	public function __construct ($data = [], $directed = false) {
		$this->directed = $directed;

		foreach ($data as $row) {
			if (is_object($row)) {
				$row = get_object_vars($row);
			}
			$from = $row["from"];
			$to = $row["to"];

			$this->nodes[$from->id] = Node::withData($from);
			$this->nodes[$to->id] = Node::withData($to);

			$row["from"] = $from->id;
			$row["to"] = $to->id;

			$edge = Edge::withData($row, $directed);

			$this->edges[] = $edge;

			if (!array_key_exists($from->id, $this->map)) {
				$this->map[$from->id] = [
					"predecessors" => [],
					"successors" => []
				];
			}

			if (!array_key_exists($to->id, $this->map)) {
				$this->map[$to->id] = [
					"predecessors" => [],
					"successors" => []
				];
			}

			$this->map[$from->id]["successors"][] = $to->id;
			$this->map[$to->id]["predecessors"][] = $from->id;

			if (!$this->directed) {
				$this->map[$from->id]["predecessors"][] = $to->id;
				$this->map[$to->id]["successors"][] = $from->id;
			}
		}
		$this->edges = array_values(array_unique($this->edges));
	}

	/**
	 * get all the nodes in the shortest Path Tree
	 * @param  string/number  $nodeId      id of the starting node
	 * @param  integer $maxDistance maximal distance to look into the graph, default is -1, means no limits
	 * @return array               [nodeid => distance]
	 */
	public function shortestPathTree ($nodeId, $maxDistance = -1, $ignoreDirection = false) {
		$pipe = [$nodeId]; $distances = [$nodeId => 0];
		$c = 0;
		while ($pipe) {
			$first = array_shift($pipe);
			$previousDistance = $distances[$first];
			$neighbors = $this->map[$first]["successors"];
			foreach ($neighbors as $id) {
				if (!array_key_exists($id, $distances)) {
					if ($maxDistance == -1 || $previousDistance < $maxDistance) {
						$distances[$id] = $previousDistance + 1 ;
						if (!in_array($id, $pipe)) $pipe[] = $id;
					}
				}
			}
			if ($ignoreDirection || !$this->directed) {
				$neighbors = $this->map[$first]["predecessors"];
				foreach ($neighbors as $id) {
					if (!array_key_exists($id, $distances)) {
						if ($maxDistance == -1 || $previousDistance < $maxDistance) {
							$distances[$id] = $previousDistance + 1 ;
							if (!in_array($id, $pipe)) $pipe[] = $id;
						}
					}
				}
			}
		}

		return $distances;
	}

	/**
	 * get the subgraph by center and radius
	 * @param  string/number $center id of the center node
	 * @param  integer $radius radius of the subgraph, default = 1	
	 * @return Graph|null        the subgraph, if $center is not found, return null
	 */
	public function subgraph ($center, $radius = 1, $ignoreDirection = false) {
		if (array_key_exists($center, $this->nodes)) {
			$distances = $this->shortestPathTree ($center, $radius, $ignoreDirection);
			$sub = new Graph ();
			$sub->map = array_filter($this->map, function ($v, $k) use ($distances) {
				return array_key_exists($k, $distances);
			}, ARRAY_FILTER_USE_BOTH);
			$sub->nodes = array_filter($this->nodes, function ($v, $k) use ($distances) {
				return array_key_exists($k, $distances);
			}, ARRAY_FILTER_USE_BOTH);
			$sub->edges = array_values(array_filter($this->edges, function ($v) use ($distances) {
				return array_key_exists($v->from, $distances) && array_key_exists($v->to, $distances);
			}));
			$sub->directed = $this->directed;
			$sub->distances = $distances;
			$sub->center = $center;
			$sub->radius = $radius;
			return $sub;
		}
	}

	/**
	 * create a json presentation of the graph
	 * @return array ["nodes" => [], "edges" => [], "distances" => []]
	 */
	public function toJSON () {
		$result = [
			"nodes" => array_values($this->nodes),
			"edges" => [],
		];

		if ($this->distances) {
			$result["distances"] = $this->distances;
		}

		foreach ($this->edges as $edge) {
			$result["edges"][] = $edge->toJSON();
		}

		return $result;
	}
}
?>