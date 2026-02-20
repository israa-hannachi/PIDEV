<?php

namespace App\Command;

use App\Service\MailingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mailing:setup-templates',
    description: 'Setup default email templates in the database',
)]
class SetupEmailTemplatesCommand extends Command
{
    public function __construct(
        private MailingService $mailingService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Setting up email templates');

        // Create password reset template
        $this->mailingService->saveTemplate(
            'password_reset',
            'Reset Your Password - Code: {{ code }}',
            'emails/password_reset.html.twig',
            'Template for password reset emails with 6-digit code'
        );
        $io->success('Created template: password_reset');

        // Create welcome template
        $this->mailingService->saveTemplate(
            'welcome',
            'Welcome to Naja7ni, {{ name }}!',
            'emails/welcome.html.twig',
            'Welcome email for new users'
        );
        $io->success('Created template: welcome');

        $io->success('All email templates have been set up successfully!');

        return Command::SUCCESS;
    }
}
