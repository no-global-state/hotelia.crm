<?php

namespace Site\Controller;

use Site\Service\MailerService;

trait MailerTrait
{
    /**
     * Notify administration about new booking
     * 
     * @param string $hotelName
     * @return boolean
     */
    protected function bookingAdminNotify(string $hotelName) : bool
    {
        $subject = 'New booking from the site';
        $to = $_ENV['adminEmail'];

        return $this->sendMail($to, $subject, 'booking-owner-new', [
            'hotelName' => $hotelName
        ]);
    }

    /**
     * Notify about new booking
     * 
     * @param string $to
     * @return boolean
     */
    protected function bookingOwnerNotify(string $to) : bool
    {
        $subject = 'You have a new booking from site';
        return $this->sendMail($to, $subject, 'booking-owner-new');
    }

    /**
     * Notify about new feedback
     * 
     * @param string $to
     * @return boolean
     */
    protected function feedbackNewNotify(string $to)
    {
        $subject = 'You have a new feedback';
        return $this->sendMail($to, $subject, 'feedback-new');
    }

    /**
     * Emails about the need of payment confirmation
     * 
     * @param string $to Client email
     * @param string $link Payment confirmation link
     * @return boolean
     */
    protected function paymentConfirmNotify(string $to, string $link) : bool
    {
        $subject = 'Please confirm booking payment';

        return $this->sendMail($to, $subject, 'payment-to-be-confirmed', [
            'link' => $link
        ]);
    }

    /**
     * Notify about successful payment
     * 
     * @param string $to
     * @param array $params Template variables
     * @return boolean
     */
    protected function voucherNotify(string $to, array $params) : bool
    {
        $subject = 'Your booking is complete';

        return $this->sendMail($to, $subject, 'voucher', $params);
    }

    /**
     * Notify admin about new transaction
     * 
     * @param string $hotelName
     * @return boolean
     */
    protected function transactionAdminNotify(string $hotelName) : bool
    {
        $subject = 'New transaction';
        $to = $_ENV['adminEmail'];

        return $this->sendMail($to, $subject, 'payment-transaction', [
            'hotelName' => $hotelName
        ]);
    }

    /**
     * Renders mail message
     * 
     * @param string $template Framework-compliant template name
     * @param array $vars Template variables
     * @return string
     */
    private function renderMail(string $template, array $vars) : string
    {
        // Original theme
        $theme = $this->view->getTheme();

        $content = $this->view->setTheme('mail')
                              ->render($template, $vars);

        // Restore original theme
        $this->view->setTheme($theme);

        return $content;
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

        $body = $this->renderMail($template, $vars);

        return $mailer->send($to, $subject, $body);
    }
}
