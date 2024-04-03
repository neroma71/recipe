<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/utilisateur/edition/{id}', name: 'app_user', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        // Vérifier si l'utilisateur est connecté et autorisé à modifier le profil
        if(!$this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }
        if($this->getUser() !== $user)
        {
            return $this->redirectToRoute('app_home');
        }

        // Créer le formulaire de modification du profil
        $form = $this->createForm(UserType::class, $user);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            // Récupérer le mot de passe saisi dans le formulaire
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword !== null) {
                // Hacher le mot de passe saisi
                $hashedPassword = $hasher->hashPassword($user, $plainPassword);
        
                // Mettre à jour le mot de passe de l'utilisateur
                $user->setPassword($hashedPassword);
            }

            // Enregistrer les modifications en base de données
            $manager->persist($user);
            $manager->flush();

            // Rediriger l'utilisateur vers une autre page après la modification
            $this->addFlash(
                'success',
                'Les informations ont été modifiés avec succès'
            );
            return $this->redirectToRoute('app_recipe');
        }

        // Afficher le formulaire de modification du profil
        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}