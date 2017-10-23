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
use onOffice\WPlugin\Form\InputModelRenderer;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageApiSettings
	extends AdminPage
{
	/** @var string */
	private $_inputApiSecretGroupSlugName = null;


	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		$labelKey = __('API token', 'onoffice');
		$labelSecret = __('API secret', 'onoffice');
		$pInputModelApiKey = new Model\InputModelOption('onoffice-settings', 'apikey', $labelKey, 'string');
		$optionNameKey = $pInputModelApiKey->getIdentifier();
		$pInputModelApiKey->setValue(get_option($optionNameKey));
		$pInputModelApiSecret = new Model\InputModelOption('onoffice-settings', 'apisecret', $labelSecret, 'string');
		$pInputModelApiSecret->setIsPassword(true);
		$pInputModelApiSecret->setSanitizeCallback(array($this, 'checkPassword'));
		$optionNameSecret = $pInputModelApiSecret->getIdentifier();
		$pInputModelApiSecret->setValue(get_option($optionNameSecret, $pInputModelApiSecret->getDefault()));
		$this->_inputApiSecretGroupSlugName = $pInputModelApiSecret->getIdentifier();

		$pFormModel = new Model\FormModel();
		$pFormModel->addInputModel($pInputModelApiSecret);
		$pFormModel->addInputModel($pInputModelApiKey);
		$pFormModel->setGroupSlug('onoffice-api');
		$pFormModel->setPageSlug($pageSlug);
		$pFormModel->setLabel(__('API settings', 'onoffice'));

		$this->addFormModel($pFormModel);

		parent::__construct($pageSlug);
	}


	/**
	 *
	 * @param string $password
	 * @return bool
	 *
	 */

	public function checkPassword($password)
	{
		return $password != '' ? $password : get_option($this->_inputApiSecretGroupSlugName);
	}


	/**
	 *
	 */

	public function handleAdminNotices()
	{
		$cacheClean = isset( $_GET['cache-refresh'] ) ? $_GET['cache-refresh'] : null ;

		if ($cacheClean === 'success')
		{
			add_action( 'admin_notices', array($this, 'displayCacheClearSuccess') );
		}
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
			$pFormBuilder = new InputModelRenderer($pFormModel);
			$pFormBuilder->buildForm();
		}

		do_settings_sections( $this->getPageSlug() );

		submit_button();
		echo '</form>';

		echo '<form method="post" action="'.plugins_url(basename(ONOFFICE_PLUGIN_DIR)).'/tools/clearCache.php">';
		wp_nonce_field( 'onoffice-clear-cache', 'onoffice-cache-nonce' );
		submit_button(__('Clear cache'), 'delete');
		echo '</form>';
	}


	/**
	 *
	 */

	public function displayCacheClearSuccess()
	{
		$class = 'notice notice-success is-dismissible';
		$message = __( 'The cache was cleaned.', 'onoffice' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}
