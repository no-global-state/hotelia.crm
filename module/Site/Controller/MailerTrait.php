<?php

namespace Site\Controller;

use Site\Service\MailerService;

trait MailerTrait
{
    /**
     * Grab translations from dictionary
     * 
     * @param string $alias
     * @param int $languageId
     * @return string
     */
    private function dict(string $alias, $langId = 1)
    {
        return $this->getModuleService('dictionaryService')->findByAlias($alias, $langId);
    }

    /**
     * Notify receivers
     * 
     * @param array $bookings
     * @return boolean
     */
    protected function notifyReceivers(array $bookings) : bool
    {
        foreach ($bookings as $booking) {
            $subject = $this->dict('BOOKING_MAIL_REVIEW_SUBJECT', $booking['lang_id']);

            $this->sendMail($booking['email'], $subject, 'rate-stay', [
                'booking' => $booking,
                'url' => $this->request->getBaseUrl() . $this->createUrl('Site:Site@leaveReviewAction', [$booking['token']])
            ]);
        }

        return true;
    }

    /**
     * Notify administration about new booking
     * 
     * @param string $hotelName
     * @return boolean
     */
    protected function bookingAdminNotify(string $hotelName) : bool
    {
        $subject = $this->dict('MAIL_SUBJECT_NEW_BOOKING');
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
        $subject = $this->dict('MAIL_SUBJECT_NEW_BOOKING');
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
        $subject = $this->dict('MAIL_SUBJECT_PAYMENT_CONF_PL');

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
        $subject = $this->dict('MAIL_SUBJECT_BOOKING_COMPLETE');

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
        $subject = $this->dict('MAIL_SUBJECT_NEW_TRANSACTION');
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
