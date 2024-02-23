<?php

namespace onOffice\tests;

use onOffice\WPlugin\Controller\InputVariableReaderFormatter;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

class TestClassInputVariableReaderFormatter
	extends WP_UnitTestCase
{
	/**
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderFormatter::formatValue
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderFormatter::formatFloatValue
	 */
	public function testFormatFloatValueWithThousandSeparatorComma()
	{
		$pInstance = new InputVariableReaderFormatter;
		update_option('onoffice-settings-thousand-separator', 'comma-separator');

		$this->assertEquals('3948.00', $pInstance->formatValue(3948.00, FieldTypes::FIELD_TYPE_FLOAT));
		$this->assertEquals('3948.00', $pInstance->formatFloatValue(3948.00));
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderFormatter::formatValue
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderFormatter::formatFloatValue
	 */
	public function testFormatFloatValueWithThousandSeparatorDot()
	{
		$pInstance = new InputVariableReaderFormatter;
		update_option('onoffice-settings-thousand-separator', 'dot-separator');

		$this->assertEquals('3948,00', $pInstance->formatValue(3948.00, FieldTypes::FIELD_TYPE_FLOAT));
		$this->assertEquals('3948,00', $pInstance->formatFloatValue(3948.00));
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderFormatter::formatValue
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderFormatter::formatDateOrDateTimeValue
	 *
	 *
	 */
	public function testFormatDateOrDateTimeValue()
	{
		$pInstance = new InputVariableReaderFormatter;

		$this->assertEquals('2018/06/28 12:01:00 pm', $pInstance->formatValue('2018-06-28 14:01:00', FieldTypes::FIELD_TYPE_DATETIME));
		$this->assertEquals('2018/06/28 12:01:00 pm', $pInstance->formatDateOrDateTimeValue('2018-06-28 14:01:00', FieldTypes::FIELD_TYPE_DATETIME));
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderFormatter::formatValue
	 */
	public function testFormatValueArrayWithThousandSeparatorComma()
	{
		$pInstance = new InputVariableReaderFormatter;
		update_option('onoffice-settings-thousand-separator', 'comma-separator');

		$this->assertEquals(['3948.00'], $pInstance->formatValue([3948.00], FieldTypes::FIELD_TYPE_FLOAT));
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderFormatter::formatValue
	 */
	public function testFormatValueArrayWithThousandSeparatorDot()
	{
		$pInstance = new InputVariableReaderFormatter;
		update_option('onoffice-settings-thousand-separator', 'dot-separator');

		$this->assertEquals(['3948,00'], $pInstance->formatValue([3948.00], FieldTypes::FIELD_TYPE_FLOAT));
	}
}