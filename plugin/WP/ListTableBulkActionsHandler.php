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

namespace onOffice\WPlugin\WP;

use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\WP\WPNonceWrapper;
use function apply_filters;

/**
 *
 */

class ListTableBulkActionsHandler
{
	/** @var RequestVariablesSanitizer */
	private $_pRequestVariableSanitizer;

	/** @var WPNonceWrapper */
	private $_pWPNonceWrapper;

	/** @var WPScreenWrapper */
	private $_pWPScreenWrapper;


	/**
	 *
	 * @param RequestVariablesSanitizer $pRequestVariableSanitizer
	 * @param WPNonceWrapper $pWPNonceWrapper
	 * @param WPScreenWrapper $pWPScreenWrapper
	 *
	 */

	public function __construct(
		RequestVariablesSanitizer $pRequestVariableSanitizer,
		WPNonceWrapper $pWPNonceWrapper,
		WPScreenWrapper $pWPScreenWrapper)
	{
		$this->_pRequestVariableSanitizer = $pRequestVariableSanitizer;
		$this->_pWPNonceWrapper = $pWPNonceWrapper;
		$this->_pWPScreenWrapper = $pWPScreenWrapper;
	}


	/**
	 *
	 * @return void
	 *
	 */

	public function processBulkAction()
	{
		$currentScreen = $this->_pWPScreenWrapper->getID();
		$pListTable = apply_filters('handle_bulk_actions-table-'.$currentScreen, null);

		if (!is_object($pListTable)) {
			return;
		}

		$pListTable->prepare_items();
		$args = $pListTable->getArgs();
		$nonce = $this->getWPNonce();
		$nonceaction = 'bulk-'.$args['plural'];
		$recordIds = $this->getRecordIds($args['singular']);
		$action = $pListTable->current_action();

		if ($action === false) {
			return;
		}

		$this->_pWPNonceWrapper->verify($nonce, $nonceaction);
		$referer = $this->_pWPNonceWrapper->getReferer();
		$redirectTo = apply_filters('handle_bulk_actions-'.$currentScreen, $referer, $pListTable, $recordIds);

		if (!__String::getNew($redirectTo)->isEmpty()) {
			$this->_pWPNonceWrapper->safeRedirect($redirectTo);
		}
    }


	/**
	 *
	 * @return string
	 *
	 */

	private function getWPNonce(): string
	{
		return $this->_pRequestVariableSanitizer->getFilteredPost
			('_wpnonce', FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL) ??
			$this->_pRequestVariableSanitizer->getFilteredGet
				('_wpnonce', FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL) ?? '';
	}


	/**
	 *
	 * @param string $name
	 * @return array
	 *
	 */

	private function getRecordIds(string $name): array
	{
		$recordIds = $this->_pRequestVariableSanitizer->getFilteredPost
			($name, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY|FILTER_NULL_ON_FAILURE) ??
			$this->_pRequestVariableSanitizer->getFilteredGet
				($name, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY|FILTER_NULL_ON_FAILURE) ?? [];
		return array_map('absint', $recordIds);
	}
}
