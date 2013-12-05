<?php


use Czenker\Wichtlr\Domain\Participant;
use Czenker\Wichtlr\Graph\Node;

class EdgeTest extends PHPUnit_Framework_TestCase {

    public function testSourceNode() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));

        $edge = new \Czenker\Wichtlr\Graph\Edge($johnNode, $janeNode);

        $this->assertSame($johnNode, $edge->getSourceNode());
    }

    public function testTargetNode() {
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));

        $edge = new \Czenker\Wichtlr\Graph\Edge($johnNode, $janeNode);

        $this->assertSame($janeNode, $edge->getTargetNode());
    }

}
