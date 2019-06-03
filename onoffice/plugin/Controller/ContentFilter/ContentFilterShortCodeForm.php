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

namespace onOffice\WPlugin\Controller\ContentFilter;

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCode;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\Field\DistinctFieldsChecker;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormBuilder;
use onOffice\WPlugin\Impressum;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\Utility\Logger;
use function shortcode_atts;

/**
 *
 */

class ContentFilterShortCodeForm
	implements ContentFilterShortCode
{
	/** @var Logger */
	private $_pLogger = null;

	/** @var DataFormConfigurationFactory */
	private $_pDataFormConfigurationFactory = null;

	/** @var Template */
	private $_pTemplate = null;

	/** @var DistinctFieldsChecker */
	private $_pDistinctFieldsChecker = null;

	/** @var Form\FormBuilder */
	private $_pFormBuilder = null;


	/**
	 *
	 * @param Template $pTemplate
	 * @param DataFormConfigurationFactory $pDataFormConfigurationFactory
	 * @param Logger $pLogger
	 * @param DistinctFieldsChecker $pDistinctFieldsChecker
	 *
	 */

	public function __construct(
		Template $pTemplate = null,
		DataFormConfigurationFactory $pDataFormConfigurationFactory = null,
		Logger $pLogger = null,
		DistinctFieldsChecker $pDistinctFieldsChecker = null,
		FormBuilder $pFormBuilder = null)
	{
		$this->_pTemplate = $pTemplate ?? (new Template(''))->setImpressum(new Impressum);
		$this->_pLogger = $pLogger ?? new Logger;
		$this->_pDataFormConfigurationFactory = $pDataFormConfigurationFactory ?? new DataFormConfigurationFactory;
		$this->_pDistinctFieldsChecker = $pDistinctFieldsChecker ?? new DistinctFieldsChecker();
		$this->_pFormBuilder = $pFormBuilder ?? new FormBuilder();
	}


	/**
	 *
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function replaceShortCodes(array $attributesInput): string
	{
		$attributes = shortcode_atts([
			'form' => '',
		], $attributesInput);

		$formName = $attributes['form'];

		try {
			$pFormConfig = $this->_pDataFormConfigurationFactory->loadByFormName($formName);

			if ($pFormConfig->getFormType() == Form::TYPE_APPLICANT_SEARCH) {
				$availableOptionsFormApplicantSearch = $pFormConfig->getAvailableOptionsFields();
				$this->_pDistinctFieldsChecker->registerScripts
					(onOfficeSDK::MODULE_SEARCHCRITERIA, $availableOptionsFormApplicantSearch);
			}

			/* @var $pFormConfig DataFormConfiguration */
			$template = $pFormConfig->getTemplate();
			$this->_pTemplate->getImpressum()->load();
			$pTemplate = $this->_pTemplate->withTemplateName($template);
			$pForm = $this->_pFormBuilder->build($formName, $pFormConfig->getFormType());
			return $pTemplate->setForm($pForm)->render();
		} catch (Exception $pException) {
			return $this->_pLogger->logErrorAndDisplayMessage($pException);
		}
	}


	/**
	 *
	 * @return Logger
	 *
	 */

	public function getLogger(): Logger
	{
		return $this->_pLogger;
	}


	/**
	 *
	 * @return DataFormConfigurationFactory
	 *
	 */

	public function getDataFormConfigurationFactory(): DataFormConfigurationFactory
	{
		return $this->_pDataFormConfigurationFactory;
	}


	/**
	 *
	 * @return Template
	 *
	 */

	public function getTemplate(): Template
	{
		return $this->_pTemplate;
	}


	/**
	 *
	 * @return DistinctFieldsChecker
	 *
	 */

	public function getDistinctFieldsChecker(): DistinctFieldsChecker
	{
		return $this->_pDistinctFieldsChecker;
	}


	/**
	 *
	 * @return FormBuilder
	 *
	 */

	public function getFormBuilder(): FormBuilder
	{
		return $this->_pFormBuilder;
	}
}