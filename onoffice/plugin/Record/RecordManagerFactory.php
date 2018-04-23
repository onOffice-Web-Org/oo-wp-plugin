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

namespace onOffice\WPlugin\Record;

use Exception;
use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class RecordManagerFactory
{
	/** */
	const ACTION_READ = 'read';

	/** */
	const ACTION_INSERT = 'insert';

	/** */
	const ACTION_UPDATE = 'update';

	/** */
	const ACTION_DELETE = 'delete';

	/** */
	const TYPE_ADDRESS = 'address';

	/** */
	const TYPE_ESTATE = 'estate';

	/** */
	const TYPE_FORM = 'form';


	/** @var array */
	private static $_mapping = array(
		self::TYPE_ADDRESS => array(
			self::ACTION_READ => 'RecordManagerReadListViewAddress',
			self::ACTION_INSERT => 'RecordManagerInsertGeneric',
			self::ACTION_UPDATE => 'RecordManagerUpdateListViewAddress',
			self::ACTION_DELETE => 'RecordManagerDeleteListViewAddress',
		),
		self::TYPE_ESTATE => array(
			self::ACTION_READ => 'RecordManagerReadListViewEstate',
			self::ACTION_INSERT => 'RecordManagerInsertListViewEstate',
			self::ACTION_UPDATE => 'RecordManagerUpdateListViewEstate',
			self::ACTION_DELETE => 'RecordManagerDeleteListViewEstate',
		),
		self::TYPE_FORM => array(
			self::ACTION_READ => 'RecordManagerReadForm',
			self::ACTION_INSERT => 'RecordManagerInsertForm',
			self::ACTION_UPDATE => 'RecordManagerUpdateForm',
			self::ACTION_DELETE => 'RecordManagerDeleteForm',
		),
	);


	/** @var array */
	private static $_genericClassTables = array(
		self::TYPE_ADDRESS => RecordManager::TABLENAME_LIST_VIEW_ADDRESS,
	);


	/**
	 *
	 * @param string $type
	 * @param string $action
	 * @return RecordManager
	 * @throws Exception
	 *
	 */

	public static function createByTypeAndAction($type, $action, $recordId = null)
	{
		$pInstance = null;

		if (isset(self::$_mapping[$type][$action])) {
			$className = self::$_mapping[$type][$action];
		} else {
			throw new Exception('Class not found in mapping. type='.$type.', action='.$action);
		}

		$classNamespacePrefixed = __NAMESPACE__.'\\'.$className;

		if (__String::getNew($classNamespacePrefixed)->endsWith('Generic')) {
			$mainTable = self::$_genericClassTables[$type];
			if ($recordId !== null) {
				$pInstance = new $classNamespacePrefixed($mainTable, $recordId);
			} else {
				$pInstance = new $classNamespacePrefixed($mainTable);
			}
		} else {
			if ($recordId !== null) {
				$pInstance = new $classNamespacePrefixed($recordId);
			} else {
				$pInstance = new $classNamespacePrefixed;
			}
		}

		return $pInstance;
	}
}
