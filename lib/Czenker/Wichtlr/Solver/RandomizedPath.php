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

namespace Czenker\Wichtlr\Solver;


use Czenker\Wichtlr\Graph\Edge;
use Czenker\Wichtlr\Graph\Graph;

class RandomizedPath {

    /**
     * selects edges from a graph so that each node is source
     * and target of exactly one edge
     *
     * (this basically creates several round paths)
     * the result is not sorted in any specific order
     *
     * @param Graph $graph
     * @param int $maxTries
     * @throws NoPathFoundException
     * @return array<Edge>
     */
    public function findRandomizedRoundPath(Graph $graph, $maxTries = 100) {
        $tryNumber = 1;
        while($tryNumber <= $maxTries) {
            try {
                return $this->tryToFindRandomizedRoundPath(clone $graph);
            } catch(BadLuckException $e) {}
            $tryNumber++;
        }
        throw new NoPathFoundException(sprintf(
            'Did not find a solution after %d tries.', $maxTries
        ));
    }

    /**
     * @param Graph $graph
     * @throws BadLuckException
     * @return array<Edge>
     */
    protected function tryToFindRandomizedRoundPath(Graph $graph) {
        $edges = array();
        while($graph->countNodes() > 0) {
            // use node with lowest number of edges to make failing less likely
            $node = $graph->getNodeWithLeastOutgoingEdges();
            if($node->countEdges() === 0) {
                throw new BadLuckException();
            }
            $edge = $this->getRandomEdge($node->getEdges());
            $edges[] = $edge;
            $graph->removeNode($node);
            $graph->removeEdgesTo($edge->getTargetNode());
        }
        return $edges;
    }

    /**
     * @param array $edges
     * @return Edge
     */
    protected function getRandomEdge($edges) {
        $key = array_rand($edges);
        return $edges[$key];
    }

}