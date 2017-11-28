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

use onOffice\WPlugin\Gui\Table\FormsTable;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageFormList
	extends AdminPage
{
	/** */
	const PARAM_TYPE = 'type';

	/**
	 *
	 */

	public function renderContent()
	{
		$this->generatePageMainTitle(__('Forms', 'onoffice'));
		$actionFile = plugin_dir_url(ONOFFICE_PLUGIN_DIR).
			plugin_basename(ONOFFICE_PLUGIN_DIR).'/tools/listview.php';

		$tab = $this->getTab();
		$pTable = new FormsTable();
		$pTable->setListType($tab);
		$pTable->prepare_items();
		echo '<p>';
		echo '<form method="post" action="'.esc_html($actionFile).'">';
		echo $pTable->views();
		$pTable->display();
		echo '</form>';
		echo '</p>';
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getTab()
	{
		$getParamType = filter_input(INPUT_GET, self::PARAM_TYPE);
		return $getParamType;
	}
}
