<?php

namespace App\Command;

use App\Service\MailingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mailing:send-pending',
    description: 'Process and send pending emails from the queue',
)]
class SendPendingEmailsCommand extends Command
{
    public function __construct(
        private MailingService $mailingService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Maximum number of emails to send', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');

        $io->title('Sending pending emails');
        $io->text("Processing up to {$limit} emails...");

        $results = $this->mailingService->sendPendingEmails($limit);

        $io->section('Results');
        $io->success("Successfully sent: {$results['sent']} emails");
        
        if ($results['failed'] > 0) {
            $io->warning("Failed: {$results['failed']} emails");
            
            if (!empty($results['errors'])) {
                $io->section('Errors');
                foreach ($results['errors'] as $error) {
                    $io->error("Email #{$error['id']} to {$error['email']}: {$error['error']}");
                }
            }
        }

        // Show queue statistics
        $stats = $this->mailingService->getQueueStatistics();
        $io->section('Queue Statistics');
        $io->table(
            ['Status', 'Count'],
            [
                ['Pending', $stats['pending']],
                ['Sent', $stats['sent']],
                ['Failed', $stats['failed']],
                ['Total', $stats['total']],
            ]
        );

        return Command::SUCCESS;
    }
}
