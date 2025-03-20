<?php

namespace App\Service;

use App\Entity\Client;
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
    public function sendClientCompletionEmail(Client $client, string $completionUrl): void
    {
        $email = (new Email())
            ->from('nepasrepondre@lodmi.com')
            ->to($client->getEmail() ?: 'chris.vermersch@hotmail.com')
            ->subject('Demande d\'information complémentaire')
            ->html($this->twig->render('emails/request_document.html.twig', [
                'client' => $client,
                'completionUrl' => $completionUrl,
            ]));

        $this->mailer->send($email);
    }

    /**
     * Envoie un email au support pour informer d'une nouvelle demande.
     */
    public function sendSupportNotification(Client $client, string $completionUrl): void
    {
        $email = (new Email())
            ->from('nepasrepondre@lodmi.com')
            ->to('contact@lodmi.com')
            ->subject('Nouvelle demande de supervision')
            ->html($this->twig->render('emails/lodmi_contract.html.twig', [
                'client' => $client,
                'completionUrl' => $completionUrl,
            ]));

        $this->mailer->send($email);
    }

    /**
     * Envoie un email à l'installateur pour confirmation.
     */
    public function sendInstallerConfirmation(Client $client, string $completionUrl): void
    {
        $email = (new Email())
            ->from('nepasrepondre@lodmi.com')
            ->to('chris.vermersch@hotmail.com')
            ->subject('Confirmation de demande de supervision')
            ->html($this->twig->render('emails/confirmation_installator.html.twig', [
                'client' => $client,
                'completionUrl' => $completionUrl,
            ]));

        $this->mailer->send($email);
    }
}

