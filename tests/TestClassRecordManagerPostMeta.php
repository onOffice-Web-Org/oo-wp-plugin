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

namespace onOffice\tests;

use onOffice\WPlugin\Record\RecordManagerPostMeta;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRecordManagerPostMeta
    extends WP_UnitTestCase
{
    /**
     * @var object
     */
    private $_pRecordManagerPostMeta;


    /**
     *
     * @before
     *
     */
    public function prepare()
    {
        $this->_pRecordManagerPostMeta = new RecordManagerPostMeta();
    }

    /**
     *
     */
    public function testGetPageIdInPostMeta()
    {
        $pFieldsPostMeta = $this->_pRecordManagerPostMeta->getPageId();
        $this->assertEquals([], $pFieldsPostMeta);
    }
}
