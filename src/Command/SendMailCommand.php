<?php

namespace App\Command;

use App\Repository\VideoGameRepository;
use App\Repository\UserRepository;
use App\Services\MailerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(
    name: 'app:send-weekly-videogames',
    description: 'Send notification emails to users subscribed to newsletter',
)]
class SendMailCommand extends Command
{
    public function __construct(
        private VideoGameRepository $videoGameRepository,
        private UserRepository $userRepository,
        private MailerService $mailerService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Envoi des emails');

        $recentGames = $this->videoGameRepository->findNextWeekGameRelease();
        
        $subscribedUsers = $this->userRepository->findByNewsletterSubscription();
        $io->text('Envoi des emails en cours...');
        foreach ($subscribedUsers as $user) {
            $this->mailerService->sendEmail(
                $user->getEmail(),
                'Les nouveaux jeux de la semaine',
                $recentGames
            );
        }

        $io->success('Emails envoyés avec succès à ' . count($subscribedUsers) . ' utilisateurs.');

        return Command::SUCCESS;
    }
}
