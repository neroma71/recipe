<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipeRepository $recipeRepository, PostRepository $postRepository ): Response
    {
        $latestPosts = $postRepository->findBy([], ['id' => 'DESC'], 3);
        $latestRecipes = $recipeRepository->findPublicRecipe(3);
        
        return $this->render('home/index.html.twig', [
            'home' => 'HomeController',
            'recipes' =>  $latestRecipes,
            'latestPosts' => $latestPosts,
        ]);
    }
}
