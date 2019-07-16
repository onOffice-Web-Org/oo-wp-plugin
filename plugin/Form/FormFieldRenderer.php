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

namespace onOffice\WPlugin\Form;

use onOffice\WPlugin\Form;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * renders a simple form field (no compound field)
 *
 * @author ana
 *
 */

class FormFieldRenderer
{

	/** @var Form */
	private $_pForm = null;

	/** @var string */
	private $_field = null;

	/** @var string */
	private $_typeInput = null;

	/** @var array */
	private $_permittedValues = [];

	/** @var string */
	private $_selectedValue = null;

	/** @var bool */
	private $_isRangeValue = true;

	/** @var string */
	private $_requiredAttribute = null;


	/**
	 *
	 * @param Form $pForm
	 *
	 */

	public function __construct(Form $pForm, string $field, $searchCriteriaRange = true)
	{
		$this->_pForm = $pForm;
		$this->_field = $field;
		$this->config($searchCriteriaRange);
	}


	/**
	 *
	 * @param bool $searchCriteriaRange
	 *
	 */

	private function config(bool $searchCriteriaRange)
	{
		$this->_isRangeValue =
				$this->_pForm->isSearchcriteriaField($this->_field) && $searchCriteriaRange;

		$this->setSelectedValue();
		$this->setRequiredAttribute();
		$this->setPermittedValues();
		$this->setTypeInput();
	}


	/**
	 *
	 */

	private function setTypeInput()
	{
		if ($this->_field == 'range')
		{
			$this->_typeInput = FieldTypes::FIELD_TYPE_INTEGER;
		}
		else
		{
			$this->_typeInput = $this->_pForm->getFieldType($this->_field);
		}
	}


	/**
	 *
	 */

	private function setPermittedValues()
	{
		$this->_permittedValues = $this->_pForm->getPermittedValues(
				$this->_field, true);
	}


	/**
	 *
	 */

	private function setRequiredAttribute()
	{
		$this->_requiredAttribute = $this->_pForm->isRequiredField($this->_field) ?
			'required ' : '';
	}


	/**
	 *
	 */

	private function setSelectedValue()
	{
		$this->_selectedValue = $this->_pForm->getFieldValue($this->_field, true);
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function renderSingleselect(): string
	{
		$fieldLabel = $this->_pForm->getFieldLabel($this->_field, true);
		$output = '<select size="1" name="'.esc_html($this->_field).'">';
		/* translators: %s will be replaced with the translated field name. */
		$output .= '<option value="">'.esc_html(sprintf(__('Choose %s', 'onoffice'), $fieldLabel)).'</option>';
		foreach ($this->_permittedValues as $key => $value) {
			if (is_array($this->_selectedValue)) {
				$isSelected = in_array($key, $this->_selectedValue, true);
			} else {
				$isSelected = $this->_selectedValue == $key;
			}
			$output .= '<option value="'.esc_attr($key).'"'.($isSelected ? ' selected' : '').'>'
				.esc_html($value).'</option>';
		}
		$output .= '</select>';

		return $output;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function renderMultiselect(): string
	{
		$output = '<div data-name="'.esc_attr($this->_field).'" class="multiselect" data-values="'
				.esc_attr(json_encode($this->_permittedValues)).'" data-selected="'
				.esc_attr(json_encode($this->_selectedValue)).'">
				<input type="button" class="onoffice-multiselect-edit" value="'
					.esc_html__('Edit values', 'onoffice').'"></div>';

		return $output;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function renderSimple(): string
	{
		$inputType = 'type="text" ';
		$value = 'value="'.esc_attr($this->_pForm->getFieldValue($this->_field, true)).'"';
		$output = '';

		if ($this->_typeInput == FieldTypes::FIELD_TYPE_BOOLEAN) {
			$inputType = 'type="checkbox" ';
			$value = 'value="1" '.($this->_pForm->getFieldValue($this->_field, true) == 1 ? 'checked="checked"' : '');
		} elseif ($this->_typeInput === FieldTypes::FIELD_TYPE_FLOAT ||
			$this->_typeInput === 'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:float') {
			$inputType = 'type="number" step="0.01" ';
		} elseif ($this->_typeInput === FieldTypes::FIELD_TYPE_INTEGER ||
				$this->_typeInput === 'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:decimal') {
			$inputType = 'type="number" step="1" ';
		}

		if ($this->_isRangeValue && $this->_pForm->inRangeSearchcriteriaInfos($this->_field) &&
			count($this->_pForm->getSearchcriteriaRangeInfosForField($this->_field)) > 0) {

			foreach ($this->_pForm->getSearchcriteriaRangeInfosForField($this->_field) as $key => $rangeDescription) {
				$value = 'value="'.esc_attr($this->_pForm->getFieldValue($key, true)).'"';
				$output .= '<input '.$inputType.$this->_requiredAttribute.' name="'.esc_attr($key).'" '
					.$value.' placeholder="'.esc_attr($rangeDescription).'">';
			}
		} else {
			$output .= '<input '.$inputType.$this->_requiredAttribute.' name="'.esc_attr($this->_field).'" '.$value.'>';
		}

		return $output;
	}



	/**
	 *
	 * @return string
	 *
	 */

	public function render(): string
	{
		$output = null;

		if ((FieldTypes::FIELD_TYPE_SINGLESELECT == $this->_typeInput &&
				!$this->_isRangeValue) ||
			in_array($this->_field, array('objektart', 'range_land', 'vermarktungsart')))
		{
			$output = $this->renderSingleselect();
		}
		elseif (FieldTypes::FIELD_TYPE_MULTISELECT === $this->_typeInput ||
			(FieldTypes::FIELD_TYPE_SINGLESELECT === $this->_typeInput &&
			 $this->_isRangeValue))
		{
			$output = $this->renderMultiselect();
		}
		else
		{
			$output = $this->renderSimple();
		}

		return $output;
	}



}