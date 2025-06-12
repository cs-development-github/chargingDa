<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use IntlDateFormatter;
use Locale;

class GithubService
{
    private string $repo = 'cs-development-github/chargingDa';
    private string $branch = 'dev'; 

    public function __construct(
        private HttpClientInterface $client,
        private string $githubToken,
    ) {}

    public function getCommitsFromMainBranch(int $perPage = 100): array
    {
        $allCommits = [];
        $seenShas = [];
        $page = 1;

        Locale::setDefault('fr_FR');
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::LONG,
            IntlDateFormatter::SHORT,
            'Europe/Paris',
            null,
            "d MMMM y 'à' HH:mm"
        );

        do {
            $commitsUrl = sprintf(
                'https://api.github.com/repos/%s/commits?sha=%s&per_page=%d&page=%d',
                $this->repo,
                $this->branch,
                $perPage,
                $page
            );

            $commitsResponse = $this->client->request('GET', $commitsUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->githubToken,
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Symfony-App',
                ],
            ]);

            $commits = $commitsResponse->toArray();

            if (empty($commits)) break;

            foreach ($commits as $commit) {
                $sha = $commit['sha'];
                $msg = $commit['commit']['message'];

                if (str_starts_with($msg, 'Merge pull request') || isset($seenShas[$sha])) {
                    continue;
                }

                $rawAuthor = $commit['commit']['author']['name'];
                $normalizedAuthor = $rawAuthor === 'cs-development-github' ? 'Chris' : $rawAuthor;

                $timestamp = strtotime($commit['commit']['author']['date']);
                $formattedDate = $formatter->format($timestamp);
                $dateRaw = date('Y-m-d', $timestamp);

                $allCommits[] = [
                    'sha' => $sha,
                    'message' => $msg,
                    'author' => $normalizedAuthor,
                    'date' => $formattedDate,
                    'dateRaw' => $dateRaw,
                    'branch' => $this->branch,
                ];

                $seenShas[$sha] = true;
            }

            $page++;
        } while (count($commits) === $perPage);

        usort($allCommits, fn($a, $b) => strtotime($b['dateRaw']) <=> strtotime($a['dateRaw']));

        return $allCommits;
    }
}
