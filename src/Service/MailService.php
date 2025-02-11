<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MailService
{
    private MailerInterface $mailer;
    private string $adminEmail;

    public function __construct(
        MailerInterface $mailer,
        #[Autowire('%env(MAILER_FROM)%')] string $adminEmail
    ) {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    public function sendWelcomeEmail(string $toEmail, string $name): void
    {
        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to($toEmail)
            ->subject('Bienvenue chez nous !')
            ->htmlTemplate('emails/welcome_email.html.twig')
            ->context(['name' => $name]);

        $this->mailer->send($email);
    }
}
