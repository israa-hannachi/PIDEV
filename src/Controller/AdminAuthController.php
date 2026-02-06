<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminAuthController extends AbstractController
{
    #[Route('/admin/login', name: 'app_admin_login')]
    public function login(Request $request, SessionInterface $session): Response
    {
        if ($request->isMethod('POST')) {
            $password = $request->request->get('admin_password');
            
            // Mot de passe admin (à configurer en production)
            if ($password === 'naja7ni_admin_2024') {
                $session->set('isAdmin', true);
                $session->set('adminLoginTime', time());
                $this->addFlash('success', 'Connexion administrateur réussie');
                return $this->redirectToRoute('app_user_index');
            }
            
            $this->addFlash('error', 'Mot de passe administrateur incorrect');
        }
        
        return $this->render('admin/login.html.twig');
    }
    
    #[Route('/admin/logout', name: 'app_admin_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->remove('isAdmin');
        $session->remove('adminLoginTime');
        $this->addFlash('success', 'Déconnexion réussie');
        return $this->redirectToRoute('app_home');
    }
}
