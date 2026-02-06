<?php

namespace App\Controller\Admin;

use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class NotificationController extends AbstractController
{
    #[Route('/notifications/clear', name: 'admin_clear_notifications')]
    public function clearNotifications(NotificationService $notificationService): Response
    {
        $notificationService->clearAdminNotifications();
        
        return $this->redirectToRoute('admin_dashboard');
    }
}
