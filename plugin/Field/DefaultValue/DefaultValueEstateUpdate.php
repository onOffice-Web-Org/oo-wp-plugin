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

use wpdb;


/**
 *
 */

class DefaultValueEstateUpdate
{
	/** @var wpdb */
	private $_pWPDB;


	/**
	 *
	 * @param wpdb $pWPDB
	 *
	 */

	public function __construct(wpdb $pWPDB)
	{
		$this->_pWPDB = $pWPDB;
	}


	/**
	 *
	 * @param DefaultValueModelSingleselect $pModel
	 *
	 */

	public function updateSingleselect(DefaultValueModelSingleselect $pModel)
	{
		$this->doPreChecks($pModel);
		$where = ['defaults_id' => $pModel->getDefaultsId()];
		$table = $this->_pWPDB->prefix.'oo_plugin_fieldconfig_estate_defaults_values';
		$this->doCheckWPDB($this->_pWPDB->delete($table, $where));
		$this->doCheckWPDB($this->_pWPDB->insert($table, [
			'defaults_id' => $pModel->getDefaultsId(),
			'value' => $pModel->getValue(),
		]));
	}


	/**
	 *
	 * @param DefaultValueModelMultiselect $pModel
	 *
	 */

	public function updateMultiselect(DefaultValueModelMultiselect $pModel)
	{
		$this->doPreChecks($pModel);
		$table = $this->_pWPDB->prefix.'oo_plugin_fieldconfig_estate_defaults_values';
		$this->doCheckWPDB($this->_pWPDB->delete($table,
			['defaults_id' => $pModel->getDefaultsId()]));

		foreach ($pModel->getValues() as $value) {
			$this->doCheckWPDB($this->_pWPDB->insert($table, [
				'defaults_id' => $pModel->getDefaultsId(),
				'value' => $value,
			]));
		}
	}


	/**
	 *
	 * @param DefaultValueModelBase $pModel
	 * @throws DefaultValueSaveException
	 *
	 */

	private function doPreChecks(DefaultValueModelBase $pModel)
	{
		if ($pModel->getDefaultsId() === 0) {
			throw new DefaultValueSaveException('defaultsId cannot be 0');
		}
	}


	/**
	 *
	 * @param mixed $result
	 * @throws DefaultValueSaveException
	 *
	 */

	private function doCheckWPDB($result)
	{
		if ($result === false) {
			throw new DefaultValueSaveException('Insert/Delete/Update failed');
		}
	}
}
