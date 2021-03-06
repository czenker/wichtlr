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
