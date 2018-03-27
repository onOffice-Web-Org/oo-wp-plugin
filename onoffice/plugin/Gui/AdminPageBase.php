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

use onOffice\WPlugin\Model\FormModel;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class AdminPageBase
{
	/** @var string */
	private $_pageSlug = null;

	/** @var FormModel[] */
	private $_formModels = array();

	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		$this->_pageSlug = $pageSlug;
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
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice');

		if ($subTitle != '') {
			echo ' › '.esc_html__($subTitle, 'onoffice');
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

	public function getFormModelByGroupSlug($groupSlug)
	{
		$pFormModel = null;

		if (array_key_exists($groupSlug, $this->_formModels)) {
			$pFormModel = $this->_formModels[$groupSlug];
		}

		return $pFormModel;
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

	/** @return FormModel[] */
	public function getFormModels()
		{ return $this->_formModels; }

	/** @return string */
	public function getPageSlug()
		{ return $this->_pageSlug; }
}
