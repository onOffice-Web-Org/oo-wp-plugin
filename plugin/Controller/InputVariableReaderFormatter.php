<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;

use DateTime;
use onOffice\WPlugin\Gui\DateTimeFormatter;
use onOffice\WPlugin\Types\FieldTypes;

class InputVariableReaderFormatter
{
	const APPLY_THOUSAND_SEPARATOR_FIELDS = [
		'mieteinnahmen_pro_jahr_ist',
		'mieteinnahmen_pro_jahr_soll',
		'kaltmiete',
		'erschliessungskosten',
		'miete_pauschal',
		'heizkosten',
		'heizkosten_brutto',
		'erbpacht',
		'hausgeld',
		'pacht',
		'provision_innen_wert',
		'mieteinnahmen_ist',
		'mieteinnahmen_soll',
		'nettokaltmiete',
		'betriebskosten',
		'stellplatzkaufpreis',
		'calculatedPrice',
		'kaufpreis',
		'kaufpreis_pro_qm',
		'mieteinnahmen_pro_monat',
		'stellplatzmiete',
		'warmmiete',
		'mietpreis_pro_qm',
		'saisonmiete',
		'nebenkosten',
		'wochmietbto',
		'x_fache',
		'x_fache_soll',
		'nebenflaeche',
		'verwaltungsflaeche',
		'calculatedArea',
		'dachbodenflaeche',
		'balkon_terrasse_flaeche',
		'kellerflaeche',
		'hallenhoehe',
		'gewerbeflaeche',
		'teilbar_ab',
		'gartenflaeche',
		'gastroflaeche',
		'raumhoehe',
		'wohnflaeche',
		'bueroflaeche',
		'sonstflaeche',
		'grundstuecksflaeche',
		'vermietbare_flaeche',
		'verkaufsflaeche',
		'lagerflaeche',
		'ladenflaeche',
		'gesamtflaeche',
		'gesamtflaeche_verfuegbar_qm',
		'nutzflaeche'
	];

	/** */
	const COMMA_THOUSAND_SEPARATOR = 'comma-separator';

	/** */
	const DOT_THOUSAND_SEPARATOR = 'dot-separator';

	/**
	 * @param $value
	 * @param string $type
	 * @return array|string
	 */
	public function formatValue($value, string $type)
	{
		if (is_float($value)) {
			return $this->formatFloatValue($value);
		} elseif (is_array($value)) {
			$value = array_map(function($val) use ($type) {
				return $this->formatValue($val, $type);
			}, $value);
		} elseif (FieldTypes::isDateOrDateTime($type) && $value != '') {
			$value = $this->formatDateOrDateTimeValue($value, $type);
		}
		return $value;
	}

	/**
	 * @param float $value
	 * @return string
	 */
	public function formatFloatValue(float $value): string
	{
		$onofficeSettingsThousandSeparator = get_option('onoffice-settings-thousand-separator');

		if ($onofficeSettingsThousandSeparator === self::COMMA_THOUSAND_SEPARATOR) {
			$parts = explode(',', $value);

			return number_format($parts[0], 2, '.', '');
		} elseif ($onofficeSettingsThousandSeparator === self::DOT_THOUSAND_SEPARATOR) {
			return number_format($value, 2, ',', '');
		}

		return number_format_i18n($value, 2);
	}

	/**
	 * @param string $value
	 * @param string $type
	 * @return string
	 */
	public function formatDateOrDateTimeValue(string $value, string $type): string
	{
		$format = DateTimeFormatter::SHORT|DateTimeFormatter::ADD_DATE;
		if ($type === FieldTypes::FIELD_TYPE_DATETIME) {
			$format |= DateTimeFormatter::ADD_TIME;
		}

		$pDate = new DateTime($value.' Europe/Berlin');
		$pDateTimeFormatter = new DateTimeFormatter();
		return $pDateTimeFormatter->formatByTimestamp($format, $pDate->getTimestamp());
	}
}