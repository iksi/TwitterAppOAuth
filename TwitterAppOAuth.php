<?php

/**
 * TwitterAppOAuth
 *
 * Application-only authentication for Twitter
 * (https://dev.twitter.com/oauth/application-only)
 *
 * @author     Iksi <info@iksi.cc>
 * @copyright  (c) 2014-2015 Iksi
 * @license    MIT
 */

namespace Iksi;

class TwitterAppOAuth
{
    protected $consumerKey;
    protected $consumerSecret;

    public function __construct($consumerKey, $consumerSecret, $cache)
    {
        session_start();

        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
    }

    protected function request($url, $header)
    {
        $context = stream_context_create($header);
        $response = file_get_contents($url, false, $context);

        return json_decode($response);
    }

    protected function getBearerTokenCredentials()
    {
        // Encode consumer key and secret
        return base64_encode(rawurlencode($this->consumerKey)
            . ':' . rawurlencode($this->consumerSecret));
    }

    protected function getBearerToken()
    {
        if ( ! isset($_SESSION['bearerToken']))
        {
            $url = 'https://api.twitter.com/oauth2/token';
    
            $header = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded;'
                        . 'charset=UTF-8' . PHP_EOL . 'Authorization: Basic '
                        . $this->getBearerTokenCredentials() . PHP_EOL,
                    'content' => 'grant_type=client_credentials'
                ),
            );
    
            $response = $this->request($url, $header);

            $_SESSION['bearerToken'] = $response->access_token;
        }

        return $_SESSION['bearerToken'];
    }

    protected function invalidateBearerToken()
    {
        $url = 'https://api.twitter.com/oauth2/invalidate_token';

        $header = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded;'
                    . 'charset=UTF-8' . PHP_EOL . 'Authorization: Basic '
                    . $this->getBearerTokenCredentials() . PHP_EOL
                    . 'Accept: */*' . PHP_EOL,
                'content' => 'access_token=' . $this->getBearerToken()
            ),
        );

        unset($_SESSION['bearerToken']);

        return $this->request($url, $header);
    }

    public function get($resource, $arguments)
    {
        $url = 'https://api.twitter.com/1.1/' . $resource . '.json?'
            . http_build_query($arguments);

        $header = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . $this->getBearerToken(),
            ),
        );

        return $this->request($url, $header);
    }
}
