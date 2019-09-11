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

use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\Utility\__String;
use const ONOFFICE_DI_CONFIG_PATH;

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
	private static $_mapping = [
		self::TYPE_ADDRESS => [
			self::ACTION_READ => RecordManagerReadListViewAddress::class,
			self::ACTION_INSERT => RecordManagerInsertGeneric::class,
			self::ACTION_UPDATE => RecordManagerUpdateListViewAddress::class,
			self::ACTION_DELETE => RecordManagerDeleteListViewAddress::class,
		],
		self::TYPE_ESTATE => [
			self::ACTION_READ => RecordManagerReadListViewEstate::class,
			self::ACTION_INSERT => RecordManagerInsertGeneric::class,
			self::ACTION_UPDATE => RecordManagerUpdateListViewEstate::class,
			self::ACTION_DELETE => RecordManagerDeleteListViewEstate::class,
		],
		self::TYPE_FORM => [
			self::ACTION_READ => RecordManagerReadForm::class,
			self::ACTION_INSERT => RecordManagerInsertGeneric::class,
			self::ACTION_UPDATE => RecordManagerUpdateForm::class,
		],
	];


	/** @var array */
	private static $_genericClassTables = [
		self::TYPE_ADDRESS => RecordManager::TABLENAME_LIST_VIEW_ADDRESS,
		self::TYPE_ESTATE => RecordManager::TABLENAME_LIST_VIEW,
		self::TYPE_FORM => RecordManager::TABLENAME_FORMS,
	];


	/**
	 *
	 * @param string $type
	 * @param string $action
	 * @param int $recordId
	 * @return RecordManager
	 * @throws Exception
	 *
	 */

	public static function createByTypeAndAction(
		string $type, string $action, int $recordId = null): RecordManager
	{
		$pInstance = null;
		$className = self::$_mapping[$type][$action] ?? null;

		if ($className === null) {
			throw new Exception('Class not found in mapping. type='.$type.', action='.$action);
		}

		if (__String::getNew($className)->endsWith('Generic')) {
			$mainTable = self::$_genericClassTables[$type];
			$pInstance = new $className($mainTable);
		} else {
			if ($recordId !== null) {
				$pInstance = new $className($recordId);
			} else {
				$pDIBuilder = new ContainerBuilder();
				$pDIBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
				$pDI = $pDIBuilder->build();
				$pInstance = $pDI->get($className);
			}
		}

		return $pInstance;
	}


	/**
	 *
	 * Fix to make this mockable
	 *
	 * @param string $type
	 * @param string $action
	 * @param int $recordId
	 * @return RecordManager
	 *
	 */

	public function create(string $type, string $action, int $recordId = null): RecordManager
	{
		return self::createByTypeAndAction($type, $action, $recordId);
	}


	/**
	 *
	 * @param string $mainTableName
	 * @return RecordManagerInsertGeneric
	 *
	 */

	public function createRecordManagerInsertGeneric(string $mainTableName): RecordManagerInsertGeneric
	{
		return new RecordManagerInsertGeneric($mainTableName);
	}


	/** @return array */
	static public function getGenericClassTables()
		{ return self::$_genericClassTables; }
}
