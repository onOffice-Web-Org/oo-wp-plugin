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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerInsertForm
	extends RecordManager
{
	/**
	 *
	 * @param array $values
	 * @return int
	 *
	 */

	public function insertByRow($values)
	{
		$pWpDb = $this->getWpdb();
		$row = $values[self::TABLENAME_FORMS];

		$pWpDb->insert($pWpDb->prefix.self::TABLENAME_FORMS, $row);
		$formId = $pWpDb->insert_id;

		return $formId;
	}


	/**
	 *
	 * @param array $values
	 * @return bool
	 *
	 */

	public function insertAdditionalValues(array $values)
	{
		$pWpDb = $this->getWpdb();

		unset($values[self::TABLENAME_FORMS]);
		$result = true;

		foreach ($values as $table => $tablevalues) {
			foreach ($tablevalues as $tablerow) {
				$result = $result && $pWpDb->insert($pWpDb->prefix.$table, $tablerow);
			}
		}

		return $result;
	}
}
