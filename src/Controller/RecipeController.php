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
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
   /**
    * This controller display all recipes
    *
    * @param RecipeRepository $repository
    * @param PaginatorInterface $paginator
    * @param Request $request
    * @return Response
    */
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
    /**
     * This controller create recipes
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $manager): Response
    {

        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Vôtre recette a été créé avec succès !'
            );

            return $this->redirectToRoute('app_recipe');

        }

        return $this->render('recipe/new.html.twig',[
            'form' => $form->createView(),
        ]);
    }
     /**
     * This controller edit recipes
     *
     * @param Recipe $recipe
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/edition/{id}', 'recipe.edit', methods: ['GET', 'POST'])]
   
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $manager, Security $security): Response
    {

        if (!$security->isGranted('ROLE_USER') || $recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Vôtre recette a été modifié avec succès !'
            );

            return $this->redirectToRoute('app_recipe');
        }

        return $this->render('recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * This controller delete recipes
     *
     * @param EntityManagerInterface $manger
     * @param Recipe $recipe
     * @return Response
     */
    #[Route('/recette/supression/{id}', 'recipe.delete', methods: ['GET'])]

    public function delete(EntityManagerInterface $manger, Recipe $recipe, Security $security):Response
    {

        if (!$security->isGranted('ROLE_USER') || $recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $manger->remove($recipe);
        $manger->flush();

        $this->addFlash(
            'success',
            'Vôtre recette a été supprimé avec succès !'
        );

        return $this->redirectToRoute('app_recipe');
    }
}
