<?php

namespace onOffice\WPlugin\Renderer;

class InputFieldMultipleSelectTwoRenderer extends InputFieldSelectRenderer {

	/** @var boolean */
	private $_multiple = true;

	/**
	 * @return void
	 */
	public function render()
	{
		$name = $this->getMultiple() ? $this->getName() . '[]' : $this->getName();
		$output = '<select class="custom-multi-select2" name="'.esc_html($name).'"'
			  .$this->renderAdditionalAttributes()
			  .' id="'.esc_html($this->getGuiId()).'"'
			  . ($this->getMultiple() ? ' multiple' : '')
			  . '>';
		$values = $this->getValue();
		$selectedValues = $this->getSelectedValue();

		foreach ($values as $key => $label) {
			$selected = null;
			if (in_array($key, $selectedValues)) {
				$selected = 'selected="selected"';
			}
			$output .= '<option value="'.esc_html($key).'" '.$selected.'>'.esc_html($label).'</option>';
		}
		$output .= '</select>';
		echo $output;
	}

	/**
	 * @return bool
	 */
	public function getMultiple(): bool
		{ return $this->_multiple; }

	/**
	 * @param bool $multiple
	 */
	public function setMultiple(bool $multiple)
		{ $this->_multiple = $multiple; }

}