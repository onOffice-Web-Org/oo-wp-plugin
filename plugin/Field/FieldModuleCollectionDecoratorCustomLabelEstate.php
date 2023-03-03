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

use function __;
use DI\Container;
use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Record\RecordManager;

use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelRead;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2021, onOffice(R) GmbH
 *
 */
class FieldModuleCollectionDecoratorCustomLabelEstate
	extends FieldModuleCollectionDecoratorAbstract
{
	/** @var Container */
	private $_pContainer;

	/** @var array */
	private $_fieldCustomLabels = [];

	/** @var FieldModuleCollection */
	private $_pFieldModuleCollection = null;

	public function __construct(FieldModuleCollection $pFieldModuleCollection, $formName, string $typeList = null, Container $pContainer = null)
	{
		parent::__construct($pFieldModuleCollection);
		$this->_pFieldModuleCollection = $pFieldModuleCollection;
		$this->_pContainer = $pContainer ?? $this->buildContainer();
		$recordManagerReadForm = $this->_pContainer->get(RecordManagerReadListViewEstate::class);
		if($typeList == null){
			$results = $recordManagerReadForm->getRowByName($formName, $typeList);
		} else {
			$results = $recordManagerReadForm->getRowByName($formName);
		}
		$fieldsByFormIds = $recordManagerReadForm->getFieldconfigByListviewId( intval( $results['listview_id'] ?? 0 ) );

		foreach ($fieldsByFormIds as $fieldsByFormId) {
			$lang = $this->_pContainer->get(Language::class);
			$customLabelRead = $this->_pContainer->get(CustomLabelRead::class);
			$query = $customLabelRead->readCustomLabelByFormIdAndFieldName(intval($results['listview_id']),
				$fieldsByFormId['fieldname'],
				$lang->getLocale(),RecordManager::TABLENAME_FIELDCONFIG_ESTATE_CUSTOMS_LABELS, RecordManager::TABLENAME_FIELDCONFIG_ESTATE_TRANSLATED_LABELS);
			if (empty($query[0]->value)) {
				continue;
			}
			$this->_fieldCustomLabels[onOfficeSDK::MODULE_ESTATE][$fieldsByFormId['fieldname']] = $query[0]->value;
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
