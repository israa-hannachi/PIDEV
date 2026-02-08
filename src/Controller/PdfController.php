<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\GameRepository;

final class PdfController extends AbstractController
{
    #[Route('/games/pdf', name: 'games_pdf')]
public function pdf(GameRepository $gameRepository): Response
{
    $games = $gameRepository->findAll();

    $dompdf = new \Dompdf\Dompdf();
    $html = $this->renderView('pdf/index.html.twig', ['games' => $games]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $response = new Response($dompdf->output());
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', 'attachment; filename="liste-jeux.pdf"');

    return $response;
}
#[Route('/games/excel', name: 'games_excel')]
public function excel(GameRepository $gameRepository): Response
{
    $games = $gameRepository->findAll();

    // Créer un nouveau fichier Excel
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Liste des Jeux');

    // En-têtes
    $headers = ['Titre', 'Type', 'Niveau', 'Score Max', 'Durée', 'Tentatives'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col.'1', $header);
        // Style : gras + fond gris
        $sheet->getStyle($col.'1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle($col.'1')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFD3D3D3');
        $sheet->getColumnDimension($col)->setAutoSize(true);
        $col++;
    }

    // Données
    $row = 2;
    foreach ($games as $game) {
        $sheet->setCellValue("A$row", $game->getTitre());
        $sheet->setCellValue("B$row", $game->getType());
        $sheet->setCellValue("C$row", $game->getNiveau());
        $sheet->setCellValue("D$row", $game->getScoreMax());
        $sheet->setCellValue("E$row", $game->getDuration());
        $sheet->setCellValue("F$row", $game->getAttemptNumber());

        // Style alterné pour les lignes (zébrage)
        if ($row % 2 == 0) {
            $sheet->getStyle("A$row:F$row")->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFF9F9F9');
        }

        $row++;
    }

    // Générer le fichier Excel
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    ob_start();
    $writer->save('php://output');
    $content = ob_get_clean();

    // Réponse HTTP pour téléchargement direct
    $response = new Response($content);
    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment; filename="liste-jeux.xlsx"');

    return $response;
}


#[Route('/games/csv', name: 'games_csv')]
public function csv(GameRepository $gameRepository): Response
{
    $games = $gameRepository->findAll();

    $handle = fopen('php://temp', 'r+');
    fputcsv($handle, ['ID', 'Titre', 'Type', 'Niveau', 'Score Max', 'Durée', 'Tentatives'], ';');

    foreach ($games as $game) {
        fputcsv($handle, [
            $game->getId(),
            $game->getTitre(),
            $game->getType(),
            $game->getNiveau(),
            $game->getScoreMax(),
            $game->getDuration(),
            $game->getAttemptNumber()
        ], ';');
    }

    rewind($handle);
    $content = stream_get_contents($handle);
    fclose($handle);

    $response = new Response($content);
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="liste-jeux.csv"');

    return $response;
}







}
