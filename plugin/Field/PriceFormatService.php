<?php

namespace onOffice\WPlugin\Field;

class PriceFormatService
{
	const THOUSAND_SEPARATOR = 'onoffice-settings-thousand-separator-custom';
	const DECIMAL_SEPARATOR = 'onoffice-settings-decimal-separator';
	const CURRENCY_POSITION = 'onoffice-settings-currency-position';

	const ACF_THOUSAND_SEPARATOR = 'options_general_price_format_thousand_separator';
	const ACF_DECIMAL_SEPARATOR = 'options_general_price_format_decimal_separator';
	const ACF_CURRENCY_POSITION = 'options_general_price_format_currency_position';

	public function getThousandSeparator(): string
	{
		$value = get_option(self::ACF_THOUSAND_SEPARATOR, null);
		if ($value !== null && $value !== '') {
			return $value;
		}
		$value = get_option(self::THOUSAND_SEPARATOR, '.');
		if ($value === '') {
			return '.';
		}
		return $value;
	}

	public function getDecimalSeparator(): string
	{
		$value = get_option(self::ACF_DECIMAL_SEPARATOR, null);
		if ($value !== null && $value !== '') {
			return $value;
		}
		$value = get_option(self::DECIMAL_SEPARATOR, ',');
		if ($value === '') {
			return ',';
		}
		return $value;
	}

	public function getCurrencyPosition(): string
	{
		$value = get_option(self::ACF_CURRENCY_POSITION, null);
		if ($value !== null && $value !== '') {
			return $value;
		}
		return get_option(self::CURRENCY_POSITION, 'after');
	}

	public function formatPrice(float $amount, string $currency, ?int $forcedDecimalPlaces = null): string
	{
		$thousandsSep = $this->getThousandSeparator();
		$decimalSep = $this->getDecimalSeparator();
		$position = $this->getCurrencyPosition();
		$decimalPlaces = $forcedDecimalPlaces ?? 2;

		$formatted = number_format($amount, $decimalPlaces, $decimalSep, $thousandsSep);
		$formatted = preg_replace('/' . preg_quote($decimalSep, '/') . '00$/', '', $formatted);

		if ($position === 'before') {
			return $currency . "\xc2\xa0" . $formatted;
		}

		return $formatted . "\xc2\xa0" . $currency;
	}

	/**
	 * $value must be onOffice's raw, unformatted numeric value (fetched with
	 * formatOutput=false). Enterprise's own configurable decimal/thousand
	 * separators (used for its formatOutput=true display values) are
	 * irrelevant here and must never be interpreted - only the WP plugin's
	 * own separator settings apply, via formatPrice() below.
	 *
	 * The raw value may still use either '.' or ',' as a separator (and,
	 * for whole-thousands amounts, as a thousands grouping rather than a
	 * decimal point) - disambiguated below by trailing digit count, since a
	 * real-world price is never fractional to 3+ decimal places.
	 */
	public function formatPriceField($value, string $currency = '€'): string
	{
		$normalized = (string) $value;

		$currencySymbols = ['€', '$', '£', '¥', 'CHF', 'USD', 'EUR', 'GBP', 'JPY', $currency];
		$normalized = str_replace($currencySymbols, '', $normalized);
		$normalized = trim($normalized);
		$normalized = preg_replace('/[^0-9.,\-]/', '', $normalized);

		$segments = preg_split('/[.,]/', $normalized);
		if (count($segments) > 1) {
			$lastSegment = array_pop($segments);
			if (strlen($lastSegment) === 3) {
				// A trailing 3-digit segment is a thousands group (e.g.
				// "55.000" / "1,234,000" = 55000/1234000), never a decimal
				// fraction.
				$segments[] = $lastSegment;
				$normalized = implode('', $segments);
			} else {
				$normalized = implode('', $segments) . '.' . $lastSegment;
			}
		}

		if (!is_numeric($normalized)) {
			return (string) $value;
		}

		return $this->formatPrice((float) $normalized, $currency);
	}
}
