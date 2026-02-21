<?php

namespace App\Controller\Back;

use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/payments')]
class PaymentController extends AbstractController
{
    #[Route('/', name: 'app_back_payment_index', methods: ['GET'])]
    public function index(RegistrationRepository $registrationRepository): Response
    {
        // Fetch all registrations for events that have a price > 0
        $queryBuilder = $registrationRepository->createQueryBuilder('r')
            ->join('r.evenement', 'e')
            ->where('e.prix > 0')
            ->orderBy('r.dateInscription', 'DESC');

        $payments = $queryBuilder->getQuery()->getResult();

        // Calculate some stats
        $totalRevenue = 0;
        $pendingCount = 0;
        $confirmedCount = 0;

        foreach ($payments as $payment) {
            if ($payment->getStatut() === 'confirmé') {
                $totalRevenue += $payment->getEvenement()->getPrix();
                $confirmedCount++;
            } elseif ($payment->getStatut() === 'en_attente') {
                $pendingCount++;
            }
        }

        return $this->render('back/payment/index.html.twig', [
            'payments' => $payments,
            'stats' => [
                'total_revenue' => $totalRevenue,
                'pending_count' => $pendingCount,
                'confirmed_count' => $confirmedCount,
                'total_count' => count($payments),
            ]
        ]);
    }

    #[Route('/{id}/confirm', name: 'app_back_payment_confirm', methods: ['POST'])]
    public function confirm(
        \App\Entity\Registration $registration, 
        EntityManagerInterface $entityManager,
        \App\Service\NotificationService $notificationService
    ): Response {
        if ($registration->getStatut() !== 'confirmé') {
            $registration->setStatut('confirmé');
            $event = $registration->getEvenement();
            $event->setInscrits($event->getInscrits() + 1);
            $entityManager->flush();
            $notificationService->notifyNewRegistration($registration);
            $this->addFlash('success', 'Paiement confirmé manuellement.');
        }

        return $this->redirectToRoute('app_back_payment_index');
    }

    #[Route('/{id}/refresh', name: 'app_back_payment_refresh', methods: ['GET'])]
    public function refresh(
        \App\Entity\Registration $registration,
        \App\Service\PaymeeService $paymeeService,
        EntityManagerInterface $entityManager,
        \App\Service\NotificationService $notificationService
    ): Response {
        // This would require a token which we might not have stored if it wasn't successful
        // But if we have one in the logs... for now let's just add the route.
        $this->addFlash('info', 'Fonctionnalité de rafraîchissement API en cours de configuration.');
        return $this->redirectToRoute('app_back_payment_index');
    }
}
