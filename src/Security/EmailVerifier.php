<?php

namespace App\Security;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\User;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $router
    ) {}

    public function sendEmailConfirmation(string $verifyRouteName, User $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $email = (new TemplatedEmail())
            ->from(new Address('nepasrepondre@lodmi.com'))
            ->to($user->getEmail())
            ->subject('Confirmez vôtre adresse email')
            ->htmlTemplate('emails/confirmation_email.html.twig')
            ->context([
                'signedUrl' => $signatureComponents->getSignedUrl(),
                'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }

    public function sendWelcomeEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('nepasrepondre@lodmi.com'))
            ->to($user->getEmail())
            ->subject('Bienvenue ! Votre compte est activé ✅')
            ->htmlTemplate('emails/welcome_email.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }

}