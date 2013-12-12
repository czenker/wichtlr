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

namespace Czenker\Wichtlr\Tests\Recovery;

use Czenker\Wichtlr\Domain\Participant;
use Czenker\Wichtlr\Graph\Edge;
use Czenker\Wichtlr\Graph\Node;
use Czenker\Wichtlr\Recovery\Service;

class ServiceTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Service
     */
    protected $recoveryService;

    public function setUp() {
        $this->recoveryService = new Service();
    }

    public function testCreateRecoveryData() {
        $donor = new Participant('alice');
        $donor->setName('Alice');
        $donee = new Participant('john');
        $donee->setName('John Doe');

        $recoveryData = $this->recoveryService->createRecoveryData($donor, $donee, strlen($donee->getName()));

        $this->assertCount(2, $recoveryData, 'createRecoveryData returns two values');
        $this->assertInstanceOf('\\Czenker\\Wichtlr\\Recovery\\RecoveryData', $recoveryData[0], 'createRecoveryData returns RecoveryData #1');
        $this->assertInstanceOf('\\Czenker\\Wichtlr\\Recovery\\RecoveryData', $recoveryData[1], 'createRecoveryData returns RecoveryData #2');

        $this->assertSame(12, strlen($recoveryData[0]->getCode()), 'recovery data contains a 12 character code #1');
        $this->assertSame(12, strlen($recoveryData[1]->getCode()), 'recovery data contains a 12 character code #2');
        $this->assertNotSame(FALSE, base64_decode($recoveryData[0]->getCode()), 'recovery data is base64 #1');
        $this->assertNotSame(FALSE, base64_decode($recoveryData[1]->getCode()), 'recovery data is base64 #2');

    }

    public function testCreateRecover() {
        $donor = new Participant('alice');
        $donor->setName('Alice');
        $donee = new Participant('john');
        $donee->setName('John Doe');

        $recoveryData = $this->recoveryService->createRecoveryData($donor, $donee, strlen($donee->getName()));

        $decoded = $this->recoveryService->decrypt($recoveryData[0]->getCode(), $recoveryData[1]->getCode());

        $this->assertSame('John Doe', $decoded, 'decrypt can decode its own encrypted strings');

    }

    public function testGenerateRecoveryDataFromEdges() {
        $john = new Participant('john');
        $john->getName('John Doe');
        $jane = new Participant('jane');
        $jane->getName('Jane');
        $alice = new Participant('alice');
        $alice->getName('Alice');

        $johnNode = new Node($john);
        $janeNode = new Node($jane);
        $aliceNode = new Node($alice);

        $edges = array(
            new Edge($johnNode, $janeNode),
            new Edge($janeNode, $aliceNode),
            new Edge($aliceNode, $johnNode)
        );

        $recovery = $this->recoveryService->generateRecoveryDataFromEdges($edges);

        $this->assertInstanceOf('\\SplObjectStorage', $recovery, 'return is an SplObjectStorage');
        $this->assertCount(3, $recovery, 'recovery contains 3 objects');
        $this->assertInstanceOf('\\Czenker\\Wichtlr\\Recovery\\RecoveryDataSet', $recovery->offsetGet($john), 'John got recoveryData');
        $this->assertInstanceOf('\\Czenker\\Wichtlr\\Recovery\\RecoveryDataSet', $recovery->offsetGet($jane), 'Jane got recoveryData');
        $this->assertInstanceOf('\\Czenker\\Wichtlr\\Recovery\\RecoveryDataSet', $recovery->offsetGet($alice), 'Alice got recoveryData');
        $this->count(2, $recovery->offsetGet($john)->getRecoveryData(), 'John has recoveryData for two people');
        $this->count(2, $recovery->offsetGet($jane)->getRecoveryData(), 'Jane has recoveryData for two people');
        $this->count(2, $recovery->offsetGet($alice)->getRecoveryData(), 'Alice has recoveryData for two people');

    }

}
