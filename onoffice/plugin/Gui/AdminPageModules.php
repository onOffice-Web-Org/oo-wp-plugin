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

use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Favorites;
use onOffice\WPlugin\Renderer\InputModelRenderer;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageModules
	extends AdminPage
{
	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		$groupSlugFavs = 'onoffice-favorization';
		$enableFavLabel = __('Enable Watchlist', 'onoffice');
		$favButtonLabel = __('Expression used', 'onoffice');
		$pInputModelEnableFav = new InputModelOption($groupSlugFavs, 'enableFav',
			$enableFavLabel, InputModelOption::SETTING_TYPE_BOOLEAN);
		$pInputModelEnableFav->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelEnableFav->setValuesAvailable(1);
		$pInputModelEnableFav->setValue(get_option($pInputModelEnableFav->getIdentifier()) == 1);
		$pInputModelFavButtonLabel = new InputModelOption($groupSlugFavs, 'favButtonLabelFav',
			$favButtonLabel, InputModelOption::SETTING_TYPE_NUMBER);
		$pInputModelFavButtonLabel->setHtmlType(InputModelOption::HTML_TYPE_RADIO);
		$pInputModelFavButtonLabel->setValue(get_option($pInputModelFavButtonLabel->getIdentifier()));
		$pInputModelFavButtonLabel->setValuesAvailable(array(
			Favorites::KEY_SETTING_MEMORIZE => __('Watchlist', 'onoffice'),
			Favorites::KEY_SETTING_FAVORIZE => __('Favorise', 'onoffice'),
		));

		$pFormModel = new FormModel();
		$pFormModel->addInputModel($pInputModelEnableFav);
		$pFormModel->addInputModel($pInputModelFavButtonLabel);
		$pFormModel->setGroupSlug($groupSlugFavs);
		$pFormModel->setPageSlug($pageSlug);
		$pFormModel->setLabel(__('Watchlist', 'onoffice'));

		$this->addFormModel($pFormModel);

		parent::__construct($pageSlug);
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$this->generatePageMainTitle('Modules');

		echo '<form method="post" action="options.php">';

		foreach ($this->getFormModels() as $pFormModel)
		{
			$pFormBuilder = new InputModelRenderer($pFormModel);
			$pFormBuilder->buildForm();
		}

		do_settings_sections( $this->getPageSlug() );

		submit_button();
		echo '</form>';
	}
}
