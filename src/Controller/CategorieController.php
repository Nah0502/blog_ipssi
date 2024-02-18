<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\User;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function tois(EntityManagerInterface $em, Request $request): Response
    { 
        $categorieRepository = $em->getRepository(Categorie::class);

        // createQueryBuilder pour créer la requête
        $query = $categorieRepository->createQueryBuilder('categorie')
            ->orderBy('categorie.id', 'DESC') // on recupere categorie.id (decroissant)
            ->setMaxResults(3); // on en prends 3
        
        // recupere le resultat
        $troisDernieresCategories = $query->getQuery()->getResult();

        return $this->render('base.html.twig', [
            'categories' => $troisDernieresCategories,
        ]);
    }

    #[Route('/categorie', name: 'app_categorie_index', methods: ['GET'])]

    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }
    

    #[Route('categorie/new', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('categorie/{id}', name: 'app_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): Response
    {
        $articles= $categorie->getArticles();
        return $this->render('categorie/show.html.twig', [
            'articles' => $articles,
            'categorie' => $categorie,
        ]);
    }

    #[Route('categorie/{id}/edit', name: 'app_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('categorie/{id}', name: 'app_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
