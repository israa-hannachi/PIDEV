<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Form\Admin\CategorieAdminType;
use App\Repository\CategorieRepository;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categories')]
final class CategorieAdminController extends AbstractController
{
    #[Route('', name: 'app_admin_categorie_index', methods: ['GET'])]
    public function index(Request $request, CategorieRepository $categorieRepository): Response
    {
        $sort = (string) $request->query->get('sort', 'date_desc');

        $qb = $categorieRepository->createQueryBuilder('c');
        if ($sort === 'alpha_asc') {
            $qb->orderBy('c.nom', 'ASC');
        } elseif ($sort === 'alpha_desc') {
            $qb->orderBy('c.nom', 'DESC');
        } else {
            $qb->orderBy('c.dateCreation', 'DESC');
        }

        return $this->render('admin/categorie/index.html.twig', [
            'categories' => $qb->getQuery()->getResult(),
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'app_admin_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieAdminType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_categorie_show', methods: ['GET'])]
    public function show(Request $request, Categorie $categorie, ModuleRepository $moduleRepository): Response
    {
        $sortModules = (string) $request->query->get('sort_modules', 'date_desc');

        $qb = $moduleRepository->createQueryBuilder('m')
            ->andWhere('m.categorie = :categorie')
            ->setParameter('categorie', $categorie);

        if ($sortModules === 'alpha_asc') {
            $qb->orderBy('m.titre', 'ASC');
        } elseif ($sortModules === 'alpha_desc') {
            $qb->orderBy('m.titre', 'DESC');
        } else {
            $qb->orderBy('m.dateCreation', 'DESC');
        }

        return $this->render('admin/categorie/show.html.twig', [
            'categorie' => $categorie,
            'modules' => $qb->getQuery()->getResult(),
            'sort_modules' => $sortModules,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieAdminType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
