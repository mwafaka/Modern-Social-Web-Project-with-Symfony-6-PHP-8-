<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\MicroPost;


use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class MicroPostVoter extends Voter
{

    public function __construct(private Security $security) {}
    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [MicroPost::EDIT, MicroPost::VIEW])
            && $subject instanceof \App\Entity\MicroPost;
    }

    /** 
     * @param MicroPost  $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        $isAuth = $user instanceof UserInterface;
        if ($this->security->isGranted('ROLE_ADMIN'))
            return true;
        
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case MicroPost::EDIT:
                return $isAuth 
                && (
                    ($subject->getAuthor()->getId() === $user->getId())||
                $this->security->isGranted('ROLE_EDITOR')
                );

            case MicroPost::VIEW:
                // logic to determine if the user can VIEW
                return true;
        }

        return false;
    }
}
