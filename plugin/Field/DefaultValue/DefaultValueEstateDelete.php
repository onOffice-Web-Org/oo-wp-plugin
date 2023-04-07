<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

namespace onOffice\WPlugin\Field\DefaultValue;

use onOffice\WPlugin\Field\DefaultValue\Exception\DefaultValueDeleteException;
use wpdb;


/**
 *
 */

class DefaultValueEstateDelete
{
	/** @var wpdb */
	private $_pWPDB;

	/**
	 * @param wpdb $pWPDB
	 */
	public function __construct(wpdb $pWPDB)
	{
		$this->_pWPDB = $pWPDB;
	}

	/**
	 * @param int $estate_id
	 * @param array $fieldNames
	 * @throws DefaultValueDeleteException
	 */
	public function deleteByEstateIdAndFieldNames(int $estate_id, array $fieldNames)
	{
		if ($fieldNames === []) {
			return;
		}

		$query = $this->getBaseDeleteQuery()." WHERE "
			."{$this->_pWPDB->prefix}oo_plugin_fieldconfig_estate_defaults.estate_id = '".esc_sql($estate_id)."' AND "
			."{$this->_pWPDB->prefix}oo_plugin_fieldconfig_estate_defaults.fieldname IN('"
			.implode("', '", esc_sql($fieldNames))
			."')";

		if (false === $this->_pWPDB->query($query)) {
			throw new DefaultValueDeleteException();
		}
	}

	/**
	 * @param int $defaultId
	 * @throws DefaultValueDeleteException
	 */
	public function deleteSingleDefaultValueById(int $defaultId)
	{
		$query = $this->getBaseDeleteQuery()." WHERE "
			."{$this->_pWPDB->prefix}oo_plugin_fieldconfig_estate_defaults.defaults_id = %d";

		if (!$this->_pWPDB->query($this->_pWPDB->prepare($query, $defaultId))) {
			throw new DefaultValueDeleteException();
		}
	}

	/**
	 * @param int $estate_id
	 * @param string $fieldname
	 * @throws DefaultValueDeleteException
	 */
	public function deleteSingleDefaultValueByFieldname(int $estate_id, string $fieldname, string $locale = null)
	{
		$query = $this->getBaseDeleteQuery()." WHERE "
			."{$this->_pWPDB->prefix}oo_plugin_fieldconfig_estate_defaults.estate_id = %d AND "
			."{$this->_pWPDB->prefix}oo_plugin_fieldconfig_estate_defaults.fieldname = %s AND "
			."{$this->_pWPDB->prefix}oo_plugin_fieldconfig_estate_defaults.locale = %s";

		if (false === $this->_pWPDB->query($this->_pWPDB->prepare($query, $estate_id, $fieldname, $locale))) {
			throw new DefaultValueDeleteException();
		}
	}

	/**
	 * @return string
	 */
	private function getBaseDeleteQuery(): string
	{
		$prefix = $this->_pWPDB->prefix;
		return "DELETE {$prefix}oo_plugin_fieldconfig_estate_defaults, {$prefix}oo_plugin_fieldconfig_estate_defaults_values "
			."FROM {$prefix}oo_plugin_fieldconfig_estate_defaults "
			."INNER JOIN {$prefix}oo_plugin_fieldconfig_estate_defaults_values "
			."ON {$prefix}oo_plugin_fieldconfig_estate_defaults.defaults_id = {$prefix}oo_plugin_fieldconfig_estate_defaults_values.defaults_id";
	}
}
