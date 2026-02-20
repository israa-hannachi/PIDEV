<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Module;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/parcours')]
final class ParcoursController extends AbstractController
{
    #[Route('', name: 'app_parcours_builder', methods: ['GET'])]
    public function builder(Request $request, ModuleRepository $moduleRepository, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $session = $request->getSession();
        $state = $session->get('parcours_state');

        $modules = $moduleRepository->createQueryBuilder('m')
            ->leftJoin('m.cours', 'c')
            ->addSelect('c')
            ->orderBy('m.dateCreation', 'DESC')
            ->addOrderBy('c.ordre', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('parcours/builder.html.twig', [
            'modules' => $modules,
            'state' => is_array($state) ? $state : null,
            'csrf_token' => $csrfTokenManager->getToken('parcours_save')->getValue(),
        ]);
    }

    #[Route('/save', name: 'app_parcours_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $entityManager): Response
    {
        $isJson = str_contains((string) $request->headers->get('content-type', ''), 'application/json');
        $payload = null;
        if ($isJson) {
            $payload = json_decode((string) $request->getContent(), true);
            if (!is_array($payload)) {
                return new JsonResponse(['ok' => false, 'error' => 'Payload invalide.'], 400);
            }
        }

        $token = (string) ($isJson ? ($payload['_token'] ?? '') : $request->request->get('_token', ''));
        if (!$this->isCsrfTokenValid('parcours_save', $token)) {
            if ($isJson) {
                return new JsonResponse(['ok' => false, 'error' => 'CSRF token invalide.'], 403);
            }

            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        $moduleOrder = $isJson ? ($payload['moduleOrder'] ?? null) : $request->request->get('moduleOrder');
        $coursOrder = $isJson ? ($payload['coursOrder'] ?? null) : $request->request->get('coursOrder');
        $coursMeta = $isJson ? ($payload['coursMeta'] ?? null) : $request->request->get('coursMeta');

        if (!$isJson) {
            $moduleOrder = is_string($moduleOrder) ? json_decode($moduleOrder, true) : null;
            $coursOrder = is_string($coursOrder) ? json_decode($coursOrder, true) : null;
            $coursMeta = is_string($coursMeta) ? json_decode($coursMeta, true) : null;
        }

        if (!is_array($moduleOrder) || !is_array($coursOrder) || ($coursMeta !== null && !is_array($coursMeta))) {
            if ($isJson) {
                return new JsonResponse(['ok' => false, 'error' => 'Données manquantes.'], 400);
            }

            $this->addFlash('danger', 'Sauvegarde impossible: données manquantes.');
            return $this->redirectToRoute('app_parcours_builder');
        }

        $moduleIds = array_values(array_unique(array_map('intval', $moduleOrder)));
        $modules = $entityManager->getRepository(Module::class)->findBy(['id' => $moduleIds]);
        $existingModuleIds = array_map(static fn (Module $m) => (int) $m->getId(), $modules);

        foreach ($moduleIds as $id) {
            if (!in_array($id, $existingModuleIds, true)) {
                if ($isJson) {
                    return new JsonResponse(['ok' => false, 'error' => 'Module invalide: ' . $id], 400);
                }

                $this->addFlash('danger', 'Sauvegarde impossible: module invalide.');
                return $this->redirectToRoute('app_parcours_builder');
            }
        }

        $sanitizedCoursOrder = [];
        $sanitizedCoursMeta = [];
        foreach ($coursOrder as $moduleId => $coursIds) {
            $mid = (int) $moduleId;
            if (!in_array($mid, $moduleIds, true)) {
                continue;
            }

            if (!is_array($coursIds)) {
                continue;
            }

            $cids = array_values(array_unique(array_map('intval', $coursIds)));
            if ($cids === []) {
                $sanitizedCoursOrder[$mid] = [];
                continue;
            }

            $cours = $entityManager->getRepository(Cours::class)->findBy(['id' => $cids]);
            $existingCoursIds = [];
            foreach ($cours as $c) {
                if ($c->getModule() && (int) $c->getModule()->getId() === $mid) {
                    $existingCoursIds[] = (int) $c->getId();
                }
            }

            foreach ($cids as $cid) {
                if (!in_array($cid, $existingCoursIds, true)) {
                    if ($isJson) {
                        return new JsonResponse(['ok' => false, 'error' => 'Cours invalide: ' . $cid], 400);
                    }

                    $this->addFlash('danger', 'Sauvegarde impossible: cours invalide.');
                    return $this->redirectToRoute('app_parcours_builder');
                }
            }

            $sanitizedCoursOrder[$mid] = $cids;
        }

        if (is_array($coursMeta)) {
            foreach ($coursMeta as $coursId => $meta) {
                $cid = (int) $coursId;
                if ($cid <= 0) {
                    continue;
                }
                if (!is_array($meta)) {
                    continue;
                }

                $statut = isset($meta['statut']) && is_string($meta['statut']) ? $meta['statut'] : 'todo';
                if (!in_array($statut, ['todo', 'doing', 'done'], true)) {
                    $statut = 'todo';
                }

                $objectif = isset($meta['objectif']) && is_string($meta['objectif']) ? $meta['objectif'] : '';
                $notes = isset($meta['notes']) && is_string($meta['notes']) ? $meta['notes'] : '';

                // limiter un peu la taille stockée en session
                $objectif = mb_substr($objectif, 0, 200);
                $notes = mb_substr($notes, 0, 2000);

                $sanitizedCoursMeta[$cid] = [
                    'statut' => $statut,
                    'objectif' => $objectif,
                    'notes' => $notes,
                ];
            }
        }

        $request->getSession()->set('parcours_state', [
            'moduleOrder' => $moduleIds,
            'coursOrder' => $sanitizedCoursOrder,
            'coursMeta' => $sanitizedCoursMeta,
            'savedAt' => time(),
        ]);

        if ($isJson) {
            return new JsonResponse(['ok' => true]);
        }

        $this->addFlash('success', 'Parcours sauvegardé.');
        return $this->redirectToRoute('app_parcours_builder');
    }
}
