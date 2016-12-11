<?php

namespace AppBundle\Services\LoLAPI;

use AppBundle\Services\CurlHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class RequestService
{
    protected $container;
    protected $api_key;

    protected $responseCode;
    protected $headers;
    protected $response;
    protected $JSONResponseToArray;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->api_key = $this->container->getParameter('riot_api_key');
    }

    public function errorMessage($responseCode)
    {
        switch($responseCode)
        {
            case '400':
                $responseError = 'Bad request';
                break;
            case '401':
                $responseError = 'Unauthorized';
                break;
            case '403':
                $responseError = 'Forbidden';
                break;
            case '404':
                $responseError = 'Data not found';
                break;

            case '429':
                $responseError = 'Rate limit exceeded';
                break;

            case '500':
                $responseError = 'Internal server error';
                break;

            case '503':
                $responseError = 'Service unavailable';
                break;

            default:
                $responseError = 'No errors';
                break;
        }
        return $responseError;
    }

    public function request($url)
    {
        //Initiate cURL
        $ch = curl_init();

        //Setup some of our options.
        curl_setopt($ch, CURLOPT_URL, $url);

        //Tell curl to write the response to a variable
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Pour obtenir le header
        curl_setopt($ch, CURLOPT_HEADER, 1);

        // Options pour API Riot
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //Execute the cURL request.
        $response = curl_exec($ch);

        //Get the resulting HTTP status code from the cURL handle.
        $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if( ($this->responseCode != 200) && ($this->responseCode != 429) )
        {
            return array('errorCode' => $this->responseCode, 'error' => $this->errorMessage($this->responseCode));
        }

        while($this->responseCode == 429)
        {
            usleep(1000000);
            //Execute the cURL request.
            $response = curl_exec($ch);

            //Get the resulting HTTP status code from the cURL handle.
            $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }


        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->headers = substr($response, 0, $header_size);
        $this->response = substr($response, $header_size);

        $this->JSONResponseToArray = json_decode($this->response, true);

        //Close cURL handle
        curl_close($ch);
        return $this->JSONResponseToArray;
    }


    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param mixed $api_key
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * @return mixed
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param mixed $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getJSONResponseToArray()
    {
        return $this->JSONResponseToArray;
    }

    /**
     * @param mixed $JSONResponseToArray
     */
    public function setJSONResponseToArray($JSONResponseToArray)
    {
        $this->JSONResponseToArray = $JSONResponseToArray;
    }
}
