<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

namespace onOffice\WPlugin\Field\Collection;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class FieldsCollectionConfiguratorForm
{
	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return FieldsCollection
	 */
	public function configureForApplicantSearchForm(FieldsCollection $pFieldsCollection): FieldsCollection
	{
		$pFieldsCollectionNew = new FieldsCollection;
		foreach ($pFieldsCollection->getAllFields() as $pField) {
			$pFieldClone = clone $pField;
			$pFieldClone->setIsRangeField(false);
			$pFieldsCollectionNew->addField($pFieldClone);
		}
		return $pFieldsCollectionNew;
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return FieldsCollection
	 */
	public function configureForInterestForm(FieldsCollection $pFieldsCollection): FieldsCollection
	{
		$pFieldsCollectionNew = new FieldsCollection;
		foreach ($pFieldsCollection->getAllFields() as $pField) {
			$pFieldClone = clone $pField;
			if ($pFieldClone->getModule() === onOfficeSDK::MODULE_SEARCHCRITERIA &&
				$pFieldClone->getType() === FieldTypes::FIELD_TYPE_SINGLESELECT &&
				!in_array($pField->getName(), ['objektart', 'range_land', 'vermarktungsart']))
			{
				$pFieldClone->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
			}
			$pFieldsCollectionNew->addField($pFieldClone);
		}
		return $pFieldsCollectionNew;
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @param string $formType
	 * @return FieldsCollection
	 */
	public function buildForFormType(FieldsCollection $pFieldsCollection, string $formType): FieldsCollection
	{
		if ($formType === Form::TYPE_APPLICANT_SEARCH) {
			return $this->configureForApplicantSearchForm($pFieldsCollection);
		} elseif ($formType === Form::TYPE_INTEREST) {
			return $this->configureForInterestForm($pFieldsCollection);
		}
		return $pFieldsCollection;
	}
}