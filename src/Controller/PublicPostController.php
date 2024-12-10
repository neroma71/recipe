<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicPostController extends AbstractController
{
    #[Route('/public/post', name: 'app_public_post', methods: ['GET'])]
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
     
        $postsQuery = $postRepository->findByIdDesc();

        $posts = $paginator->paginate(
            $postsQuery,
            $request->query->getInt('page', 1),
           5
        );

        return $this->render('public_post/index.html.twig', [
            'posts' => $posts, // Passer les posts à la vue
        ]);
    }
    #[Route('/public/post/{id}', name: 'app_public_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('public_post/show.html.twig', [
            'post' => $post,
        ]);
    }
    
}