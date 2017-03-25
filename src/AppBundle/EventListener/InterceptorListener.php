<?php

namespace AppBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;

class InterceptorListener
{
    const DEFAULT_REGION = 'euw';
    protected $twig;
    protected $static_data_version;

    public function __construct(\Twig_Environment $twig, $static_data_version)
    {
        $this->twig = $twig;
        $this->static_data_version = $static_data_version;
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
        $this->twig->addGlobal('static_data_version', $this->static_data_version);
    }
}