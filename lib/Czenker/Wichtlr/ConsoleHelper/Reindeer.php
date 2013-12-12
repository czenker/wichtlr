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

namespace Czenker\Wichtlr\ConsoleHelper;


use Czenker\Wichtlr\Graph\Graph;
use Czenker\Wichtlr\Graph\Node;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

class Reindeer extends Helper {

    /**
     * @var array lines of text making up an ASCII art reindeer
     */
    protected $reindeer = array();
    /**
     * @var int lines needed to show the reindeer
     */
    protected $reindeerWidth = 0;
    /**
     * @var int columns needed to show the reindeer
     */
    protected $reindeerHeight = 0;

    /**
     * @var int number of ASCII chars between reindeer and the text
     */
    protected $gutterWidth = 2;

    /**
     * @var int width of the text
     */
    protected $textWidth = 80;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct() {
        $this->initReindeer(__DIR__ . '/../Resources/reindeer.txt');
    }

    public function setOutput(OutputInterface $output) {
        $this->output = $output;
    }

    protected function initReindeer($filePath) {
        $reindeer = file_get_contents($filePath);
        $this->reindeer = explode("\n", $reindeer);
        foreach($this->reindeer as $line) {
            if(strlen($line) > $this->reindeerWidth) {
                $this->reindeerWidth = strlen($line);
            }
        }
        $this->reindeerHeight = count($this->reindeer);

        $this->textWidth = $this->textWidth - $this->gutterWidth - $this->reindeerWidth;
    }


    public function say($message, OutputInterface $output = NULL) {
        $output = $output ?: $this->output;
        $message = wordwrap($message, $this->textWidth);
        $message = explode("\n", $message);
        array_unshift($message, '');
        $reindeer = $this->reindeer;

        $output->writeln('');
        while(count($reindeer) > 0 || count($message) > 0) {
            $line =
                str_pad(array_shift($reindeer) ?: '', $this->reindeerWidth) .
                str_pad(' ', $this->gutterWidth) .
                array_shift($message) ?: ''
            ;

            $output->writeln($line);

        }
        $output->writeln('');
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName() {
        return 'reindeer';
    }

    public function sayHello() {
        $this->say(<<<EOF
Hi there.

I am the reindeer with the name that I can't tell you for legal reasons. But I think you know me quite well.

Santa asked me to help you figuring out how to give presents without everyone knowing it was YOU who gave away this crappy gift.
EOF
        );
    }

    public function sayBye() {
        $this->say(<<<EOF
Sorry, but I just can't work like that!

You know where to find me if you need help.
EOF
        );
    }

    public function sayCreateParticipantsYaml($fileName) {
        $this->say(<<<EOF
I need a list of people who should participate in this fun little game.

Let me just create a list for you where you fill in all your friends and I take care of the rest.

Please edit <comment>$fileName</comment> and let me know when you are done.

I'll just wait here.
EOF
        );
    }

    public function sayCreateMailYaml($fileName) {
        $this->say(<<<EOF
There are not many post offices here at the north pole. Please tell my how to send the mail I'll write for you.

I created a file for you where you could write everything down.

Please edit <comment>$fileName</comment> and let me know when you are done.

I'll just wait here.
EOF
        );
    }

    public function sayCreateDefaultTemplate($fileName) {
        $this->say(<<<EOF
Now is the time to tell me how the mails should look like.

I created a file for you where you could write everything down.

Please edit <comment>$fileName</comment> and let me know when you are done.

I'll just wait here.
EOF
        );
    }

    public function sayParticipants(Graph $graph) {
        $participantNames = array();
        foreach($graph->getNodes() as $node) {
            /** @var $node Node */
            $participantNames[] = $node->getParticipant()->getName();
        }
        $participantNames = implode(', ', $participantNames);
        $participantCount = count($graph->getNodes());

        $this->say(<<<EOF
So you have $participantCount people who want to join:

$participantNames
EOF
        );
    }

    public function saySendMails() {
        $this->say(<<<EOF
The elves are writing your letters now.

Do you want me to send the letters right away or do you want to catch a peek into them?
EOF
        );
    }

    public function saySendMailDummies() {
        $this->say(<<<EOF
Hah, I thought so.

Just remember that the elves will write new letters the next time - so you will probably get a different result when you call me again.

If you give me your address I'll send all the mails to you instead. I promise I won't give your address to Santa!
EOF
        );
    }

    public function sayCreateRecoveryData() {
        $this->say(<<<EOF
I have to confess something: Sometimes mails get lost during the transit.

If you want I can create some recovery data that can help you if one mail gets lost. The risk is of course that if two people combine their knowledge they can potentially reveal the identity of a giver.
EOF
        );
    }
}