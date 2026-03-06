<?php

/**
 *
 *    Copyright (C) 2017-2019 onOffice GmbH
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

namespace onOffice\WPlugin\Model;

/**
 * Custom InputModel to preserve brackets and underscores for array fields.
 * Bypasses the strict sanitization of InputModelDB for complex field names.
 */

class InputModelDBArray 
	extends InputModelDB
{
    /** @var string */
    private $_rawField = '';

    /** @var string */
    private $_rawName = '';


    /**
     * Captures the raw field name before the parent model sanitizes it.
     * * @param string $field
     */
    public function setField($field)
    {
        $this->_rawField = $field;
        parent::setField($field);
    }


    /**
     * Returns the raw field name including underscores and brackets.
     * * @return string
     */
    public function getField(): string
    {
        return $this->_rawField;
    }


    /**
     * Captures the raw HTML name.
     * * @param string $name
     */
    public function setName($name)
    {
        $this->_rawName = $name;
        parent::setName($name);
    }


    /**
     * Returns the raw HTML name.
     * * @return string
     */
    public function getName(): string
    {
        return $this->_rawName;
    }


    /**
     * Bypasses the parent identifier generation to preserve underscores and brackets.
     * * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getTable() . '-' . $this->_rawField;
    }
}