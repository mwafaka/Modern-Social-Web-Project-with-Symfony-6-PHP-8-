<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function show(Security $security): Response
    {
        $user = $security->getUser(); // Returns the logged-in User
    
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
    
        return $this->render('profile/show.html.twig', [
            'user' => $user,
        ]);
    }
}
    
