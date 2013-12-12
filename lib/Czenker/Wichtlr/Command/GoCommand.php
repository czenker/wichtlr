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

class GoCommand extends AbstractCommand {

    /**
     * @var array
     */
    protected $mailConfiguration = array();

    protected function configure() {
        $this
            ->setName('go')
            ->setDescription('Interactively create and send mails')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $this->sayHello();
            $participants = $this->getParticipantsArray();
            $this->mailConfiguration = $this->getMailConfigurationArray();
            $graph = $this->getGraph($participants);
            $edges = $this->findSolution($graph);
            $recoveryData = $this->createRecoveryData($edges);
            $mails = $this->prepareMails($edges, $recoveryData);
            $this->sendMails($mails);
        } catch (QuitException $e) {
            $this->reindeerHelper->sayBye();
            return 0;
        }
        return 0;
    }

    /**
     * greet the user
     *
     * @throws QuitException
     */
    protected function sayHello() {
        $this->reindeerHelper->sayHello();
        if(!$this->dialogHelper->askConfirmation($this->output, "So let's start right away. Shall we?[Y/n]", TRUE)) {
            throw new QuitException();
        }
    }

    /**
     * @return array
     */
    public function getParticipantsArray() {
        if(!$this->participantsYaml->exists()) {
            $this->participantsYaml->copyExampleFile();
            $this->reindeerHelper->sayCreateParticipantsYaml($this->participantsYaml->getPath());
            $this->dialogHelper->askConfirmation($this->output, 'Press Enter when you are done.');
        }

        return $this->participantsYaml->getConfiguration();
    }

    /**
     * @return array
     */
    public function getMailConfigurationArray() {
        if(!$this->mailYaml->exists()) {
            $this->mailYaml->copyExampleFile();
            $this->reindeerHelper->sayCreateMailYaml($this->mailYaml->getPath());
            $this->dialogHelper->askConfirmation($this->output, 'Press Enter when you are done.');
        }

        return $this->mailYaml->getConfiguration();
    }

    /**
     * @param $participantsConfiguration
     * @throws QuitException
     * @return Graph
     */
    protected function getGraph($participantsConfiguration) {

        $nodeRepository = new NodeRepository();
        $graphService = new Service();
        $graphFactory = new GraphFactory($nodeRepository, $graphService);

        $graph = $graphFactory->buildGraphFromArray($participantsConfiguration);

        $this->reindeerHelper->sayParticipants($graph);
        if(!$this->dialogHelper->askConfirmation($this->output, 'Is that right?[Y/n]', TRUE)) {
            throw new QuitException;
        }

        return $graph;
    }

    /**
     * @param \Czenker\Wichtlr\Graph\Graph $graph
     * @return array <Edge>
     */
    protected function findSolution(Graph $graph) {
        $solver = new RandomizedPath();
        return $solver->findRandomizedRoundPath($graph);
    }

    protected function createRecoveryData(array $edges) {
        $this->reindeerHelper->sayCreateRecoveryData();
        if($this->dialogHelper->askConfirmation($this->output, 'Do you want to create recovery data?[Y/n]', TRUE)) {
            return $this->recoveryService->generateRecoveryDataFromEdges($edges);
        } else {
            return NULL;
        }
    }


    /**
     * @param $edges
     * @param \SplObjectStorage $recoveryData
     * @throws QuitException
     * @return array <\Swift_Message>
     */
    protected function prepareMails($edges, $recoveryData) {
        if(!$this->defaultTemplate->exists()) {
            $this->defaultTemplate->copyExampleFile();
            $templatePath = $this->defaultTemplate->getPath();
            $this->reindeerHelper->sayCreateDefaultTemplate($templatePath);
            $this->dialogHelper->askConfirmation($this->output, 'Press Enter when you are done.');
        }

        $this->emailFactory->setConfiguration($this->mailConfiguration['mail']);
        $mails = $this->emailFactory->getMailsFromEdges($edges, basename($this->defaultTemplate->getPath()), $recoveryData);

        return $mails;
    }

    protected function sendMails($mails) {
        $this->reindeerHelper->saySendMails();

        if($this->dialogHelper->askConfirmation($this->output, 'Do you want to send the mails to everyone? [y/N]', FALSE)) {
            $this->sendMailsForReal($mails);
        } else {
            $this->sendMailDummies($mails);
        }
    }

    protected function sendMailDummies($mails) {
        $this->reindeerHelper->saySendMailDummies();

        $email = $this->dialogHelper->ask($this->output, 'Your email address: ');
        $message = $this->emailFactory->getNewMessage();
        $message->setSubject('Test sending mails');
        $message->setTo(array($email));
        $message->addPart(
            'Hi. This is a test mail. You find all mails that would have been sent appended to this mail.',
            'text/plain'
        );

        foreach($mails as $key=>$mail) {
            /** @var \Swift_Message $mail */
            $message->attach(
                \Swift_Attachment::newInstance(
                    $mail->toString(),
                    sprintf('%s.eml', $key),
                    'application/octet-stream'
                )
            );
        }

        $mailer = \Swift_Mailer::newInstance($this->getMailTransport());
        $mailer->send($message);

    }

    protected function sendMailsForReal($mails) {
        $mailer = \Swift_Mailer::newInstance($this->getMailTransport());

        foreach($mails as $mail) {
            /** @var \Swift_Message $mail */
            $mailer->send($mail);
        }
    }

    /**
     * @return \Swift_Transport
     */
    protected function getMailTransport() {
        return $this->mailTransportFactory->getTransportFromConfiguration(
            $this->mailConfiguration['swiftmailer']
        );
    }

}