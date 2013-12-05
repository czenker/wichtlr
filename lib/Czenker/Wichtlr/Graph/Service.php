<?php


namespace Czenker\Wichtlr\Graph;


class Service {

    /**
     * connects all nodes in a graph with each other - but not to themselves
     *
     * @param Graph $graph
     */
    public function connectAllNodes(Graph $graph) {
        foreach($graph->getNodes() as $sourceNode) {
            /** @var Node $sourceNode */
            foreach($graph->getNodes() as $targetNode) {
                /** @var Node $targetNode */
                if($sourceNode !== $targetNode) {
                    $sourceNode->addEdgeTo($targetNode);
                }
            }
        }
    }

}