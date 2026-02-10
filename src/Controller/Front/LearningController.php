<?php

namespace App\Controller\Front;

use App\Entity\Categorie;
use App\Entity\Module;
use App\Entity\Cours;
use App\Repository\CategorieRepository;
use App\Repository\ModuleRepository;
use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/learning')]
class LearningController extends AbstractController
{
    #[Route('/categories', name: 'app_categorie_index')]
    public function categories(CategorieRepository $categorieRepository): Response
    {
        return $this->render('front/learning/categories.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/category/{id}', name: 'app_categorie_show')]
    public function category(Categorie $categorie): Response
    {
        return $this->render('front/learning/category_show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/module/{id}', name: 'app_module_show')]
    public function module(Module $module): Response
    {
        return $this->render('front/learning/module_show.html.twig', [
            'module' => $module,
        ]);
    }

    #[Route('/cours/{id}', name: 'app_cours_show')]
    public function cours(Cours $cours): Response
    {
        return $this->render('front/learning/cours_show.html.twig', [
            'cour' => $cours,
        ]);
    }
}
