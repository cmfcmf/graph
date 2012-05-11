<?php

class Graph implements Countable{
    /**
     * array of all edges in this graph
     * 
     * @var array[Edge]
     */
	private $edges = array();
    
	/**
	 * array of all vertices in this graph
	 * 
	 * @var array[Vertex]
	 */
	private $vertices = array();
	
	/**
	 * create a new Vertex in the Graph
	 * 
	 * @param int|NULL $vertex          instance of Vertex or new vertex ID to use
	 * @param boolean  $returnDuplicate normal operation is to throw an exception if given id already exists. pass true to return original vertex instead
	 * @return Vertex (chainable)
	 * @throws Exception if given vertex $id is invalid or already exists
	 * @uses Vertex::getId()
	 */
	public function createVertex($id=NULL,$returnDuplicate=false){
	    if($id === NULL){    // no ID given
	        $id = $this->getNextId();
	    }else if(!is_int($id) && !is_string($id)){
	        throw new Exception('Vertex ID has to be of type integer or string');
	    }
	    if(isset($this->vertices[$id])){
	        if($returnDuplicate){
	            return $this->vertices[$id];
	        }
	        throw new Exception('ID must be unique');
	    }
	    $vertex = new Vertex($id,$this);
		$this->vertices[$id] = $vertex;
		return $vertex;
	}
	
	/**
	 * create a new Vertex in this Graph from the given input Vertex of another graph
	 * 
	 * @param Vertex $vertex
	 * @return Vertex new vertex in this graph
	 * @throws Exception
	 */
	public function createVertexClone($originalVertex){
	    $id = $originalVertex->getId();
	    if(isset($this->vertices[$id])){
	        throw new Exception('Id of cloned vertex already exists');
	    }
	    $newVertex = new Vertex($id,$this);
	    // TODO: properly set attributes of vertex
	    $this->vertices[$id] = $newVertex;
	    return $newVertex;
	}
	
	/**
	 * create new clone/copy of this graph - copy all attributes and vertices, but do NOT copy edges
	 * 
	 * using this method is faster than creating a new graph and calling createEdgeClone() yourself
	 *
	 * @return Graph
	 */
	public function createGraphCloneEdgeless(){
	    $graph = new Graph();
	    // TODO: set additional graph attributes
	    foreach($this->getVertices() as $vid=>$originalVertex){
	        $vertex = new Vertex($vid,$graph);
	        // TODO: set additional vertex attributes
	        $graph->vertices[$vid] = $vertex;
	    }
	    return $graph;
	}
	
	/**
	 * create new clone/copy of this graph - copy all attributes and vertices. but only copy all given edges
	 *
	 * @param array[Edge] $edge array of edges to be cloned
	 * @return Graph
	 * @uses Graph::createGraphCloneEdgeless()
	 * @uses Graph::createEdgeClone() for each edge to be cloned
	 */
	public function createGraphCloneEdges($edges){
	    $graph = $this->createGraphCloneEdgeless();
	    foreach($edges as $edge){
	        $graph->createEdgeClone($edge);
	    }
	    return $graph;
	}
	
	/**
	 * create new clone/copy of this graph - copy all attributes, vertices and edges
	 *
	 * @return Graph
	 * @uses Graph::createGraphCloneEdges() to clone graph with current edges
	 */
	public function createGraphClone(){
	    return $this->createGraphCloneEdges($this->edges);
	}
	
	/**
	 * create new clone of the given edge between adjacent vertices
	 * 
	 * @param Edge $originalEdge original edge from old graph
	 * @return Edge new edge in this graph
	 * @uses Edge::getVerticesId()
	 * @uses Graph::getVertex()
	 * @uses Vertex::createEdge() to create a new undirected edge if given edge was undrected
	 * @uses Vertex::createEdgeTo() to create a new directed edge if given edge was directed
	 * @uses Edge::getWeight()
	 * @uses Edge::setWeight()
	 */
	public function createEdgeClone($originalEdge){
	    $ends = $originalEdge->getVerticesId();
	    
	    $a = $this->getVertex($ends[0]); // get start vertex from old start vertex id
	    $b = $this->getVertex($ends[1]); // get target vertex from old target vertex id
	    
	    if($originalEdge instanceof EdgeDirected){
	        $newEdge = $a->createEdgeTo($b);
	    }else{
	        $newEdge = $a->createEdge($b); // create new edge between new a and b
	    }
	    // TODO: copy edge attributes
	    $newEdge->setWeight($originalEdge->getWeight());
	    
	    return $newEdge;
	}
	
	/**
	 * Return string with graph visualisation
	 *
	 * @return string
	 */
	public function toString(){
		$return = "Vertices of graph:\n";
	
		foreach ($this->vertices as $vertex){
			$return .= "\t".$vertex->toString()."\n";
		}
	
		return $return;
	}
	
