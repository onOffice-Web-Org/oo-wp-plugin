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

namespace onOffice\WPlugin\Model;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModel
{
	/** @var string */
	private $_groupSlug = null;

	/** @var string */
	private $_pageSlug = null;

	/** @var string Label of section */
	private $_label = null;

	/** @var InputModelBase[] */
	private $_inputModels = array();

	/** @param InputModelBase $pInputModel */
	public function addInputModel(InputModelBase $pInputModel)
		{ $this->_inputModels []= $pInputModel; }

	/** @return string */
	public function getGroupSlug()
		{ return $this->_groupSlug; }

	/** @param string $groupSlug */
	public function setGroupSlug($groupSlug)
		{ $this->_groupSlug = $groupSlug; }

	/** @return onOffice\WPlugin\Model\InputModelBase */
	public function getInputModel()
		{ return $this->_inputModels; }

	/** @return string */
	public function getPageSlug()
		{ return $this->_pageSlug; }

	/** @return string */
	public function getLabel()
		{ return $this->_label; }

	/** @param string $pageSlug */
	public function setPageSlug($pageSlug)
		{ $this->_pageSlug = $pageSlug; }

	/** @param string $label */
	public function setLabel($label)
		{ $this->_label = $label; }
}
