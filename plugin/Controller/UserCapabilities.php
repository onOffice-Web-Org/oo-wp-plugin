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

declare (strict_types=1);

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\Controller\Exception\UserCapabilitiesException;
use UnexpectedValueException;
use function current_user_can;

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

	/** */
	const OO_PLUGINCAP_MANAGE_FORM_OWNER = 'oo_plugincap_manage_form_owner';

	/** */
	const OO_PLUGINCAP_MANAGE_FORM_OWNER_LEADGENERATOR = 'oo_plugincap_manage_form_owner_leadgenerator';

	/** */
	const OO_PLUGINCAP_MANAGE_FORM_APPLICANTSEARCH = 'oo_plugincap_manage_form_applicantsearch';

	/** */
	const OO_PLUGINCAP_MANAGE_FORM_INTEREST = 'oo_plugincap_manage_form_interest';

	/** */
	const OO_PLUGINCAP_MANAGE_FORM_NEWSLETTER = 'oo_plugincap_manage_form_newsletter';

	const OO_PLUGINCAP_MANAGE_PLUGIN_TEMPLATES = 'oo_plugincap_manage_plugin_templates';


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

	/**
	 *
	 * @param string $rule
	 * @throws UserCapabilitiesException
	 *
	 */

	public function checkIfCurrentUserCan(string $rule)
	{
		$capability = $this->getCapabilityForRule($rule);

		if (!current_user_can($capability)) {
			throw new UserCapabilitiesException();
		}
	}


	public function add_plugin_capabilities_to_roles()
	{
		$roles = ['administrator', 'editor'];
		
		foreach ($roles as $role_name) {
			$role = get_role($role_name);
			if($role) {
				$role->add_cap(
					self::OO_PLUGINCAP_MANAGE_FORM_OWNER,
				);

				$role->add_cap(
					self::OO_PLUGINCAP_MANAGE_FORM_INTEREST,
				);

				$role->add_cap(
					self::OO_PLUGINCAP_MANAGE_FORM_OWNER_LEADGENERATOR,
				);
				$role->add_cap(
					self::OO_PLUGINCAP_MANAGE_FORM_APPLICANTSEARCH,
				);
				$role->add_cap(
					self::OO_PLUGINCAP_MANAGE_FORM_NEWSLETTER,
				);
				$role->add_cap(
					self::OO_PLUGINCAP_MANAGE_PLUGIN_TEMPLATES,
				);
			}
		}
	}
}