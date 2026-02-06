<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/payment')]
class PaymentController extends AbstractController
{
    #[Route('/', name: 'admin_payment_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/payment/index.html.twig', [
            'title' => 'Payment'
        ]);
    }
}
