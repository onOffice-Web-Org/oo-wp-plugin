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

use onOffice\WPlugin\Renderer\InputModelRenderer;

/**
 *
 */

abstract class AdminPage
	extends AdminPageBase
{
	/**
	 *
	 */

	public function registerForms()
	{
		/* @var $pInputModelRenderer InputModelRenderer */
		$pInputModelRenderer = $this->getContainer()->get(InputModelRenderer::class);

		foreach ($this->getFormModels() as $pFormModel) {
			$pInputModelRenderer->registerFields($pFormModel);
		}
	}

	public function generateSearchForm($page,$button)
	{
		echo '<form action="'.admin_url('admin.php').'" method="get" id="onoffice-form-search">'.html_entity_decode(htmlentities($page)).
			'<input type="text" id="fname" name="search">
			<input type="submit" value="'.html_entity_decode(htmlentities($button)).'">
			</form>';
	}
}
