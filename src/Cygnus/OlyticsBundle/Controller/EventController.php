<?php

namespace Cygnus\OlyticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{
    public function indexAction($vertical, $product)
    {
        // Get the incoming request
        $request = $this->get('request');

        // Handle OPTIONS method for CORS
        if ($request->getMethod() == 'OPTIONS') {
            return $this->handleResponse($request);
        }

        // Handle bots
        $botDetector = $this->get('cygnus_olytics.bot_detector');
        $ua = $request->headers->get('USER_AGENT');
        if ($botDetector->hasMatch($ua)) {
            // Do logging or storage of bot data here

            // Return response
            $responseBody = array('created' => false, 'reason' => 'robot');
            return $this->handleResponse($request, 202, $responseBody);
        }

        // Load the website event request manager
        $requestManager = $this->get('cygnus_olytics.events.website.request_manager');
        try {
            // Create and manage event from the HTTP request
            $requestManager->createAndManage($request, $vertical, $product);
            // Persist to the DB
            $requestManager->persist();

            $responseBody = array('created' => true);
            $responseCode = 201;
        } catch (Exception $e) {
            $responseBody = array('created' => false, 'reason'  => 'exception');
            $responseCode = 500;
        }
        // Return response
        return $this->handleResponse($request, $responseCode, $responseBody);
        
        
    }

    public function handleResponse(Request $request, $responseCode = 200, array $responseBody = array())
    {
        // Set the response skeleton
        $globalHeaders = array(
            'Expires'       => 'Sat, 01 Jan 2000 01:01:01 GMT',
            'Cache-Control' => 'private, no-cache, no-cache=Set-Cookie, max-age=0, s-maxage=0',
            'Pragma'        => 'no-cache',
        );
        $response = new Response('', $responseCode, $globalHeaders);

        switch ($request->getMethod()) {
            case 'GET':
                if ($request->query->has('callback')) {
                    // JSONP
                    extract($this->buildJsonpResponse($responseBody, $request->query->get('callback')));
                } else {
                    // Send image beacon
                    extract($this->buildImageResponse());
                }
                break;
            case 'POST':
                // Send JSON response for POST requests
                extract($this->buildJsonResponse($responseBody));
                break;
            case 'OPTIONS':
                // Send CORS response
                extract($this->buildCorsResponse());
                break;
            default:
                // Send image beacon
                extract($this->buildImageResponse());
                break;
        }

        $response->headers->add($headers);
        $response->setContent($content);
        return $response;
    }



    public function buildJsonpResponse(array $responseBody, $callback)
    {
        $content = $callback . '(' . @json_encode($responseBody) . ')';
        return array(
            'content'   => $content,
            'headers'   => array(
                'Content-Type'  => 'application/json',
                'Content-Length'=> strlen($content),
            ),
        );
    }

    public function buildJsonResponse(array $responseBody)
    {
        $content = @json_encode($responseBody);
        return array(
            'content'   => $content,
            'headers'   => array(
                'Content-Type'                  => 'application/json',
                'Content-Length'                => strlen($content),
                'Access-Control-Allow-Origin'   => '*',
            ),
        );
    }

    public function buildCorsResponse()
    {
        // @todo Ensure the request origin header matches a known domain
        return array(
            'content'   => '',
            'headers'   => array(
                'Access-Control-Allow-Origin'   => '*',
                'Access-Control-Allow-Methods'  => $this->getAccessControlAllowMethods(),
                'Access-Control-Allow-Headers'  => $this->getAccessControlAllowHeaders(),
                // 'Access-Control-Max-Age'        => 60*60*24*30 // One month, in seconds
            ),
        );
    }

    public function getAccessControlAllowMethods()
    {
        return 'POST, OPTIONS';
    }

    public function getAccessControlAllowHeaders()
    {
        return 'origin, content-type, accept, user-agent';
    }

    public function buildImageResponse()
    {
        $content = sprintf(
            '%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%',
            71, 73,70,56,57,97,1,0,1,0,128,255,0,192,192,192,0,0,0,33,249,4,1,0,0,0,0,44,0,0,0,0,1,0,1,0,0,2,2,68,1,0,59
        );
        return array(
            'content'   => $content,
            'headers'   => array(
                'Content-Type'      => 'image/gif',
                'Content-Length'    => strlen($content),
            ),
        );
    }
}