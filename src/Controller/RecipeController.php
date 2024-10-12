<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RecipeController extends AbstractController
{
    #[Route('/recette', name: 'app_recipe', methods: ['GET'])]
    public function index(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recette/creation', 'recipe.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été créée avec succès !'
            );
            return $this->redirectToRoute('app_recipe');
        }

        return $this->render('recipe/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/recette/edition/{id}', 'recipe.edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $manager, Security $security): Response
    {
        if (!$security->isGranted('ROLE_USER') || $recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été modifiée avec succès !'
            );

            return $this->redirectToRoute('app_recipe');
        }

        return $this->render('recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/recette/supression/{id}', 'recipe.delete', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function delete(EntityManagerInterface $manager, Recipe $recipe, Security $security): Response
    {
        if (!$security->isGranted('ROLE_USER') || $recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre recette a été supprimée avec succès !'
        );

        return $this->redirectToRoute('app_recipe');
    }
    #[Route('/recette/publique/', 'recipe.index.publique', methods: ['GET'] )]
public function indexPublic(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
{
    $recipes = $paginator->paginate(
        $repository->findPublicRecipe(null),
        $request->query->getInt('page', 1),
        10
    );
    return $this->render('recipe/index_public.html.twig', [
        'recipes' => $recipes
    ]);
  
}

    #[Route('/recette/{id}', name: 'recipe_show', methods: ['GET'])]
public function show(Recipe $recipe): Response
{
    // Autoriser l'accès aux recettes publiques même pour les utilisateurs non authentifiés
    if ($recipe->isIsPublic()) {
        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }

    // Vérifiez si l'utilisateur est authentifié
    if (!$this->isGranted('ROLE_USER') || $recipe->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette recette.');
    }

    return $this->render('recipe/show.html.twig', [
        'recipe' => $recipe
    ]);
}

}
