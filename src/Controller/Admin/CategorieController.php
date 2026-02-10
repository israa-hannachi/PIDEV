<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categorie')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'admin_categorie_index', methods: ['GET'])]
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
        ]);
    }

    #[Route('/new', name: 'admin_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('admin_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_categorie_show', methods: ['GET'])]
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

    #[Route('/{id}/edit', name: 'admin_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $referer = $request->headers->get('referer');
        $deletedId = $categorie->getId();

        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        if ($referer) {
            $refererPath = (string) (parse_url($referer, PHP_URL_PATH) ?? '');
            $deletedShowPath = $this->generateUrl('admin_categorie_show', ['id' => $deletedId]);
            if ($refererPath !== '' && $refererPath !== $deletedShowPath) {
                return $this->redirect($referer);
            }
        }

        return $this->redirectToRoute('admin_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
