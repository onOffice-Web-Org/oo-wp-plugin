<?php

namespace onOffice\tests;

use onOffice\WPlugin\Controller\InputVariableReaderParser;
use onOffice\WPlugin\Types\FieldTypes;

class TestClassInputVariableReaderParser
	extends WP_UnitTest_Localized
{
	/** @var InputVariableReaderParser */
	private $_pInstance;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pInstance = new InputVariableReaderParser('Europe/Berlin');
	}

	/**
	 */
	public function testInstance()
	{
		$this->assertInstanceOf(InputVariableReaderParser::class, $this->_pInstance);
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderParser::parseBool
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderParser::parseValue
	 */
	public function testParseBool()
	{
		$this->assertEquals(true, $this->_pInstance->parseBool('y'));
		$this->assertEquals(false, $this->_pInstance->parseBool('u'));
		$this->assertEquals(false, $this->_pInstance->parseValue('', FieldTypes::FIELD_TYPE_BOOLEAN));
	}

	/**
	 * covers \onOffice\WPlugin\Controller\InputVariableReaderParser::parseFloat
	 * covers \onOffice\WPlugin\Controller\InputVariableReaderParser::parseValue
	 */
	public function testParseFloat()
	{
		$this->assertEquals(399.99 , $this->_pInstance->parseFloat('399,99'));
		$this->assertEquals(399.99, $this->_pInstance->parseValue('399,99', FieldTypes::FIELD_TYPE_FLOAT));
		$this->assertEquals(null, $this->_pInstance->parseFloat(''));

	}

	/**
	 * covers \onOffice\WPlugin\Controller\InputVariableReaderParser::parseValue
	 * covers \onOffice\WPlugin\Controller\InputVariableReaderParser::parseDate
	 */
	public function testParseDate()
	{
		$this->assertEquals('2014-09-14 00:00:00', $this->_pInstance->parseValue('14.09.2014', FieldTypes::FIELD_TYPE_DATE));
		$this->assertEquals('2014-09-14 00:00:00', $this->_pInstance->parseDate('14.09.2014'));

		$this->assertEquals('2018-06-28 14:01:00', $this->_pInstance->parseValue('28.06.2018 14:01:00', FieldTypes::FIELD_TYPE_DATETIME));
		$this->assertEquals('2018-06-28 14:01:00', $this->_pInstance->parseDate('28.06.2018 14:01:00'));

		$this->assertEquals(null, $this->_pInstance->parseDate(''));
		$this->assertEquals(null, $this->_pInstance->parseDate('asd'));
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\InputVariableReaderParser::parseValue
	 */
	public function testParseValueArray()
	{
		$this->assertEquals([399.99], $this->_pInstance->parseValue(['399,99'], FieldTypes::FIELD_TYPE_FLOAT));
		$this->assertEquals(null, $this->_pInstance->parseValue('', FieldTypes::FIELD_TYPE_FLOAT));
	}
}