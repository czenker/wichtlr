<?php


namespace Czenker\Wichtlr\Graph;

/**
 * a graph with nodes (participant) and directional edges (possible donee)
 *
 * @package Czenker\Wichtlr\Graph
 */
class Graph {

    /**
     * @var array
     */
    protected $nodes = array();

    public function addNode(Node $node) {
        if($this->hasNode($node)) {
            throw new \InvalidArgumentException(sprintf('Collection already contains node %s.', $node));
        }
        $this->nodes[] = $node;
    }

    public function removeNode(Node $node) {
        if(!$this->hasNode($node)) {
            throw new \LogicException(sprintf('graph does not contain node %s.', $node));
        }

        $key = array_search($node, $this->nodes, TRUE);
        unset($this->nodes[$key]);
    }

    public function removeEdgesTo(Node $node) {
        foreach($this->nodes as $otherNode) {
            /** @var $otherNode Node */
            $otherNode->removeEdgesTo($node);
        }
    }

    public function hasNode(Node $node) {
        return in_array($node, $this->nodes, TRUE);
    }

    public function countNodes() {
        return count($this->nodes);
    }

    public function getNodeWithLeastOutgoingEdges() {
        $leastNode = NULL;
        $leastCount = NULL;

        foreach($this->nodes as $node) {
            /** @var $node Node */
            if($leastCount === NULL || $node->countEdges() < $leastCount) {
                $leastCount = $node->countEdges();
                $leastNode = $node;
            }
        }

        return $leastNode;
    }

    /**
     * @return array
     */
    public function getNodes() {
        return $this->nodes;
    }

    /**
     * clone the whole graph object, but not the participants
     */
    public function __clone() {
        $newNodes = array();

        // maps original nodes to the cloned ones
        $nodeMap = new \SplObjectStorage();
        // clone the nodes
       foreach($this->nodes as $node) {
            /** @var Node $node */
            $newNode = new Node($node->getParticipant());
            $newNodes[] = $newNode;
            $nodeMap->attach($node, $newNode);
       }
       // clone the edges
       foreach($this->nodes as $node) {
           /** @var Node $node */
           /** @var Node $newNode */
           $newNode = $nodeMap->offsetGet($node);
           foreach($node->getEdges() as $edge) {
               /** @var $edge Edge */
               $newEdge = new Edge(
                   $nodeMap->offsetGet($edge->getSourceNode()),
                   $nodeMap->offsetGet($edge->getTargetNode())
               );
               $newNode->addEdge($newEdge);
           }

       }
        $this->nodes = $newNodes;
    }


}