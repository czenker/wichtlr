<?php


namespace Czenker\Wichtlr\Graph;


class Edge {

    /**
     * @var Node
     */
    protected $sourceNode;

    /**
     * @var Node
     */
    protected $targetNode;

    public function __construct(Node $sourceNode, Node $targetNode) {
        $this->sourceNode = $sourceNode;
        $this->targetNode = $targetNode;
    }

    /**
     * @return \Czenker\Wichtlr\Graph\Node
     */
    public function getSourceNode() {
        return $this->sourceNode;
    }

    /**
     * @return \Czenker\Wichtlr\Graph\Node
     */
    public function getTargetNode() {
        return $this->targetNode;
    }

    public function __toString() {
        return sprintf('%s->%s', $this->sourceNode, $this->targetNode);
    }



}