<?php

/**
 * Application-only authentication for Twitter
 * (https://dev.twitter.com/oauth/application-only)
 *
 * @author     Iksi <info@iksi.cc>
 * @copyright  (c) 2014-2015 Iksi
 * @license    MIT
 */

namespace Iksi;

class TwitterOAuth
{
    protected $consumerKey;
    protected $consumerSecret;

    public function __construct($consumerKey, $consumerSecret)
    {
        session_start();

        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
    }

    protected function fetch($url, $header, $postFields = array())
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ( ! empty($postFields)) {
            $postFields = urldecode(http_build_query($postFields));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return ($responseCode === 200) ? json_decode($response) : false;
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
                'Authorization: Basic ' . $this->getBearerTokenCredentials(),
                'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
            );

            $postFields = array(
                'grant_type' => 'client_credentials'
            );

            $response = $this->fetch($url, $header, $postFields);

            $_SESSION['bearerToken'] = ($response !== false)
                ? $response->access_token
                : false;
        }

        return $_SESSION['bearerToken'];
    }

    protected function invalidateBearerToken()
    {
        $url = 'https://api.twitter.com/oauth2/invalidate_token';

        $header = array( 
            'Authorization: Basic ' . $this->getBearerTokenCredentials(),
            'Accept: */*',
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
        );

        $postFields = array(
            'access_token' => $this->getBearerToken()
        );

        unset($_SESSION['bearerToken']);

        return $this->fetch($url, $header, $postFields);
    }

    /**
     * Api request
     */
    public function request($resource, $arguments)
    {
        $url = 'https://api.twitter.com/1.1/' . $resource . '.json?'
            . http_build_query($arguments);

        $header = array(
            'Authorization: Bearer ' . $this->getBearerToken()
        );

        return $this->fetch($url, $header);
    }
}
