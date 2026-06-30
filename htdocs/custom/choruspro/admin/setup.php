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
 * \brief      Basic setup page for the Chorus Pro module.
 */

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

$langs->loadLangs(array('admin', 'choruspro@choruspro'));

if (!$user->admin) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');

if ($action === 'set') {
    setEventMessages($langs->trans('ChorusProNoSettingYet'), null, 'mesgs');
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
print '<div class="center">';
print '<input class="button" type="submit" value="' . $langs->trans('Save') . '">';
print '</div>';
print '</form>';
print '</div>';

llxFooter();
$db->close();
