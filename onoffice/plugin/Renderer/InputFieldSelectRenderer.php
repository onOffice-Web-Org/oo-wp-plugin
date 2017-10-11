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

	/**
	 *
	 * @param string $name
	 * @param array $value
	 *
	 */

	public function __construct($name, $values)
	{
		parent::__construct('select', $name, $values);
	}


	/**
	 *
	 */
	
	public function render()
	{
		echo '<select name="'.esc_html($this->getName())
			 .$this->renderAdditionalAttributes()
			 .'>';

		foreach ($values as $value => $label)
		{
			echo '<option value="'.esc_html($label).'">'.esc_html($label).'</option>';
		}

		echo '</select>';
	}
}
