<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Article;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/commentaire')]
class CommentaireController extends AbstractController
{
    #[Route('/', name: 'app_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    
        
        #[Route('/new/{id_article}', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
        public function new(Request $request, EntityManagerInterface $entityManager, int $id_article): Response
        {
            $article = $entityManager->getRepository(Article::class)->find($id_article);

            $commentaire = new Commentaire();
            $commentaire->setArticle($article);

            $form = $this->createForm(CommentaireType::class, $commentaire);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if (!$this->security->getUser()) {
                    throw $this->createAccessDeniedException('Vous devez être connecté pour commenter.');
                }
    
                // Récupérer l'utilisateur connecté
                $user = $this->security->getUser();
                // Associer l'ID de l'utilisateur au commentaire
                $commentaire->setAuteur($user);
    
                $commentaire->setEtat("active");
                $commentaire->setDatePublication(new \DateTime());
                $entityManager->persist($commentaire);
                $entityManager->flush();

                $this->addFlash('success', 'Commentaire est creer avec succes');
    
                return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('commentaire/new.html.twig', [
                'commentaire' => $commentaire,
                'form' => $form->createView(),
            ]);
        }

    #[Route('/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire est modifier avec succes');

            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire est supprimer avec succes');
        }

        return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
