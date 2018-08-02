<?php

namespace Site\Controller;

use Site\Service\MailerService;

trait MailerTrait
{
    /**
     * Renders mail message
     * 
     * @param string $template Framework-compliant template name
     * @param array $vars Template variables
     * @return string
     */
    private function renderMail(string $template, array $vars) : string
    {
        return $this->view->setTheme('mail')
                          ->render($template, $vars);
    }

    /**
     * Sends mail
     * 
     * @param string $to
     * @param string $subject
     * @param string $template
     * @param array $vars
     * @return boolean
     */
    protected function sendMail(string $to, string $subject, string $template, array $vars = [])
    {
        $mailer = new MailerService([
            'from' => sprintf('no-reply@%s', $this->request->getDomain())
        ]);

        return $mailer->send($to, $subject, $this->renderMail($template, $vars));
    }
}
