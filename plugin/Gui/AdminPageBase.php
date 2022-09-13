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

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelDB;
use const ONOFFICE_DI_CONFIG_PATH;
use function esc_html__;

/**
 *
 */

abstract class AdminPageBase
{
	/** @var string */
	private $_pageSlug = null;

	/** @var FormModel[] */
	private $_formModels = array();

	/** @var Container */
	private $_pContainer;

	/**
	 * @param string $pageSlug
	 * @throws Exception
	 */
	public function __construct($pageSlug)
	{
		$this->_pageSlug = $pageSlug;
		$pDIContainerBuilder = new ContainerBuilder();
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pDIContainerBuilder->build();
	}


	/**
	 *
	 * renders the page, but using `echo`
	 *
	 */

	abstract public function renderContent();


	/**
	 *
	 */

	public function render()
	{
		echo '<div class="wrap">';
		$this->renderContent();
		echo '</div>';
	}


	/**
	 *
	 * @param string $subTitle
	 *
	 */

	public function generatePageMainTitle($subTitle)
	{
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice-for-wp-websites');

		if ($subTitle != '') {
			echo ' › ' . esc_html( $subTitle );
		}

		echo '</h1>';
		echo '<hr class="wp-header-end">';
	}


	/**
	 *
	 * @param FormModel $pFormModel
	 *
	 */

	protected function addFormModel(FormModel $pFormModel)
	{
		$key = $pFormModel->getGroupSlug();
		$this->_formModels[$key] = $pFormModel;
	}


	/**
	 *
	 * @param string $groupSlug
	 * @return FormModel
	 *
	 */

	public function getFormModelByGroupSlug(string $groupSlug)
	{
		return $this->_formModels[$groupSlug] ?? null;
	}


	/**
	 *
	 */

	public function handleAdminNotices()
		{}


	/**
	 *
	 * place extra wp_enqueue_script() and wp_enqueue_style() only for this page
	 *
	 */

	public function doExtraEnqueues()
		{}

	/**
	 *
	 * @return Container
	 *
	 */

	protected function getContainer(): Container
	{
		return $this->_pContainer;
	}


	/**
	 *
	 */

	public function preOutput()
		{}

	/** @return FormModel[] */
	public function getFormModels()
		{ return $this->_formModels; }

	/** @return string */
	public function getPageSlug()
		{ return $this->_pageSlug; }
}
