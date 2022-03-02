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

use onOffice\WPlugin\Renderer\InputFieldCheckboxButtonRenderer;
use onOffice\WPlugin\Renderer\InputFieldRenderer;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\WP\WPOptionWrapperTest;

class TestClassInputFieldCheckboxButtonRenderer
    extends \WP_UnitTestCase
{
    public function testAttributes()
    {
        InputFieldRenderer::resetGuiId();
        $pRenderer = new InputFieldCheckboxButtonRenderer(null, 'testIdentifier');
        $pRenderer->setLabel('testLabel');
        $this->assertEquals('testLabel', $pRenderer->getLabel());
        $pRenderer->setId('idTest');
        $this->assertEquals('idTest', $pRenderer->getId());
    }

    /**
     *
     */
    public function testRenderWithValues()
    {
        $pRenderer = new InputFieldCheckboxButtonRenderer(null, 'testIdentifier');
        $pRenderer->setValue('John Doe');
        ob_start();
        global $wpdb;

        $pWpOption = new WPOptionWrapperTest();
        $pDbChanges = new DatabaseChanges($pWpOption, $wpdb);
        $pDbChanges->install();
        $pRenderer->render();
        $output = ob_get_clean();
        $this->assertEquals('<input type="checkbox" name="" value="John Doe" id="checkbox_2"><p>'
            .'<input type="button" class="inputFieldCheckboxButton button" name="" value="Add to List &gt;&gt;" data-onoffice-category="">'
            .'</p>', $output);
    }

    /**
     *
     */
    public function testRenderEmptyValues()
    {
        $pSubject = new InputFieldCheckboxButtonRenderer('testRenderer',true);
        ob_start();
        global $wpdb;

        $pWpOption = new WPOptionWrapperTest();
        $pDbChanges = new DatabaseChanges($pWpOption, $wpdb);
        $pDbChanges->install();
        $pSubject->render();
        $output = ob_get_clean();
        $this->assertEquals('<input type="checkbox" name="testRenderer" value="1" id="checkbox_3"><p>'
            .'<input type="button" class="inputFieldCheckboxButton button" name="" value="Add to List &gt;&gt;" data-onoffice-category="">'
            .'</p>', $output);
    }
}