	/**
	 * create the given number of vertices
	 * 
	 * @param int $n
	 * @return Graph (chainable)
	 * @uses Graph::getNextId()
	 */
	public function createVertices($n){
	    for($id=$this->getNextId(),$n+=$id;$id<$n;++$id){
	        $this->vertices[$id] = new Vertex($id,$this);
	    }
	    return $this;
	}
	
	/**
	 * get next free/unused/available vertex ID
	 * 
	 * its guaranteed there's NO other vertex with a greater ID
	 * 
	 * @return int
	 */
	private function getNextId(){
	    if(!$this->vertices){
	        return 0;
	    }
	    return max(array_keys($this->vertices))+1; // auto ID
	}
	
	/**
	 * returns the Vertex with identifier $id
	 * 
	 * @param int|string $id identifier of Vertex
	 * @return Vertex
	 * @throws Exception
	 */
	public function getVertex($id){
		if( ! isset($this->vertices[$id]) ){
			throw new Exception('Vertex '.$id.' does not exist');
		}
		
		return $this->vertices[$id];
	}
	
	/**
	 * return first vertex found
	 * 
	 * some algorithms do not need a particular vertex, but merely a (random)
	 * starting point. this is a convenience function to just pick the first
	 * vertex from the list of known vertices.
	 *
	 * @return Vertex first vertex found in this graph
	 * @throws Exception if Graph has no vertices
	 * @see Vertex::getFirst() if you need to apply ordering first
	 */
	public function getVertexFirst(){
	    foreach ($this->vertices as $vertex){
	        return $vertex;
	    }
	    
	    throw new Exception("Graph has no vertices");
	}
	
	/**
	 * returns an array of all Vertices
	 * 
	 * @return array[Vertex]
	 */
	public function getVertices(){
		return $this->vertices;
	}
	
	/**
	 * return number of vertices (implements Countable, allows calling count($graph))
	 * 
	 * @return int
	 * @see Countable::count()
	 */
	public function count(){
	    return count($this->vertices);
	}
	
	/**
	 * return number of vertices (aka. size of graph, |V| or just 'n')
	 * 
	 * @return int
	 */
	public function getNumberOfVertices(){
	    return count($this->vertices);
	}
	
	/**
	 * return number of edges
	 * 
	 * @return int
	 */
	public function getNumberOfEdges(){
	    return count($this->edges);
	}
	
	/**
	 * get degree for k-regular-graph (only if each vertex has the same degree)
	 * 
	 * @return int
	 * @throws Exception if graph is empty or not regular (i.e. vertex degrees are not equal)
	 * @uses Vertex::getIndegree()
	 * @uses Vertex::getOutdegree()
	 */
	public function getDegree(){
	    $degree = $this->getVertexFirst()->getIndegree(); // get initial degree of any start vertex to compare others to
	    
	    foreach($this->vertices as $vertex){
	        $i = $vertex->getIndegree();
	        
	        if($i !== $degree || $i !== $vertex->getOutdegree()){ // degree same (and for digraphs: indegree=outdegree)
	            throw new Exception('Graph is not k-regular');
	        }
	    }
	    
	    return $degree;
	}
	
	/**
	 * get minimum degree of vertices
	 *
	 * @return int
	 * @throws Exception if graph is empty or directed
	 * @uses Vertex::getFirst()
	 * @uses Vertex::getDegree()
	 */
	public function getMinDegree(){
	    return Vertex::getFirst($this->vertices,Vertex::ORDER_DEGREE)->getDegree();
	}
	
	/**
	 * get maximum degree of vertices
	 *
	 * @return int
	 * @throws Exception if graph is empty or directed
	 * @uses Vertex::getFirst()
	 * @uses Vertex::getDegree()
	 */
	public function getMaxDegree(){
	    return Vertex::getFirst($this->vertices,Vertex::ORDER_DEGREE,true)->getDegree();
	}
	
	
	/**
	 * checks whether this graph is regular, i.e. each vertex has the same indegree/outdegree
	 * 
	 * @return boolean
	 * @uses Graph::getDegree()
	 */
	public function isRegular(){
	    try{
	        $this->getDegree();
	        return true;
	    }
	    catch(Exception $ignore){ }
	    return false;
	}
	
	/**
	 * check whether graph is consecutive (i.e. all vertices are connected)
	 * 
	 * @return boolean
	 * @see Graph::getNumberOfComponents()
	 * @uses AlgorithmConnectedComponents::isSingle()
	 */
	public function isConsecutive(){
	    $alg = new AlgorithmConnectedComponents($this);
	    return $alg->isSingle();
	}
	
	/**
	 * check whether this graph has an eulerian cycle
	 * 
	 * @return boolean
	 * @uses AlgorithmEulerian::hasCycle()
	 * @link http://en.wikipedia.org/wiki/Eulerian_path
	 */
	public function hasEulerianCycle(){
	    $alg = new AlgorithmEulerian($this);
	    return $alg->hasCycle();
	}
	
	/**
	 * checks whether this graph is trivial (one vertex and no edges)
	 * 
	 * @return boolean
	 */
	public function isTrivial(){
	    return (!$this->edges && count($this->vertices) === 1);
	}
	
