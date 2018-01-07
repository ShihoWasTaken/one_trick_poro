<?php

namespace AppBundle\EventListener;

use AppBundle\Controller\AbstractAjaxController;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class InterceptorListener
{
    const DEFAULT_REGION = 'euw';
    protected $logger;
    protected $env;
    protected $twig;
    protected $static_data_version;

    public function __construct(Logger $logger, $env, \Twig_Environment $twig, $static_data_version)
    {
        $this->logger = $logger;
        $this->env = $env;
        $this->twig = $twig;
        $this->static_data_version = $static_data_version;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }
        // Si c'est un controller de type Ajax
        if ($controller[0] instanceof AbstractAjaxController) {
            // Si on est pas en dev et que c'est une requête non Ajax
            if ($this->env !== 'dev' && !$event->getRequest()->isXmlHttpRequest()) {
                $this->logger->error("Quelqu'un a tenté d'utiliser une requête non AJAX en prod");
                throw new Exception("Non AJAX Request");
            }
        }
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