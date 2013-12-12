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



use TYPO3\SwiftMailer\Transport\MboxTransport;

class TransportFactory {

    /**
     * @param $configuration
     * @throws \InvalidArgumentException
     * @return \Swift_Transport
     */
    public function getTransportFromConfiguration($configuration) {
        if(!array_key_exists('transport', $configuration)) {
            throw new \InvalidArgumentException('configuration "transport" is missing.');
        }

        if($configuration['transport'] === 'smtp') {
            return $this->getSmtpTransport($configuration);
        } elseif($configuration['transport'] === 'sendmail') {
            return $this->getSendmailTransport($configuration);
        } elseif($configuration['transport'] === 'mail') {
            return $this->getMailTransport($configuration);
        } elseif($configuration['transport'] === 'mbox') {
            return $this->getMboxTransport($configuration);
        }
        throw new \InvalidArgumentException(sprintf('Invalid transport "%s".', $configuration['transport']));
    }

    protected function getSmtpTransport($configuration) {
        $transport = new \Swift_SmtpTransport();
        if(array_key_exists('host', $configuration)) {
            $transport->setHost($configuration['host']);
        }
        if(array_key_exists('port', $configuration)) {
            $transport->setPort($configuration['port']);
        }
        if(array_key_exists('username', $configuration)) {
            $transport->setUsername($configuration['username']);
        }
        if(array_key_exists('password', $configuration)) {
            $transport->setPassword($configuration['password']);
        }
        if(array_key_exists('auth_mode', $configuration)) {
            $transport->setAuthMode($configuration['auth_mode']);
        }
        if(array_key_exists('encryption', $configuration)) {
            $transport->setEncryption($configuration['encryption']);
        }

        return $transport;
    }

    protected function getSendmailTransport($configuration) {
        $transport = new \Swift_SendmailTransport();
        return $transport;
    }

    protected function getMailTransport($configuration) {
        $transport = new \Swift_MailTransport();
        return $transport;
    }

    protected function getMboxTransport($configuration) {
        $transport = new MboxTransport();
        $transport->setMboxPathAndFilename($configuration['mbox_file'] ?: 'out-mails.mbox');

        return $transport;
    }
}