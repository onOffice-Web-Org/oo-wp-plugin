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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\Record\RecordManagerDeleteForm;
use WP_UnitTestCase;
use wpdb;

/**
 *
 */

class TestClassRecordManagerDeleteForm
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testDeleteByIds()
	{
		$pWPDB = $this->getMockBuilder(wpdb::class)
			->disableOriginalConstructor()
			->setMethods(['delete'])
			->getMock();
		$pWPDB->prefix = 'wp_test_';
		$pWPDB->expects($this->exactly(4))->method('delete')
			->will($this->returnCallback(function(string $table, array $where): int {
					if (in_array($table, ['wp_test_oo_plugin_forms', 'wp_test_oo_plugin_form_fieldconfig']) &&
						($where === ['form_id' => 13] || $where === ['form_id' => 15])) {
						return 1;
					}
					throw new \Exception($table.'/'. var_export($where, true));
				}
			));
		$pRecordManagerDeleteForm = new RecordManagerDeleteForm($pWPDB);
		$pRecordManagerDeleteForm->deleteByIds([13, 15]);
	}
}
