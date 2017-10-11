<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerReadListView
	extends RecordManagerRead
{

	/** */
	const PICTURES = 'pictures';

	/** */
	const FIELDS = 'fields';


	/**
	 *
	 * @return object[]
	 *
	 */

	public function getRecords()
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$columns = implode(', ', $this->getColumns());
		$join = implode("\n", $this->getJoins());
		$sql = "SELECT SQL_CALC_FOUND_ROWS {$columns}
				FROM {$prefix}oo_plugin_listviews
				{$join}
				ORDER BY `listview_id` ASC
				LIMIT {$this->getOffset()}, {$this->getLimit()}";
		$this->setFoundRows($pWpDb->get_results($sql, OBJECT));
		$this->setCountOverall($pWpDb->get_var('SELECT FOUND_ROWS()'));

		return $this->getFoundRows();
	}


	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	public function getRow($listviewId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = "SELECT *
				FROM {$prefix}oo_plugin_listviews
				WHERE `listview_id` = ".$listviewId;

		$result = $pWpDb->get_row($sql, ARRAY_A);

		$result[self::PICTURES] = $this->getPictureTypesByListviewId($listviewId);
		$result[self::FIELD] = $this->getFieldconfigByListviewId($listviewId);

		return $result;
	}


	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	private function readPicturetypesByListviewId($listviewId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sqlPictures = "SELECT *
				FROM {$prefix}oo_plugin_picturetypes
				WHERE `listview_id` = ".$listviewId;

		$pWpDb->get_results($sqlPictures);
		$pictures = $pWpDb->num_rows;
		$result = array();

		if (count($pictures) > 0)
		{
			foreach ($pictures as $picture)
			{
				$result[$picture->picturetype_id] =
						array
						(
							'picturetype' => $picture->picturetype
						);
			}
		}

		return $result;
	}



	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	private function readFieldconfigByListviewId($listviewId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sqlFields = "SELECT *
				FROM {$prefix}oo_plugin_fieldconfig
				WHERE `listview_id` = ".$listviewId;

		$pWpDb->get_results($sqlFields);
		$fields = $pWpDb->num_rows;
		$result = array();

		if (count($fields) > 0)
		{
			foreach ($fields as $field)
			{
				$result[$field->fieldconfig_id] =
						array
						(
							'order' => $field->order,
							'name'	=> $field->name,
						);
			}
		}

		return $result;
	}


	/**
	 *
	 * @param int $listviewId
	 * @param string $column
	 * @return string
	 *
	 */

	public function getColumn($listviewId, $column)
	{
		$result = null;
		$values = $this->getRow($listviewId);

		if (is_array($values))
		{
			if (array_key_exists($column, $values))
			{
				$result = $values[$column];
			}
		}

		return $result;
	}


	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	public function getPictureTypesByListviewId($listviewId)
	{
		$result = array();
		$values = $this->readPicturetypesByListviewId($listviewId);

		if (is_array($values))
		{
			$result = $values;
		}

		return $result;
	}



	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	public function getFieldconfigByListviewId($listviewId)
	{
		$result = array();
		$values = $this->getFieldconfigByListviewId($listviewId);

		if (is_array($values))
		{
			$result = $values;
		}

		return $result;
	}
}
