<?php

namespace App\Controller;

use App\Entity\Meet;
use App\Entity\Participant;
use App\Form\MeetType;
use App\Repository\MeetRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

#[Route('/meet')]
class MeetController extends AbstractController
{
    #[Route('/', name: 'app_meet_index', methods: ['GET'])]
    public function index(Request $request, MeetRepository $meetRepository, ParticipantRepository $participantRepository): Response
    {
        $course = $request->query->get('course');
        $teacher = $request->query->get('teacher');

        return $this->render('meet/index_front.html.twig', [
            'meets' => $meetRepository->search($course, $teacher),
            'participants' => $participantRepository->findAll(),
            'search_course' => $course,
            'search_teacher' => $teacher,
        ]);
    }

    #[Route('/data', name: 'app_meet_data', methods: ['GET'])]
    public function data(Request $request, MeetRepository $meetRepository, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $course = $request->query->get('course');
        $teacher = $request->query->get('teacher');
        $sort = $request->query->get('sort');
        $dir = $request->query->get('dir');

        $items = [];
        $now = new \DateTimeImmutable();
        $meets = $meetRepository->searchCalendarAjax($course, $teacher, null, $sort, $dir);
        foreach ($meets as $meet) {
            if (!$meet instanceof Meet) {
                continue;
            }

            $dateDebut = $meet->getDateDebut();
            $dateFin = $meet->getDateFin();
            $isEnded = $dateFin !== null && $dateFin < $now;
            $isNotStarted = $dateDebut !== null && $dateDebut > $now;

            $p = $meet->getParticipant();
            $items[] = [
                'id' => $meet->getId(),
                'titre' => $meet->getTitre(),
                'profNom' => $p ? $p->getNom() : null,
                'profPrenom' => $p ? $p->getPrenom() : null,
                'profInitial' => $p && $p->getNom() ? mb_strtoupper(mb_substr($p->getNom(), 0, 1)) : null,
                'dateDebut' => $dateDebut ? $dateDebut->format('d/m/Y H:i') : null,
                'dateDebutTime' => $dateDebut ? $dateDebut->format('H:i') : null,
                'dateFin' => $dateFin ? $dateFin->format('d/m/Y H:i') : null,
                'timestampDebut' => $dateDebut ? $dateDebut->getTimestamp() : null,
                'timestampFin' => $dateFin ? $dateFin->getTimestamp() : null,
                'participantsCount' => $meet->getParticipants()->count(),
                'isEnded' => $isEnded,
                'isNotStarted' => $isNotStarted,
                'joinUrl' => $this->generateUrl('app_meet_join', ['id' => $meet->getId()]),
                'showUrl' => $this->generateUrl('app_meet_show', ['id' => $meet->getId()]),
                'editUrl' => $this->generateUrl('app_meet_edit', ['id' => $meet->getId()]),
                'deleteUrl' => $this->generateUrl('app_meet_delete', ['id' => $meet->getId()]),
                'deleteToken' => $csrfTokenManager->getToken('delete' . $meet->getId())->getValue(),
            ];
        }

        return new JsonResponse([
            'items' => $items,
        ]);
    }

    #[Route('/join', name: 'app_meet_join_code', methods: ['GET'])]
    public function joinByCode(Request $request, MeetRepository $meetRepository): Response
    {
        $code = strtoupper(trim((string) $request->query->get('code', '')));
        if ($code === '') {
            $this->addFlash('error', 'Veuillez entrer un code de réunion.');
            return $this->redirectToRoute('app_meet_index');
        }

        $id = null;
        if (preg_match('/^MEET-(\d+)$/', $code, $m)) {
            $id = (int) $m[1];
        } elseif (ctype_digit($code)) {
            $id = (int) $code;
        }

        if ($id === null || $id <= 0) {
            $this->addFlash('error', 'Code de réunion invalide. Utilisez par exemple MEET-12.');
            return $this->redirectToRoute('app_meet_index');
        }

        $meet = $meetRepository->find($id);
        if (!$meet) {
            $this->addFlash('error', 'Aucune réunion trouvée pour ce code.');
            return $this->redirectToRoute('app_meet_index');
        }

        return $this->redirectToRoute('app_meet_join', ['id' => $meet->getId()]);
    }

