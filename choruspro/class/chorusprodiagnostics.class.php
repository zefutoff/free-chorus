<?php

/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/class/chorusprodiagnostics.class.php
 * \ingroup    choruspro
 * \brief      Diagnostics service for the Chorus Pro module.
 */

/**
 * Runs technical diagnostics for the Chorus Pro module.
 *
 * The class returns structured arrays only and intentionally contains no HTML.
 * New diagnostics should be added as independent public methods so the report
 * can grow with future API, cron and persistence features.
 */
class ChorusProDiagnostics
{
    public const STATE_SUCCESS = 'SUCCESS';
    public const STATE_WARNING = 'WARNING';
    public const STATE_ERROR = 'ERROR';

    public const CATEGORY_GENERAL = 'general';
    public const CATEGORY_CONFIGURATION = 'configuration';
    public const CATEGORY_SECURITY = 'security';
    public const CATEGORY_PERMISSIONS = 'permissions';
    public const CATEGORY_INFRASTRUCTURE = 'infrastructure';
    public const CATEGORY_OAUTH = 'oauth';
    public const CATEGORY_STRUCTURES = 'structures';

    /**
     * Database handler.
     *
     * @var DoliDB|null
     */
    protected $db;

    /**
     * Dolibarr configuration object.
     *
     * @var Conf|null
     */
    protected $conf;

    /**
     * Current Dolibarr user.
     *
     * @var User|null
     */
    protected $user;

    /**
     * Server variables used by security diagnostics.
     *
     * @var array
     */
    protected $server;

    /**
     * Constructor.
     *
     * @param DoliDB|null $db     Database handler
     * @param Conf|null   $conf   Dolibarr configuration object
     * @param User|null   $user   Current user
     * @param array       $server Server variables
     */
    public function __construct($db = null, $conf = null, $user = null, array $server = array())
    {
        $this->db = $db;
        $this->conf = $conf;
        $this->user = $user;
        $this->server = $server;
    }

    /**
     * Run all diagnostics, with live API tests optional.
     *
     * @param bool   $includeOAuth   Whether the OAuth live authentication test must run
     * @param string $structureSiret SIRET used for the Structure search test
     * @return array Diagnostics grouped by category
     */
    public function runAll($includeOAuth = false, $structureSiret = '')
    {
        $results = array(
            self::CATEGORY_GENERAL => array(
                $this->testPhpVersion(),
                $this->testDolibarrVersion(),
                $this->testModuleVersion(),
                $this->testCurlExtension(),
                $this->testOpenSslExtension(),
                $this->testJsonExtension(),
                $this->testMbstringExtension(),
                $this->testXmlExtension(),
            ),
            self::CATEGORY_CONFIGURATION => array(
                $this->testEnvironment(),
                $this->testClientIdConfigured(),
                $this->testClientSecretConfigured(),
                $this->testTechnicalAccountConfigured(),
                $this->testTechnicalPasswordConfigured(),
                $this->testEmitterSiretConfigured(),
            ),
            self::CATEGORY_SECURITY => array(
                $this->testHttpsAvailable(),
                $this->testCaCertificatesAccessible(),
                $this->testCurlTlsAvailable(),
            ),
            self::CATEGORY_PERMISSIONS => array(
                $this->testReadPermission(),
                $this->testSendPermission(),
                $this->testAdminPermission(),
                $this->testLogsPermission(),
            ),
            self::CATEGORY_INFRASTRUCTURE => array(
                $this->testTempDirectory(),
                $this->testSqlDirectory(),
                $this->testLangDirectory(),
                $this->testLogWriting(),
            ),
            self::CATEGORY_OAUTH => array(),
            self::CATEGORY_STRUCTURES => array(),
        );

        if ($includeOAuth) {
            $results[self::CATEGORY_OAUTH][] = $this->testOAuthAuthentication();
        } else {
            $results[self::CATEGORY_OAUTH][] = $this->buildResult(
                self::CATEGORY_OAUTH,
                'OAuthAuthentication',
                'Authentication OAuth PISTE',
                'Teste uniquement ChorusProClient::authenticate().',
                self::STATE_WARNING,
                'Test non exécuté. Utilisez le bouton dédié pour lancer un appel PISTE.'
            );
        }

        if ($structureSiret !== '') {
            $results[self::CATEGORY_STRUCTURES][] = $this->testStructureSearch($structureSiret);
        } else {
            $results[self::CATEGORY_STRUCTURES][] = $this->buildResult(
                self::CATEGORY_STRUCTURES,
                'StructureSearch',
                'Recherche structure par SIRET',
                'Appelle uniquement l’API Structures via ChorusProClient::searchStructureBySiret().',
                self::STATE_WARNING,
                'Test non exécuté. Saisissez un SIRET et utilisez le bouton dédié.'
            );
        }

        return $results;
    }

