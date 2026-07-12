<?php
/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/core/modules/modChorusPro.class.php
 * \ingroup    choruspro
 * \brief      Module descriptor for Chorus Pro integration.
 */

include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

/**
 * Module descriptor for the Chorus Pro external module.
 */
class modChorusPro extends DolibarrModules
{
    /**
     * Constructor.
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->numero = 500000;
        $this->rights_class = 'choruspro';
        $this->family = 'interface';
        $this->module_position = '90';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = 'ChorusProModuleDescription';
        $this->descriptionlong = 'ChorusProModuleDescriptionLong';
        $this->editor_name = 'Free Chorus contributors';
        $this->editor_url = '';
        $this->version = 'development';
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
        $this->picto = 'generic';

        $this->module_parts = array(
            'hooks' => array('invoicecard'),
            'triggers' => 0,
            'login' => 0,
            'substitutions' => 0,
            'menus' => 0,
            'tpl' => 0,
            'barcode' => 0,
            'models' => 0,
            'css' => array(),
            'js' => array(),
        );

        $this->dirs = array('/choruspro/temp');
        $this->config_page_url = array('setup.php@choruspro', 'diagnostics.php@choruspro');
        $this->langfiles = array('choruspro@choruspro');
        $this->depends = array();
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->phpmin = array(8, 0);
        $this->need_dolibarr_version = array(18, 0);
        $this->warnings_activation = array();
        $this->warnings_activation_ext = array();
        $this->const = array(
            array(0 => 'CHORUSPRO_ENV', 1 => 'chaine', 2 => 'sandbox', 3 => 'Chorus Pro environment', 4 => 0, 5 => 'current', 6 => 1),
            array(0 => 'CHORUSPRO_CLIENT_ID', 1 => 'chaine', 2 => '', 3 => 'PISTE client identifier', 4 => 0, 5 => 'current', 6 => 1),
            array(0 => 'CHORUSPRO_CLIENT_SECRET', 1 => 'chaine', 2 => '', 3 => 'PISTE client secret', 4 => 0, 5 => 'current', 6 => 1),
            array(0 => 'CHORUSPRO_TECHNICAL_ACCOUNT', 1 => 'chaine', 2 => '', 3 => 'Chorus Pro technical account', 4 => 0, 5 => 'current', 6 => 1),
            array(0 => 'CHORUSPRO_TECHNICAL_PASSWORD', 1 => 'chaine', 2 => '', 3 => 'Chorus Pro technical account password', 4 => 0, 5 => 'current', 6 => 1),
            array(0 => 'CHORUSPRO_EMITTER_SIRET', 1 => 'chaine', 2 => '', 3 => 'Emitter SIRET', 4 => 0, 5 => 'current', 6 => 1),
            array(0 => 'CHORUSPRO_DEBUG', 1 => 'yesno', 2 => '0', 3 => 'Enable Chorus Pro debug mode', 4 => 0, 5 => 'current', 6 => 1),
            array(0 => 'CHORUSPRO_LOG_LEVEL', 1 => 'chaine', 2 => 'INFO', 3 => 'Chorus Pro log level', 4 => 0, 5 => 'current', 6 => 1),
        );
        $this->tabs = array();
        $this->dictionaries = array();
        $this->boxes = array();
        $this->cronjobs = array();
        $this->rights = array();
        $r = 0;

        $this->rights[$r][0] = 500001;
        $this->rights[$r][1] = 'ChorusProPermissionRead';
        $this->rights[$r][4] = 'read';
        $this->rights[$r][5] = 'read';
        $r++;

        $this->rights[$r][0] = 500002;
        $this->rights[$r][1] = 'ChorusProPermissionSend';
        $this->rights[$r][4] = 'send';
        $this->rights[$r][5] = 'send';
        $r++;

        $this->rights[$r][0] = 500003;
        $this->rights[$r][1] = 'ChorusProPermissionAdmin';
        $this->rights[$r][4] = 'admin';
        $this->rights[$r][5] = 'admin';
        $r++;

        $this->rights[$r][0] = 500004;
        $this->rights[$r][1] = 'ChorusProPermissionLogs';
        $this->rights[$r][4] = 'logs';
        $this->rights[$r][5] = 'logs';

        $this->menus = array();
    }

    /**
     * Initialize module.
     *
     * @param string $options Initialization options
     * @return int 1 on success, <=0 on error
     */
    public function init($options = '')
    {
        $sql = array();

        $result = $this->_load_tables('/choruspro/sql/');
        if ($result < 0) {
            return -1;
        }

        $result = $this->addChorusProExtraFields();
        if ($result < 0) {
            return -1;
        }

        return $this->_init($sql, $options);
    }

    /**
     * Remove module.
     *
     * @param string $options Removal options
     * @return int 1 on success, <=0 on error
     */
    public function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }

    /**
     * Register Dolibarr extrafields used to prepare Chorus Pro invoices.
     *
     * @return int 1 on success, -1 on error
     */
    protected function addChorusProExtraFields()
    {
        if (!class_exists('ExtraFields')) {
            include_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
        }
        if (!class_exists('ExtraFields')) {
            return 1;
        }

        $extraFields = new ExtraFields($this->db);
        $fields = array(
            array('choruspro_structure_id', 'ChorusProStructureId', 'varchar', 100, 128, 'societe'),
            array('choruspro_code_service_default', 'ChorusProServiceCodeDefault', 'varchar', 110, 128, 'societe'),
            array('choruspro_code_service_required', 'ChorusProServiceCodeRequired', 'boolean', 120, '', 'societe'),
            array('choruspro_engagement_required', 'ChorusProLegalCommitmentRequired', 'boolean', 130, '', 'societe'),
            array('choruspro_engagement_default', 'ChorusProLegalCommitmentDefault', 'varchar', 140, 128, 'societe'),
            array('choruspro_code_service', 'ChorusProServiceCode', 'varchar', 100, 128, 'facture'),
            array('choruspro_engagement_juridique', 'ChorusProLegalCommitment', 'varchar', 110, 128, 'facture'),
        );

        foreach ($fields as $field) {
            $result = $extraFields->addExtraField($field[0], $field[1], $field[2], $field[3], $field[4], $field[5], 0, 0, '', array(), 1, '', 1, '', '', '', 'choruspro@choruspro');
            if ($result < 0) {
                return -1;
            }
        }

        return 1;
    }
}
