<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ClientMailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * Envoie un email au client pour compléter ses informations.
     */
    public function sendClientCompletionEmail(string $to, string $completionUrl): void
    {
        $email = (new Email())
            ->from('no-reply@tonsite.com')
            ->to($to)
            ->subject('Demande d\'information complémentaire')
            ->html($this->twig->render('emails/request_document.html.twig', [
                'completionUrl' => $completionUrl,
            ]));

        $this->mailer->send($email);
    }

    /**
     * Envoie un email au support pour informer d'une nouvelle demande.
     */
    public function sendSupportNotification(string $to, string $completionUrl): void
    {
        $email = (new Email())
            ->from('no-reply@tonsite.com')
            ->to($to)
            ->subject('Nouvelle demande de supervision')
            ->html($this->twig->render('emails/lodmi_contract.html.twig', [
                'completionUrl' => $completionUrl,
            ]));

        $this->mailer->send($email);
    }

    /**
     * Envoie un email à l'installateur pour confirmation.
     */
    public function sendInstallerConfirmation(string $to, string $completionUrl): void
    {
        $email = (new Email())
            ->from('no-reply@tonsite.com')
            ->to($to)
            ->subject('Confirmation de demande de supervision')
            ->html($this->twig->render('emails/confirmation_installator.html.twig', [
                'completionUrl' => $completionUrl,
            ]));

        $this->mailer->send($email);
    }
}