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

namespace onOffice\WPlugin\Renderer;

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\WPlugin\Field\FieldModuleCollection;
use onOffice\WPlugin\Field\FieldnamesEnvironment;
use onOffice\WPlugin\Fieldnames;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class InputFieldRenderer
{
	/** @var string */
	private $_type;

	/** @var string */
	private $_name;

	/** @var mixed */
	private $_value = null;

	/** @var array  */
	private $_additionalAttributes = [];

	/** @var int */
	private static $_guiId = 0;

	/** @var string */
	private $_oOModule = '';

	/** @var string */
	private $_hint = '';

	/** @var string */
	private $_label = null;

	/** @var array */
	private $_checkedValues = [];

	/** @var array */
	private $_selectedValue = [];
	
	/** @var int */
	private $_minValue = 0;

	/** @var int */
	private $_maxValue = 0;

	/** @var bool */
    private $_isDisabled = false;
	
	/**
	 *
	 * @param string $type
	 * @param string $name
	 * @param mixed $value
	 *
	 */

	public function __construct($type, $name, $value = null)
	{
		$this->_type = $type;
		$this->_name = $name;
		$this->_value = $value;

		self::$_guiId++;
	}


	/**
	 *
	 */

	abstract public function render();


	/**
	 *
	 * @param string $name
	 * @param string $value
	 *
	 */

	public function addAdditionalAttribute($name, $value)
	{
		if (array_key_exists($name, $this->_additionalAttributes) &&
				$this->_additionalAttributes[$name] != '' &&
				$name == 'class') {
			$this->_additionalAttributes[$name] .= ' '.$value;
		} else {
			$this->_additionalAttributes[$name] = $value;
		}
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getGuiId(): string
	{
		return $this->_type.'_'.self::$_guiId;
	}


	/**
	 * @param FieldModuleCollection $pExtraFieldsCollection
	 * @param bool $inactiveOnly
	 * @param FieldnamesEnvironment|null $pEnvironment
	 *
	 * @return Fieldnames
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws Exception
	 */

	protected function getFieldnames(
		FieldModuleCollection $pExtraFieldsCollection,
		bool $inactiveOnly = false,
		FieldnamesEnvironment $pEnvironment = null
	): Fieldnames {
		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions( ONOFFICE_DI_CONFIG_PATH );

		return $pContainerBuilder->build()->make( Fieldnames::class, [
			'pExtraFieldsCollection' => $pExtraFieldsCollection,
			'inactiveOnly'           => $inactiveOnly,
			'pEnvironment'           => $pEnvironment
		] );
	}


	/**
	 *
	 * @return string
	 *
	 */

	protected function renderAdditionalAttributes(): string
	{
		$outputValues = array_map(function(string $name, string $value): string {
			return esc_html($name).'="'.esc_html($value).'"';
		}, array_keys($this->_additionalAttributes), $this->_additionalAttributes);

		return implode(' ', $outputValues);
	}


	/**
	 *
	 * For testing only!
	 *
	 */

	public static function resetGuiId()
	{
		self::$_guiId = 0;
	}


	/** @return string */
	public function getName()
		{ return $this->_name; }

	/** @return mixed */
	public function getValue()
		{ return $this->_value; }

	/** @param mixed $value */
	public function setValue($value)
		{ $this->_value = $value; }

	/** @return string */
	public function getType()
		{ return $this->_type;}

	/** @param string $module */
	public function setOoModule(string $module)
		{ $this->_oOModule = $module; }

	/** @return string */
	public function getOoModule(): string
		{ return $this->_oOModule; }

	/** @return string */
	public function getHint()
	{ return $this->_hint; }

	/** @param string $hint */
	public function setHint(string $hint)
	{ $this->_hint = $hint; }

	/** @return string */
	public function getLabel()
		{ return $this->_label; }

	/** @param string $label */
	public function setLabel( $label )
		{ $this->_label = $label; }

	/** @param $checkedValues */
	public function setCheckedValues($checkedValues)
		{ $this->_checkedValues = $checkedValues;}

	/** @return array */
	public function getCheckedValues()
		{ return $this->_checkedValues; }

	/** @param string $selectedValue */
	public function setSelectedValue($selectedValue)
		{ $this->_selectedValue = $selectedValue; }

	/** @return array */
	public function getSelectedValue()
		{ return $this->_selectedValue; }

	/**@return int */
	public function getMaxValue(): int
		{ return $this->_maxValue; }

	/** @param int $maxValue */
	public function setMaxValue(int $maxValue)
		{ $this->_maxValue = $maxValue; }

	/**@return int */
	public function getMinValue(): int
		{ return $this->_minValue; }

	/** @param int $minValue */
	public function setMinValue(int $minValue)
		{ $this->_minValue = $minValue; }

    /** @return bool */
    public function getIsDisabled()
    { return $this->_isDisabled; }

    /** @param bool $isDisabled */
    public function setIsDisabled(bool $isDisabled)
    { $this->_isDisabled = $isDisabled; }
}
