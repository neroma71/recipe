<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
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
            $repository->findBy([], ['createdAt' => 'DESC']),
            $request->query->getInt('page', 1), 
            10 
        );
        
        return $this->render('ingredient/index.html.twig', [
            'ingredients' => $ingredients,
           
        ]);
    }
    /**
     * ajout d'ingredient
     *
     * @param Request $request
     * @param SluggerInterface $slugger
     * @param EntityManagerInterface $manager
     * @return Response
     */
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

            $this->addFlash(
                'success',
                'L\'ingrédient a été ajouté avec succès !'
            );
            
            return $this->redirectToRoute('app_ingredient');
        }
       

        return $this->render('ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/ingredient/edition/{id}', 'ingredient.edit', methods: ['GET', 'POST'])]
    public function edit(Ingredient $ingredient, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Vôtre ingrédient a été modifié avec succes !'
            );

            return $this->redirectToRoute('app_ingredient');
        }

        return $this->render('ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/ingredient/supression/{id}', 'ingredient.delete', methods: ['GET'])]
    public function delete(EntityManagerInterface $manager, Ingredient $ingredient): Response
    {
        if(!$ingredient){
            $this->addFlash(
                'success',
                'L\'ingrédient n\'a pas été trouvé !'
            );
            return $this->redirectToRoute('app_ingredient');
        }

        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash(
            'success',
            'Vôtre ingrédient a été supprimé avec succès !'
        );

        return $this->redirectToRoute('app_ingredient');
    }
}
