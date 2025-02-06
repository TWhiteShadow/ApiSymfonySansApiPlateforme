<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Services\MailerService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendEmailMessageHandler
{

    public function __construct(private MailerService $mailerService)
    {
    }

    public function __invoke(SendEmailMessage $message): void
    {
        $this->mailerService->sendEmail(
            'thewhiteshadowgaming@gmail.com',
            'Hello!',
            'This is a test email'
        );
    }
}
