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
            'hooks' => array(),
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
        $this->config_page_url = array('setup.php@choruspro');
        $this->langfiles = array('choruspro@choruspro');
        $this->depends = array();
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->phpmin = array(8, 2);
        $this->need_dolibarr_version = array(18, 0);
        $this->warnings_activation = array();
        $this->warnings_activation_ext = array();
        $this->const = array();
        $this->tabs = array();
        $this->dictionaries = array();
        $this->boxes = array();
        $this->cronjobs = array();
        $this->rights = array();
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
}
