<?php
/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/class/actions_choruspro.class.php
 * \ingroup    choruspro
 * \brief      Dolibarr hooks for Chorus Pro invoice preparation.
 */

require_once __DIR__ . '/chorusproinvoiceservice.class.php';

/**
 * Hook actions for the Chorus Pro module.
 */
class ActionsChorusPro
{
    /** @var string Hook output */
    public $resprints = '';

    /**
     * Display Chorus Pro preparation block on customer invoice cards.
     *
     * @param array         $parameters  Hook parameters
     * @param Facture|object $object      Current Dolibarr object
     * @param string        $action      Current action
     * @param HookManager   $hookmanager Hook manager
     * @return int 0 to keep standard hook processing
     */
    public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
        global $user, $langs;

        if (!$this->canDisplayInvoiceBlock($object, $user)) {
            return 0;
        }

        if (is_object($langs)) {
            $langs->loadLangs(array('choruspro@choruspro'));
        }

        $this->applyPostedPreparationValues($object, $action);

        $service = new ChorusProInvoiceService();
        $validation = $service->validateInvoice($object);
        $resolved = $validation['resolvedData'];
        $this->resprints = $this->renderPreparationBlock($object, $validation, $resolved);

        return 0;
    }

    /**
     * Return whether the preparation block may be displayed.
     *
     * @param Facture|object $object Current Dolibarr object
     * @param User|object    $user   Current user
     * @return bool True when display is allowed
     */
    protected function canDisplayInvoiceBlock($object, $user)
    {
        if (!is_object($object) || empty($object->element) || $object->element !== 'facture') {
            return false;
        }

        if (!is_object($user) || (empty($user->admin) && empty($user->rights->choruspro->read))) {
            return false;
        }

        return true;
    }

    /**
     * Apply submitted transient values before validation.
     *
     * @param Facture|object $object Current Dolibarr object
     * @param string        $action Current action
     * @return void
     */
    protected function applyPostedPreparationValues(&$object, $action)
    {
        if ($action !== 'choruspro_check') {
            return;
        }

        if (function_exists('checkToken') && !checkToken()) {
            return;
        }

        if (empty($object->array_options) || !is_array($object->array_options)) {
            $object->array_options = array();
        }

        $object->array_options['options_choruspro_code_service'] = $this->getPostValue('choruspro_code_service');
        $object->array_options['options_choruspro_engagement_juridique'] = $this->getPostValue('choruspro_engagement_juridique');

        if (method_exists($object, 'insertExtraFields')) {
            $object->insertExtraFields();
        }
    }

    /**
     * Render the invoice preparation block.
     *
     * @param Facture|object $object     Current Dolibarr object
     * @param array         $validation Validation result
     * @param array         $resolved   Resolved data
     * @return string HTML block
     */
    protected function renderPreparationBlock($object, array $validation, array $resolved)
    {
        $html = '<div class="div-table-responsive-no-min">';
        $html .= '<table class="noborder centpercent">';
        $html .= '<tr class="liste_titre"><td colspan="2">' . $this->escape($this->trans('ChorusProInvoicePreparation')) . '</td></tr>';
        $html .= $this->renderRow($this->trans('ChorusProRecipientSiret'), isset($resolved['recipient_siret']) ? $resolved['recipient_siret'] : '');
        $html .= $this->renderRow($this->trans('ChorusProStructureId'), isset($resolved['structure_id']) ? $resolved['structure_id'] : '');
        $html .= $this->renderFormRow($object, $resolved);
        $html .= $this->renderRow($this->trans('ChorusProPreparationStatus'), $validation['valid'] ? $this->trans('ChorusProReady') : $this->trans('ChorusProBlockingErrors'));
        $html .= $this->renderMessages($validation);
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render one read-only row.
     *
     * @param string $label Row label
     * @param string $value Row value
     * @return string HTML row
     */
    protected function renderRow($label, $value)
    {
        $display = $value === '' ? $this->trans('ChorusProNotAvailable') : $value;

        return '<tr class="oddeven"><td class="titlefield">' . $this->escape($label) . '</td><td>' . $this->escape($display) . '</td></tr>';
    }

    /**
     * Render the transient preparation form row.
     *
     * @param Facture|object $object   Current Dolibarr object
     * @param array         $resolved Resolved data
     * @return string HTML row
     */
    protected function renderFormRow($object, array $resolved)
    {
        $html = '<tr class="oddeven"><td>' . $this->escape($this->trans('ChorusProPreparationData')) . '</td><td>';
        $html .= '<form method="POST" action="' . $this->escape($this->getCurrentUrl()) . '">';
        $html .= '<input type="hidden" name="token" value="' . $this->escape(function_exists('newToken') ? newToken() : '') . '">';
        $html .= '<input type="hidden" name="action" value="choruspro_check">';
        $html .= '<label>' . $this->escape($this->trans('ChorusProServiceCode')) . '</label> ';
        $html .= '<input class="flat" type="text" name="choruspro_code_service" value="' . $this->escape(isset($resolved['service_code']) ? $resolved['service_code'] : '') . '"> ';
        $html .= '<label>' . $this->escape($this->trans('ChorusProLegalCommitment')) . '</label> ';
        $html .= '<input class="flat" type="text" name="choruspro_engagement_juridique" value="' . $this->escape(isset($resolved['legal_commitment']) ? $resolved['legal_commitment'] : '') . '"> ';
        $html .= '<input class="button" type="submit" value="' . $this->escape($this->trans('ChorusProCheckPreparation')) . '">';
        $html .= '</form></td></tr>';

        return $html;
    }

    /**
     * Render validation errors and warnings.
     *
     * @param array $validation Validation result
     * @return string HTML rows
     */
    protected function renderMessages(array $validation)
    {
        $html = '';
        foreach (array('errors' => 'ERROR', 'warnings' => 'WARNING') as $key => $label) {
            if (empty($validation[$key])) {
                continue;
            }

            $html .= '<tr class="oddeven"><td>' . $this->escape($label) . '</td><td><ul>';
            foreach ($validation[$key] as $message) {
                $html .= '<li>' . $this->escape($message) . '</li>';
            }
            $html .= '</ul></td></tr>';
        }

        return $html;
    }

    /**
     * Return a translated label.
     *
     * @param string $key Translation key
     * @return string Translated label
     */
    protected function trans($key)
    {
        global $langs;

        return is_object($langs) ? $langs->trans($key) : $key;
    }

    /**
     * Escape HTML output.
     *
     * @param string $value Raw value
     * @return string Escaped value
     */
    protected function escape($value)
    {
        return function_exists('dol_escape_htmltag') ? dol_escape_htmltag($value) : htmlspecialchars($value, ENT_QUOTES);
    }

    /**
     * Read a POST value safely.
     *
     * @param string $key POST key
     * @return string Value
     */
    protected function getPostValue($key)
    {
        return function_exists('GETPOST') ? GETPOST($key, 'alphanohtml') : '';
    }

    /**
     * Return the current page URL.
     *
     * @return string Current URL
     */
    protected function getCurrentUrl()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            return (string) $_SERVER['REQUEST_URI'];
        }

        return isset($_SERVER['PHP_SELF']) ? (string) $_SERVER['PHP_SELF'] : '';
    }
}
