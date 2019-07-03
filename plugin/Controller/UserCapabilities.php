<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;

use UnexpectedValueException;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class UserCapabilities
{
	/** */
	const RULE_VIEW_MAIN_PAGE = 'viewMainPage';

	/** */
	const RULE_EDIT_VIEW_ESTATE = 'editViewEstate';

	/** */
	const RULE_EDIT_VIEW_ADDRESS = 'editViewAddress';

	/** */
	const RULE_EDIT_VIEW_FORM = 'editViewForm';

	/** */
	const RULE_EDIT_MODULES = 'editModules';

	/** */
	const RULE_EDIT_SETTINGS = 'editSettings';

	/** */
	const RULE_DEBUG_OUTPUT = 'debugOutput';

	/** @var array */
	private $_ruleToCapability = [
		self::RULE_VIEW_MAIN_PAGE => 'edit_pages',
		self::RULE_EDIT_VIEW_ADDRESS => 'edit_pages',
		self::RULE_EDIT_VIEW_ESTATE => 'edit_pages',
		self::RULE_EDIT_VIEW_FORM => 'edit_pages',
		self::RULE_EDIT_MODULES => 'edit_pages',
		self::RULE_EDIT_SETTINGS => 'edit_pages',
		self::RULE_DEBUG_OUTPUT => 'edit_pages',
	];


	/**
	 *
	 * @param string $rule
	 * @return string
	 * @throws UnexpectedValueException
	 *
	 */

	public function getCapabilityForRule(string $rule): string
	{
		$capability = $this->_ruleToCapability[$rule] ?? null;

		if ($capability !== null) {
			return $capability;
		}

		throw new UnexpectedValueException($rule);
	}
}
