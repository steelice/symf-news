<?php


namespace App\Security;


use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class ArticleVoter extends Voter
{
    public const IS_MY = 'is_my';

    protected function supports($attribute, $subject): bool
    {
        return ($subject instanceof Article) && ($attribute === self::IS_MY);
    }

    protected function voteOnAttribute($attribute, $article, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return $user === $article->getUser();
    }
}