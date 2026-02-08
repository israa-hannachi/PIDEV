<?php

namespace App\EventSubscriber;

use App\Service\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class AdminNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NotificationService $notificationService,
        private Environment $twig
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        // Check if it's an admin controller
        if (is_array($controller)) {
            $controllerObject = $controller[0];
            $controllerClass = get_class($controllerObject);
            
            if (str_contains($controllerClass, 'App\\Controller\\Admin\\')) {
                // Add notifications as a global Twig variable
                $this->twig->addGlobal('admin_notifications', $this->notificationService->getAdminNotifications());
            }
        }
    }
}
