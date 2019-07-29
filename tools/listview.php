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
use onOffice\WPlugin\Record\RecordManagerFactory;

// set up WP environment
require '../../../../wp-load.php';

$type = filter_input(INPUT_GET, 'type');

$redirectFile = wp_get_referer();
$actionGet = filter_input(INPUT_GET, 'action');
$actionPost = filter_input(INPUT_POST, 'action2', FILTER_FORCE_ARRAY|FILTER_NULL_ON_FAILURE);

$pUserCapabilities = new UserCapabilities();
$roleEditEstateView = $pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_EDIT_VIEW_ESTATE);

if (!current_user_can($roleEditEstateView) || !(isset($actionGet) || isset($actionPost)) ) {
	wp_safe_redirect( add_query_arg( 'delete', 0, $redirectFile ) );
}

$pRecordManagerDelete = RecordManagerFactory::createByTypeAndAction
	($type, RecordManagerFactory::ACTION_DELETE);
$actionSelected = ListTable::currentAction();

switch ($actionSelected) {
	case 'bulk_delete':
		$listpages = filter_input(INPUT_POST, 'listpage', FILTER_DEFAULT, FILTER_FORCE_ARRAY) ?? [];
		check_admin_referer('bulk-listpages');
		$pRecordManagerDelete->deleteByIds($listpages);

		wp_safe_redirect( add_query_arg( 'delete', count($listpages), $redirectFile ) );
		break;

	case 'delete':
		$listId = filter_input(INPUT_GET, 'list_id', FILTER_SANITIZE_NUMBER_INT);
		check_admin_referer('delete-listview_'.$listId);
		$pRecordManagerDelete->deleteByIds(array($listId));

		wp_safe_redirect( add_query_arg( 'delete', 1, $redirectFile ) );
		break;

	default:
		wp_safe_redirect( add_query_arg( 'delete', 0, $redirectFile ) );
		break;
}
