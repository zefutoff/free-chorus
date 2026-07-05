<?php
/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/class/chorusprosubmission.class.php
 * \ingroup    choruspro
 * \brief      Submission persistence skeleton for Chorus Pro.
 */

/**
 * Encapsulates future persistence operations for Chorus Pro submissions.
 *
 * SQL access for submissions must be implemented here instead of being spread
 * across pages, hooks or scripts.
 */
class ChorusProSubmission
{
    /**
     * Database handler.
     *
     * @var DoliDB|null
     */
    protected $db;

    /**
     * Database table name without Dolibarr prefix substitution.
     *
     * @var string
     */
    public $tableElement = 'choruspro_submission';

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
     * Create a local submission record.
     *
     * @param array $data Submission data
     * @return int Created row identifier
     */
    public function create(array $data)
    {
        throw new BadMethodCallException('Chorus Pro submission creation is not implemented yet.');
    }

    /**
     * Fetch the local submission attached to a Dolibarr invoice.
     *
     * @param int $invoiceId Dolibarr invoice identifier
     * @return array|null Submission data or null when not found
     */
    public function fetchByInvoice($invoiceId)
    {
        throw new BadMethodCallException('Chorus Pro submission lookup is not implemented yet.');
    }

    /**
     * Update the local status of a submission.
     *
     * @param int    $submissionId Submission row identifier
     * @param string $status       Local status code
     * @param string $statusLabel  Human-readable status label
     * @return int Number of affected rows
     */
    public function updateStatus($submissionId, $status, $statusLabel = '')
    {
        throw new BadMethodCallException('Chorus Pro submission status update is not implemented yet.');
    }

    /**
     * Mark a submission as errored.
     *
     * @param int    $submissionId Submission row identifier
     * @param string $message      Error message safe for local storage
     * @return int Number of affected rows
     */
    public function markAsError($submissionId, $message)
    {
        throw new BadMethodCallException('Chorus Pro submission error handling is not implemented yet.');
    }
}
