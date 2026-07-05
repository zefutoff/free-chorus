<?php

/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/class/chorusprolog.class.php
 * \ingroup    choruspro
 * \brief      Log persistence skeleton for Chorus Pro.
 */

/**
 * Encapsulates future Chorus Pro technical and functional log persistence.
 *
 * Secrets must be masked before any future data reaches this class.
 */
class ChorusProLog
{
    public const LEVEL_INFO = 'INFO';
    public const LEVEL_WARNING = 'WARNING';
    public const LEVEL_ERROR = 'ERROR';

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
    public $tableElement = 'choruspro_log';

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
     * Add an informational log entry.
     *
     * @param string $message Log message without secrets
     * @param array  $context Optional structured context
     * @return int Created row identifier
     */
    public function addInfo($message, array $context = array())
    {
        throw new BadMethodCallException('Chorus Pro info logging is not implemented yet.');
    }

    /**
     * Add a warning log entry.
     *
     * @param string $message Log message without secrets
     * @param array  $context Optional structured context
     * @return int Created row identifier
     */
    public function addWarning($message, array $context = array())
    {
        throw new BadMethodCallException('Chorus Pro warning logging is not implemented yet.');
    }

    /**
     * Add an error log entry.
     *
     * @param string $message Log message without secrets
     * @param array  $context Optional structured context
     * @return int Created row identifier
     */
    public function addError($message, array $context = array())
    {
        throw new BadMethodCallException('Chorus Pro error logging is not implemented yet.');
    }
}
