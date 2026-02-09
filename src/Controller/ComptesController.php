<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ComptesController extends AbstractController
{
    #[Route('/comptes', name: 'app_comptes', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('comptes/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/comptes/{id}/toggle-status', name: 'app_comptes_toggle_status', methods: ['POST'])]
    public function toggleStatus(User $user, EntityManagerInterface $em): Response
    {
        $newStatus = ($user->getStatut() === 'ACTIF') ? 'INACTIF' : 'ACTIF';
        $user->setStatut($newStatus);
        $em->flush();

        $this->addFlash('success', sprintf('Le statut de %s a été mis à jour vers %s.', $user->getEmail(), $newStatus));

        return $this->redirectToRoute('app_comptes');
    }

    #[Route('/comptes/export/pdf', name: 'app_comptes_export_pdf', methods: ['GET'])]
    public function exportPdf(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($pdfOptions);
        
        $html = $this->renderView('comptes/pdf.html.twig', [
            'users' => $users,
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="liste_comptes.pdf"',
            ]
        );
    }
}
