<?php
declare (strict_types=1);

namespace onOffice\tests;
use onOffice\WPlugin\EstateListHandle;
use WP_UnitTestCase;
class TestClassEstateListHandle extends WP_UnitTestCase
{
	private $_listCustomField = [
			'objekttitel' => 'onoffice_title',
			'objektbeschreibung' => 'onoffice_description',
			'ort' => 'onoffice_city',
			'plz' => 'onoffice_postal_code',
			'objektart' => 'onoffice_property_class',
			'vermarktungsart' => 'onoffice_marketing_method',
			'Id' => 'onoffice_id'
	];
	public function testConstruct()
	{
		$pWPOptionWrapper = new EstateListHandle();
		$this->assertInstanceOf(EstateListHandle::class, $pWPOptionWrapper);
	}
	public function testHandleRecord()
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