<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Registration;
use Symfony\Component\HttpFoundation\RequestStack;

class NotificationService
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Check if event capacity is nearly full and add warning notification
     */
    public function checkCapacityWarning(Event $event): void
    {
        $capacity = $event->getCapacite();
        $registered = $event->getInscrits();
        
        if ($capacity > 0) {
            $percentage = ($registered / $capacity) * 100;
            
            if ($percentage >= 90) {
                $this->addAdminNotification(
                    'capacity_warning',
                    sprintf(
                        'L\'événement "%s" est presque complet (%d/%d inscrits - %.0f%%)',
                        $event->getTitre(),
                        $registered,
                        $capacity,
                        $percentage
                    ),
                    'warning'
                );
            }
        }
    }

    /**
     * Notify admin of new registration
     */
    public function notifyNewRegistration(Registration $registration): void
    {
        $event = $registration->getEvenement();
        
        $this->addAdminNotification(
            'new_registration',
            sprintf(
                'Nouvelle inscription de %s pour l\'événement "%s"',
                $registration->getVisitorName(),
                $event->getTitre()
            ),
            'info'
        );
    }

    /**
     * Add notification to admin session
     */
    private function addAdminNotification(string $type, string $message, string $level): void
    {
        $session = $this->requestStack->getSession();
        
        // Get existing notifications
        $notifications = $session->get('admin_notifications', []);
        
        // Add new notification with timestamp
        $notifications[] = [
            'type' => $type,
            'message' => $message,
            'level' => $level,
            'timestamp' => new \DateTime(),
        ];
        
        // Keep only last 10 notifications
        if (count($notifications) > 10) {
            $notifications = array_slice($notifications, -10);
        }
        
        $session->set('admin_notifications', $notifications);
    }

    /**
     * Get all admin notifications
     */
    public function getAdminNotifications(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get('admin_notifications', []);
    }

    /**
     * Clear all admin notifications
     */
    public function clearAdminNotifications(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove('admin_notifications');
    }
}
