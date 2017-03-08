<?php

namespace AppBundle\EventListener;

class InterceptorListener
{
    const DEFAULT_REGION = 'euw';
    protected $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $cookies = $request->cookies;
        if (!$cookies->has('favorite_region')) {
            $favoriteRegionCookie = self::DEFAULT_REGION;
        } else {
            $favoriteRegionCookie = $cookies->get('favorite_region');
        }
        $this->twig->addGlobal('favorite_region', $favoriteRegionCookie);
    }
}