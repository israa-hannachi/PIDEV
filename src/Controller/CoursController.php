<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Cours;
use App\Entity\Module;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cours')]
final class CoursController extends AbstractController
{
    #[Route(name: 'app_cours_index', methods: ['GET'])]
    public function index(Request $request, CoursRepository $coursRepository): Response
    {
        $sort = (string) $request->query->get('sort', 'date_desc');

        $session = $request->getSession();
        $favorisIds = (array) $session->get('favoris_ids', []);
        $favorisIds = array_values(array_unique(array_map('intval', $favorisIds)));

        $qb = $coursRepository->createQueryBuilder('co');
        if ($sort === 'alpha_asc') {
            $qb->orderBy('co.titre', 'ASC');
        } elseif ($sort === 'alpha_desc') {
            $qb->orderBy('co.titre', 'DESC');
        } else {
            $qb->orderBy('co.dateCreation', 'DESC');
        }

        return $this->render('cours/index.html.twig', [
            'cours' => $qb->getQuery()->getResult(),
            'favoris_ids' => $favorisIds,
        ]);
    }

    #[Route('/new', name: 'app_cours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $cour = new Cours();
        
        $moduleId = $request->query->get('module');
        if (!$moduleId) {
            throw $this->createNotFoundException('Création de cours: module manquant.');
        }

        $module = $entityManager->find(Module::class, $moduleId);
        if (!$module) {
            throw $this->createNotFoundException('Module introuvable.');
        }

        $cour->setModule($module);
        
        $form = $this->createForm(CoursType::class, $cour, [
            'lock_module' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichierFile = $form->get('fichierContenu')->getData();

            if ($fichierFile !== null) {
                try {
                    $fichierFileName = $fileUploader->upload($fichierFile);
                } catch (\Throwable $e) {
                    $this->addFlash('danger', 'Upload du fichier impossible: ' . $e->getMessage());

                    return $this->render('cours/new.html.twig', [
                        'cour' => $cour,
                        'form' => $form,
                    ]);
                }
                $cour->setFichierContenu($fichierFileName);
                $cour->setContenu(null);
            } else {
                $cour->setFichierContenu(null);
            }

            $entityManager->persist($cour);
            $entityManager->flush();

            // Rediriger vers la page du module si disponible
            if ($cour->getModule()) {
                return $this->redirectToRoute('app_module_show', ['id' => $cour->getModule()->getId()], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cours/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_show', methods: ['GET'])]
    public function show(Request $request, Cours $cour): Response
    {
        $session = $request->getSession();
        $favorisIds = (array) $session->get('favoris_ids', []);
        $favorisIds = array_values(array_unique(array_map('intval', $favorisIds)));

        return $this->render('cours/show.html.twig', [
            'cour' => $cour,
            'favoris_ids' => $favorisIds,
        ]);
    }

    #[Route('/{id}/pdf', name: 'app_cours_pdf', methods: ['GET'])]
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

    #[Route('/{id}/edit', name: 'app_cours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cour, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(CoursType::class, $cour, [
            'lock_module' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichierFile = $form->get('fichierContenu')->getData();

            if ($fichierFile !== null) {
                try {
                    $fichierFileName = $fileUploader->upload($fichierFile);
                } catch (\Throwable $e) {
                    $this->addFlash('danger', 'Upload du fichier impossible: ' . $e->getMessage());

                    return $this->render('cours/edit.html.twig', [
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

            // Rediriger vers la page du module si disponible
            if ($cour->getModule()) {
                return $this->redirectToRoute('app_module_show', ['id' => $cour->getModule()->getId()], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        $moduleId = $cour->getModule() ? $cour->getModule()->getId() : null;
        $referer = $request->headers->get('referer');

        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        if ($referer) {
            return $this->redirect($referer);
        }

        if ($moduleId) {
            return $this->redirectToRoute('app_module_show', ['id' => $moduleId], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
    }
}
