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

use onOffice\WPlugin\Form;

/**
 *
 * Facade class for AdminPageForm*-classes
 *
 */

class AdminPageFormSettingsMain
	extends AdminPageAjax
{
	/** */
	const GET_PARAM_TYPE = 'type';

	/** @var array */
	private $_mappingTypeClass = array(
		Form::TYPE_CONTACT => '\onOffice\WPlugin\Gui\AdminPageFormSettingsContact',
		// search searchcriteria
		Form::TYPE_APPLICANT_SEARCH => null,
		Form::TYPE_INTEREST => '\onOffice\WPlugin\Gui\AdminPageFormSettingsContact',
		Form::TYPE_OWNER => '\onOffice\WPlugin\Gui\AdminPageFormSettingsContact',
		Form::TYPE_FREE => '\onOffice\WPlugin\Gui\AdminPageFormSettingsFree',
	);

	/** @var AdminPageFormSettingsBase */
	private $_pInstance = null;


	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function initSubClass($pageSlug)
	{
		$type = filter_input(INPUT_GET, self::GET_PARAM_TYPE, FILTER_SANITIZE_STRING);

		$className = $this->getClassNameByType($type);

		if ($className === null) {
			throw new \UnexpectedValueException($type);
		}

		$this->_pInstance = new $className($pageSlug);
		$this->configureAdminPage($this->_pInstance, $type);
	}


	/**
	 *
	 * @param \onOffice\WPlugin\Gui\AdminPageFormSettingsBase $pAdminPage
	 * @param string $type
	 *
	 */

	private function configureAdminPage(AdminPageFormSettingsBase $pAdminPage, $type)
	{
		switch ($type) {
			case Form::TYPE_INTEREST:
				/* @var $pAdminPage \onOffice\WPlugin\Gui\AdminPageFormSettingsContact */
				$pAdminPage->setShowSearchCriteriaBoxes(true);
				$pAdminPage->setShowAddressFields(true);
				break;
			case Form::TYPE_OWNER:
				/* @var $pAdminPage \onOffice\WPlugin\Gui\AdminPageFormSettingsContact */
				$pAdminPage->setShowPagesOption(true);
				$pAdminPage->setShowEstateFields(true);
				$pAdminPage->setShowAddressFields(true);
				break;
		}
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$this->_pInstance->renderContent();
	}


	/**
	 *
	 */

	public function handleAdminNotices()
	{
		$this->_pInstance->handleAdminNotices();
	}


	/**
	 *
	 * @param string $type
	 * @return string
	 *
	 */

	private function getClassNameByType($type)
	{
		$result = null;

		if (isset($this->_mappingTypeClass[$type]))
		{
			$result = $this->_mappingTypeClass[$type];
		}

		return $result;
	}


	/**
	 *
	 */

	protected function buildForms()
	{
		$this->_pInstance->buildForms();
	}


	/**
	 *
	 */

	public function ajax_action()
	{
		$this->_pInstance->ajax_action();
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		$this->_pInstance->doExtraEnqueues();
	}
}
