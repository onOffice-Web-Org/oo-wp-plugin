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

namespace onOffice\WPlugin\Gui;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateUnitList
	extends AdminPage
{
	/**
	 *
	 */

	public function renderContent()
	{
		$actionFile = plugin_dir_url(ONOFFICE_PLUGIN_DIR).
			plugin_basename(ONOFFICE_PLUGIN_DIR).'/tools/listview.php';

		$pTable = new EstateUnitsTable();
		$pTable->prepare_items();
		echo '<p>';
		echo '<form method="post" action="'.esc_html($actionFile).'">';
		$pTable->display();
		echo '</form>';
		echo '</p>';
	}


	/**
	 *
	 * @param string $subTitle
	 *
	 */

	public function generatePageMainTitle($subTitle)
	{
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice');

		if ($subTitle != '')
		{
			echo ' › '.esc_html__($subTitle, 'onoffice');
		}

		echo ' › '.esc_html__('Units Lists', 'onoffice');

		$new_link = admin_url('admin.php?page=onoffice-editunitlist');

		echo '</h1>';
		echo '<a href="'.$new_link.'" class="page-title-action">'.esc_html__('Add New', 'onoffice').'</a>';
		echo '<hr class="wp-header-end">';
	}
}
