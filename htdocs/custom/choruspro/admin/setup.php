<?php
/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/admin/setup.php
 * \ingroup    choruspro
 * \brief      Setup page for the Chorus Pro module.
 */

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

$langs->loadLangs(array('admin', 'choruspro@choruspro'));

$canConfigure = $user->admin || !empty($user->rights->choruspro->admin);
if (!$canConfigure) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$entity = (int) $conf->entity;

$plainSettings = array(
    'CHORUSPRO_ENV' => 'chaine',
    'CHORUSPRO_CLIENT_ID' => 'chaine',
    'CHORUSPRO_TECHNICAL_ACCOUNT' => 'chaine',
    'CHORUSPRO_EMITTER_SIRET' => 'chaine',
    'CHORUSPRO_DEBUG' => 'yesno',
    'CHORUSPRO_LOG_LEVEL' => 'chaine',
);
$secretSettings = array(
    'CHORUSPRO_CLIENT_SECRET' => 'chaine',
    'CHORUSPRO_TECHNICAL_PASSWORD' => 'chaine',
);

if ($action === 'set') {
    $errors = 0;

    foreach ($plainSettings as $name => $type) {
        $value = GETPOST($name, 'nohtml');
        if ($name === 'CHORUSPRO_ENV') {
            $value = in_array($value, array('sandbox', 'production'), true) ? $value : 'sandbox';
        } elseif ($name === 'CHORUSPRO_DEBUG') {
            $value = GETPOST($name, 'int') ? '1' : '0';
        } elseif ($name === 'CHORUSPRO_LOG_LEVEL') {
            $value = in_array($value, array('INFO', 'WARNING', 'ERROR'), true) ? $value : 'INFO';
        }

        if (dolibarr_set_const($db, $name, $value, $type, 0, '', $entity) <= 0) {
            $errors++;
        }
    }

    foreach ($secretSettings as $name => $type) {
        $value = GETPOST($name, 'nohtml');
        if ($value === '') {
            continue;
        }

        if (dolibarr_set_const($db, $name, $value, $type, 0, '', $entity) <= 0) {
            $errors++;
        }
    }

    if ($errors === 0) {
        setEventMessages($langs->trans('SetupSaved'), null, 'mesgs');
    } else {
        setEventMessages($langs->trans('Error'), null, 'errors');
    }
}

/**
 * Return a Dolibarr global configuration value without exposing undefined properties.
 *
 * @param string $name Constant name
 * @return string Stored value or empty string
 */
function chorusproGetGlobalValue($name)
{
    global $conf;

    return empty($conf->global->{$name}) ? '' : (string) $conf->global->{$name};
}

/**
 * Print a table row for a text configuration value.
 *
 * @param string $name  Constant name
 * @param string $label Translation key
 * @return void
 */
function chorusproPrintTextSetting($name, $label)
{
    global $langs;

    print '<tr class="oddeven">';
    print '<td>' . $langs->trans($label) . '</td>';
    print '<td><input class="flat minwidth300" type="text" name="' . dol_escape_htmltag($name) . '" value="'
        . dol_escape_htmltag(chorusproGetGlobalValue($name)) . '"></td>';
    print '</tr>';
}

/**
 * Print a table row for a secret configuration value.
 *
 * @param string $name  Constant name
 * @param string $label Translation key
 * @return void
 */
function chorusproPrintSecretSetting($name, $label)
{
    global $langs;

    $isConfigured = chorusproGetGlobalValue($name) !== '';
    $placeholder = $isConfigured ? '********' : '';
    $status = $isConfigured ? $langs->trans('ChorusProSecretConfigured') : $langs->trans('ChorusProSecretNotConfigured');

    print '<tr class="oddeven">';
    print '<td>' . $langs->trans($label) . '</td>';
    print '<td>';
    print '<input class="flat minwidth300" type="password" autocomplete="new-password" name="'
        . dol_escape_htmltag($name) . '" value="" placeholder="' . dol_escape_htmltag($placeholder) . '">';
    print ' <span class="opacitymedium">' . dol_escape_htmltag($status) . '</span>';
    print '</td>';
    print '</tr>';
}

llxHeader('', $langs->trans('ChorusProSetup'));

$linkBack = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">'
    . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($langs->trans('ChorusProSetup'), $linkBack, 'title_setup');

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';
print '<div class="opacitymedium">' . $langs->trans('ChorusProSetupIntro') . '</div>';
print '<br>';
print '<form method="POST" action="' . dol_escape_htmltag($_SERVER['PHP_SELF']) . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="set">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td colspan="2">' . $langs->trans('ChorusProApiSettings') . '</td></tr>';

print '<tr class="oddeven">';
print '<td>' . $langs->trans('ChorusProEnv') . '</td>';
print '<td><select class="flat" name="CHORUSPRO_ENV">';
foreach (array('sandbox', 'production') as $environment) {
    $selected = chorusproGetGlobalValue('CHORUSPRO_ENV') === $environment ? ' selected' : '';
    print '<option value="' . dol_escape_htmltag($environment) . '"' . $selected . '>'
        . $langs->trans('ChorusProEnv' . ucfirst($environment)) . '</option>';
}
print '</select></td>';
print '</tr>';

chorusproPrintTextSetting('CHORUSPRO_CLIENT_ID', 'ChorusProClientId');
chorusproPrintSecretSetting('CHORUSPRO_CLIENT_SECRET', 'ChorusProClientSecret');
chorusproPrintTextSetting('CHORUSPRO_TECHNICAL_ACCOUNT', 'ChorusProTechnicalAccount');
chorusproPrintSecretSetting('CHORUSPRO_TECHNICAL_PASSWORD', 'ChorusProTechnicalPassword');
chorusproPrintTextSetting('CHORUSPRO_EMITTER_SIRET', 'ChorusProEmitterSiret');

print '<tr class="oddeven">';
print '<td>' . $langs->trans('ChorusProDebug') . '</td>';
$checked = chorusproGetGlobalValue('CHORUSPRO_DEBUG') === '1' ? ' checked' : '';
print '<td><input type="checkbox" name="CHORUSPRO_DEBUG" value="1"' . $checked . '></td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>' . $langs->trans('ChorusProLogLevel') . '</td>';
print '<td><select class="flat" name="CHORUSPRO_LOG_LEVEL">';
foreach (array('INFO', 'WARNING', 'ERROR') as $level) {
    $selected = chorusproGetGlobalValue('CHORUSPRO_LOG_LEVEL') === $level ? ' selected' : '';
    print '<option value="' . dol_escape_htmltag($level) . '"' . $selected . '>' . dol_escape_htmltag($level) . '</option>';
}
print '</select></td>';
print '</tr>';

print '</table>';
print '<br>';
print '<div class="center">';
print '<input class="button" type="submit" value="' . $langs->trans('Save') . '">';
print '</div>';
print '</form>';
print '</div>';

llxFooter();
$db->close();