	/**
	 * checks whether this graph is empty (no vertex - and thus no edges, aka null graph)
	 * 
	 * @return boolean
	 */
	public function isEmpty(){
	    return !$this->vertices;
	}
	
	/**
	 * checks whether this graph has no edges
	 * 
	 * @return boolean
	 */
	public function isEdgeless(){
	    return !$this->edges;
	}
	
	/**
	 * checks whether this graph is complete (every vertex has an edge to any other vertex)
	 * 
	 * @return boolean
	 * @uses Vertex::hasEdgeTo()
	 */
	public function isComplete(){
	    $c = $this->vertices;                                                   // copy of array (separate iterator but same vertices)
	    foreach($this->vertices as $vertex){                                    // from each vertex
	        foreach($c as $other){                                              // to each vertex
	            if($other !== $vertex && !$vertex->hasEdgeTo($other)){          // missing edge => fail
	                return false;
	            }
	        }
	    }
	    return true;
	}
	
	/**
	 * checks whether the indegree of every vertex equals its outdegree
	 * 
	 * @return boolean
	 * @uses Vertex::getIndegree()
	 * @uses Vertex::getOutdegree()
	 */
	public function isBalanced(){
	    foreach($this->vertices as $vertex){
	        if($vertex->getIndegree() !== $vertex->getOutdegree()){
	            return false;
	        }
	    }
	    return true;
	}
	
	/**
	 * checks whether the graph has any directed edges (aka digraph)
	 * 
	 * @return boolean
	 */
	public function isDirected(){
	    foreach($this->edges as $edge){
	        if($edge instanceof EdgeDirected){
	            return true;
	        }
	    }
	    return false;
	}
	
	/**
	 * checks whether this graph has any weighted edges
	 * 
	 * edges usually have no weight attached. a weight explicitly set to (int)0
	 * will be considered as 'weighted'.
	 * 
	 * @return boolean
	 * @uses Edge::getWeight()
	 */
	public function isWeighted(){
	    foreach($this->edges as $edge){
	        if($edge->getWeight() !== NULL){
	            return true;
	        }
	    }
	    return false;
	}
	
	/**
	 * get total weight of graph (sum of weight of all edges)
	 * 
	 * edges with no weight assigned will evaluate to weight (int)0. thus an
	 * unweighted graph (see isWeighted()) will return total weight of (int)0.
	 * 
	 * returned weight can also be negative or (int)0 if edges have been
	 * assigned a negative weight or a weight of (int)0.
	 * 
	 * @return float total weight
	 * @see Graph::isWeighted()
	 * @uses Edge::getWeight()
	 */
	public function getWeight(){
	    $weight = 0;
	    foreach($this->edges as $edge){
	        $w = $edge->getWeight();
	        if($w !== NULL){
	            $weight += $w;
	        }
	    }
	    return $weight;
	}
	
	/**
	 * checks whether this graph has any loops (edges from vertex to itself)
	 * 
	 * @return boolean
	 * @uses Edge::isLoop()
	 */
	public function hasLoop(){
	    foreach($this->edges as $edge){
	        if($edge->isLoop()){
	            return true;
	        }
	    }
	    return false;
	}
	
	/**
	 * adds a new Edge to the Graph (MUST NOT be called manually!)
	 *
	 * @param Edge $edge instance of the new Edge
	 * @return void
	 * @private
	 * @see Vertex::createEdge()
	 */
	public function addEdge($edge){
	    $this->edges []= $edge;
	}
	
	/**
	 * remove the given edge from list of connected edges (MUST NOT be called manually!)
	 *
	 * @param Edge $edge
	 * @return void
	 * @private
	 * @see Edge::destroy() instead!
	 */
	public function removeEdge($edge){
	    $id = array_search($edge,$this->edges,true);
	    if($id === false){
	        throw new Exception('Given edge does NOT exist');
	    }
	    unset($this->edges[$id]);
	}
	
    /**
	 * remove the given vertex from list of known vertices (MUST NOT be called manually!)
	 *
	 * @param Vertex $vertex
	 * @private
	 * @see Vertex::destroy() instead!
	 */
	public function removeVertex($vertex){
	    $id = array_search($vertex,$this->vertices,true);
	    if($id === false){
	        throw new Exception('Given vertex does NOT exist');
	    }
	    unset($this->vertices[$id]);
	}
	
	/**
	 * returns an array of ALL Edges in this graph
	 *
	 * @return array[Edge]
	 * @private
	 */
	public function getEdges(){
	    return $this->edges;
	}
	
	/**
	 * @return int number of components of this graph
	 */
	public function getNumberOfComponents(){
		$alg = new AlgorithmConnectedComponents($this);
		return $alg->getNumberOfComponents();
	}
	
	/**
	 * do NOT allow cloning of objects
	 *
	 * @throws Exception
	 */
	private function __clone(){
	    throw new Exception();
	}
}
