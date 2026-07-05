<?php

/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/class/chorusproinvoiceservice.class.php
 * \ingroup    choruspro
 * \brief      Business service skeleton for Chorus Pro invoice workflows.
 */

/**
 * Coordinates future invoice validation, payload preparation and status sync.
 *
 * This service must remain free of HTML and direct HTTP calls. Network access
 * will be delegated to ChorusProClient once the API integration phase starts.
 */
class ChorusProInvoiceService
{
    /**
     * Future Chorus Pro API client.
     *
     * @var ChorusProClient|null
     */
    protected $client;

    /**
     * Future submission repository.
     *
     * @var ChorusProSubmission|null
     */
    protected $submission;

    /**
     * Future technical logger.
     *
     * @var ChorusProLog|null
     */
    protected $log;

    /**
     * Constructor.
     *
     * @param ChorusProClient|null     $client     API client
     * @param ChorusProSubmission|null $submission Submission persistence helper
     * @param ChorusProLog|null        $log        Log persistence helper
     */
    public function __construct($client = null, $submission = null, $log = null)
    {
        $this->client = $client;
        $this->submission = $submission;
        $this->log = $log;
    }

    /**
     * Validate whether a Dolibarr invoice can be prepared for Chorus Pro.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return array Validation result
     */
    public function validateInvoice($invoice)
    {
        throw new BadMethodCallException('Chorus Pro invoice validation is not implemented yet.');
    }

    /**
     * Prepare the payload expected by the future Chorus Pro submission call.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return array Prepared payload
     */
    public function preparePayload($invoice)
    {
        throw new BadMethodCallException('Chorus Pro payload preparation is not implemented yet.');
    }

    /**
     * Submit an invoice through the future Chorus Pro workflow.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return array Submission result
     */
    public function submitInvoice($invoice)
    {
        throw new BadMethodCallException('Chorus Pro invoice submission workflow is not implemented yet.');
    }

    /**
     * Synchronize the local status of an already submitted invoice.
     *
     * @param ChorusProSubmission|array|object $submission Submission data or object
     * @return array Synchronization result
     */
    public function syncInvoiceStatus($submission)
    {
        throw new BadMethodCallException('Chorus Pro status synchronization is not implemented yet.');
    }
}
