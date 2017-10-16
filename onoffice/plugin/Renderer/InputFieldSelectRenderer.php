<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace onOffice\WPlugin\Renderer;

/**
 * Description of InputFieldSelectRenderer
 *
 * @author ana
 */
class InputFieldSelectRenderer
	extends InputFieldRenderer
{

	/** @var bool */
	private $_multiple = false;

	/** @var string */
	private $_selectedValue = null;

	/**
	 *
	 * @param string $name
	 * @param array $value
	 *
	 */

	public function __construct($name, $value = array())
	{
		parent::__construct('select', $name, $value);
	}


	/**
	 *
	 */

	public function render()
	{
		echo '<select name="'.esc_html($this->getName()).'" '
			 .($this->_multiple ? ' multiple = "multiple" ' : null)
			 .$this->renderAdditionalAttributes()
			 .'>';

		foreach ($this->getValue() as $key => $label)
		{
			echo '<option value="'.esc_html($key).'" '
				.($key == $this->_selectedValue ? ' selected="selected" ' : null)
				.' >'
				.esc_html($label)
				.'</option>';
		}
		
		echo '</select>';
	}


	/**
	 *
	 * @param string $selectedValue
	 *
	 */

	public function setSelectedValue($selectedValue)
	 { $this->_selectedValue = $selectedValue; }


	/** @param string $selectedValue */
	public function getSelectedValue()
	 { return $this->_selectedValue; }
}
