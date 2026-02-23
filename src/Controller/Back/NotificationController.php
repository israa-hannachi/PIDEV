<?php

namespace App\Controller\Back;

use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back')]
class NotificationController extends AbstractController
{
    #[Route('/notifications/clear', name: 'back_clear_notifications')]
    public function clearNotifications(NotificationService $notificationService): Response
    {
        $notificationService->clearAdminNotifications();
        
        return $this->redirectToRoute('back_event_index');
    }
}
