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

namespace Czenker\Wichtlr\Graph;
use Czenker\Wichtlr\Domain\Participant;

/**
 * a node (participant) with edges to possible donees
 *
 * edges are directional and not weighted
 *
 * @package Czenker\Wichtlr\Graph
 */
class Node {

    /**
     * @var Participant
     */
    protected $participant;

    /**
     * @var array
     */
    protected $edges = array();

    /**
     * @param Participant $participant
     */
    public function __construct(Participant $participant) {
        $this->participant = $participant;
    }

    /**
     * @return \Czenker\Wichtlr\Domain\Participant
     */
    public function getParticipant() {
        return $this->participant;
    }

    public function addEdge(Edge $edge) {
        if($edge->getSourceNode() !== $this) {
            throw new \LogicException(sprintf(
                'source node was supposed to be %s, but got %s',
                $this, $edge->getSourceNode()
            ));
        }
        if($this->hasEdge($edge)) {
            throw new \InvalidArgumentException(sprintf(
                '%s already contains the edge %s.',
                $this, $edge
            ));
        }
        $this->edges[] = $edge;
    }

    public function removeEdge(Edge $edge) {
        if(!$this->hasEdge($edge)) {
            throw new \LogicException(sprintf(
                'node %s does not contain edge %s.',
                $this, $edge
            ));
        }

        $key = array_search($edge, $this->edges, TRUE);
        unset($this->edges[$key]);
    }

    public function hasEdge(Edge $edge) {
        return in_array($edge, $this->edges, TRUE);
    }

    public function countEdges() {
        return count($this->edges);
    }

    public function getEdges() {
        return $this->edges;
    }

    public function addEdgeTo(Node $node) {
        $this->addEdge(new Edge($this, $node));
    }

    public function removeEdgesTo(Node $node) {
        foreach($this->edges as $key=>$otherEdge) {
            /** @var $otherEdge Edge */
            if($node === $otherEdge->getTargetNode()) {
                unset($this->edges[$key]);
            }
        }
    }

    public function hasEdgeTo(Node $node) {
        foreach($this->edges as $otherEdge) {
            /** @var $otherEdge Edge */
            if($node === $otherEdge->getTargetNode()) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function __toString() {
        return (string)$this->participant;
    }

}