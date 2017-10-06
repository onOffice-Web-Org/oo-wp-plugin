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

abstract class AdminPage
{
	/** @var string */
	private $_pageSlug = null;

	/** @var \onOffice\WPlugin\Model\FormModel[] */
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

	public function registerForms()
	{
		foreach ($this->_formModels as $pFormModel)
		{
			$pFormBuilder = new FormBuilder($pFormModel);
			$pFormBuilder->registerFields();
		}
	}


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
	 * @param \onOffice\WPlugin\Model\FormModel $pFormModel
	 *
	 */

	protected function addFormModel(\onOffice\WPlugin\Model\FormModel $pFormModel)
	{
		$key = $pFormModel->getGroupSlug();
		$this->_formModels[$key] = $pFormModel;
	}

	/**
	 *
	 * @param string $subTitle
	 *
	 */

	public function generatePageMainTitle($subTitle)
	{
		echo '<h1 class="wp-heading-inline">'.esc_html_x('onOffice', 'onoffice');

		if ($subTitle != '')
		{
			echo ' › '.esc_html_x($subTitle, 'onoffice');
		}

		echo '</h1>';
		echo '<hr class="wp-header-end">';
	}


	/**
	 * 
	 */

	public function handleAdminNotices()
		{}

	/** @return string */
	public function getPageSlug()
		{ return $this->_pageSlug; }

	/** @return \onOffice\WPlugin\Model\FormModel[] */
	public function getFormModels()
		{ return $this->_formModels; }
}
