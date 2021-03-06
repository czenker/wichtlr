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

namespace Czenker\Wichtlr\Command;

use Czenker\Wichtlr\Email\EmailFactory;
use Czenker\Wichtlr\Graph\Graph;
use Czenker\Wichtlr\Graph\GraphFactory;
use Czenker\Wichtlr\Graph\Node;
use Czenker\Wichtlr\Graph\NodeRepository;
use Czenker\Wichtlr\Graph\Service;
use Czenker\Wichtlr\Solver\RandomizedPath;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\SwiftMailer\Transport\MboxTransport;

class RecoverCommand extends AbstractCommand {

    /**
     * @var array
     */
    protected $mailConfiguration = array();

    protected function configure() {
        $this
            ->setName('recover')
            ->setDescription('Recover the name of a donee if a mail went missing')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $string1 = $this->dialogHelper->ask($this->output, 'The first string: ');
        $string2 = $this->dialogHelper->ask($this->output, 'The second string: ');

        $output->writeln($this->recoveryService->decrypt($string1, $string2));
    }
}