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

declare(strict_types=1);

namespace onOffice\WPlugin\Field;

use DI\Container;
use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Record\RecordManager;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelRead;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Types\Field;

use function __;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2021, onOffice(R) GmbH
 *
 */
class FieldModuleCollectionDecoratorCustomLabelForm
	extends FieldModuleCollectionDecoratorAbstract
{
	/** @var Container */
	private $_pContainer;

	/** @var array */
	private $_fieldCustomLabels = [];

	/** @var FieldModuleCollection */
	private $_pFieldModuleCollection = null;


	public function __construct(FieldModuleCollection $pFieldModuleCollection, $formName, Container $pContainer = null)
	{
		parent::__construct($pFieldModuleCollection);
		$this->_pFieldModuleCollection = $pFieldModuleCollection;
		$this->_pContainer = $pContainer ?? $this->buildContainer();
		$recordManagerReadForm = $this->_pContainer->get(RecordManagerReadForm::class);
		$results = $recordManagerReadForm->getRowByName($formName);
		$fieldsByFormIds = $recordManagerReadForm->readFieldsByFormId(intval($results['form_id']));
		$fieldNames = array_column($fieldsByFormIds, 'fieldname');
		$customLabelRead = $this->_pContainer->get(CustomLabelRead::class);
		$lang = $this->_pContainer->get(Language::class);
		$labelsByField = $customLabelRead->readCustomLabelsByFormIdAndFieldNames(
			intval($results['form_id']),
			$fieldNames,
			$lang->getLocale(),
			RecordManager::TABLENAME_FIELDCONFIG_FORM_CUSTOMS_LABELS,
			RecordManager::TABLENAME_FIELDCONFIG_FORM_TRANSLATED_LABELS
		);
		foreach ($fieldsByFormIds as $fieldsByFormId) {
			$value = $labelsByField[$fieldsByFormId['fieldname']] ?? null;
			if (empty($value)) {
				continue;
			}
			$allowedModules = [onOfficeSDK::MODULE_ADDRESS, onOfficeSDK::MODULE_SEARCHCRITERIA, onOfficeSDK::MODULE_ESTATE];
			$module = in_array($fieldsByFormId['module'], $allowedModules, true) ? $fieldsByFormId['module'] : '';
			$this->_fieldCustomLabels[$module][$fieldsByFormId['fieldname']] = $value;
		}
	}

	/**
	 *
	 * @return Field[]
	 *
	 */

	public function getAllFields(): array
	{
		$fields = parent::getAllFields();
		$cloneFields = array();
		foreach ($fields as $key => $field) {
			$cloneFields[$key] = clone $field;
			$module = $cloneFields[$key]->getModule();
			$name = $cloneFields[$key]->getName();
			$label = $this->_fieldCustomLabels[$module][$name] ?? null;

			if ($label !== null) {
				$cloneFields[$key]->setLabel($label);
			}
		}
		return $cloneFields;
	}

	/**
	 * @return Container
	 * @throws \Exception
	 */
	private function buildContainer(): Container
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		return $pContainerBuilder->build();
	}

	/**
	 *
	 * @return array
	 *
	 */

	public function getFieldCustomLabels(): array
	{
		return $this->_fieldCustomLabels;
	}

}
