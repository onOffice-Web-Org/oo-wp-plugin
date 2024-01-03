<?php

namespace onOffice\tests;

use onOffice\WPlugin\Renderer\InputFieldSelectTwoRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */

class TestClassInputFieldSelect2Renderer extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderWithMultiple()
	{
		$pSubject = new InputFieldSelectTwoRenderer('testRenderer');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer[]" id="select_1" multiple></select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithoutMultiple()
	{
		$pSubject = new InputFieldSelectTwoRenderer('testRenderer2');
		ob_start();
		$pSubject->setMultiple(false);
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer2" id="select_1"></select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldSelectTwoRenderer('testRenderer3');
		$dataValueGroup = [
			'group' => [
				'Popular' => [
					'kaufpreis' => 'Kaufpreis',
					'kaltmiete' => 'Kaltmiete'
				]
			]
		];
		$pSubject->setValue($dataValueGroup);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer3[]" id="select_1" multiple>'
			.'<optgroup label="Popular">'
				.'<option value="kaufpreis" >Kaufpreis</option>'
				.'<option value="kaltmiete" >Kaltmiete</option>'
			.'</optgroup>'
			.'</select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithSelectedValue()
	{
		$pSubject = new InputFieldSelectTwoRenderer('testRenderer4');
		$dataValueGroup = [
			'group' => [
				'Popular' => [
					'kaufpreis' => 'Kaufpreis',
					'kaltmiete' => 'Kaltmiete'
				]
			]
		];
		$pSubject->setValue($dataValueGroup);
		$pSubject->setSelectedValue(['kaufpreis']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer4[]" id="select_1" multiple>'
			.'<optgroup label="Popular">'
				.'<option value="kaufpreis" selected="selected">Kaufpreis</option>'
				.'<option value="kaltmiete" >Kaltmiete</option>'
			.'</optgroup>'
			.'</select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithSingleSelectedValue()
	{
		$pSubject = new InputFieldSelectTwoRenderer('testRenderer5');
		$dataValueGroup = [
			'group' => [
				'Popular' => [
					'kaufpreis' => 'Kaufpreis',
					'kaltmiete' => 'Kaltmiete'
				]
			]
		];
		$pSubject->setMultiple(false);
		$pSubject->setValue($dataValueGroup);
		$pSubject->setSelectedValue('kaufpreis');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer5" id="select_1">'
			.'<optgroup label="Popular">'
				.'<option value="kaufpreis" selected="selected">Kaufpreis</option>'
				.'<option value="kaltmiete" >Kaltmiete</option>'
			.'</optgroup>'
			.'</select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithoutGroup()
	{
		$pSubject = new InputFieldSelectTwoRenderer('testRenderer6');
		$dataValue = ['kaufpreis' => 'Kaufpreis', 'kaltmiete' => 'Kaltmiete'];
		$pSubject->setValue($dataValue);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer6[]" id="select_1" multiple>'
			.'<option value="kaufpreis" >Kaufpreis</option>'
			.'<option value="kaltmiete" >Kaltmiete</option>'
			.'</select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithoutGroupSelected()
	{
		$pSubject = new InputFieldSelectTwoRenderer('testRenderer7');
		$dataValue = ['kaufpreis' => 'Kaufpreis', 'kaltmiete' => 'Kaltmiete'];
		$pSubject->setValue($dataValue);
		$pSubject->setSelectedValue(['kaufpreis']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer7[]" id="select_1" multiple>'
			.'<option value="kaufpreis" selected="selected">Kaufpreis</option>'
			.'<option value="kaltmiete" >Kaltmiete</option>'
			.'</select>', $output);
	}
}