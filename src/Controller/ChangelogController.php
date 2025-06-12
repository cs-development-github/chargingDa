<?php

namespace App\Controller;

use App\Service\GithubService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChangelogController extends AbstractController
{
    #[Route('/changelog', name: 'app_changelog')]
    public function index(GithubService $githubService): Response
    {
        $commits = $githubService->getCommitsFromMainBranch();

        return $this->render('changelog/index.html.twig', [
            'commits' => $commits,
        ]);
    }
}
