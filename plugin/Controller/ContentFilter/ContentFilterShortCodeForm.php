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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\Form\FormBuilder;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\Utility\Logger;
use function shortcode_atts;

class ContentFilterShortCodeForm
	implements ContentFilterShortCode
{
	/** @var Logger */
	private $_pLogger;

	/** @var DataFormConfigurationFactory */
	private $_pDataFormConfigurationFactory;

	/** @var Template */
	private $_pTemplate;

	/** @var FormBuilder */
	private $_pFormBuilder;

	/**
	 * @param Template $pTemplate
	 * @param DataFormConfigurationFactory $pDataFormConfigurationFactory
	 * @param Logger $pLogger
	 * @param FormBuilder $pFormBuilder
	 */
	public function __construct(
		Template $pTemplate,
		DataFormConfigurationFactory $pDataFormConfigurationFactory,
		Logger $pLogger,
		FormBuilder $pFormBuilder)
	{
		$this->_pTemplate = $pTemplate;
		$this->_pLogger = $pLogger;
		$this->_pDataFormConfigurationFactory = $pDataFormConfigurationFactory;
		$this->_pFormBuilder = $pFormBuilder;
	}

	/**
	 * @param array $attributesInput
	 * @return string
	 */
	public function replaceShortCodes(array $attributesInput): string
	{
		$attributes = shortcode_atts([
			'form' => ''
		], $attributesInput);

		try {
			$pFormConfig = $this->_pDataFormConfigurationFactory->loadByFormName($attributes['form']);
			/* @var $pFormConfig DataFormConfiguration */
			$template = $pFormConfig->getTemplate();
			$pTemplate = $this->_pTemplate->withTemplateName($template);
			$pForm = $this->_pFormBuilder->build($attributes['form'], $pFormConfig->getFormType());
			return $pTemplate->withForm($pForm)->render();
		} catch (Exception $pException) {
			return $this->_pLogger->logErrorAndDisplayMessage($pException);
		}
	}

	/**
	 * @return string
	 */
	public function getTag(): string
	{
		return 'oo_form';
	}
}
