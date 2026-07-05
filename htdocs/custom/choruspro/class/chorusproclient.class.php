<?php

/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/class/chorusproclient.class.php
 * \ingroup    choruspro
 * \brief      API client for Chorus Pro authentication.
 */

/**
 * Centralizes Chorus Pro API communication.
 *
 * Only OAuth 2.0 authentication against PISTE is implemented at this stage.
 * Other Chorus Pro endpoints remain intentionally unavailable until their
 * dedicated development phases.
 */
class ChorusProClient
{
    public const ENV_SANDBOX = 'sandbox';
    public const ENV_PRODUCTION = 'production';
    public const DEFAULT_SCOPE = 'openid';
    public const TOKEN_EXPIRATION_LEEWAY = 60;
    public const HTTP_TIMEOUT = 30;
    public const SANDBOX_TOKEN_URL = 'https://sandbox-oauth.piste.gouv.fr/api/oauth/token';
    public const PRODUCTION_TOKEN_URL = 'https://oauth.piste.gouv.fr/api/oauth/token';

    /**
     * Database handler, kept for future configuration or logging needs.
     *
     * @var DoliDB|null
     */
    protected $db;

    /**
     * Injectable HTTP client used to make authentication testable.
     *
     * @var callable|null
     */
    protected $httpClient;

    /**
     * Optional configuration override used by tests.
     *
     * @var array
     */
    protected $config;

    /**
     * Cached OAuth access token for the current PHP execution.
     *
     * @var string
     */
    protected $accessToken = '';

    /**
     * Unix timestamp at which the cached access token must be considered expired.
     *
     * @var int
     */
    protected $accessTokenExpiresAt = 0;

    /**
     * Constructor.
     *
     * @param DoliDB|null   $db         Database handler
     * @param callable|null $httpClient Optional HTTP client mock
     * @param array         $config     Optional configuration override
     */
    public function __construct($db = null, $httpClient = null, array $config = array())
    {
        $this->db = $db;
        $this->httpClient = $httpClient;
        $this->config = $config;
    }

    /**
     * Authenticate against PISTE and return a cached OAuth access token.
     *
     * @param bool $forceRefresh Force renewal even when a cached token is valid
     * @return string Access token
     */
    public function authenticate($forceRefresh = false)
    {
        if (!$forceRefresh && $this->hasValidAccessToken()) {
            return $this->accessToken;
        }

        $clientId = $this->getRequiredConfigValue('CHORUSPRO_CLIENT_ID');
        $clientSecret = $this->getRequiredConfigValue('CHORUSPRO_CLIENT_SECRET');
        $tokenUrl = $this->getTokenUrl();

        $response = $this->sendTokenRequest($tokenUrl, array(
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => self::DEFAULT_SCOPE,
        ));

        $this->storeTokenResponse($response);

        return $this->accessToken;
    }

    /**
     * Force renewal of the cached OAuth access token.
     *
     * @return string New access token
     */
    public function forceTokenRenewal()
    {
        $this->clearAccessToken();

        return $this->authenticate(true);
    }

    /**
     * Execute a low-level Chorus Pro API call.
     *
     * @param string $method   HTTP method expected by the future endpoint
     * @param string $endpoint Endpoint path or identifier
     * @param array  $payload  Request payload
     * @param array  $headers  Request headers
     * @return array Decoded response data
     */
    public function callApi($method, $endpoint, array $payload = array(), array $headers = array())
    {
        throw new BadMethodCallException('Chorus Pro API calls are not implemented yet.');
    }

    /**
     * Search a Chorus Pro structure by SIRET.
     *
     * @param string $siret Recipient SIRET
     * @return array Matching structures
     */
    public function searchStructureBySiret($siret)
    {
        throw new BadMethodCallException('Chorus Pro structure search is not implemented yet.');
    }

    /**
     * Retrieve services attached to a Chorus Pro structure.
     *
     * @param string $structureId Chorus Pro structure identifier
     * @return array Available services
     */
    public function getServicesByStructure($structureId)
    {
        throw new BadMethodCallException('Chorus Pro service retrieval is not implemented yet.');
    }

    /**
     * Upload an invoice PDF to Chorus Pro.
     *
     * @param string $filePath Absolute path to the PDF file
     * @return array Upload result
     */
    public function uploadInvoicePdf($filePath)
    {
        throw new BadMethodCallException('Chorus Pro PDF upload is not implemented yet.');
    }

    /**
     * Submit a prepared invoice payload to Chorus Pro.
     *
     * @param array $payload Prepared invoice payload
     * @return array Submission result
     */
    public function submitInvoice(array $payload)
    {
        throw new BadMethodCallException('Chorus Pro invoice submission is not implemented yet.');
    }

    /**
     * Retrieve a Chorus Pro invoice status.
     *
     * @param string $chorusInvoiceId Chorus Pro invoice identifier
     * @return array Status result
     */
    public function getInvoiceStatus($chorusInvoiceId)
    {
        throw new BadMethodCallException('Chorus Pro status retrieval is not implemented yet.');
    }

