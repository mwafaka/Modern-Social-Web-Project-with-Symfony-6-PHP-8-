<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class SettingsProfileController extends AbstractController
{
    #[Route('/settings/profile', name: 'app_settings_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserRepository $users): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        // Check if the user is authenticated
        if (!$user) {
            // Redirect to the login page if the user is not authenticated
            return $this->redirectToRoute('app_login');
        }
        $userProfile = $user->getUserProfile() ?? new UserProfile();
        $form = $this->createForm(
            UserProfileType::class,
            $userProfile
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userProfile = $form->getData();
            $user->setUserProfile($userProfile);

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Your profile has been verified.');
            return $this->redirectToRoute('app_settings_profile');
        }



        return $this->render('settings_profile/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
