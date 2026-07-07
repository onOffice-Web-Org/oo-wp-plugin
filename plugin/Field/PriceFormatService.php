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
		$decimalPlaces = $forcedDecimalPlaces ?? (floor($amount) == $amount ? 0 : 2);

		$formatted = number_format($amount, $decimalPlaces, $decimalSep, $thousandsSep);

		if ($position === 'before') {
			return $currency . "\xc2\xa0" . $formatted;
		}

		return $formatted . "\xc2\xa0" . $currency;
	}

	public function formatPriceField($value, string $currency = '€'): string
	{
		$normalized = (string) $value;

		$currencySymbols = ['€', '$', '£', '¥', 'CHF', 'USD', 'EUR', 'GBP', 'JPY', $currency];
		$normalized = str_replace($currencySymbols, '', $normalized);
		$normalized = trim($normalized);

		// Values coming from the onOffice API are always pre-formatted using '.'
		// as thousand separator and ',' as decimal separator (e.g. "299.000,00"),
		// independent of the separators configured for the frontend display.
		// That fixed source format must be detected/normalized on its own,
		// never based on the currently configured separators - otherwise
		// choosing '.' as the decimal separator makes the code treat the
		// source's thousand dots as decimal dots and silently drop digits.
		$forceDecimals = false;
		if (preg_match('/^-?\d{1,3}(\.\d{3})+(,\d+)?$/', $normalized) || preg_match('/^-?\d+,\d+$/', $normalized)) {
			$forceDecimals = strpos($normalized, ',') !== false;
			$normalized = str_replace('.', '', $normalized);
			$normalized = str_replace(',', '.', $normalized);
		} elseif (!is_numeric($normalized)) {
			$decimalSep = $this->getDecimalSeparator();
			$thousandsSep = $this->getThousandSeparator();
			if ($thousandsSep !== '') {
				$normalized = str_replace($thousandsSep, '', $normalized);
			}
			$normalized = str_replace($decimalSep, '.', $normalized);
			$normalized = preg_replace('/[^0-9.\-]/', '', $normalized);
			if (substr_count($normalized, '.') > 1) {
				$parts = explode('.', $normalized);
				$intPart = implode('', array_slice($parts, 0, -1));
				$decPart = end($parts);
				$normalized = $intPart . '.' . $decPart;
			}
			$forceDecimals = strpos($normalized, '.') !== false;
		}

		if (!is_numeric($normalized)) {
			return (string) $value;
		}

		return $this->formatPrice((float) $normalized, $currency, $forceDecimals ? 2 : null);
	}
}
