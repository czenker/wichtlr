<?php


use Czenker\Wichtlr\Domain\Participant;
use Czenker\Wichtlr\Graph\Edge;
use Czenker\Wichtlr\Graph\Node;

class NodeTest extends PHPUnit_Framework_TestCase {

    public function testParticipant() {
        $participant = new Participant('john');
        $node = new Node($participant);

        $this->assertSame($participant, $node->getParticipant());
    }

    public function testAddingEdges() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));

        $johnJaneEdge = new Edge($johnNode, $janeNode);
        $johnNode->addEdge($johnJaneEdge);

        $this->assertSame(1, $johnNode->countEdges(), 'node has one edge');
        $this->assertTrue($johnNode->hasEdge($johnJaneEdge), 'node has the added edge');
    }

    public function testAddingEdgesThrowsExceptionIfSourceNodeNotMatching() {
        $this->setExpectedException('LogicException');

        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));
        $aliceNode = new Node(new Participant('alice'));

        $janeAliceEdge = new Edge($janeNode, $aliceNode);

        $johnNode->addEdge($janeAliceEdge);
    }

    public function testAddingEdgesTwiceThrowsException() {
        $this->setExpectedException('InvalidArgumentException');

        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));

        $johnJaneEdge = new Edge($johnNode, $janeNode);

        $johnNode->addEdge($johnJaneEdge);
        $johnNode->addEdge($johnJaneEdge);
    }

    public function testRemovingEdges() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));
        $aliceNode = new Node(new Participant('alice'));

        $johnJaneEdge = new Edge($johnNode, $janeNode);
        $johnAliceEdge = new Edge($johnNode, $aliceNode);

        $johnNode->addEdge($johnJaneEdge);
        $johnNode->addEdge($johnAliceEdge);
        $johnNode->removeEdge($johnJaneEdge);

        $this->assertSame(1, $johnNode->countEdges(), 'node has one edge');
        $this->assertFalse($johnNode->hasEdge($johnJaneEdge), 'edge to jane was removed');
        $this->assertTrue($johnNode->hasEdge($johnAliceEdge), 'edge to alice was not removed');
    }

    public function testAddEdgesTo() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));

        $johnNode->addEdgeTo($janeNode);

        $this->assertSame(1, $johnNode->countEdges(), 'node has one edge');
        $this->assertSame($janeNode, current($johnNode->getEdges())->getTargetNode(), 'target of edge is janeNode');
    }

    public function testAddMultipleEdgesToSameNode() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));

        $johnNode->addEdgeTo($janeNode);
        $johnNode->addEdgeTo($janeNode);
        $johnNode->addEdgeTo($janeNode);

        $this->assertSame(3, $johnNode->countEdges(), 'multiple edges can be added');
        $this->assertSame($janeNode, current($johnNode->getEdges())->getTargetNode(), 'target of edge is janeNode');
    }

    public function testRemoveEdgesTo() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));
        $aliceNode = new Node(new Participant('alice'));

        $johnNode->addEdgeTo($janeNode);
        $johnNode->addEdgeTo($janeNode);
        $johnNode->addEdgeTo($aliceNode);

        $johnNode->removeEdgesTo($janeNode);

        $this->assertSame(1, $johnNode->countEdges(), 'one node is still there');
        $this->assertSame($aliceNode, current($johnNode->getEdges())->getTargetNode(), 'target of edge is aliceNode');

    }

}
