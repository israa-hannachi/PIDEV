<?php

namespace App\Controller\Admin;

use App\Entity\Sponsor;
use App\Form\SponsorType;
use App\Repository\SponsorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/sponsor')]
class SponsorController extends AbstractController
{
    #[Route('/', name: 'admin_sponsor_index', methods: ['GET'])]
    public function index(SponsorRepository $sponsorRepository): Response
    {
        return $this->render('admin/sponsor/index.html.twig', [
            'sponsors' => $sponsorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_sponsor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sponsor = new Sponsor();
        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$sponsor->getStatut()) {
                $sponsor->setStatut('actif');
            }
            
            $entityManager->persist($sponsor);
            $entityManager->flush();

            return $this->redirectToRoute('admin_sponsor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/sponsor/new.html.twig', [
            'sponsor' => $sponsor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_sponsor_show', methods: ['GET'])]
    public function show(Sponsor $sponsor): Response
    {
        return $this->render('admin/sponsor/show.html.twig', [
            'sponsor' => $sponsor,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_sponsor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sponsor $sponsor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_sponsor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/sponsor/edit.html.twig', [
            'sponsor' => $sponsor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_sponsor_delete', methods: ['POST'])]
    public function delete(Request $request, Sponsor $sponsor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sponsor->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sponsor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_sponsor_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/export/pdf', name: 'admin_sponsor_export_pdf', methods: ['GET'])]
    public function exportPdf(Sponsor $sponsor): Response
    {
        $html = $this->renderView('admin/sponsor/report_pdf.html.twig', [
            'sponsor' => $sponsor,
            'date' => new \DateTime(),
        ]);

        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="report-' . $sponsor->getNom() . '.pdf"',
            ]);
        }

        return new Response("PDF library not installed.", 200);
    }

    #[Route('/{id}/export/excel', name: 'admin_sponsor_export_excel', methods: ['GET'])]
    public function exportExcel(Sponsor $sponsor): Response
    {
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'Sponsor Report');
            $sheet->setCellValue('A2', 'Name:');
            $sheet->setCellValue('B2', $sponsor->getNom());
            $sheet->setCellValue('A3', 'Type:');
            $sheet->setCellValue('B3', $sponsor->getType());
            $sheet->setCellValue('A4', 'Amount:');
            $sheet->setCellValue('B4', $sponsor->getMontant() . ' €');
            $sheet->setCellValue('A5', 'Contact:');
            $sheet->setCellValue('B5', $sponsor->getContactPersonne() . ' (' . $sponsor->getContactEmail() . ')');
            $sheet->setCellValue('A7', 'Summary of Collaboration:');
            $sheet->setCellValue('A8', 'This sponsor has been a key partner in our recent events, contributing significantly to the success of our community.');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $fileName = 'sponsor-' . $sponsor->getNom() . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);

            return $this->file($tempFile, $fileName);
        }

        return new Response("Excel library not installed.", 200);
    }
}
