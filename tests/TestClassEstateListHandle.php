<?php
declare (strict_types=1);

namespace onOffice\tests;
use onOffice\WPlugin\EstateListHandle;
use WP_UnitTestCase;
class TestClassEstateListHandle extends WP_UnitTestCase
{
	private $_listCustomField = [
		'objekttitel' => '',
		'objektbeschreibung' => '',
		'ort' => '',
		'plz' => '',
		'objektart' => '',
		'vermarktungsart' => '',
		'Id' => ''
	];
	public function testConstruct()
	{
		$pWPOptionWrapper = new EstateListHandle();
		$this->assertInstanceOf(EstateListHandle::class, $pWPOptionWrapper);
	}
	public function testHandleRecordNotData()
	{
		$listField = [
			'objekttitel' => '',
			'objektbeschreibung' => '',
			'ort' => '',
			'plz' => '',
			'objektart' => '',
			'vermarktungsart' => '',
			'Id' => ''
		];
		$pWPOptionWrapper = new EstateListHandle();
		$record = $pWPOptionWrapper->handleRecord($listField);
		$this->assertEquals($record,$this->_listCustomField);
	}
}