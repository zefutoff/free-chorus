<?php
/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/admin/diagnostics.php
 * \ingroup    choruspro
 * \brief      Diagnostics page for the Chorus Pro module.
 */

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/custom/choruspro/class/chorusproclient.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/choruspro/class/chorusprodiagnostics.class.php';

$langs->loadLangs(array('admin', 'choruspro@choruspro'));

$canDiagnose = $user->admin || !empty($user->rights->choruspro->admin) || !empty($user->rights->choruspro->logs);
if (!$canDiagnose) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$runOAuth = $action === 'test_oauth';
$structureSiret = $action === 'test_structure' ? GETPOST('siret', 'alphanohtml') : '';
$servicesStructureId = $action === 'test_services' ? GETPOST('structure_id', 'alphanohtml') : '';
$servicesSiret = $action === 'test_services' ? GETPOST('services_siret', 'alphanohtml') : '';
$diagnostics = new ChorusProDiagnostics($db, $conf, $user, $_SERVER);
$results = $diagnostics->runAll($runOAuth, $structureSiret, $servicesStructureId, $servicesSiret);

if ($action === 'export_diagnostics') {
    $exportFormat = GETPOST('format', 'aZ09') === 'json' ? 'json' : 'text';
    $exportContent = $diagnostics->exportReport($results, $exportFormat);

    header('Content-Type: ' . $diagnostics->getExportMimeType($exportFormat));
    header('Content-Disposition: attachment; filename="' . $diagnostics->getExportFilename($exportFormat) . '"');
    header('Content-Length: ' . strlen($exportContent));
    print $exportContent;
    $db->close();
    exit;
}

/**
 * Return display metadata for a diagnostic state.
 *
 * @param string $state Diagnostic state
 * @return array Display metadata
 */
function chorusproDiagnosticStateDisplay($state)
{
    if ($state === ChorusProDiagnostics::STATE_SUCCESS) {
        return array('icon' => 'tick', 'class' => 'badge badge-status4', 'label' => 'SUCCESS');
    }
    if ($state === ChorusProDiagnostics::STATE_WARNING) {
        return array('icon' => 'warning', 'class' => 'badge badge-status1', 'label' => 'WARNING');
    }

    return array('icon' => 'error', 'class' => 'badge badge-status8', 'label' => 'ERROR');
}

llxHeader('', $langs->trans('ChorusProDiagnostics'));

$linkBack = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">'
    . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($langs->trans('ChorusProDiagnostics'), $linkBack, 'title_setup');

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';
print '<div class="opacitymedium">' . $langs->trans('ChorusProDiagnosticsIntro') . '</div>';
print '<br>';
print '<form method="POST" action="' . dol_escape_htmltag($_SERVER['PHP_SELF']) . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="test_oauth">';
print '<input class="button" type="submit" value="' . $langs->trans('ChorusProTestOAuthAuthentication') . '">';
print '</form>';
print '<br>';
print '<form method="POST" action="' . dol_escape_htmltag($_SERVER['PHP_SELF']) . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="test_structure">';
print '<label for="choruspro_structure_siret">' . $langs->trans('ChorusProStructureSiret') . '</label> ';
print '<input id="choruspro_structure_siret" class="flat" type="text" name="siret" maxlength="20" value="' . dol_escape_htmltag($structureSiret) . '" placeholder="11000201100044"> ';
print '<input class="button" type="submit" value="' . $langs->trans('ChorusProTestStructureSearch') . '">';
print '</form>';
print '<br>';
print '<form method="POST" action="' . dol_escape_htmltag($_SERVER['PHP_SELF']) . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="test_services">';
print '<label for="choruspro_services_structure_id">' . $langs->trans('ChorusProStructureId') . '</label> ';
print '<input id="choruspro_services_structure_id" class="flat" type="text" name="structure_id" maxlength="20" value="' . dol_escape_htmltag($servicesStructureId) . '"> ';
print '<span class="opacitymedium">' . $langs->trans('Or') . '</span> ';
print '<label for="choruspro_services_siret">' . $langs->trans('ChorusProStructureSiret') . '</label> ';
print '<input id="choruspro_services_siret" class="flat" type="text" name="services_siret" maxlength="20" value="' . dol_escape_htmltag($servicesSiret) . '" placeholder="11000201100044"> ';
print '<input class="button" type="submit" value="' . $langs->trans('ChorusProTestStructureServices') . '">';
print '</form>';
print '<br>';
print '<form method="POST" action="' . dol_escape_htmltag($_SERVER['PHP_SELF']) . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="export_diagnostics">';
print '<button class="button" type="submit" name="format" value="text">' . $langs->trans('ChorusProExportDiagnosticsText') . '</button> ';
print '<button class="button" type="submit" name="format" value="json">' . $langs->trans('ChorusProExportDiagnosticsJson') . '</button>';
print '</form>';
print '<br>';

$categoryLabels = array(
    ChorusProDiagnostics::CATEGORY_GENERAL => $langs->trans('ChorusProDiagnosticsGeneral'),
    ChorusProDiagnostics::CATEGORY_CONFIGURATION => $langs->trans('ChorusProDiagnosticsConfiguration'),
    ChorusProDiagnostics::CATEGORY_SECURITY => $langs->trans('ChorusProDiagnosticsSecurity'),
    ChorusProDiagnostics::CATEGORY_PERMISSIONS => $langs->trans('ChorusProDiagnosticsPermissions'),
    ChorusProDiagnostics::CATEGORY_INFRASTRUCTURE => $langs->trans('ChorusProDiagnosticsInfrastructure'),
    ChorusProDiagnostics::CATEGORY_OAUTH => $langs->trans('ChorusProDiagnosticsOAuth'),
    ChorusProDiagnostics::CATEGORY_STRUCTURES => $langs->trans('ChorusProDiagnosticsStructures'),
    ChorusProDiagnostics::CATEGORY_SERVICES => $langs->trans('ChorusProDiagnosticsServices'),
);

foreach ($results as $category => $categoryResults) {
    print '<table class="noborder centpercent">';
    print '<tr class="liste_titre"><td colspan="4">' . dol_escape_htmltag($categoryLabels[$category]) . '</td></tr>';
    print '<tr class="liste_titre"><td>' . $langs->trans('Name') . '</td><td>'
        . $langs->trans('Description') . '</td><td>' . $langs->trans('Status') . '</td><td>'
        . $langs->trans('ChorusProDiagnosticsDetail') . '</td></tr>';

    foreach ($categoryResults as $result) {
        $display = chorusproDiagnosticStateDisplay($result['state']);
        print '<tr class="oddeven">';
        print '<td>' . img_picto('', $display['icon']) . ' ' . dol_escape_htmltag($result['name']) . '</td>';
        print '<td>' . dol_escape_htmltag($result['description']) . '</td>';
        print '<td><span class="' . dol_escape_htmltag($display['class']) . '">' . dol_escape_htmltag($display['label']) . '</span></td>';
        print '<td>' . dol_escape_htmltag($result['detail']) . '</td>';
        print '</tr>';
    }

    print '</table>';
    print '<br>';
}

print '</div>';

llxFooter();
$db->close();