    /**
     * Test the PHP runtime version.
     *
     * @return array Diagnostic result
     */
    public function testPhpVersion()
    {
        $state = version_compare(PHP_VERSION, '8.0.0', '>=') ? self::STATE_SUCCESS : self::STATE_ERROR;

        return $this->buildResult(
            self::CATEGORY_GENERAL,
            'PhpVersion',
            'Version PHP',
            'Vérifie que PHP est compatible avec le module.',
            $state,
            'PHP ' . PHP_VERSION
        );
    }

    /**
     * Test the Dolibarr version constant.
     *
     * @return array Diagnostic result
     */
    public function testDolibarrVersion()
    {
        if (!defined('DOL_VERSION')) {
            return $this->buildResult(self::CATEGORY_GENERAL, 'DolibarrVersion', 'Version Dolibarr', 'Vérifie la version de Dolibarr.', self::STATE_WARNING, 'Version Dolibarr inconnue.');
        }

        return $this->buildResult(self::CATEGORY_GENERAL, 'DolibarrVersion', 'Version Dolibarr', 'Vérifie la version de Dolibarr.', self::STATE_SUCCESS, 'Dolibarr ' . DOL_VERSION);
    }

    /**
     * Test the module version information.
     *
     * @return array Diagnostic result
     */
    public function testModuleVersion()
    {
        return $this->buildResult(self::CATEGORY_GENERAL, 'ModuleVersion', 'Version du module', 'Affiche la version déclarée du module.', self::STATE_SUCCESS, 'Version de développement');
    }

    /**
     * Test if the cURL extension is loaded.
     *
     * @return array Diagnostic result
     */
    public function testCurlExtension()
    {
        return $this->testExtension('CurlExtension', 'Extension cURL', 'Vérifie que PHP cURL est disponible.', 'curl');
    }

    /**
     * Test if the OpenSSL extension is loaded.
     *
     * @return array Diagnostic result
     */
    public function testOpenSslExtension()
    {
        return $this->testExtension('OpenSslExtension', 'Extension OpenSSL', 'Vérifie que PHP OpenSSL est disponible.', 'openssl');
    }

    /**
     * Test if the JSON extension is loaded.
     *
     * @return array Diagnostic result
     */
    public function testJsonExtension()
    {
        return $this->testExtension('JsonExtension', 'Extension JSON', 'Vérifie que PHP JSON est disponible.', 'json');
    }

    /**
     * Test if the mbstring extension is loaded.
     *
     * @return array Diagnostic result
     */
    public function testMbstringExtension()
    {
        return $this->testExtension('MbstringExtension', 'Extension mbstring', 'Vérifie que PHP mbstring est disponible.', 'mbstring');
    }

    /**
     * Test if the XML extension is loaded.
     *
     * @return array Diagnostic result
     */
    public function testXmlExtension()
    {
        return $this->testExtension('XmlExtension', 'Extension XML', 'Vérifie que PHP XML est disponible.', 'xml');
    }

    /**
     * Test the configured Chorus Pro environment.
     *
     * @return array Diagnostic result
     */
    public function testEnvironment()
    {
        $environment = $this->getGlobalValue('CHORUSPRO_ENV');
        if ($environment === 'sandbox' || $environment === 'production') {
            return $this->buildResult(self::CATEGORY_CONFIGURATION, 'Environment', 'Environnement', 'Vérifie l’environnement Chorus Pro.', self::STATE_SUCCESS, $environment);
        }

        return $this->buildResult(self::CATEGORY_CONFIGURATION, 'Environment', 'Environnement', 'Vérifie l’environnement Chorus Pro.', self::STATE_ERROR, 'Environnement absent ou invalide.');
    }

