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


use Czenker\Wichtlr\ConsoleHelper\Reindeer;
use Czenker\Wichtlr\Email\EmailFactory;
use Czenker\Wichtlr\Email\TransportFactory;
use Czenker\Wichtlr\Helper\DefaultTemplate;
use Czenker\Wichtlr\Helper\MailYaml;
use Czenker\Wichtlr\Helper\ParticipantsYaml;
use Czenker\Wichtlr\Recovery\Service as RecoveryService;
use Czenker\Wichtlr\Twig\MailExtension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command {


    /**
     * @var DialogHelper
     */
    protected $dialogHelper;

    /**
     * @var Reindeer
     */
    protected $reindeerHelper;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ParticipantsYaml
     */
    protected $participantsYaml;

    /**
     * @var MailYaml
     */
    protected $mailYaml;

    /**
     * @var DefaultTemplate
     */
    protected $defaultTemplate;

    /**
     * @var \Twig_Environment
     */
    protected $twigEnvironment;

    /**
     * @var EmailFactory
     */
    protected $emailFactory;

    /**
     * @var RecoveryService
     */
    protected $recoveryService;

    /**
     * @var TransportFactory
     */
    protected $mailTransportFactory;

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;
        $this->dialogHelper = $this->getHelper('dialog');
        $this->reindeerHelper = $this->getHelper('reindeer');
        $this->reindeerHelper->setOutput($this->output);
        $this->participantsYaml = new ParticipantsYaml();
        $this->mailYaml = new MailYaml();
        $this->defaultTemplate = new DefaultTemplate();

        $this->setTwigEnvironment();

        $this->emailFactory = new EmailFactory($this->twigEnvironment);
        $this->mailTransportFactory = new TransportFactory();

        $this->recoveryService = new RecoveryService();

    }

    public function setTwigEnvironment() {
        $loader = new \Twig_Loader_Filesystem('config');
        $this->twigEnvironment = new \Twig_Environment($loader);
        $this->twigEnvironment->addExtension(new MailExtension());
    }

}