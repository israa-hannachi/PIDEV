<?php

namespace App\Service;

use App\Entity\EmailQueue;
use App\Entity\EmailTemplate;
use App\Repository\EmailQueueRepository;
use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class MailingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private Environment $twig,
        private EmailQueueRepository $queueRepository,
        private EmailTemplateRepository $templateRepository,
        private LoggerInterface $logger,
        private string $fromEmail = 'noreply@example.com',
        private string $fromName = 'Naja7ni Platform'
    ) {
    }

    /**
     * Send email immediately without queuing
     */
    public function sendEmailNow(
        string $recipientEmail,
        string $subject,
        string $body,
        ?string $recipientName = null
    ): bool {
        try {
            $email = (new Email())
                ->from($this->fromEmail)
                ->to($recipientEmail)
                ->subject($subject)
                ->html($body);

            $this->mailer->send($email);
            $this->logger->info('Email sent successfully', [
                'recipient' => $recipientEmail,
                'subject' => $subject
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email', [
                'recipient' => $recipientEmail,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Send email from template immediately
     */
    public function sendEmailFromTemplateNow(
        string $recipientEmail,
        string $templateName,
        array $variables = [],
        ?string $recipientName = null
    ): bool {
        $template = $this->templateRepository->findByName($templateName);
        
        if (!$template) {
            $this->logger->error('Email template not found', ['template' => $templateName]);
            return false;
        }

        try {
            $body = $this->twig->render($template->getTemplatePath(), $variables);
            $subject = $this->twig->createTemplate($template->getSubject())->render($variables);
            
            return $this->sendEmailNow($recipientEmail, $subject, $body, $recipientName);
        } catch (\Exception $e) {
            $this->logger->error('Failed to render and send template', [
                'template' => $templateName,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Queue email for later sending
     */
    public function queueEmail(
        string $recipientEmail,
        string $subject,
        string $body,
        ?string $recipientName = null,
        ?array $variables = null
    ): EmailQueue {
        $queueItem = new EmailQueue();
        $queueItem->setRecipientEmail($recipientEmail)
            ->setRecipientName($recipientName)
            ->setSubject($subject)
            ->setBody($body)
            ->setVariables($variables);

        $this->entityManager->persist($queueItem);
        $this->entityManager->flush();

        return $queueItem;
    }

    /**
     * Queue email from template
     */
    public function queueEmailFromTemplate(
        string $recipientEmail,
        string $templateName,
        array $variables = [],
        ?string $recipientName = null
    ): ?EmailQueue {
        $template = $this->templateRepository->findByName($templateName);
        
        if (!$template) {
            $this->logger->error('Email template not found', ['template' => $templateName]);
            return null;
        }

        try {
            $body = $this->twig->render($template->getTemplatePath(), $variables);
            $subject = $this->twig->createTemplate($template->getSubject())->render($variables);
            
            return $this->queueEmail($recipientEmail, $subject, $body, $recipientName, $variables);
        } catch (\Exception $e) {
            $this->logger->error('Failed to queue email from template', [
                'template' => $templateName,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Queue bulk emails to multiple recipients
     */
    public function queueBulkEmails(
        array $recipients, // [['email' => '...', 'name' => '...', 'variables' => [...]], ...]
        string $subject,
        string $body
    ): int {
        $count = 0;
        
        foreach ($recipients as $recipient) {
            $this->queueEmail(
                $recipient['email'],
                $subject,
                $body,
                $recipient['name'] ?? null,
                $recipient['variables'] ?? null
            );
            $count++;
        }

        return $count;
    }

    /**
     * Queue bulk emails from template
     */
    public function queueBulkEmailsFromTemplate(
        array $recipients, // [['email' => '...', 'name' => '...', 'variables' => [...]], ...]
        string $templateName
    ): int {
        $count = 0;
        
        foreach ($recipients as $recipient) {
            $result = $this->queueEmailFromTemplate(
                $recipient['email'],
                $templateName,
                $recipient['variables'] ?? [],
                $recipient['name'] ?? null
            );
            
            if ($result) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Send pending emails from queue
     */
    public function sendPendingEmails(int $limit = 10): array
    {
        $pendingEmails = $this->queueRepository->findPendingEmails($limit);
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($pendingEmails as $queueItem) {
            try {
                $email = (new Email())
                    ->from($this->fromEmail)
                    ->to($queueItem->getRecipientEmail())
                    ->subject($queueItem->getSubject())
                    ->html($queueItem->getBody());

                $this->mailer->send($email);
                
                $queueItem->markAsSent();
                $results['sent']++;
                
                $this->logger->info('Queued email sent', [
                    'id' => $queueItem->getId(),
                    'recipient' => $queueItem->getRecipientEmail()
                ]);
            } catch (\Exception $e) {
                $queueItem->incrementAttempts();
                
                if ($queueItem->getAttempts() >= 3) {
                    $queueItem->markAsFailed($e->getMessage());
                    $this->logger->error('Email permanently failed after 3 attempts', [
                        'id' => $queueItem->getId(),
                        'error' => $e->getMessage()
                    ]);
                } else {
                    $this->logger->warning('Email send attempt failed', [
                        'id' => $queueItem->getId(),
                        'attempt' => $queueItem->getAttempts(),
                        'error' => $e->getMessage()
                    ]);
                }
                
                $results['failed']++;
                $results['errors'][] = [
                    'id' => $queueItem->getId(),
                    'email' => $queueItem->getRecipientEmail(),
                    'error' => $e->getMessage()
                ];
            }

            $this->entityManager->flush();
        }

        return $results;
    }

    /**
     * Get queue statistics
     */
    public function getQueueStatistics(): array
    {
        return $this->queueRepository->getStatistics();
    }

    /**
     * Create or update email template
     */
    public function saveTemplate(
        string $name,
        string $subject,
        string $templatePath,
        ?string $description = null
    ): EmailTemplate {
        $template = $this->templateRepository->findByName($name);
        
        if (!$template) {
            $template = new EmailTemplate();
            $template->setName($name);
        }

        $template->setSubject($subject)
            ->setTemplatePath($templatePath)
            ->setDescription($description)
            ->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        return $template;
    }
}
