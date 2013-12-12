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

namespace Czenker\Wichtlr\Email;


use Czenker\Wichtlr\Graph\Edge;
use Czenker\Wichtlr\Twig\MailExtension;

class EmailFactory {

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    protected $fromEmail;
    protected $fromName;

    protected $replyToEmail;
    protected $replyToName;

    public function __construct(\Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function setConfiguration($configuration) {
        if(array_key_exists('from_email', $configuration)) {
            $this->fromEmail = $configuration['from_email'];
        }
        if(array_key_exists('from_name', $configuration)) {
            $this->fromName = $configuration['from_name'];
        }
        if(array_key_exists('reply_to_email', $configuration)) {
            $this->replyToEmail = $configuration['reply_to_email'];
        }
        if(array_key_exists('reply_to_name', $configuration)) {
            $this->replyToName = $configuration['reply_to_name'];
        }
    }

    /**
     * @param array $edges
     * @param string $templateFile
     * @param null|\SplObjectStorage $recoveryData
     * @throws \RuntimeException
     * @return array
     */
    public function getMailsFromEdges($edges, $templateFile, $recoveryData = NULL) {
        $mails = array();

        $template = $this->twig->loadTemplate($templateFile);
        /** @var MailExtension $mailExtension */
        $mailExtension = $this->twig->getExtension('mail');

        foreach($edges as $edge) {
            /** @var Edge $edge */
            $donor = $edge->getSourceNode()->getParticipant();
            $donee = $edge->getTargetNode()->getParticipant();
            $context = array('donor' => $donor, 'donee' => $donee);
            if($recoveryData && $recoveryData->offsetExists($donor)) {
                $context['recovery'] = $recoveryData->offsetGet($donor);
            } else {
                $context['recovery'] = NULL;
            }

            $message = $this->getNewMessage();

            $mailExtension->setCurrentMessage($message);

            $title = $template->renderBlock('title', $context);
            $plain = $template->renderBlock('plain', $context);
            $html = $template->renderBlock('html', $context);

            if(!$plain && !$html) {
                throw new \RuntimeException(sprintf(
                    'Mail to %s has neither a plain text nor an html body', $donor
                ));
            }

            $message->setSubject($title);
            if($plain) {
                $message->addPart($plain, 'text/plain');

            }
            if($html) {
                $message->addPart($html, 'text/html');
            }

            $message->setTo(array($donor->getEmail() => $donor->getName()));

            $mails[] = $message;
        }

        return $mails;
    }

    public function getNewMessage() {
        $message = new \Swift_Message();
        if($this->fromEmail) {
            $message->setFrom($this->fromEmail, $this->fromName);
        }
        if($this->replyToEmail) {
            $message->setReplyTo($this->replyToEmail, $this->replyToName);
        }
        return $message;
    }
}