    /**
     * Test if the PISTE Client ID is configured.
     *
     * @return array Diagnostic result
     */
    public function testClientIdConfigured()
    {
        return $this->testConfiguredValue('ClientIdConfigured', 'Client ID configuré', 'Vérifie que CHORUSPRO_CLIENT_ID est renseigné.', 'CHORUSPRO_CLIENT_ID', false);
    }

    /**
     * Test if the PISTE Client Secret is configured.
     *
     * @return array Diagnostic result
     */
    public function testClientSecretConfigured()
    {
        return $this->testConfiguredValue('ClientSecretConfigured', 'Client Secret configuré', 'Vérifie que CHORUSPRO_CLIENT_SECRET est renseigné.', 'CHORUSPRO_CLIENT_SECRET', true);
    }

    /**
     * Test if the Chorus Pro technical account is configured.
     *
     * @return array Diagnostic result
     */
    public function testTechnicalAccountConfigured()
    {
        return $this->testConfiguredValue('TechnicalAccountConfigured', 'Compte technique configuré', 'Vérifie que CHORUSPRO_TECHNICAL_ACCOUNT est renseigné.', 'CHORUSPRO_TECHNICAL_ACCOUNT', true);
    }

    /**
     * Test if the Chorus Pro technical password is configured.
     *
     * @return array Diagnostic result
     */
    public function testTechnicalPasswordConfigured()
    {
        return $this->testConfiguredValue('TechnicalPasswordConfigured', 'Mot de passe configuré', 'Vérifie que CHORUSPRO_TECHNICAL_PASSWORD est renseigné.', 'CHORUSPRO_TECHNICAL_PASSWORD', true);
    }

    /**
     * Test if the emitter SIRET is configured.
     *
     * @return array Diagnostic result
     */
    public function testEmitterSiretConfigured()
    {
        return $this->testConfiguredValue('EmitterSiretConfigured', 'SIRET configuré', 'Vérifie que CHORUSPRO_EMITTER_SIRET est renseigné.', 'CHORUSPRO_EMITTER_SIRET', false);
    }

