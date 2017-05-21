<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class StaticController extends Controller
{
    public function homepageAction()
    {
        return $this->render('AppBundle:Static:homepage.html.twig');
    }

    public function notFoundAction()
    {
        throw new NotFoundHttpException("Page not found");
    }

    public function aboutAction()
    {
        return $this->render('AppBundle:Static:about.html.twig');
    }

    public function comingSoonAction()
    {
        return $this->render('AppBundle:Static:coming_soon.html.twig');
    }

    public function contactAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 2, 'max' => 20)))
            ))
            ->add('email', EmailType::class, array(
                'constraints' => array(
                    new NotBlank())
            ))
            ->add('message', TextareaType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 50, 'max' => 1000)))
            ))
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
        }

        return $this->render('AppBundle:Static:contact.html.twig', array('form' => $form->createView()));
    }

    public function searchbarAction(Request $request)
    {
        /**
         * @var \AppBundle\Services\LoLAPI\LoLAPIService
         */
        $api = $this->container->get('app.lolapi');

        /**
         * @var \AppBundle\Services\SummonerService
         */
        $sum = $this->container->get('app.lolsummoner');

        // On doit traiter le nom du summoner
        $originalName = $request->request->get('searchbar-summonerName', 'UTF-8');

        // Si rien n'est rentré dans l'input
        if (empty(trim($originalName))) {
            return $this->render('AppBundle:Summoner:not_existing_error.html.twig',
                array(
                    'name' => $originalName
                )
            );
        }
        $summonerName = $api->toSafeLowerCase($originalName);

        $region = $sum->getRegionBySlug($request->request->get('searchbar-region'));
        $summoner = $api->getSummonerByNames($region, array($summonerName));
        if ($api->getResponseCode() == 404) {
            $data = $sum->getSummonerByNameForAllRegions($summonerName);
            return $this->render('AppBundle:Summoner:not_existing.html.twig',
                array(
                    'region' => $request->request->get('searchbar-region'),
                    'name' => $originalName,
                    'data' => $data,
                    'region' => $region,
                    'formattedName' => $summonerName
                )
            );
        } else if ($api->getResponseCode() == 500) {
            return $this->render('AppBundle:Summoner:not_existing_error.html.twig',
                array(
                    'name' => $originalName
                )
            );
        }
        return $this->redirectToRoute('app_summoner',
            array(
                'region' => $request->request->get('searchbar-region'),
                'summonerId' => $summoner[$summonerName]['id']
            )
        );
    }
}
