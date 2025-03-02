<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    public function sendEmail(string $to, string $subject, array $videoGames): void
    {
        // send email
        $email = (new TemplatedEmail())
            ->from('thewhiteshadowgaming@gmail.com')
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('emails/weekly_videogames.html.twig')
            ->context([
                'games' => $videoGames
            ]); 

    
        $this->mailer->send($email);
    }
}