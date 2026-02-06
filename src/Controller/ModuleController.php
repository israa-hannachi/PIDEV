<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\CoursRepository;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/module')]
final class ModuleController extends AbstractController
{
    #[Route(name: 'app_module_index', methods: ['GET'])]
    public function index(Request $request, ModuleRepository $moduleRepository): Response
    {
        $sort = (string) $request->query->get('sort', 'date_desc');

        $qb = $moduleRepository->createQueryBuilder('m');
        if ($sort === 'alpha_asc') {
            $qb->orderBy('m.titre', 'ASC');
        } elseif ($sort === 'alpha_desc') {
            $qb->orderBy('m.titre', 'DESC');
        } else {
            $qb->orderBy('m.dateCreation', 'DESC');
        }

        return $this->render('module/index.html.twig', [
            'modules' => $qb->getQuery()->getResult(),
        ]);
    }

    #[Route('/new', name: 'app_module_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $module = new Module();

        $categorieId = $request->query->get('categorie');
        if (!$categorieId) {
            throw $this->createNotFoundException('Création de module: catégorie manquante.');
        }

        $categorie = $entityManager->find(Categorie::class, $categorieId);
        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        $module->setCategorie($categorie);
        
        $form = $this->createForm(ModuleType::class, $module, [
            'lock_categorie' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($module);
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_show', ['id' => $module->getCategorie()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('module/new.html.twig', [
            'module' => $module,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_module_show', methods: ['GET'])]
    public function show(Request $request, Module $module, CoursRepository $coursRepository): Response
    {
        $sortCours = (string) $request->query->get('sort_cours', 'date_desc');

        $session = $request->getSession();
        $favorisIds = (array) $session->get('favoris_ids', []);
        $favorisIds = array_values(array_unique(array_map('intval', $favorisIds)));

        $qb = $coursRepository->createQueryBuilder('co')
            ->andWhere('co.module = :module')
            ->setParameter('module', $module);

        if ($sortCours === 'alpha_asc') {
            $qb->orderBy('co.titre', 'ASC');
        } elseif ($sortCours === 'alpha_desc') {
            $qb->orderBy('co.titre', 'DESC');
        } else {
            $qb->orderBy('co.dateCreation', 'DESC');
        }

        return $this->render('module/show.html.twig', [
            'module' => $module,
            'cours' => $qb->getQuery()->getResult(),
            'sort_cours' => $sortCours,
            'favoris_ids' => $favorisIds,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_module_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Module $module, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ModuleType::class, $module, [
            'lock_categorie' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_module_show', ['id' => $module->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('module/edit.html.twig', [
            'module' => $module,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_module_delete', methods: ['POST'])]
    public function delete(Request $request, Module $module, EntityManagerInterface $entityManager): Response
    {
        $categorieId = $module->getCategorie() ? $module->getCategorie()->getId() : null;
        $referer = $request->headers->get('referer');

        if ($this->isCsrfTokenValid('delete'.$module->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($module);
            $entityManager->flush();
        }

        if ($referer) {
            return $this->redirect($referer);
        }

        if ($categorieId) {
            return $this->redirectToRoute('app_categorie_show', ['id' => $categorieId], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_module_index', [], Response::HTTP_SEE_OTHER);
    }
}
