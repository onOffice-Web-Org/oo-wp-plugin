<?php

namespace onOffice\WPlugin\Field;

class PriceFormatService
{
	const THOUSAND_SEPARATOR = 'onoffice-settings-thousand-separator-custom';
	const DECIMAL_SEPARATOR = 'onoffice-settings-decimal-separator';
	const CURRENCY_POSITION = 'onoffice-settings-currency-position';

	public function getThousandSeparator(): string
	{
		$value = get_option(self::THOUSAND_SEPARATOR, '.');
		if ($value === '') {
			return '.';
		}
		return $value;
	}

	public function getDecimalSeparator(): string
	{
		$value = get_option(self::DECIMAL_SEPARATOR, ',');
		if ($value === '') {
			return ',';
		}
		return $value;
	}

	public function getCurrencyPosition(): string
	{
		return get_option(self::CURRENCY_POSITION, 'after');
	}

	public function formatPrice(float $amount, string $currency): string
	{
		$thousandsSep = $this->getThousandSeparator();
		$decimalSep = $this->getDecimalSeparator();
		$position = $this->getCurrencyPosition();
		$decimalPlaces = floor($amount) == $amount ? 0 : 2;

		$formatted = number_format($amount, $decimalPlaces, $decimalSep, $thousandsSep);

		if ($position === 'before') {
			return $currency . "\xc2\xa0" . $formatted;
		}

		return $formatted . "\xc2\xa0" . $currency;
	}

	public function formatPriceField($value, string $currency = '€'): string
	{
		$normalized = (string) $value;
		if (is_numeric($normalized)) {
			return $this->formatPrice((float) $normalized, $currency);
		}
		$normalized = str_replace($this->getDecimalSeparator(), '.', $normalized);
		$normalized = preg_replace('/[^0-9.\-]/', '', $normalized);
		if (substr_count($normalized, '.') > 1) {
			$parts = explode('.', $normalized);
			$intPart = implode('', array_slice($parts, 0, -1));
			$decPart = end($parts);
			$normalized = $intPart . '.' . $decPart;
		}
		if (!is_numeric($normalized)) {
			return (string) $value;
		}
		return $this->formatPrice((float) $normalized, $currency);
	}
}
