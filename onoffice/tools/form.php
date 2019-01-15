<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\Gui\Table\WP\ListTable;
use onOffice\WPlugin\Record\RecordManagerDeleteForm;

// set up WP environment
require '../../../../wp-load.php';
$redirectFile = admin_url('admin.php?page=onoffice-forms');
$pUserCapabilities = new UserCapabilities;
$roleEditForms = $pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_EDIT_VIEW_FORM);

if (!current_user_can($roleEditForms) ||
	!(isset($_GET['action']) || isset($_POST['action2'])) )
{
	wp_safe_redirect( add_query_arg( 'delete', 0, $redirectFile ) );
}

$action = ListTable::currentAction();

$pRecordManagerDelete = new RecordManagerDeleteForm();

switch ($action)
{
case 'bulk_delete':
	$formIds = filter_input(INPUT_POST, 'form', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
	check_admin_referer('bulk-forms');
	$pRecordManagerDelete->deleteByIds($formIds);

	wp_safe_redirect( add_query_arg( 'delete', count($formIds), $redirectFile ) );
	break;

case 'delete':
	$formId = filter_input(INPUT_GET, 'form_id', FILTER_SANITIZE_NUMBER_INT);
	check_admin_referer('delete-form_'.$formId);
	$pRecordManagerDelete->deleteByIds(array($formId));

	wp_safe_redirect( add_query_arg( 'delete', 1, $redirectFile ) );
	break;
}
