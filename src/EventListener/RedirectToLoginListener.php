<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class RedirectToLoginListener
{
    private Security $security;
    private RouterInterface $router;

    public function __construct(Security $security, RouterInterface $router)
    {
        $this->security = $security;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        $publicRoutes = ['app_login', 'app_register', 'app_reset_password', 'logout'];

        if (!in_array($route, $publicRoutes) && !$this->security->getUser()) {
            $event->setResponse(new RedirectResponse($this->router->generate('app_login')));
        }
    }
}
?>