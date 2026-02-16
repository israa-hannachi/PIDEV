<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Entity\Module;
use App\Form\Admin\ModuleAdminType;
use App\Repository\CoursRepository;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/modules')]
final class ModuleAdminController extends AbstractController
{
    #[Route('', name: 'app_admin_module_index', methods: ['GET'])]
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

        return $this->render('admin/module/index.html.twig', [
            'modules' => $qb->getQuery()->getResult(),
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'app_admin_module_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $module = new Module();

        $categorieId = $request->query->get('categorie');
        if ($categorieId) {
            $categorie = $entityManager->find(Categorie::class, $categorieId);
            if ($categorie) {
                $module->setCategorie($categorie);
            }
        }

        $form = $this->createForm(ModuleAdminType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $module->setCreeParAdmin(true);
            $entityManager->persist($module);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_module_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/module/new.html.twig', [
            'module' => $module,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_module_show', methods: ['GET'])]
    public function show(Request $request, ?Module $module, CoursRepository $coursRepository): Response
    {
        if ($module === null) {
            return $this->redirectToRoute('app_admin_module_index');
        }

        $sortCours = (string) $request->query->get('sort_cours', 'date_desc');

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

        return $this->render('admin/module/show.html.twig', [
            'module' => $module,
            'cours' => $qb->getQuery()->getResult(),
            'sort_cours' => $sortCours,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_module_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Module $module, EntityManagerInterface $entityManager): Response
    {
        if ($module === null) {
            return $this->redirectToRoute('app_admin_module_index');
        }

        $form = $this->createForm(ModuleAdminType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_module_show', ['id' => $module->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/module/edit.html.twig', [
            'module' => $module,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_module_delete', methods: ['POST'])]
    public function delete(Request $request, ?Module $module, EntityManagerInterface $entityManager): Response
    {
        if ($module === null) {
            return $this->redirectToRoute('app_admin_module_index');
        }

        if ($this->isCsrfTokenValid('delete'.$module->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($module);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_module_index', [], Response::HTTP_SEE_OTHER);
    }
}
