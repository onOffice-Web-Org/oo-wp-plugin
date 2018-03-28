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


// set up WP environment
require '../../../../wp-load.php';
$redirectFile = admin_url('admin.php?page=onoffice-estates');

if (!current_user_can('edit_pages') ||
	!(isset($_GET['action']) || isset($_POST['action2'])) )
{
	wp_safe_redirect( add_query_arg( 'delete', 0, $redirectFile ) );
}

$action = \onOffice\WPlugin\Gui\Table\EstateListTable::currentAction();

$pRecordManagerDelete = new \onOffice\WPlugin\Record\RecordManagerDeleteListViewEstate();

switch ($action)
{
case 'bulk_delete':
	$listpages = isset($_POST['listpage']) ? $_POST['listpage'] : array();
	check_admin_referer('bulk-listpages');
	$pRecordManagerDelete->deleteByIds($listpages);

	wp_safe_redirect( add_query_arg( 'delete', count($listpages), $redirectFile ) );
	break;

case 'delete':
	$listId = $_GET['list_id'];
	check_admin_referer('delete-listview_'.$listId);
	$pRecordManagerDelete->deleteByIds(array($listId));

	wp_safe_redirect( add_query_arg( 'delete', 1, $redirectFile ) );
	break;
}
