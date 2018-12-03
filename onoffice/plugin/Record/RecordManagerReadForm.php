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

namespace onOffice\WPlugin\Record;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\GeoPositionFormSettings;
use const ARRAY_A;
use const OBJECT;
use function esc_sql;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerReadForm
	extends RecordManagerRead
{

	/** @var bool */
	private $_forAdminInterface = false;


	/**
	 *
	 */

	public function __construct()
	{
		$this->setMainTable(self::TABLENAME_FORMS);
		$this->setIdColumnMain('form_id');
	}


	/**
	 *
	 * @param bool $forAdminInterface
	 *
	 */

	public function setForadminInterface(bool $forAdminInterface)
		{ $this->_forAdminInterface = $forAdminInterface; }


	/**
	 *
	 * @return object[]
	 *
	 */

	public function getRecords()
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$columns = implode(', ', $this->getColumns());
		$join = implode("\n", $this->getJoins());
		$where = "(".implode(") AND (", $this->getWhere()).")";
		$sql = "SELECT SQL_CALC_FOUND_ROWS {$columns}
				FROM {$prefix}oo_plugin_forms
				{$join}
				WHERE {$where}
				ORDER BY `form_id` ASC
				LIMIT {$this->getOffset()}, {$this->getLimit()}";
		$this->setFoundRows($pWpDb->get_results($sql, OBJECT));
		$this->setCountOverall($pWpDb->get_var('SELECT FOUND_ROWS()'));

		return $this->getFoundRows();
	}


	/**
	 *
	 * @param string $formName
	 * @return array
	 *
	 * @throws UnknownFormException
	 *
	 */

	public function getRowByName($formName)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = "SELECT *
				FROM {$prefix}oo_plugin_forms
				WHERE `name` = '".esc_sql($formName)."'";

		$result = $pWpDb->get_row($sql, ARRAY_A);

		if ($result === null) {
			throw new UnknownFormException($formName);
		}

		return $result;
	}


	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function readFieldsByFormId($formId, $pGeoPositionFormSettings)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sqlFields = "SELECT *
				FROM {$prefix}oo_plugin_form_fieldconfig
				WHERE `form_id` = ".esc_sql((int)$formId)."
				ORDER BY `order` ASC";

		$result = $pWpDb->get_results($sqlFields, ARRAY_A);

		if (!$this->_forAdminInterface) {
			$result = $this->readFieldsForNonAdminInterface($result, $pGeoPositionFormSettings);
		}

		return $result;
	}



	/**
	 *
	 * @param array $result
	 * @return array
	 *
	 */

	private function readFieldsForNonAdminInterface($result, $pGeoPositionFormSettings)
	{
		$pGeoPosition = new GeoPosition();

		foreach ($result as $id => $row) {
			if ($row['fieldname'] == 'geoPosition' &&
				$row['module'] === onOfficeSDK::MODULE_SEARCHCRITERIA) {
				$formId = $row['form_id'];
				$required = $row['required'];

				unset($result[$id]);

				$geoPositionSettings = $pGeoPositionFormSettings->getSettings();

				foreach ($geoPositionSettings as $field) {
					$geoPositionField = [
						'form_id' => $formId,
						'required' => $required,
						'fieldname' => $field,
						'fieldlabel' => null,
						'module' => 'searchcriteria',
						'individual_fieldname' => 0,
					];

					$result []= $geoPositionField;
				}
			}
		}

		return $result;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getCountByType()
	{
		$pWpDb = $this->getWpdb();
		$prefix = $this->getTablePrefix();

		$sql = "SELECT `form_type`, COUNT(`form_id`) as count
				FROM {$prefix}oo_plugin_forms
				GROUP BY `form_type`";
		$result = $pWpDb->get_results($sql, ARRAY_A);
		$returnValues = array();

		foreach ($result as $row)
		{
			$returnValues[$row['form_type']] = $row['count'];
		}

		$returnValues['all'] = array_sum($returnValues);

		return $returnValues;
	}
}
