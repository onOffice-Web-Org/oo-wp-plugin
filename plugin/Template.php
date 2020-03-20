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

namespace onOffice\WPlugin;

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Controller\EstateListBase;
use onOffice\WPlugin\Template\TemplateCallbackBuilder;
use const WP_PLUGIN_DIR;

/**
 *
 */

class Template
{
	/** */
	const KEY_ESTATELIST = 'estatelist';

	/** */
	const KEY_FORM = 'form';

	/** */
	const KEY_ADDRESSLIST = 'addresslist';

	/** @var EstateListBase */
	private $_pEstateList = null;

	/** @var string */
	private $_templateName = null;

	/** @var Form */
	private $_pForm = null;

	/** @var AddressList */
	private $_pAddressList = null;


	/**
	 *
	 * @param string $templateName
	 *
	 */

	public function __construct(string $templateName)
	{
		$this->_templateName = $templateName;
	}

	/**
	 *
	 * @return string
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function render(): string
	{
		$templateData = [
			self::KEY_FORM => $this->_pForm,
			self::KEY_ESTATELIST => $this->_pEstateList,
			self::KEY_ADDRESSLIST => $this->_pAddressList,
		];
		$filename = $this->buildFilePath();
		$result = '';

		if (file_exists($filename)) {
			$pDIContainerBuilder = new ContainerBuilder;
			$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
			$pContainer = $pDIContainerBuilder->build();
			$result = self::getIncludeContents($templateData, $filename, $pContainer);
		}

		return $result;
	}


	/** @return string */
	protected function getTemplateName(): string
	{ return $this->_templateName; }

	/**
	 *
	 * Method that provides important variables to template
	 * Must not expose $this
	 *
	 * @param array $templateData
	 * @param string $templatePath
	 * @param Container $pContainer
	 * @return string
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	private static function getIncludeContents(array $templateData, $templatePath, Container $pContainer)
	{
		// vars which might be used in template
		$pEstates = $templateData[self::KEY_ESTATELIST];
		$pForm = $templateData[self::KEY_FORM];
		$pAddressList = $templateData[self::KEY_ADDRESSLIST];
		/** @var TemplateCallbackBuilder $pTemplateCallback */
		$pTemplateCallback = $pContainer->get(TemplateCallbackBuilder::class);
		$generateSortDropDown = $pTemplateCallback->buildCallbackListSortDropDown($pEstates);
		unset($templateData);
		unset($pTemplateCallback);
		unset($pContainer);

		ob_start();
		include $templatePath;
		return ob_get_clean();
	}


	/**
	 *
	 * @return string
	 *
	 */

	protected function buildFilePath(): string
	{
		return WP_PLUGIN_DIR.'/'.$this->_templateName;
	}


	/**
	 *
	 * @param string $templateName
	 * @return self
	 *
	 */

	public function withTemplateName(string $templateName): self
	{
		$pNewTemplate = clone $this;
		$pNewTemplate->_templateName = $templateName;
		return $pNewTemplate;
	}


	/**
	 *
	 * @param Form $pForm
	 * @return $this
	 *
	 */

	public function setForm(Form $pForm): self
	{
		$this->_pForm = $pForm;
		return $this;
	}


	/**
	 *
	 * @param AddressList $pAddressList
	 * @return $this
	 *
	 */

	public function setAddressList(AddressList $pAddressList): self
	{
		$this->_pAddressList = $pAddressList;
		return $this;
	}


	/**
	 *
	 * @param EstateList $pEstateList
	 * @return $this
	 *
	 */

	public function setEstateList(EstateListBase $pEstateList): self
	{
		$this->_pEstateList = $pEstateList;
		return $this;
	}

}