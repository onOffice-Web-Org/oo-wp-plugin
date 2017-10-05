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

use onOffice\WPlugin\Model;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageApiSettings
	extends AdminPage
{

	public function __construct($pageSlug)
	{
		$labelKey = __('API-key', 'onoffice');
		$labelSecret = __('API-key', 'onoffice');
		$pInputModelApiKey = new Model\InputModel('onoffice-settings', 'apikey', $labelKey, 'string');
		$pInputModelApiKey->setValue(get_option('onoffice-settings-apikey'));
		$pInputModelApiSecret = new Model\InputModel('onoffice-settings', 'apisecret', $labelSecret, 'string');
		$pInputModelApiSecret->setIsPassword(true);

		$pFormModel = new Model\FormModel();
		$pFormModel->addInputModel($pInputModelApiKey);
		$pFormModel->addInputModel($pInputModelApiSecret);
		$pFormModel->setGroupSlug('onoffice-api');
		$pFormModel->setPageSlug($pageSlug);
		$pFormModel->setLabel(__('API settings', 'onoffice'));

		$this->addFormModel($pFormModel);

		parent::__construct($pageSlug);
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$this->generatePageMainTitle('Settings');

		echo '<form method="post" action="options.php">';

		foreach ($this->getFormModels() as $pFormModel)
		{
			$pFormBuilder = new FormBuilder($pFormModel);
			$pFormBuilder->buildForm();
		}

		do_settings_sections( $this->getPageSlug() );

		submit_button();
		echo '</form>';
	}
}
