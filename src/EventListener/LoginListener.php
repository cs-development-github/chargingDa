<?php

namespace App\EventListener;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {}

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $this->logger->info('💥 LoginListener triggered !');

        $user = $event->getAuthenticationToken()->getUser();

        dd("Je suis call !");

        if (!$user instanceof User) {
            return;
        }

        if ($user->isVerified() && !$user->getEmailVerifiedAt()) {
            $this->emailVerifier->sendWelcomeEmail($user);
            $user->setEmailVerifiedAt(new \DateTimeImmutable());
            $this->em->flush();
        }
    }
}
