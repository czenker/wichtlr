<?php


use Czenker\Wichtlr\Domain\Participant;
use Czenker\Wichtlr\Graph\Graph;
use Czenker\Wichtlr\Graph\Node;
use Czenker\Wichtlr\Graph\Service;

class ServiceTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Service
     */
    protected $graphService;

    public function setUp() {
        $this->graphService = new Service();
    }

    public function testConnectAllNodes() {
        $graph = new Graph();
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));
        $aliceNode = new Node(new Participant('alice'));
        $bobNode = new Node(new Participant('bob'));

        $graph->addNode($johnNode);
        $graph->addNode($janeNode);
        $graph->addNode($aliceNode);
        $graph->addNode($bobNode);

        $this->graphService->connectAllNodes($graph);

        $this->assertSame(4, $graph->countNodes(), 'still 4 nodes');

        foreach($graph->getNodes() as $node) {
            /** @var Node $node */
            $this->assertSame(
                3, $node->countEdges(),
                sprintf('%s has 3 edges', $node)
            );
            $this->assertFalse(
                $node->hasEdgeTo($node),
                sprintf('%s has no edge to itself', $node)
            );
        }
    }

}
