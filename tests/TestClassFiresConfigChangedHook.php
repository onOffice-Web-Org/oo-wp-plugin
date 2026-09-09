<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

use onOffice\WPlugin\Gui\FiresConfigChangedHook;
use WP_UnitTestCase;

/**
 * Minimal test double exposing the protected trait method for direct testing.
 */
class FiresConfigChangedHookTestDouble
{
	use FiresConfigChangedHook;

	public static function trigger(bool $success, string $type, string $action, int $recordId = null): void
	{
		self::fireConfigChangedHook($success, $type, $action, $recordId);
	}
}

/**
 * Verifies that AdminPageEstate/AdminPageAddress/AdminPageFormList's shared
 * "fire onoffice/config_changed only on success" logic actually fires the
 * hook with the expected arguments - deliberately WITHOUT any WordPress
 * nonce, capability check, or database access, since fireConfigChangedHook()
 * itself has none of those dependencies (they only exist in the surrounding
 * bulk-action closures, which call this method after already having
 * confirmed the operation's success/failure).
 */
class TestClassFiresConfigChangedHook
	extends WP_UnitTestCase
{
	/** @var array */
	private $_capturedCalls = [];


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_capturedCalls = [];
		add_action('onoffice/config_changed', function ($type, $action, $recordId) {
			$this->_capturedCalls[] = [$type, $action, $recordId];
		}, 10, 3);
	}


	/**
	 *
	 * @after
	 *
	 */

	public function cleanup()
	{
		remove_all_actions('onoffice/config_changed');
	}


	public function testFiresHookOnSuccess()
	{
		FiresConfigChangedHookTestDouble::trigger(true, 'estate', 'delete', 5);
		$this->assertSame([['estate', 'delete', 5]], $this->_capturedCalls);
	}


	public function testDoesNotFireHookOnFailure()
	{
		FiresConfigChangedHookTestDouble::trigger(false, 'estate', 'delete', 5);
		$this->assertSame([], $this->_capturedCalls);
	}


	public function testFiresHookWithNullRecordIdForBulkActions()
	{
		// Bulk delete/duplicate affects multiple/unspecified records, so
		// $recordId is null in that case - must still be passed through.
		FiresConfigChangedHookTestDouble::trigger(true, 'form', 'duplicate', null);
		$this->assertSame([['form', 'duplicate', null]], $this->_capturedCalls);
	}


	public function testFiresHookOncePerSuccessfulCall()
	{
		FiresConfigChangedHookTestDouble::trigger(true, 'address', 'delete', 1);
		FiresConfigChangedHookTestDouble::trigger(false, 'address', 'delete', 2);
		FiresConfigChangedHookTestDouble::trigger(true, 'address', 'delete', 3);

		$this->assertCount(2, $this->_capturedCalls);
		$this->assertSame(['address', 'delete', 1], $this->_capturedCalls[0]);
		$this->assertSame(['address', 'delete', 3], $this->_capturedCalls[1]);
	}
}
