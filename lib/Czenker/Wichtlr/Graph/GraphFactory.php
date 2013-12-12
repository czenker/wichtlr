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

class GraphFactory {

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @var Service
     */
    protected $graphService;

    public function __construct(NodeRepository $nodeRepository, Service $graphService) {
        $this->nodeRepository = $nodeRepository;
        $this->graphService = $graphService;
    }

    /**
     * data is an assoc array of the following structure:
     *
     * * key is a unique identifier for the participant
     * * value is an assoc array with the following values:
     *     * name: full name of the partipant
     *     * email: the email address of the participant
     *     * not_to: an array with identifiers of other participants this person will not have to give to
     *
     * @param $data
     * @return Graph
     */
    public function buildGraphFromArray($data) {
        $graph = new Graph();

        // create participant objects
        foreach($data as $identifier=>$configuration) {
            $participant = new Participant($identifier);
            $participant->setEmail($configuration['email']);
            $participant->setName($configuration['name']);

            $node = new Node($participant);

            $this->nodeRepository->addNode($node);
            $graph->addNode($node);
        }

        $this->graphService->connectAllNodes($graph);

        foreach($data as $identifier=>$configuration) {
            if(array_key_exists('not_to', $configuration)) {
                $sourceNode = $this->nodeRepository->findOneByIdentifier($identifier);
                foreach($configuration['not_to'] as $id) {
                    $targetNode = $this->nodeRepository->findOneByIdentifier($id);
                    $sourceNode->removeEdgesTo($targetNode);
                }
            }
        }

        return $graph;
    }

}