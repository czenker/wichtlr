<?php


use Czenker\Wichtlr\Domain\Participant;
use Czenker\Wichtlr\Graph\Edge;
use Czenker\Wichtlr\Graph\Graph;
use Czenker\Wichtlr\Graph\Node;

class GraphTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Graph
     */
    protected $graph;

    public function setUp() {
        $this->graph = new Graph();
    }

    public function testAddingNodes() {
        $johnNode = new Node(new Participant('john'));

        $this->graph->addNode($johnNode);

        $this->assertSame(1, $this->graph->countNodes(), 'graph has one node');
        $this->assertTrue($this->graph->hasNode($johnNode), 'graph contains johnNode');
    }

    public function testAddingNodesTwiceThrowsException() {
        $this->setExpectedException('InvalidArgumentException');

        $johnNode = new Node(new Participant('john'));

        $this->graph->addNode($johnNode);
        $this->graph->addNode($johnNode);
    }

    public function testRemovingNodes() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));

        $this->graph->addNode($johnNode);
        $this->graph->addNode($janeNode);
        $this->graph->removeNode($johnNode);

        $this->assertSame(1, $this->graph->countNodes(), 'graph has one node');
        $this->assertFalse($this->graph->hasNode($johnNode), 'johnNode was removed');
        $this->assertTrue($this->graph->hasNode($janeNode), 'janeNode was not removed');
    }

    public function testRemoveEdgesTo() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));
        $aliceNode = new Node(new Participant('alice'));

        $this->graph->addNode($johnNode);
        $this->graph->addNode($janeNode);
        $this->graph->addNode($aliceNode);
        $johnNode->addEdgeTo($janeNode);
        $janeNode->addEdgeTo($johnNode);
        $janeNode->addEdgeTo($aliceNode);

        $this->graph->removeEdgesTo($johnNode);

        $this->assertSame(1, $janeNode->countEdges(), 'janeNode has only one edge');
        $this->assertSame($aliceNode, current($janeNode->getEdges())->getTargetNode(), 'janeNode has edge to alice still.');
    }

    public function testGetNodeWithLeastOutgoingEdges() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));
        $aliceNode = new Node(new Participant('alice'));

        $this->graph->addNode($johnNode);
        $this->graph->addNode($janeNode);
        $this->graph->addNode($aliceNode);
        $johnNode->addEdgeTo($janeNode);
        $janeNode->addEdgeTo($johnNode);
        $janeNode->addEdgeTo($aliceNode);

        $this->assertSame($aliceNode, $this->graph->getNodeWithLeastOutgoingEdges());
    }

    public function testCloning() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));
        $aliceNode = new Node(new Participant('alice'));

        $this->graph->addNode($johnNode);
        $this->graph->addNode($janeNode);
        $this->graph->addNode($aliceNode);
        $johnNode->addEdgeTo($janeNode);
        $janeNode->addEdgeTo($johnNode);
        $janeNode->addEdgeTo($aliceNode);

        $otherGraph = clone $this->graph;

        $this->assertNotSame($this->graph, $otherGraph, 'Graph is a different object');
        /** @var Node $node */
        $node = current($this->graph->getNodes());
        /** @var Node $otherNode */
        $otherNode = current($otherGraph->getNodes());
        $this->assertNotSame($node, $otherNode, 'Node is a different object');
        /** @var Edge $edge */
        $edge = current($node->getEdges());
        /** @var Edge $otherEdge */
        $otherEdge = current($otherNode->getEdges());
        $this->assertNotSame($edge, $otherEdge, 'Edge is a different object');
        $this->assertSame($otherEdge->getSourceNode(), $otherNode, 'Nodes in edges are also cloned correctly');

    }
}
