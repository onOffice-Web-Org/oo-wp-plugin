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

use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\Renderer\InputFieldLabelRenderer;
use onOffice\WPlugin\Renderer\InputFieldRenderer;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassInputFieldLabelRenderer
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testAttributes()
	{
		InputFieldRenderer::resetGuiId();
		$pRenderer = new InputFieldLabelRenderer(null, 'testIdentifier');
		$pRenderer->setLabel('hello');
		$this->assertEquals('hello', $pRenderer->getLabel());

		$this->assertEquals(InputModelLabel::VALUE_ENCLOSURE_ITALIC, $pRenderer->getValueEnclosure());
		$pRenderer->setValueEnclosure(InputModelLabel::VALUE_ENCLOSURE_CODE);
		$this->assertEquals(InputModelLabel::VALUE_ENCLOSURE_CODE, $pRenderer->getValueEnclosure());
	}
}
