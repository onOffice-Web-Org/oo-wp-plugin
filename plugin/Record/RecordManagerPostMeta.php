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

use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use const ARRAY_A;
use const OBJECT;
use function esc_sql;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerPostMeta
{
    /**
     *
     */
    const TABLENAME_POSTMETA = 'postmeta';

    /**
     * @var string
     */
    private $_mainTable;

    /**
     * @var string
     */
    private $_idColumnMain;


    public function __construct()
    {
        $this->setMainTable(self::TABLENAME_POSTMETA);
        $this->setIdColumnMain('meta_id');
    }

    /**
     * @param string $mainTable
     */
    public function setMainTable(string $mainTable)
    {
        $this->_mainTable = $mainTable;
    }

    /**
     * @return string
     */
    public function getMainTable()
    {
        return $this->_mainTable;
    }

    /**
     * @param string $idColumnMain
     */
    public function setIdColumnMain(string $idColumnMain)
    {
        $this->_idColumnMain = $idColumnMain;
    }

    /**
     * @return string
     */
    public function getIdColumnMain()
    {
        return $this->_idColumnMain;
    }

    /**
     *
     * @return array
     *
     */

    public function getPageIdInPostMeta()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $post_meta_sql="SELECT `post_id`
                        FROM {$prefix}postmeta
                        WHERE meta_key not like '\_%' and meta_value like '%[oo_estate%'";
        $post_meta_results = $wpdb->get_row( $post_meta_sql ,ARRAY_A);
        return $post_meta_results;
    }


}