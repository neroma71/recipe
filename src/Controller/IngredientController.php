<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class IngredientController extends AbstractController
{
    /**
     * This function display all ingredients
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/ingredient', name: 'app_ingredient', methods: ['GET'])]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1), 
            10 
        );
        
        return $this->render('ingredient/index.html.twig', [
            'ingredients' => $ingredients,
           
        ]);
    }
    #[Route('/ingredient/nouveau', 'ingredient.new', methods: ['GET', 'POST'])]
    public function new(Request $request,SluggerInterface $slugger, EntityManagerInterface $manager): Response
    {
        $ingredient = new Ingredient();
        $ingredient->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $imageFile = $form->get('images')->getData();

            // Vérifier si un fichier a été téléchargé
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
    
                // Sécuriser le nom du fichier
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                // Déplacer le fichier vers le dossier public/upload
                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/asset/upload',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'erreur si le déplacement du fichier échoue
                }
    
                // Mettre à jour la propriété 'images' de l'entité Ingredient avec le nom du fichier
                $ingredient->setImages($newFilename);
            }
    

            $manager->persist($ingredient);
            $manager->flush();
        }
       

        return $this->render('ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
