<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

namespace onOffice\tests;

use onOffice\WPlugin\Renderer\InputFieldChosenRenderer;
use onOffice\WPlugin\Renderer\InputFieldSelectRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */

class TestClassInputFieldChosenRenderer
	extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderMultiple()
	{
		$pSubject = new InputFieldChosenRenderer('testRenderer');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer[]" id="select_1" multiple></select>', $output);
	}

	/**
	 *
	 */
	public function testRenderNotMultiple()
	{
		$pSubject = new InputFieldChosenRenderer('testRenderer');
		ob_start();
		$pSubject->setMultiple(false);
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer" id="select_1"></select>', $output);
	}

		/**
	 *
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldChosenRenderer('testRenderer');
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
		$this->assertEquals('<select name="testRenderer[]" id="select_1" multiple>'
			.'<optgroup label="Popular">'
				.'<option value="kaufpreis" >Kaufpreis</option>'
				.'<option value="kaltmiete" >Kaltmiete</option>'
			.'</optgroup>'
			.'</select>', $output);
	}

	/**
	 *
	 */
	public function testRenderSelectedValue()
	{
		$pSubject = new InputFieldChosenRenderer('testRenderer');
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
		$this->assertEquals('<select name="testRenderer[]" id="select_1" multiple>'
			.'<optgroup label="Popular">'
			.'<option value="kaufpreis" selected="selected">Kaufpreis</option>'
			.'<option value="kaltmiete" >Kaltmiete</option>'
			.'</optgroup>'
			.'</select>', $output);
	}


	/**
	 *
	 */
	public function testRenderSingleSelectedValue()
	{
		$pSubject = new InputFieldChosenRenderer('testRenderer');
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
		$this->assertEquals('<select name="testRenderer" id="select_1">'
		                    .'<optgroup label="Popular">'
		                    .'<option value="kaufpreis" selected="selected">Kaufpreis</option>'
		                    .'<option value="kaltmiete" >Kaltmiete</option>'
		                    .'</optgroup>'
		                    .'</select>', $output);
	}


	/**
	 *
	 */
	public function testRenderNotGroup()
	{
		$pSubject = new InputFieldChosenRenderer('testRenderer');
		$dataValue = ['kaufpreis' => 'Kaufpreis', 'kaltmiete' => 'Kaltmiete'];
		$pSubject->setValue($dataValue);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer[]" id="select_1" multiple>'
			.'<option value="kaufpreis" >Kaufpreis</option>'
			.'<option value="kaltmiete" >Kaltmiete</option>'
			.'</select>', $output);
	}

	/**
	 *
	 */
	public function testRenderNotGroupSelected()
	{
		$pSubject = new InputFieldChosenRenderer('testRenderer');
		$dataValue = ['kaufpreis' => 'Kaufpreis', 'kaltmiete' => 'Kaltmiete'];
		$pSubject->setValue($dataValue);
		$pSubject->setSelectedValue(['kaufpreis']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer[]" id="select_1" multiple>'
			.'<option value="kaufpreis" selected="selected">Kaufpreis</option>'
			.'<option value="kaltmiete" >Kaltmiete</option>'
			.'</select>', $output);
	}
}
