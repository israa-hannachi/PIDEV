<?php

namespace App\Controller;

use App\Entity\EmailTemplate;
use App\Repository\EmailTemplateRepository;
use App\Repository\EmailQueueRepository;
use App\Service\MailingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/mailing', name: 'mailing_')]
#[IsGranted('ROLE_ADMIN')]
class MailingController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(MailingService $mailingService, EmailQueueRepository $emailQueueRepository): Response
    {
        $stats = $mailingService->getQueueStatistics();
        $recentEmails = $emailQueueRepository->findRecentEmails(10);

        return $this->render('mailing/dashboard.html.twig', [
            'stats' => $stats,
            'recentEmails' => $recentEmails,
        ]);
    }

    #[Route('/templates', name: 'templates', methods: ['GET'])]
    public function templates(EmailTemplateRepository $templateRepository): Response
    {
        return $this->render('mailing/templates.html.twig', [
            'templates' => $templateRepository->findAllOrderedByName(),
        ]);
    }

    #[Route('/templates/new', name: 'template_new', methods: ['GET', 'POST'])]
    public function templateNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $subject = trim((string) $request->request->get('subject', ''));
            $templatePath = trim((string) $request->request->get('template_path', ''));
            $description = trim((string) $request->request->get('description', ''));

            if ($name === '' || $subject === '' || $templatePath === '') {
                $this->addFlash('error', 'Name, subject and template path are required.');

                return $this->render('mailing/template_form.html.twig', [
                    'mode' => 'create',
                    'template' => null,
                    'old' => [
                        'name' => $name,
                        'subject' => $subject,
                        'template_path' => $templatePath,
                        'description' => $description,
                    ],
                ]);
            }

            $template = new EmailTemplate();
            $template
                ->setName($name)
                ->setSubject($subject)
                ->setTemplatePath($templatePath)
                ->setDescription($description !== '' ? $description : null)
                ->setUpdatedAt(new \DateTime());

            $entityManager->persist($template);
            $entityManager->flush();

            $this->addFlash('success', 'Template created successfully.');

            return $this->redirectToRoute('mailing_templates');
        }

        return $this->render('mailing/template_form.html.twig', [
            'mode' => 'create',
            'template' => null,
            'old' => [
                'name' => '',
                'subject' => '',
                'template_path' => '',
                'description' => '',
            ],
        ]);
    }

    #[Route('/templates/{id}/edit', name: 'template_edit', methods: ['GET', 'POST'])]
    public function templateEdit(EmailTemplate $template, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $subject = trim((string) $request->request->get('subject', ''));
            $templatePath = trim((string) $request->request->get('template_path', ''));
            $description = trim((string) $request->request->get('description', ''));

            if ($name === '' || $subject === '' || $templatePath === '') {
                $this->addFlash('error', 'Name, subject and template path are required.');

                return $this->render('mailing/template_form.html.twig', [
                    'mode' => 'edit',
                    'template' => $template,
                    'old' => [
                        'name' => $name,
                        'subject' => $subject,
                        'template_path' => $templatePath,
                        'description' => $description,
                    ],
                ]);
            }

            $template
                ->setName($name)
                ->setSubject($subject)
                ->setTemplatePath($templatePath)
                ->setDescription($description !== '' ? $description : null)
                ->setUpdatedAt(new \DateTime());

            $entityManager->flush();

            $this->addFlash('success', 'Template updated successfully.');

            return $this->redirectToRoute('mailing_templates');
        }

        return $this->render('mailing/template_form.html.twig', [
            'mode' => 'edit',
            'template' => $template,
            'old' => [
                'name' => $template->getName() ?? '',
                'subject' => $template->getSubject() ?? '',
                'template_path' => $template->getTemplatePath() ?? '',
                'description' => $template->getDescription() ?? '',
            ],
        ]);
    }

    #[Route('/queue', name: 'queue', methods: ['GET'])]
    public function queue(Request $request, EmailQueueRepository $emailQueueRepository): Response
    {
        $status = $request->query->get('status');

        $qb = $emailQueueRepository->createQueryBuilder('e')
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults(100);

        if (
            is_string($status)
            && in_array($status, ['pending', 'sent', 'failed'], true)
        ) {
            $qb->andWhere('e.status = :status')->setParameter('status', $status);
        }

        return $this->render('mailing/queue.html.twig', [
            'emails' => $qb->getQuery()->getResult(),
            'status' => $status,
        ]);
    }

    #[Route('/bulk-send', name: 'bulk_send', methods: ['GET', 'POST'])]
    public function bulkSend(
        Request $request,
        MailingService $mailingService,
        EmailTemplateRepository $templateRepository
    ): Response {
        $templates = $templateRepository->findAllOrderedByName();

        if ($request->isMethod('POST')) {
            $templateName = trim((string) $request->request->get('template_name', ''));
            $recipientsRaw = trim((string) $request->request->get('recipients', ''));
            $loginUrl = trim((string) $request->request->get('login_url', ''));

            if ($templateName === '' || $recipientsRaw === '') {
                $this->addFlash('error', 'Template and recipients are required.');

                return $this->render('mailing/bulk_send.html.twig', [
                    'templates' => $templates,
                    'old' => [
                        'template_name' => $templateName,
                        'recipients' => $recipientsRaw,
                        'login_url' => $loginUrl,
                    ],
                ]);
            }

            $lines = preg_split('/\r\n|\r|\n/', $recipientsRaw) ?: [];
            $recipients = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $parts = array_map('trim', explode(',', $line, 2));
                $email = $parts[0] ?? '';
                $name = $parts[1] ?? $email;

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $variables = ['name' => $name];
                if ($loginUrl !== '') {
                    $variables['loginUrl'] = $loginUrl;
                }

                $recipients[] = [
                    'email' => $email,
                    'name' => $name,
                    'variables' => $variables,
                ];
            }

            if ($recipients === []) {
                $this->addFlash('error', 'No valid recipient email addresses found.');

                return $this->render('mailing/bulk_send.html.twig', [
                    'templates' => $templates,
                    'old' => [
                        'template_name' => $templateName,
                        'recipients' => $recipientsRaw,
                        'login_url' => $loginUrl,
                    ],
                ]);
            }

            $queuedCount = $mailingService->queueBulkEmailsFromTemplate($recipients, $templateName);
            $this->addFlash('success', sprintf('%d emails queued successfully.', $queuedCount));

            return $this->redirectToRoute('mailing_queue');
        }

        return $this->render('mailing/bulk_send.html.twig', [
            'templates' => $templates,
            'old' => [
                'template_name' => '',
                'recipients' => '',
                'login_url' => '',
            ],
        ]);
    }

    #[Route('/send-pending', name: 'send_pending', methods: ['POST'])]
    public function sendPending(Request $request, MailingService $mailingService): Response
    {
        $limit = (int) $request->request->get('limit', 50);
        if ($limit <= 0) {
            $limit = 50;
        }

        $result = $mailingService->sendPendingEmails($limit);
        $this->addFlash('success', sprintf('Processed queue: %d sent, %d failed.', $result['sent'], $result['failed']));

        return $this->redirectToRoute('mailing_queue');
    }

    #[Route('/test-send', name: 'test_send', methods: ['POST'])]
    public function testSend(Request $request, MailingService $mailingService): Response
    {
        $recipientEmail = trim((string) $request->request->get('recipient_email', ''));
        $subject = trim((string) $request->request->get('subject', 'Mailing test email'));
        $message = trim((string) $request->request->get('message', 'This is a test email from Naja7ni mailing dashboard.'));

        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Please enter a valid recipient email address.');
            return $this->redirectToRoute('mailing_dashboard');
        }

        $body = sprintf(
            '<h2>Test Email</h2><p>%s</p><p><small>Sent at %s</small></p>',
            htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        $sent = $mailingService->sendEmailNow($recipientEmail, $subject, $body);

        if ($sent) {
            $this->addFlash('success', sprintf('Test email sent successfully to %s.', $recipientEmail));
        } else {
            $this->addFlash('error', 'Failed to send test email. Please verify your MAILER_DSN and Gmail App Password.');
        }

        return $this->redirectToRoute('mailing_dashboard');
    }
}
