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
	 * @param DataFormConfiguration $pDataFormConfiguration
	 * @return int
	 *
	 */

	public function insertByDataFormConfiguration(DataFormConfiguration $pDataFormConfiguration)
	{
		$rowMain = array(
			'template' => $pDataFormConfiguration->getTemplate(),
			'name' => $pDataFormConfiguration->getFormName(),
			'form_type' => $pDataFormConfiguration->getFormType(),
		);

		if ($pDataFormConfiguration instanceof DataFormConfigurationApplicantSearch) {
			$rowMain += array(
				'limitresults' => $pDataFormConfiguration->getLimitResults(),
			);
		} elseif ($pDataFormConfiguration instanceof DataFormConfigurationContact) {
			$rowMain += array(
				'checkduplicates' => $pDataFormConfiguration->getCheckDuplicateOnCreateAddress(),
				'createaddress' => $pDataFormConfiguration->getCreateAddress(),
				'recipient' => $pDataFormConfiguration->getRecipient(),
				'subject' => $pDataFormConfiguration->getSubject(),
			);
		}

		$newFormId = $this->insertByRow(array(self::TABLENAME_FORMS => $rowMain));
		return $newFormId;
	}


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
}
