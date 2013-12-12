<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Christian Zenker <dev@xopn.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

use Czenker\Wichtlr\Domain\Participant;
use Czenker\Wichtlr\Graph\Edge;
use Czenker\Wichtlr\Graph\Graph;
use Czenker\Wichtlr\Graph\Node;
use Czenker\Wichtlr\Graph\Service;
use Czenker\Wichtlr\Solver\RandomizedPath;

class RandomizedPathTest extends PHPUnit_Framework_TestCase {

    /**
     * @var RandomizedPath
     */
    protected $randomizedPath;

    public function setUp() {
        $this->randomizedPath = new RandomizedPath();
    }

    protected function assertValidSolution($edges) {
        $sources = array();
        $targets = array();
        foreach($edges as $edge) {
            /** @var Edge $edge */

            $this->assertFalse(
                in_array($edge->getSourceNode()->getParticipant()->getIdentifier(), $sources, TRUE),
                sprintf('%s is not yet source of an edge', $edge->getSourceNode()->getParticipant()->getIdentifier())
            );
            $sources[] = $edge->getSourceNode()->getParticipant()->getIdentifier();

            $this->assertFalse(
                in_array($edge->getTargetNode()->getParticipant()->getIdentifier(), $targets, TRUE),
                sprintf('%s is not yet target of an edge', $edge->getTargetNode()->getParticipant()->getIdentifier())
            );
            $targets[] = $edge->getTargetNode()->getParticipant()->getIdentifier();
        }
    }

    protected function assertDifferentEdges($edges, $otherEdges, $message) {
        $success = FALSE;
        foreach($edges as $key=>$edge) {
            /** @var Edge $edge */
            /** @var Edge $otherEdge */
            $otherEdge = $otherEdges[$key];
            $this->assertInstanceOf('\\Czenker\\Wichtlr\\Graph\\Edge', $edge,
                sprintf('first solution at position %d is an edge', $key)
            );
            $this->assertInstanceOf('\\Czenker\\Wichtlr\\Graph\\Edge', $edge,
                sprintf('first solution at position %d is an edge', $key)
            );
            if($edge->getSourceNode()->getParticipant() !== $otherEdge->getSourceNode()->getParticipant() ||
                $edge->getTargetNode()->getParticipant() !== $otherEdge->getTargetNode()->getParticipant()
            ) {
                $success = TRUE;
            }
        }

        if($success) {
            $this->addToAssertionCount(1);
            return;
        } else {
            $this->fail($message);
        }

    }

    public function testFindRandomizedPath() {
        $graphService = new Service();

        $graph = new Graph();
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));
        $aliceNode = new Node(new Participant('alice'));
        $bobNode = new Node(new Participant('bob'));
        $graph->addNode($johnNode);
        $graph->addNode($janeNode);
        $graph->addNode($aliceNode);
        $graph->addNode($bobNode);
        $graphService->connectAllNodes($graph);

        $edges = $this->randomizedPath->findRandomizedRoundPath($graph);

        $this->assertCount(4, $edges);
        $this->assertValidSolution($edges);
    }

    public function testThrowsExceptionIfUnsolvable() {
        $this->setExpectedException('\\Czenker\\Wichtlr\Solver\\NoPathFoundException');

        $graph = new Graph();
        $johnNode = new Node(new Participant('john'));
        $janeNode = new Node(new Participant('jane'));
        $aliceNode = new Node(new Participant('alice'));
        $bobNode = new Node(new Participant('bob'));
        $graph->addNode($johnNode);
        $graph->addNode($janeNode);
        $graph->addNode($aliceNode);
        $graph->addNode($bobNode);
        $johnNode->addEdgeTo($janeNode);
        $janeNode->addEdgeTo($aliceNode);
        $aliceNode->addEdgeTo($johnNode);

        $edges = $this->randomizedPath->findRandomizedRoundPath($graph);
    }

    public function testPathIsRandom() {
        $nodeCount = 20; // 19! possible solutions - so no collisions expected
        $count = 0;

        $graphService = new Service();
        $graph = new Graph();
        while(($count++) < $nodeCount) {
            $node = new Node(new Participant('participant ' . $count));
            $graph->addNode($node);
        }
        $graphService->connectAllNodes($graph);

        $solution = $this->randomizedPath->findRandomizedRoundPath($graph);
        $this->assertCount($nodeCount, $solution, sprintf('first solution returns %d edges', $nodeCount));
        $this->assertValidSolution($solution, 'first solution is valid');
        $otherSolution = $this->randomizedPath->findRandomizedRoundPath($graph);
        $this->assertCount($nodeCount, $otherSolution, sprintf('second solution returns %d edges', $nodeCount));
        $this->assertValidSolution($otherSolution, 'second solution is valid');

        $this->assertDifferentEdges($solution, $otherSolution, 'both solutions are different');


    }


}
