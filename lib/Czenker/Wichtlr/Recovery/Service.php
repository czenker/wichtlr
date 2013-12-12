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

namespace Czenker\Wichtlr\Recovery;


use Czenker\Wichtlr\Domain\Participant;
use Czenker\Wichtlr\Graph\Edge;

class Service {

    /**
     * the idea is relatively simple:
     *
     *  * take the name of the participant and a random string.
     *  * XOR those to get another encrypted string
     *  * give the next two participants the random string and the XORed string
     *
     * @param array $edges
     * @return \SplObjectStorage
     */
    public function generateRecoveryDataFromEdges(array $edges) {
        // make sure all names have the same size so there is no figuring out of the name by string length
        $bytelen = $this->getMaxNameLength($edges);

        $dataSets = array();
        foreach($edges as $edge) {
            /** @var Edge $edge */
            $dataSets[] = new RecoveryDataSet($edge->getSourceNode()->getParticipant());
        }

        $max = count($dataSets);
        foreach($edges as $i => $edge) {
            $recovery = $this->createRecoveryData(
                $edge->getSourceNode()->getParticipant(),
                $edge->getTargetNode()->getParticipant(),
                $bytelen
            );

            $dataSets[($i + 1) % $max]->addRecoveryData($recovery[0]);
            $dataSets[($i + 2) % $max]->addRecoveryData($recovery[1]);
        }

        $return = new \SplObjectStorage();
        foreach($dataSets as $dataSet) {
            /** @var RecoveryDataSet $dataSet */
            $return->attach($dataSet->getParticipant(), $dataSet);
        }

        return $return;
    }

    protected function getMaxNameLength(array $edges) {
        $maxLength = 0;
        foreach($edges as $edge) {
            /** @var Edge $edge */
            $strlen = strlen($edge->getSourceNode()->getParticipant()->getName());
            if($strlen > $maxLength) {
                $maxLength = $strlen;
            }
        }
        return $maxLength;
    }


    public function createRecoveryData(Participant $donor, Participant $donee, $byteLength) {
        // just create a random string and XOR
        $name = str_pad($donee->getName(), $byteLength);
        $name = str_split($name);
        $string1 = '';
        $string2 = '';
        while($char = array_shift($name)) {
            $ord1 = $this->getRandomByte();
            $ord2 = ord($char) ^ $ord1;

            $string1 .= chr($ord1);
            $string2 .= chr($ord2);
        }

        return array(
            new RecoveryData($donor, base64_encode($string1)),
            new RecoveryData($donor, base64_encode($string2))
        );
    }

    protected function getRandomByte() {
        return mt_rand(0,255);
    }

    public function decrypt($string1, $string2) {
        if($string1 instanceof RecoveryData) {
            $string1 = $string1->getCode();
        }
        if($string2 instanceof RecoveryData) {
            $string2 = $string2->getCode();
        }
        $string1 = base64_decode($string1);
        $string2 = base64_decode($string2);

        if(strlen($string1) !== strlen($string2)) {
            throw new \RuntimeException(sprintf(
                'String length of string1 and string2 is different. %d and %d',
                strlen($string1),
                strlen($string2)
            ));
        }

        $name = '';
        $string1 = str_split($string1);
        $string2 = str_split($string2);
        while(!empty($string1)) {
            $chr1 = array_shift($string1);
            $chr2 = array_shift($string2);

            $ord = ord($chr1) ^ ord($chr2);
            $name .= chr($ord);
        }

        return rtrim($name);
    }

}