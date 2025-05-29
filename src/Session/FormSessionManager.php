<?php

namespace App\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FormSessionManager
{
    private const FORM_KEY = 'supervision_form';

    public function __construct(private SessionInterface $session) {}

    public function saveStepData(int $step, array $data): void
    {
        $all = $this->session->get(self::FORM_KEY, []);
        $all[$step] = $data;
        $this->session->set(self::FORM_KEY, $all);
    }

    public function getStepData(int $step): array
    {
        return $this->session->get(self::FORM_KEY, [])[$step] ?? [];
    }

    public function getAll(): array
    {
        return $this->session->get(self::FORM_KEY, []);
    }

    public function clear(): void
    {
        $this->session->remove(self::FORM_KEY);
    }
}