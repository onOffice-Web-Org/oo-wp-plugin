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

declare (strict_types=1);

namespace onOffice\WPlugin\Model;

use Closure;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModel
{
	/** @var string */
	private $_groupSlug = '';

	/** @var string */
	private $_pageSlug = '';

	/** @var string Label of section */
	private $_label = '';

	/** @var InputModelBase[] */
	private $_inputModels = [];

	/** @var bool Use this if you need the value but displaying is discouraged */
	private $_isInvisibleForm = false;

	/** @var Closure */
	private $_pTextCallback = null;

	/** @var string */
	private $_ooModule = '';


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pTextCallback = function() {};
	}


	/** @param InputModelBase $pInputModel */
	public function addInputModel(InputModelBase $pInputModel)
		{ $this->_inputModels []= $pInputModel; }

	/** @return string */
	public function getGroupSlug(): string
		{ return $this->_groupSlug; }

	/** @param string $groupSlug */
	public function setGroupSlug(string $groupSlug)
		{ $this->_groupSlug = $groupSlug; }

	/** @return InputModelBase[] */
	public function getInputModel(): array
		{ return $this->_inputModels; }

	/** @return string */
	public function getPageSlug(): string
		{ return $this->_pageSlug; }

	/** @return string */
	public function getLabel(): string
		{ return $this->_label; }

	/** @param string $pageSlug */
	public function setPageSlug(string $pageSlug)
		{ $this->_pageSlug = $pageSlug; }

	/** @param string $label */
	public function setLabel(string $label)
		{ $this->_label = $label; }

	/** @return bool */
	public function getIsInvisibleForm(): bool
		{ return $this->_isInvisibleForm; }

	/** @param bool $isInvisibleForm */
	public function setIsInvisibleForm(bool $isInvisibleForm)
		{ $this->_isInvisibleForm = $isInvisibleForm; }

	/** @return Closure */
	public function getTextCallback(): Closure
		{ return $this->_pTextCallback; }

	/** @param Closure $pTextCallback */
	public function setTextCallback(Closure $pTextCallback)
		{ $this->_pTextCallback = $pTextCallback; }

	/** @param string $module */
	public function setOoModule(string $module)
		{ $this->_ooModule = $module; }

	/** @return string */
	public function getOoModule(): string
		{ return $this->_ooModule; }
}
