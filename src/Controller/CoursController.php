<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Cours;
use App\Entity\ReclamationCours;
use App\Entity\Module;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use App\Service\CloudinaryUploader;
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
        $now = new \DateTime();

        $session = $request->getSession();
        $favorisIds = (array) $session->get('favoris_ids', []);
        $favorisIds = array_values(array_unique(array_map('intval', $favorisIds)));

        $qb = $coursRepository->createQueryBuilder('co')
            ->andWhere('co.visible = :visible')
            ->andWhere('co.visibleFrom IS NULL OR co.visibleFrom <= :now')
            ->setParameter('visible', true)
            ->setParameter('now', $now);
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
    public function new(Request $request, EntityManagerInterface $entityManager, CloudinaryUploader $cloudinaryUploader): Response
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
                    $fichierFileName = $cloudinaryUploader->upload($fichierFile);
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
        $now = new \DateTime();
        if (!$cour->isVisible() || ($cour->getVisibleFrom() !== null && $cour->getVisibleFrom() > $now)) {
            throw $this->createNotFoundException('Cours indisponible.');
        }

        $session = $request->getSession();
        $favorisIds = (array) $session->get('favoris_ids', []);
        $favorisIds = array_values(array_unique(array_map('intval', $favorisIds)));

        $notes = (array) $session->get('cours_notes', []);
        $noteValue = '';
        $cid = (int) ($cour->getId() ?? 0);
        if ($cid > 0) {
            $raw = $notes[$cid] ?? $notes[(string) $cid] ?? '';
            $noteValue = is_string($raw) ? $raw : '';
        }

        return $this->render('cours/show.html.twig', [
            'cour' => $cour,
            'favoris_ids' => $favorisIds,
            'note_value' => $noteValue,
        ]);
    }

    #[Route('/{id}/note', name: 'app_cours_note_save', methods: ['POST'])]
    public function saveNote(Request $request, Cours $cour): Response
    {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('cours_note' . $cour->getId(), $token)) {
            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        $note = (string) $request->request->get('note', '');
        $note = trim($note);
        if (mb_strlen($note) > 5000) {
            $note = mb_substr($note, 0, 5000);
        }

        $session = $request->getSession();
        $notes = (array) $session->get('cours_notes', []);
        $notes[(int) $cour->getId()] = $note;
        $session->set('cours_notes', $notes);

        $this->addFlash('success', 'Note enregistrée.');
        return $this->redirectToRoute('app_cours_show', ['id' => $cour->getId()]);
    }

    #[Route('/{id}/note/clear', name: 'app_cours_note_clear', methods: ['POST'])]
    public function clearNote(Request $request, Cours $cour): Response
    {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('cours_note_clear' . $cour->getId(), $token)) {
            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        $session = $request->getSession();
        $notes = (array) $session->get('cours_notes', []);
        unset($notes[(int) $cour->getId()], $notes[(string) $cour->getId()]);
        $session->set('cours_notes', $notes);

        $this->addFlash('success', 'Note supprimée.');
        return $this->redirectToRoute('app_cours_show', ['id' => $cour->getId()]);
    }

    #[Route('/{id}/reclamation', name: 'app_cours_reclamation_submit', methods: ['POST'])]
    public function submitReclamation(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('cours_reclamation' . $cour->getId(), $token)) {
            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        $message = (string) $request->request->get('message', '');
        $message = trim($message);
        if ($message === '') {
            $this->addFlash('danger', 'Veuillez saisir une remarque / réclamation.');
            return $this->redirectToRoute('app_cours_show', ['id' => $cour->getId()]);
        }
        if (mb_strlen($message) > 5000) {
            $message = mb_substr($message, 0, 5000);
        }

        $reclamation = new ReclamationCours();
        $reclamation->setCours($cour);
        $reclamation->setMessage($message);

        $entityManager->persist($reclamation);
        $entityManager->flush();

        $this->addFlash('success', 'Réclamation envoyée.');
        return $this->redirectToRoute('app_cours_show', ['id' => $cour->getId()]);
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
    public function edit(Request $request, Cours $cour, EntityManagerInterface $entityManager, CloudinaryUploader $cloudinaryUploader): Response
    {
        $form = $this->createForm(CoursType::class, $cour, [
            'lock_module' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichierFile = $form->get('fichierContenu')->getData();

            if ($fichierFile !== null) {
                try {
                    $fichierFileName = $cloudinaryUploader->upload($fichierFile);
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
    public function delete(Request $request, ?Cours $cour, EntityManagerInterface $entityManager): Response
    {
        if ($cour === null) {
            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($cour->isCreeParAdmin()) {
            throw $this->createAccessDeniedException('Suppression interdite: ce cours a été créé par un administrateur.');
        }

        $deletedId = $cour->getId();
        $moduleId = $cour->getModule() ? $cour->getModule()->getId() : null;
        $referer = $request->headers->get('referer');

        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        if ($referer) {
            $refererPath = (string) (parse_url($referer, PHP_URL_PATH) ?? '');
            $deletedShowPath = $this->generateUrl('app_cours_show', ['id' => $deletedId]);
            if ($refererPath !== '' && $refererPath !== $deletedShowPath) {
                return $this->redirect($referer);
            }
        }

        if ($moduleId) {
            return $this->redirectToRoute('app_module_show', ['id' => $moduleId], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
    }
}