    /**
     * Return whether the in-memory token can still be reused.
     *
     * @return bool True when the cached token is still valid
     */
    protected function hasValidAccessToken()
    {
        return $this->accessToken !== '' && $this->accessTokenExpiresAt > time();
    }

    /**
     * Clear the in-memory OAuth token cache.
     *
     * @return void
     */
    protected function clearAccessToken()
    {
        $this->accessToken = '';
        $this->accessTokenExpiresAt = 0;
    }

    /**
     * Resolve the PISTE OAuth token endpoint from configuration.
     *
     * @return string OAuth token endpoint URL
     */
    protected function getTokenUrl()
    {
        $configuredTokenUrl = $this->getConfigValue('CHORUSPRO_OAUTH_TOKEN_URL');
        if ($configuredTokenUrl !== '') {
            return $configuredTokenUrl;
        }

        if ($this->getEnvironment() === self::ENV_PRODUCTION) {
            return self::PRODUCTION_TOKEN_URL;
        }

        return self::SANDBOX_TOKEN_URL;
    }

    /**
     * Resolve the configured Chorus Pro environment.
     *
     * @return string Environment name
     */
    protected function getEnvironment()
    {
        $environment = $this->getConfigValue('CHORUSPRO_ENV');

        return $environment === self::ENV_PRODUCTION ? self::ENV_PRODUCTION : self::ENV_SANDBOX;
    }

    /**
     * Read a required configuration value.
     *
     * @param string $name Constant name
     * @return string Configuration value
     */
    protected function getRequiredConfigValue($name)
    {
        $value = $this->getConfigValue($name);
        if ($value === '') {
            throw new RuntimeException($name . ' is required for PISTE OAuth authentication.');
        }

        return $value;
    }

    /**
     * Read a configuration value from test overrides or Dolibarr globals.
     *
     * @param string $name Constant name
     * @return string Configuration value
     */
    protected function getConfigValue($name)
    {
        if (array_key_exists($name, $this->config)) {
            return (string) $this->config[$name];
        }

        global $conf;

        if (is_object($conf) && isset($conf->global) && isset($conf->global->{$name})) {
            return (string) $conf->global->{$name};
        }

        return '';
    }

    /**
     * Send the OAuth token request through the configured HTTP client.
     *
     * @param string $tokenUrl OAuth token endpoint URL
     * @param array  $payload  Form payload
     * @return array Decoded token response
     */
    protected function sendTokenRequest($tokenUrl, array $payload)
    {
        $httpClient = $this->httpClient;
        if (!is_callable($httpClient)) {
            $httpClient = array($this, 'executeHttpRequest');
        }

        $response = call_user_func($httpClient, 'POST', $tokenUrl, array(
            'Content-Type: application/x-www-form-urlencoded',
        ), http_build_query($payload, '', '&'));

        return $this->decodeTokenResponse($response);
    }

    /**
     * Execute an HTTP request using PHP cURL.
     *
     * @param string $method  HTTP method
     * @param string $url     Target URL
     * @param array  $headers HTTP headers
     * @param string $body    Request body
     * @return array HTTP response with http_code and body keys
     */
    protected function executeHttpRequest($method, $url, array $headers = array(), $body = '')
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is required for PISTE OAuth authentication.');
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => self::HTTP_TIMEOUT,
            CURLOPT_TIMEOUT => self::HTTP_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));

        $responseBody = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($responseBody === false) {
            throw new RuntimeException('PISTE OAuth request failed: ' . $curlError);
        }

        return array(
            'http_code' => $httpCode,
            'body' => (string) $responseBody,
        );
    }

    /**
     * Decode and validate the OAuth token HTTP response.
     *
     * @param array $response HTTP response returned by the HTTP client
     * @return array Decoded token response
     */
    protected function decodeTokenResponse(array $response)
    {
        $httpCode = empty($response['http_code']) ? 0 : (int) $response['http_code'];
        $body = isset($response['body']) ? (string) $response['body'] : '';

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('PISTE OAuth authentication failed with HTTP status ' . $httpCode . '.');
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('PISTE OAuth authentication returned an invalid JSON response.');
        }

        return $decoded;
    }

    /**
     * Store token data in memory with an expiration timestamp.
     *
     * @param array $response Decoded OAuth token response
     * @return void
     */
    protected function storeTokenResponse(array $response)
    {
        if (empty($response['access_token'])) {
            throw new RuntimeException('PISTE OAuth response does not contain an access_token.');
        }

        $expiresIn = empty($response['expires_in']) ? 0 : (int) $response['expires_in'];
        if ($expiresIn <= self::TOKEN_EXPIRATION_LEEWAY) {
            throw new RuntimeException('PISTE OAuth response does not contain a usable expires_in value.');
        }

        $this->accessToken = (string) $response['access_token'];
        $this->accessTokenExpiresAt = time() + $expiresIn - self::TOKEN_EXPIRATION_LEEWAY;
    }
}
