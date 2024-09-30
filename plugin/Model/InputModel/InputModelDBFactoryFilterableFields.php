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

namespace onOffice\WPlugin\Model\InputModel;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

interface InputModelDBFactoryFilterableFields
{
	/** */
	const INPUT_FIELD_FILTERABLE = 'inputfilterable';

	/** If filterable, it can also be hidden */
	const INPUT_FIELD_HIDDEN = 'inputhidden';

	/** If filterable, it can also be availableOptions */
	const INPUT_FIELD_AVAILABLE_OPTIONS = 'inputavailableOptions';

	/** */
	const INPUT_FIELD_CONVERT_TEXT_TO_SELECT_FOR_CITY_FIELD = 'inputconvertTextToSelectForCityField';

	/** */
	const INPUT_FIELD_CONVERT_INPUT_TEXT_TO_SELECT_FOR_FIELD = 'convertInputTextToSelectForField';
}
