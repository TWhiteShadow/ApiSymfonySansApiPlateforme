<?php

namespace App\Services;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    public function sendEmail(string $to, string $subject, string $content): void
    {
        // send email
        $email = (new Email())
            ->from('thewhiteshadowgaming@gmail.com')
            ->to($to)
            ->subject($subject)
            ->text($content)
            ->html("<p>$content</p>");

    
        $this->mailer->send($email);
    }
}