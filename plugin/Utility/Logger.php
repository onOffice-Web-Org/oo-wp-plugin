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

namespace onOffice\WPlugin\Utility;

use Exception;
use onOffice\WPlugin\Controller\Exception\ExceptionPrettyPrintable;
use onOffice\WPlugin\Controller\UserCapabilities;
use function current_user_can;
use function esc_html;
use function esc_html__;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class Logger
{
	/** @var LoggerEnvironment */
	private $_pEnvironment = null;

	/**
	 *
	 * @param LoggerEnvironment $pEnvironment
	 *
	 */

	public function __construct(LoggerEnvironment $pEnvironment = null)
	{
		$this->_pEnvironment = $pEnvironment ?? new LoggerEnvironmentDefault();
	}


	/**
	 *
	 * @param Exception $pException
	 * @return string
	 *
	 */

	public function logErrorAndDisplayMessage(Exception $pException): string
	{
		$output = '';
		$pUserCapabilities = $this->_pEnvironment->getUserCapabilities();
		$roleDebugOutput = $pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_DEBUG_OUTPUT);

		if (current_user_can($roleDebugOutput)) {
			if ($pException instanceof ExceptionPrettyPrintable) {
				$output = sprintf('<big><strong>%s</strong></big>',
					esc_html($pException->printFormatted()));
			} else {
				$output = '<pre>'
					.'<u><strong>[onOffice-Plugin]</strong> '
					.esc_html__('An error occured:', 'onoffice').'</u><p>'
					.esc_html((string) $pException).'</pre></p>';
			}
		}

		$this->logError($pException);

		return $output;
	}


	/**
	 *
	 * @param Exception $pException
	 *
	 */

	public function logError(Exception $pException)
	{
		$this->_pEnvironment->log('[onOffice-Plugin]: '.strval($pException));
	}
}
