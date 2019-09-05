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

use onOffice\WPlugin\Gui\Table\WP\ListTable;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\WP\ListTableBulkActionsHandler;
use onOffice\WPlugin\WP\WPNonceWrapper;
use onOffice\WPlugin\WP\WPScreenWrapper;
use WP_List_Table;
use WP_UnitTestCase;
use function add_filter;


/**
 *
 */

class TestClassListTableBulkActionsHandler
	extends WP_UnitTestCase
{
	/** @var RequestVariablesSanitizer */
	private $_pRequestVariablesSanitizer = null;

	/** @var WPNonceWrapper */
	private $_pWPNonceWrapper = null;

	/** @var WPScreenWrapper */
	private $_pWPScreenWrapper = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pRequestVariablesSanitizer = $this->getMockBuilder(RequestVariablesSanitizer::class)
			->getMock();
		$this->_pWPNonceWrapper = $this->getMockBuilder(WPNonceWrapper::class)
			->getMock();

		$this->_pWPScreenWrapper = $this->getMockBuilder(WPScreenWrapper::class)
			->getMock();
		$this->_pWPScreenWrapper->method('getID')
			->will($this->returnValue(__CLASS__));
	}


	/**
	 *
	 * @dataProvider createListTable
	 *
	 */

	public function testProcessBulkAction(ListTable $pListTable, bool $expectsCallbackCall)
	{
		$pListTableBulkActionsHandler = new ListTableBulkActionsHandler
			($this->_pRequestVariablesSanitizer, $this->_pWPNonceWrapper, $this->_pWPScreenWrapper);

		$this->_pRequestVariablesSanitizer->expects($this->exactly(2))->method('getFilteredPost')
			->will($this->returnValue(null));
		$this->_pRequestVariablesSanitizer->expects($this->exactly(2))->method('getFilteredGet')
			->will($this->returnCallback(function(string $var) {
				$values = [
					'_wpnonce' => 'eb0344545',
					'form' => [13],
				];
				return $values[$var] ?? null;
			}));

		if ($expectsCallbackCall) {
			$this->_pWPNonceWrapper->expects($this->once())->method('verify')->with('eb0344545', 'bulk-forms');
			$this->_pWPNonceWrapper->expects($this->once())->method('getReferer')->will($this->returnValue('https://example.org/abc'));
			$this->_pWPNonceWrapper->expects($this->once())->method('safeRedirect')->with('https://example.org/cde');
		}

		$functionCalled = false;
		add_filter('handle_bulk_actions-'.__CLASS__, function(string $referer, string $action, array $recordIds)
			use (&$functionCalled): string {
			$functionCalled = true;
			$this->assertEquals('https://example.org/abc', $referer);
			$this->assertEquals('bulk_delete', $action);
			$this->assertEquals([13], $recordIds);
			return 'https://example.org/cde';
		}, 10, 3);

		add_filter('handle_bulk_actions-table-'.__CLASS__, function() use ($pListTable): WP_List_Table {
			return $pListTable;
		});

		$pListTableBulkActionsHandler->processBulkAction();

		$this->assertSame($expectsCallbackCall, $functionCalled);
	}


	/**
	 *
	 */

	public function testEmptyTable()
	{
		$pListTableBulkActionsHandler = new ListTableBulkActionsHandler
			($this->_pRequestVariablesSanitizer, $this->_pWPNonceWrapper, $this->_pWPScreenWrapper);
		$this->assertNull($pListTableBulkActionsHandler->processBulkAction());
	}


	/**
	 *
	 * @return ListTable
	 *
	 */

	public function createListTable(): array
	{
		$pListTable = $this->getMockBuilder(ListTable::class)
			->disableOriginalConstructor()
			->getMock();

		$pListTable->method('getArgs')
			->will($this->returnValue(['singular' => 'form', 'plural' => 'forms']));
		$pListTable->method('current_action')
			->will($this->onConsecutiveCalls('bulk_delete', false));
		return [
			[$pListTable, true],
			[$pListTable, false],
		];
	}
}
