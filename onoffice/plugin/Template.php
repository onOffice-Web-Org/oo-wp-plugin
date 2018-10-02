<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

namespace onOffice\WPlugin;

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
	const KEY_BASICDATA = 'basicdata';

	/** */
	const KEY_ADDRESSLIST = 'addresslist';


	/** @var EstateList */
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
	 * @param string $defaultTemplateName
	 *
	 */

	public function __construct($templateName)
	{
		$this->_templateName = $templateName;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function render()
	{
		$templateData = array(
			self::KEY_FORM => $this->_pForm,
			self::KEY_ESTATELIST => $this->_pEstateList,
			self::KEY_BASICDATA => new Impressum(),
			self::KEY_ADDRESSLIST => $this->_pAddressList,
		);
		$filename = $this->buildFilePath();

		$result = '';
		if ( file_exists( $filename ) ) {
			$result = self::getIncludeContents($templateData, $filename);
		}
		return $result;
	}


	/**
	 *
	 * Method that provides important variables to template
	 * Must not expose $this
	 *
	 * @return string
	 *
	 */

	private static function getIncludeContents(array $templateData, $templatePath)
	{
		// vars which might be used in template
		$pEstates = $templateData[self::KEY_ESTATELIST];
		$pForm = $templateData[self::KEY_FORM];
		$pBasicData = $templateData[self::KEY_BASICDATA];
		$pAddressList = $templateData[self::KEY_ADDRESSLIST];
		ob_start();
		include $templatePath;
		return ob_get_clean();
	}


	/**
	 *
	 * @param string $templateName
	 * @return string
	 *
	 */

	private function buildFilePath()
	{
		return ConfigWrapper::getTemplateBasePath().'/'.$this->_templateName;
	}

	/** @param AddressList $pAddressList */
	public function setAddressList(AddressList $pAddressList)
		{ $this->_pAddressList = $pAddressList; }

	/** @param EstateList $pEstateList */
	public function setEstateList(EstateList $pEstateList)
		{ $this->_pEstateList = $pEstateList; }

	/** @param Form $pForm */
	public function setForm(Form $pForm)
		{ $this->_pForm = $pForm; }
}
