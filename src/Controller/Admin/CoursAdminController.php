<?php

namespace App\Controller\Admin;

use App\Entity\Cours;
use App\Form\Admin\CoursAdminType;
use App\Repository\CoursRepository;
use App\Service\CloudinaryUploader;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/cours')]
final class CoursAdminController extends AbstractController
{
    #[Route('', name: 'app_admin_cours_index', methods: ['GET'])]
    public function index(Request $request, CoursRepository $coursRepository): Response
    {
        $sort = (string) $request->query->get('sort', 'date_desc');

        $qb = $coursRepository->createQueryBuilder('co');
        if ($sort === 'alpha_asc') {
            $qb->orderBy('co.titre', 'ASC');
        } elseif ($sort === 'alpha_desc') {
            $qb->orderBy('co.titre', 'DESC');
        } else {
            $qb->orderBy('co.dateCreation', 'DESC');
        }

        return $this->render('admin/cours/index.html.twig', [
            'cours' => $qb->getQuery()->getResult(),
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'app_admin_cours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CloudinaryUploader $cloudinaryUploader): Response
    {
        $cour = new Cours();

        $form = $this->createForm(CoursAdminType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichierFile = $form->get('fichierContenu')->getData();

            if ($fichierFile !== null) {
                try {
                    $fichierFileName = $cloudinaryUploader->upload($fichierFile);
                } catch (\Throwable $e) {
                    $this->addFlash('danger', 'Upload du fichier impossible: ' . $e->getMessage());

                    return $this->render('admin/cours/new.html.twig', [
                        'cour' => $cour,
                        'form' => $form,
                    ]);
                }
                $cour->setFichierContenu($fichierFileName);
                $cour->setContenu(null);
            } else {
                $cour->setFichierContenu(null);
            }

            $cour->setCreeParAdmin(true);

            $entityManager->persist($cour);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/cours/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_cours_show', methods: ['GET'])]
    public function show(Cours $cour): Response
    {
        return $this->render('admin/cours/show.html.twig', [
            'cour' => $cour,
        ]);
    }

    #[Route('/{id}/pdf', name: 'app_admin_cours_pdf', methods: ['GET'])]
    public function pdf(Cours $cour): Response
    {
        $contenuRaw = (string) ($cour->getContenu() ?? '');
        $contenuText = html_entity_decode(strip_tags($contenuRaw));
        $contenuText = preg_replace('/\x{00A0}/u', ' ', (string) $contenuText);
        $contenuText = trim((string) $contenuText);

        if ($contenuText === '') {
            throw $this->createNotFoundException('Ce cours ne contient pas de contenu texte à exporter.');
        }

        $html = $this->renderView('cours/pdf.html.twig', [
            'cour' => $cour,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();
        $fileName = sprintf('cours-%d.pdf', (int) $cour->getId());

        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_cours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cour, EntityManagerInterface $entityManager, CloudinaryUploader $cloudinaryUploader): Response
    {
        $form = $this->createForm(CoursAdminType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichierFile = $form->get('fichierContenu')->getData();

            if ($fichierFile !== null) {
                try {
                    $fichierFileName = $cloudinaryUploader->upload($fichierFile);
                } catch (\Throwable $e) {
                    $this->addFlash('danger', 'Upload du fichier impossible: ' . $e->getMessage());

                    return $this->render('admin/cours/edit.html.twig', [
                        'cour' => $cour,
                        'form' => $form,
                    ]);
                }
                $cour->setFichierContenu($fichierFileName);
                $cour->setContenu(null);
            } else {
                $contenuRaw = (string) ($cour->getContenu() ?? '');
                $contenuText = html_entity_decode(strip_tags($contenuRaw));
                $contenuText = preg_replace('/\x{00A0}/u', ' ', (string) $contenuText);
                $contenuText = trim((string) $contenuText);

                if ($contenuText !== '') {
                    $cour->setFichierContenu(null);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_admin_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_cours_index', [], Response::HTTP_SEE_OTHER);
    }
}