    #[Route('/{id}/join', name: 'app_meet_join', methods: ['GET', 'POST'])]
    public function join(Request $request, Meet $meet): Response
    {
        $now = new \DateTime();
        if ($meet->getDateDebut() !== null && $meet->getDateDebut() > $now) {
            $this->addFlash('error', 'Cette réunion n\'a pas encore commencé.');
            return $this->redirectToRoute('app_meet_index');
        }
        if ($meet->getDateFin() !== null && $meet->getDateFin() <= $now) {
            $this->addFlash('error', 'Cette réunion est terminée.');
            return $this->redirectToRoute('app_meet_index');
        }

        $form = $this->createFormBuilder(null)
            ->add('mode', ChoiceType::class, [
                'choices' => [
                    'Email' => 'email',
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'required' => false,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = (array) $form->getData();
            $email = isset($data['email']) ? trim((string) $data['email']) : '';
            if ($email === '') {
                $this->addFlash('error', 'Veuillez saisir votre email.');
            } else {
                $authorized = false;
                foreach ($meet->getParticipants() as $p) {
                    if (strtolower(trim((string) $p->getEmail())) === strtolower($email)) {
                        $authorized = true;
                        break;
                    }
                }

                if (!$authorized) {
                    $this->addFlash('error', 'Accès refusé: vous n\'êtes pas inscrit(e) à cette réunion.');
                } else {
                    $session = $request->getSession();
                    $session->set('meet_access_' . $meet->getId(), strtolower($email));
                    return $this->redirectToRoute('app_meet_room', ['id' => $meet->getId()]);
                }
            }
        }

        return $this->render('meet/join_front.html.twig', [
            'meet' => $meet,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_meet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository, MailerInterface $mailer, Environment $twig, LoggerInterface $logger): Response
    {
        $meet = new Meet();
        $meet->setCreatedAt(new \DateTime());
        
        $form = $this->createForm(MeetType::class, $meet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que le participant est un enseignant
            $participant = $meet->getParticipant();
            if ($participant && $participant->getRole() !== 'enseignant') {
                $this->addFlash('error', 'Seuls les enseignants peuvent créer des meets!');
                return $this->render('meet/new_front.html.twig', [
                    'meet' => $meet,
                    'form' => $form,
                ]);
            }

            $entityManager->persist($meet);
            $entityManager->flush();

            $teacherMailer = $mailer;
            $fromAddress = null;
            $smtpEmail = $participant ? trim((string) $participant->getSmtpEmail()) : '';
            if ($smtpEmail === '' && $participant) {
                $smtpEmail = trim((string) $participant->getEmail());
            }
            $smtpAppPassword = $participant ? (string) $participant->getSmtpAppPassword() : '';
            $smtpAppPassword = preg_replace('/\s+/', '', $smtpAppPassword ?? '') ?? '';

            if ($participant && $participant->getRole() === 'enseignant' && $smtpEmail !== '' && trim($smtpAppPassword) !== '') {
                $dsn = sprintf(
                    'smtp://%s:%s@smtp.gmail.com:465?encryption=ssl&auth_mode=login',
                    rawurlencode($smtpEmail),
                    rawurlencode($smtpAppPassword)
                );
                $transport = Transport::fromDsn($dsn);
                $teacherMailer = new Mailer($transport);
                $fromAddress = new Address($smtpEmail, trim((string) ($participant->getNom() . ' ' . $participant->getPrenom())));
            } else {
                $fromEmailRaw = '';
                if (isset($_SERVER['MAILER_FROM'])) {
                    $fromEmailRaw = trim((string) $_SERVER['MAILER_FROM']);
                }
                if ($fromEmailRaw === '' && isset($_ENV['MAILER_FROM'])) {
                    $fromEmailRaw = trim((string) $_ENV['MAILER_FROM']);
                }
                if ($fromEmailRaw === '') {
                    $fromEmailRaw = trim((string) getenv('MAILER_FROM'));
                }
                if ($fromEmailRaw === '') {
                    $fromEmailRaw = 'no-reply@naja7ni.com';
                }

                $fromAddress = Address::create($fromEmailRaw);
                if ($participant && $participant->getRole() === 'enseignant') {
                    $this->addFlash('error', 'Email non envoyé: l\'enseignant n\'a pas configuré SMTP (Gmail) (app password manquant).');
                }
            }

            $joinUrl = $this->generateUrl('app_meet_join', ['id' => $meet->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            $sentCount = 0;
            $failedCount = 0;
            $failures = [];
            foreach ($meet->getParticipants() as $student) {
                if (!$student instanceof Participant) {
                    continue;
                }
                if ($student->getRole() !== 'etudiant') {
                    continue;
                }

                $to = trim((string) $student->getEmail());
                if ($to === '') {
                    continue;
                }

                try {
                    $html = $twig->render('emails/meet_created.html.twig', [
                        'meet' => $meet,
                        'student' => $student,
                        'joinUrl' => $joinUrl,
                    ]);

                    $email = (new Email())
                        ->from($fromAddress)
                        ->to(new Address($to, trim((string) ($student->getNom() . ' ' . $student->getPrenom()))))
                        ->subject('Nouvelle réunion: ' . (string) ($meet->getTitre() ?? ''))
                        ->html($html);

                    $teacherMailer->send($email);
                    $sentCount++;
                } catch (TransportExceptionInterface $e) {
                    $failedCount++;
                    $reason = trim((string) $e->getMessage());
                    $failures[] = ['to' => $to, 'reason' => $reason];
                    $logger->error('Meet email send failed (transport).', [
                        'meet_id' => $meet->getId(),
                        'to' => $to,
                        'from_email' => $fromAddress instanceof Address ? $fromAddress->getAddress() : null,
                        'from_name' => $fromAddress instanceof Address ? $fromAddress->getName() : null,
                        'exception_class' => $e::class,
                        'exception_code' => $e->getCode(),
                        'exception_message' => $reason,
                        'exception_file' => $e->getFile(),
                        'exception_line' => $e->getLine(),
                        'exception_trace' => $e->getTraceAsString(),
                    ]);
                } catch (\Throwable $e) {
                    $failedCount++;
                    $reason = trim((string) $e->getMessage());
                    $failures[] = ['to' => $to, 'reason' => $reason];
                    $logger->error('Meet email send failed (unexpected).', [
                        'meet_id' => $meet->getId(),
                        'to' => $to,
                        'from_email' => $fromAddress instanceof Address ? $fromAddress->getAddress() : null,
                        'from_name' => $fromAddress instanceof Address ? $fromAddress->getName() : null,
                        'exception_class' => $e::class,
                        'exception_code' => $e->getCode(),
                        'exception_message' => $reason,
                        'exception_file' => $e->getFile(),
                        'exception_line' => $e->getLine(),
                        'exception_trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            if ($failedCount > 0) {
                $details = [];
                foreach (array_slice($failures, 0, 3) as $f) {
                    $details[] = sprintf('%s (%s)', (string) ($f['to'] ?? ''), (string) ($f['reason'] ?? 'erreur inconnue'));
                }
                $suffix = count($failures) > 3 ? sprintf(' (+%d autres)', count($failures) - 3) : '';
                $this->addFlash('error', sprintf(
                    'Meet créé, mais %d email(s) n\'ont pas pu être envoyés. Détails: %s%s',
                    $failedCount,
                    implode(' | ', $details),
                    $suffix
                ));
            } elseif ($sentCount > 0) {
                $this->addFlash('success', sprintf('Meet créé et %d email(s) envoyés aux étudiants.', $sentCount));
            }

            if ($sentCount === 0 && $failedCount === 0) {
                $this->addFlash('success', 'Meet créé avec succès!');
            }

            return $this->redirectToRoute('app_meet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('meet/new_front.html.twig', [
            'meet' => $meet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meet_show', methods: ['GET'])]
    public function show(Meet $meet): Response
    {
        return $this->render('meet/show_front.html.twig', [
            'meet' => $meet,
        ]);
    }

    #[Route('/{id}/room', name: 'app_meet_room', methods: ['GET'])]
    public function room(Request $request, Meet $meet): Response
    {
        $now = new \DateTime();
        if ($meet->getDateDebut() !== null && $meet->getDateDebut() > $now) {
            $this->addFlash('error', 'Cette réunion n\'a pas encore commencé.');
            return $this->redirectToRoute('app_meet_index');
        }
        if ($meet->getDateFin() !== null && $meet->getDateFin() <= $now) {
            $this->addFlash('error', 'Cette réunion est terminée.');
            return $this->redirectToRoute('app_meet_index');
        }

        $session = $request->getSession();
        $email = (string) $session->get('meet_access_' . $meet->getId(), '');
        if (trim($email) === '') {
            $this->addFlash('error', 'Veuillez rejoindre la réunion via la page d\'accès.');
            return $this->redirectToRoute('app_meet_join', ['id' => $meet->getId()]);
        }

        $authorized = false;
        foreach ($meet->getParticipants() as $p) {
            if (strtolower(trim((string) $p->getEmail())) === strtolower(trim($email))) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            $this->addFlash('error', 'Accès refusé: participant non autorisé.');
            return $this->redirectToRoute('app_meet_join', ['id' => $meet->getId()]);
        }

        $rawTitle = $meet->getTitre() ?? 'meet';
        $safeTitle = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $rawTitle));
        $safeTitle = trim($safeTitle, '-');
        $roomName = 'naja7ni-' . $meet->getId() . ($safeTitle ? '-' . $safeTitle : '');

        $jitsiDomain = $_ENV['JITSI_DOMAIN'] ?? 'meet.jit.si';

        return $this->render('meet/room_front.html.twig', [
            'meet' => $meet,
            'roomName' => $roomName,
            'jitsiDomain' => $jitsiDomain,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_meet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Meet $meet, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que le participant actuel est un enseignant
        $participant = $meet->getParticipant();
        if ($participant && $participant->getRole() !== 'enseignant') {
            $this->addFlash('error', 'Seuls les enseignants peuvent modifier des meets!');
            return $this->redirectToRoute('app_meet_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(MeetType::class, $meet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Meet modifié avec succès!');

            return $this->redirectToRoute('app_meet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('meet/edit_front.html.twig', [
            'meet' => $meet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meet_delete', methods: ['POST'])]
    public function delete(Request $request, Meet $meet, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que le participant est un enseignant
        $participant = $meet->getParticipant();
        if ($participant && $participant->getRole() !== 'enseignant') {
            $this->addFlash('error', 'Seuls les enseignants peuvent supprimer des meets!');
            return $this->redirectToRoute('app_meet_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$meet->getId(), $request->request->get('_token'))) {
            $entityManager->remove($meet);
            $entityManager->flush();

            $this->addFlash('success', 'Meet supprimé avec succès!');
        } else {
            $this->addFlash('error', 'Token CSRF invalide!');
        }

        return $this->redirectToRoute('app_meet_index', [], Response::HTTP_SEE_OTHER);
    }
}
