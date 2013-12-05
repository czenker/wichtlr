<?php


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