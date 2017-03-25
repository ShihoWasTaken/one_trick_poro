<?php

namespace AppBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;

class InterceptorListener
{
    const DEFAULT_REGION = 'euw';
    protected $twig;
    protected $container;

    public function __construct(\Twig_Environment $twig, ContainerInterface $container)
    {
        $this->twig = $twig;
        $this->container = $container;
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
        $this->twig->addGlobal('static_data_version', $this->container->getParameter('static_data_version'));
    }
}