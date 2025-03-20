<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private string $adminEmail;
    private Environment $twig;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        #[Autowire('%env(MAILER_FROM)%')] string $adminEmail
    ) {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->twig = $twig;
    }

    public function sendEmail(string $to, string $subject, string $template, array $context): void
    {
        $htmlContent = $this->twig->render($template, $context);

        $email = (new Email())
            ->from('nepasrepondrelodmi@lodmi.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    public function sendEmailWithAttachment(string $to, string $subject, string $htmlTemplate, string $pdfPath)
    {
        $email = (new Email())
            ->from('nepasrepondre@lodmi.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlTemplate)
            ->attachFromPath($pdfPath, 'Contrat_Client.pdf');

        $this->mailer->send($email);
    }
}
