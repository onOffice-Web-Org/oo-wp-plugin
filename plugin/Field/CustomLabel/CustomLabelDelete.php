<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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

namespace onOffice\WPlugin\Field\CustomLabel;

use onOffice\WPlugin\Field\CustomLabel\Exception\CustomLabelDeleteException;
use wpdb;


/**
 *
 */
class CustomLabelDelete
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
	 * @param int $formId
	 * @param array $fieldNames
	 * @throws CustomLabelDeleteException
	 */
	public function deleteByFormIdAndFieldNames(int $formId, array $fieldNames, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField)
	{
		if ($fieldNames === []) {
			return;
		}

		$query = $this->getBaseDeleteQuery($pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField) . " WHERE "
			. "{$this->_pWPDB->prefix}$pCustomsLabelConfigurationField.form_id = '" . esc_sql($formId) . "' AND "
			. "{$this->_pWPDB->prefix}$pCustomsLabelConfigurationField.fieldname IN('"
			. implode("', '", esc_sql($fieldNames))
			. "')";

		if (false === $this->_pWPDB->query($query)) {
			throw new CustomLabelDeleteException();
		}
	}

	/**
	 * @param int $defaultId
	 * @throws CustomLabelDeleteException
	 */
	public function deleteSingleCustomLabelById(int $defaultId, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField)
	{
		$query = $this->getBaseDeleteQuery($pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField) . " WHERE "
			. "{$this->_pWPDB->prefix}$pCustomsLabelConfigurationField.customs_labels_id = %d";

		if (!$this->_pWPDB->query($this->_pWPDB->prepare($query, $defaultId))) {
			throw new CustomLabelDeleteException();
		}
	}

	/**
	 * @param int $formId
	 * @param string $fieldname
	 * @throws CustomLabelDeleteException
	 */
	public function deleteSingleCustomLabelByFieldname(int $formId, string $fieldname, string $locale = null, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField)
	{
		$query = $this->getBaseDeleteQuery($pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField) . " WHERE "
			. "{$this->_pWPDB->prefix}$pCustomsLabelConfigurationField.form_id = %d AND "
			. "{$this->_pWPDB->prefix}$pCustomsLabelConfigurationField.fieldname = %s AND "
			. "{$this->_pWPDB->prefix}$pCustomsLabelConfigurationField.locale = %s";

		if (false === $this->_pWPDB->query($this->_pWPDB->prepare($query, $formId, $fieldname, $locale))) {
			throw new CustomLabelDeleteException();
		}
	}

	/**
	 * @return string
	 */
	private function getBaseDeleteQuery($pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField): string
	{
		$prefix = $this->_pWPDB->prefix;
		return "DELETE {$prefix}$pCustomsLabelConfigurationField, {$prefix}$pTranslateLabelConfigurationField "
			. "FROM {$prefix}$pCustomsLabelConfigurationField "
			. "INNER JOIN {$prefix}$pTranslateLabelConfigurationField "
			. "ON {$prefix}$pCustomsLabelConfigurationField.customs_labels_id = {$prefix}$pTranslateLabelConfigurationField.input_id";
	}
}
