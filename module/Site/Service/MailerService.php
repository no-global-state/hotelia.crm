<?php

namespace Site\Service;

final class MailerService
{
    /**
     * Configuration
     * 
     * @var array
     */
    private $config;

    /**
     * State initialization
     * 
     * @param array $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Sends a message
     * 
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return boolean
     */
    public function send(string $to, string $subject, string $body) : bool
    {
        $from = [$this->config['from']];
        $transport = \Swift_MailTransport::newInstance(null);

        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($transport);

        // Build Swift's message
        $message = \Swift_Message::newInstance($subject)
                              ->setFrom($from)
                              ->setContentType('text/html')
                              ->setTo($to)
                              ->setBody($body);

        return $mailer->send($message, $failed) != 0;
    }
}
