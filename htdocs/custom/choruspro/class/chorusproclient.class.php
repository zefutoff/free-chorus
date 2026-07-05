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
 * \brief      API client skeleton for Chorus Pro.
 */

/**
 * Centralizes future Chorus Pro API communication.
 *
 * This class intentionally contains no HTTP implementation yet. Future API
 * calls must be added here only, so pages, hooks and cron scripts never call
 * the network directly.
 */
class ChorusProClient
{
    /**
     * Database handler, kept for future configuration or logging needs.
     *
     * @var DoliDB|null
     */
    protected $db;

    /**
     * Constructor.
     *
     * @param DoliDB|null $db Database handler
     */
    public function __construct($db = null)
    {
        $this->db = $db;
    }

    /**
     * Authenticate against the Chorus Pro API provider.
     *
     * @return string Access token
     */
    public function authenticate()
    {
        throw new BadMethodCallException('Chorus Pro authentication is not implemented yet.');
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
}
