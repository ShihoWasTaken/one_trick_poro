<?php

namespace AppBundle\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Translator;

class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("Page not found");
        } else {
            $result = array('success' => true, 'error' => false);
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("Page not found");
        } else {
            $result = array(
                'success' => false,
                'function' => 'onAuthenticationFailure',
                'error' => true,
                'message' => $this->translator->trans($exception->getMessage(), array(), 'FOSUserBundle')
            );
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}