    /**
     * Test if HTTPS is available for the current request.
     *
     * @return array Diagnostic result
     */
    public function testHttpsAvailable()
    {
        $https = !empty($this->server['HTTPS']) && strtolower((string) $this->server['HTTPS']) !== 'off';
        $forwardedProto = !empty($this->server['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $this->server['HTTP_X_FORWARDED_PROTO']) === 'https';
        if ($https || $forwardedProto) {
            return $this->buildResult(self::CATEGORY_SECURITY, 'HttpsAvailable', 'HTTPS disponible', 'Vérifie que l’administration est servie en HTTPS.', self::STATE_SUCCESS, 'HTTPS détecté.');
        }

        return $this->buildResult(self::CATEGORY_SECURITY, 'HttpsAvailable', 'HTTPS disponible', 'Vérifie que l’administration est servie en HTTPS.', self::STATE_WARNING, 'HTTPS non détecté pour cette requête.');
    }

    /**
     * Test if CA certificates are readable.
     *
     * @return array Diagnostic result
     */
    public function testCaCertificatesAccessible()
    {
        $paths = array_filter(array(
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/etc/ssl/cert.pem',
        ));

        foreach ($paths as $path) {
            if (is_readable($path)) {
                return $this->buildResult(self::CATEGORY_SECURITY, 'CaCertificatesAccessible', 'Certificats CA accessibles', 'Vérifie qu’un magasin CA est lisible.', self::STATE_SUCCESS, $path);
            }
        }

        return $this->buildResult(self::CATEGORY_SECURITY, 'CaCertificatesAccessible', 'Certificats CA accessibles', 'Vérifie qu’un magasin CA est lisible.', self::STATE_ERROR, 'Aucun fichier CA lisible détecté.');
    }

    /**
     * Test if cURL has TLS support.
     *
     * @return array Diagnostic result
     */
    public function testCurlTlsAvailable()
    {
        if (!function_exists('curl_version')) {
            return $this->buildResult(self::CATEGORY_SECURITY, 'CurlTlsAvailable', 'cURL avec TLS disponible', 'Vérifie le support TLS de cURL.', self::STATE_ERROR, 'cURL indisponible.');
        }

        $version = curl_version();
        if (!empty($version['ssl_version'])) {
            return $this->buildResult(self::CATEGORY_SECURITY, 'CurlTlsAvailable', 'cURL avec TLS disponible', 'Vérifie le support TLS de cURL.', self::STATE_SUCCESS, $version['ssl_version']);
        }

        return $this->buildResult(self::CATEGORY_SECURITY, 'CurlTlsAvailable', 'cURL avec TLS disponible', 'Vérifie le support TLS de cURL.', self::STATE_ERROR, 'Support TLS cURL non détecté.');
    }

    /**
     * Test the read permission.
     *
     * @return array Diagnostic result
     */
    public function testReadPermission()
    {
        return $this->testPermission('ReadPermission', 'Droit lecture', 'Vérifie choruspro->read.', 'read');
    }

    /**
     * Test the send permission.
     *
     * @return array Diagnostic result
     */
    public function testSendPermission()
    {
        return $this->testPermission('SendPermission', 'Droit envoi', 'Vérifie choruspro->send.', 'send');
    }

    /**
     * Test the admin permission.
     *
     * @return array Diagnostic result
     */
    public function testAdminPermission()
    {
        return $this->testPermission('AdminPermission', 'Droit administration', 'Vérifie choruspro->admin.', 'admin');
    }

    /**
     * Test the logs permission.
     *
     * @return array Diagnostic result
     */
    public function testLogsPermission()
    {
        return $this->testPermission('LogsPermission', 'Droit logs', 'Vérifie choruspro->logs.', 'logs');
    }

    /**
     * Test the module temporary directory.
     *
     * @return array Diagnostic result
     */
    public function testTempDirectory()
    {
        $path = $this->getDolDataRoot() . '/choruspro/temp';
        if (is_dir($path) && is_writable($path)) {
            return $this->buildResult(self::CATEGORY_INFRASTRUCTURE, 'TempDirectory', 'Répertoire temporaire', 'Vérifie le répertoire temporaire du module.', self::STATE_SUCCESS, $path);
        }

        if (is_dir($path)) {
            return $this->buildResult(self::CATEGORY_INFRASTRUCTURE, 'TempDirectory', 'Répertoire temporaire', 'Vérifie le répertoire temporaire du module.', self::STATE_WARNING, 'Répertoire non inscriptible : ' . $path);
        }

        return $this->buildResult(self::CATEGORY_INFRASTRUCTURE, 'TempDirectory', 'Répertoire temporaire', 'Vérifie le répertoire temporaire du module.', self::STATE_WARNING, 'Répertoire absent : ' . $path);
    }

    /**
     * Test the module SQL directory.
     *
     * @return array Diagnostic result
     */
    public function testSqlDirectory()
    {
        return $this->testReadableDirectory('SqlDirectory', 'Répertoire SQL', 'Vérifie le répertoire SQL du module.', $this->getModuleRoot() . '/sql');
    }

    /**
     * Test the module language directory.
     *
     * @return array Diagnostic result
     */
    public function testLangDirectory()
    {
        return $this->testReadableDirectory('LangDirectory', 'Répertoire langues', 'Vérifie le répertoire de langues du module.', $this->getModuleRoot() . '/langs');
    }

    /**
     * Test if Dolibarr logging is available.
     *
     * @return array Diagnostic result
     */
    public function testLogWriting()
    {
        if (function_exists('dol_syslog')) {
            return $this->buildResult(self::CATEGORY_INFRASTRUCTURE, 'LogWriting', 'Écriture des logs', 'Vérifie que la fonction de log Dolibarr est disponible.', self::STATE_SUCCESS, 'dol_syslog disponible.');
        }

        return $this->buildResult(self::CATEGORY_INFRASTRUCTURE, 'LogWriting', 'Écriture des logs', 'Vérifie que la fonction de log Dolibarr est disponible.', self::STATE_WARNING, 'dol_syslog indisponible dans ce contexte.');
    }

    /**
     * Test OAuth authentication against PISTE.
     *
     * @return array Diagnostic result
     */
    public function testOAuthAuthentication()
    {
        $startedAt = microtime(true);
        $client = new ChorusProClient($this->db);
        $context = $client->getOAuthDiagnosticContext();

        try {
            $client->authenticate();
            $context = $client->getOAuthDiagnosticContext();
            $safeDetail = $this->formatOAuthDiagnosticContext($context);
            $responseTime = (int) round((microtime(true) - $startedAt) * 1000);
            $expiresIn = $client->getAccessTokenExpiresIn();

            return $this->buildResult(
                self::CATEGORY_OAUTH,
                'OAuthAuthentication',
                'Authentification OAuth PISTE',
                'Appelle uniquement ChorusProClient::authenticate().',
                self::STATE_SUCCESS,
                $safeDetail . ' Token obtenu. Temps de réponse : ' . $responseTime . ' ms. Expiration : ' . $expiresIn . ' secondes.',
                array_merge($context, array('response_time_ms' => $responseTime, 'expires_in' => $expiresIn))
            );
        } catch (Throwable $exception) {
            $context = $client->getOAuthDiagnosticContext();
            $safeDetail = $this->formatOAuthDiagnosticContext($context);
            $responseTime = (int) round((microtime(true) - $startedAt) * 1000);

            return $this->buildResult(
                self::CATEGORY_OAUTH,
                'OAuthAuthentication',
                'Authentification OAuth PISTE',
                'Appelle uniquement ChorusProClient::authenticate().',
                self::STATE_ERROR,
                $safeDetail . $this->sanitizeErrorMessage($exception->getMessage()) . ' Temps de réponse : ' . $responseTime . ' ms.',
                array_merge($context, array('response_time_ms' => $responseTime))
            );
        }
    }

    /**
     * Test Chorus Pro Structure search by SIRET.
     *
     * @param string $siret SIRET to search
     * @return array Diagnostic result
     */
    public function testStructureSearch($siret)
    {
        $startedAt = microtime(true);
        $client = new ChorusProClient($this->db);
        $context = $client->getStructureSearchDiagnosticContext();

        try {
            $result = $client->searchStructureBySiret($siret);
            $context = $client->getStructureSearchDiagnosticContext();
            $responseTime = (int) round((microtime(true) - $startedAt) * 1000);
            $safeDetail = $this->formatStructureDiagnosticContext($context);
            $state = !empty($result['found']) ? self::STATE_SUCCESS : self::STATE_WARNING;
            $foundDetail = !empty($result['found']) ? 'Structure trouvée.' : 'Structure non trouvée.';
            $structureId = empty($result['structure_id']) ? 'non disponible' : $this->sanitizeErrorMessage((string) $result['structure_id']);
            $businessName = empty($result['business_name']) ? 'non disponible' : $this->sanitizeErrorMessage((string) $result['business_name']);

            return $this->buildResult(
                self::CATEGORY_STRUCTURES,
                'StructureSearch',
                'Recherche structure par SIRET',
                'Appelle uniquement l’API Structures via ChorusProClient::searchStructureBySiret().',
                $state,
                $safeDetail . $foundDetail . ' Identifiant structure : ' . $structureId
                    . '. Raison sociale : ' . $businessName . '. Temps de réponse : ' . $responseTime . ' ms.',
                array_merge($context, array(
                    'response_time_ms' => $responseTime,
                    'found' => !empty($result['found']),
                    'structure_id_available' => $structureId !== 'non disponible',
                    'business_name_available' => $businessName !== 'non disponible',
                ))
            );
        } catch (Throwable $exception) {
            $context = $client->getStructureSearchDiagnosticContext();
            $responseTime = (int) round((microtime(true) - $startedAt) * 1000);
            $safeDetail = $this->formatStructureDiagnosticContext($context);

            return $this->buildResult(
                self::CATEGORY_STRUCTURES,
                'StructureSearch',
                'Recherche structure par SIRET',
                'Appelle uniquement l’API Structures via ChorusProClient::searchStructureBySiret().',
                self::STATE_ERROR,
                $safeDetail . $this->sanitizeErrorMessage($exception->getMessage())
                    . ' Temps de réponse : ' . $responseTime . ' ms.',
                array_merge($context, array('response_time_ms' => $responseTime))
            );
        }
    }

    /**
     * Test if a PHP extension is loaded.
     *
     * @param string $key         Diagnostic key
     * @param string $name        Diagnostic name
     * @param string $description Diagnostic description
     * @param string $extension   PHP extension name
     * @return array Diagnostic result
     */
    protected function testExtension($key, $name, $description, $extension)
    {
        if (extension_loaded($extension)) {
            return $this->buildResult(self::CATEGORY_GENERAL, $key, $name, $description, self::STATE_SUCCESS, $extension . ' disponible.');
        }

        return $this->buildResult(self::CATEGORY_GENERAL, $key, $name, $description, self::STATE_ERROR, $extension . ' indisponible.');
    }

    /**
     * Test if a configuration value exists without exposing it.
     *
     * @param string $key         Diagnostic key
     * @param string $name        Diagnostic name
     * @param string $description Diagnostic description
     * @param string $constant    Dolibarr constant name
     * @param bool   $secret      Whether the value is sensitive
     * @return array Diagnostic result
     */
    protected function testConfiguredValue($key, $name, $description, $constant, $secret)
    {
        $value = $this->getGlobalValue($constant);
        if ($value !== '') {
            $detail = $secret ? 'Configuré : ********' : 'Configuré.';

            return $this->buildResult(self::CATEGORY_CONFIGURATION, $key, $name, $description, self::STATE_SUCCESS, $detail);
        }

        return $this->buildResult(self::CATEGORY_CONFIGURATION, $key, $name, $description, self::STATE_ERROR, $constant . ' absent.');
    }

    /**
     * Test if the current user has a module permission.
     *
     * @param string $key         Diagnostic key
     * @param string $name        Diagnostic name
     * @param string $description Diagnostic description
     * @param string $permission  Permission name
     * @return array Diagnostic result
     */
    protected function testPermission($key, $name, $description, $permission)
    {
        if (!is_object($this->user)) {
            return $this->buildResult(self::CATEGORY_PERMISSIONS, $key, $name, $description, self::STATE_WARNING, 'Utilisateur indisponible.');
        }

        if (!empty($this->user->admin) || !empty($this->user->rights->choruspro->{$permission})) {
            return $this->buildResult(self::CATEGORY_PERMISSIONS, $key, $name, $description, self::STATE_SUCCESS, 'Droit disponible.');
        }

        return $this->buildResult(self::CATEGORY_PERMISSIONS, $key, $name, $description, self::STATE_WARNING, 'Droit absent pour l’utilisateur courant.');
    }

    /**
     * Test if a directory exists and is readable.
     *
     * @param string $key         Diagnostic key
     * @param string $name        Diagnostic name
     * @param string $description Diagnostic description
     * @param string $path        Directory path
     * @return array Diagnostic result
     */
    protected function testReadableDirectory($key, $name, $description, $path)
    {
        if (is_dir($path) && is_readable($path)) {
            return $this->buildResult(self::CATEGORY_INFRASTRUCTURE, $key, $name, $description, self::STATE_SUCCESS, $path);
        }

        return $this->buildResult(self::CATEGORY_INFRASTRUCTURE, $key, $name, $description, self::STATE_ERROR, 'Répertoire absent ou illisible : ' . $path);
    }

    /**
     * Read a Dolibarr global constant without exposing undefined properties.
     *
     * @param string $name Constant name
     * @return string Constant value
     */
    protected function getGlobalValue($name)
    {
        if (is_object($this->conf) && isset($this->conf->global) && isset($this->conf->global->{$name})) {
            return (string) $this->conf->global->{$name};
        }

        return '';
    }

    /**
     * Return the Dolibarr document root.
     *
     * @return string Document root
     */
    protected function getDolDocumentRoot()
    {
        return defined('DOL_DOCUMENT_ROOT') ? DOL_DOCUMENT_ROOT : dirname(__DIR__, 4);
    }

    /**
     * Return the Dolibarr data root.
     *
     * @return string Data root
     */
    protected function getDolDataRoot()
    {
        return defined('DOL_DATA_ROOT') ? DOL_DATA_ROOT : $this->getDolDocumentRoot() . '/documents';
    }

    /**
     * Return the module root directory.
     *
     * @return string Module root
     */
    protected function getModuleRoot()
    {
        return $this->getDolDocumentRoot() . '/custom/choruspro';
    }

    /**
     * Sanitize an error message before displaying it to an administrator.
     *
     * @param string $message Raw error message
     * @return string Sanitized error message
     */
    protected function sanitizeErrorMessage($message)
    {
        if ($message === '') {
            return 'Erreur OAuth inconnue.';
        }

        $patterns = array(
            '/CHORUSPRO_CLIENT_SECRET[^ ]*/',
            '/client_secret=[^&\s]*/',
            '/access_token=[^&\s]*/',
            '/refresh_token=[^&\s]*/',
            '/authorization:\s*bearer\s+[^\s]+/i',
            '/authorization:\s*basic\s+[^\s]+/i',
        );

        return preg_replace($patterns, '********', $message);
    }

    /**
     * Format safe OAuth diagnostic metadata for display.
     *
     * @param array $context Safe OAuth context
     * @return string Human-readable detail
     */
    protected function formatOAuthDiagnosticContext(array $context)
    {
        $clientId = !empty($context['client_id_configured']) ? 'présent' : 'absent';
        $clientSecret = !empty($context['client_secret_configured']) ? 'présent' : 'absent';

        return 'Endpoint OAuth : ' . $context['endpoint']
            . '. Environnement : ' . $context['environment']
            . '. Méthode HTTP : ' . $context['method']
            . '. Client ID : ' . $clientId
            . '. Client Secret : ' . $clientSecret
            . '. Scope : ' . $context['scope']
            . '. Content-Type : ' . $context['content_type']
            . '. Code HTTP : ' . $this->formatOptionalHttpCode($context)
            . '. Body erreur PISTE : ' . $this->formatOptionalErrorBody($context) . '. ';
    }

    /**
     * Format safe Structure search diagnostic metadata for display.
     *
     * @param array $context Safe Structure search context
     * @return string Human-readable detail
     */
    protected function formatStructureDiagnosticContext(array $context)
    {
        $token = !empty($context['oauth_token_present']) ? 'présent' : 'absent avant appel';
        $technicalAccount = !empty($context['technical_account_configured']) ? 'présent' : 'absent';
        $technicalPassword = !empty($context['technical_password_configured']) ? 'présent' : 'absent';
        $authorizationHeader = !empty($context['authorization_header_present']) ? 'présent' : 'absent';
        $cproAccountHeader = !empty($context['cpro_account_header_present']) ? 'présent' : 'absent';

        return 'Endpoint Structures : ' . $context['endpoint']
            . '. Environnement : ' . $context['environment']
            . '. Méthode HTTP : ' . $context['method']
            . '. Content-Type : ' . $context['content_type']
            . '. Accept : ' . $context['accept']
            . '. Token OAuth en mémoire : ' . $token
            . '. Compte technique : ' . $technicalAccount
            . '. Mot de passe technique : ' . $technicalPassword
            . '. Header Authorization : ' . $authorizationHeader
            . '. Header cpro-account : ' . $cproAccountHeader
            . '. Format cpro-account : ' . $context['cpro_account_format']
            . '. Longueur login technique : ' . (int) $context['technical_account_length']
            . '. Longueur mot de passe technique : ' . (int) $context['technical_password_length']
            . '. Code HTTP : ' . $this->formatOptionalHttpCode($context)
            . '. Body erreur Chorus Pro : ' . $this->formatOptionalErrorBody($context) . '. ';
    }

    /**
     * Format an optional HTTP status code.
     *
     * @param array $context Safe OAuth context
     * @return string Human-readable HTTP code
     */
    protected function formatOptionalHttpCode(array $context)
    {
        return empty($context['http_code']) ? 'non disponible' : (string) $context['http_code'];
    }

    /**
     * Format an optional sanitized PISTE error body.
     *
     * @param array $context Safe OAuth context
     * @return string Human-readable body
     */
    protected function formatOptionalErrorBody(array $context)
    {
        return empty($context['error_body']) ? 'non disponible' : $context['error_body'];
    }

    /**
     * Build a structured diagnostic result.
     *
     * @param string $category    Diagnostic category
     * @param string $key         Stable diagnostic key
     * @param string $name        Human-readable name
     * @param string $description Human-readable description
     * @param string $state       Diagnostic state
     * @param string $detail      Human-readable detail
     * @param array  $meta        Optional machine-readable metadata
     * @return array Diagnostic result
     */
    protected function buildResult($category, $key, $name, $description, $state, $detail, array $meta = array())
    {
        return array(
            'category' => $category,
            'key' => $key,
            'name' => $name,
            'description' => $description,
            'state' => $state,
            'detail' => $detail,
            'meta' => $meta,
        );
    }
}
