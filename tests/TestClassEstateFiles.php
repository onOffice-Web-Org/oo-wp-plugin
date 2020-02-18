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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\EstateFiles;
use WP_UnitTestCase;
use function esc_url;

class TestClassEstateFiles
	extends WP_UnitTestCase
{
	/** */
	const CATEGORIES = ['Titelbild', "Foto"];

	/** @var EstateFiles */
	private $_pInstance;

	/** @var array  */
	private static $_pictures = [
		2 => [
			'id' => 2,
			'url' => 'https://test.url/image/2.jpg',
			'title' => 'Awesome image',
			'text' => 'An image for test',
			'type' => 'Titelbild',
		],
		3 => [
			'id' => 3,
			'url' => 'https://test.url/image/3.png',
			'title' => 'Another awesome image',
			'text' => 'Another image for test',
			'type' => 'Foto',
		],
		4 => [
			'id' => 4,
			'url' => 'https://test.url/image/4.png',
			'title' => 'Another awesome image',
			'text' => 'Another image for test',
			'type' => 'Foto_gross',
		],
	];

	/**
	 * @before
	 */
	public function prepare()
	{
		$pSDKWrapperMocker = new SDKWrapperMocker();

		$responseGetEstatePictures = json_decode
		(file_get_contents(__DIR__.'/resources/ApiResponseGetEstatePictures.json'), true);

		$pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_GET, 'estatepictures', '', [
			'estateids' => [15],
			'categories' => self::CATEGORIES,
			'language' => 'ENG'
		], null, $responseGetEstatePictures);

		$this->_pInstance = new EstateFiles(self::CATEGORIES, [15], $pSDKWrapperMocker);
	}

	/**
	 * @covers \onOffice\WPlugin\EstateFiles::__construct
	 * @covers \onOffice\WPlugin\EstateFiles::collectEstateFiles
	 * @covers \onOffice\WPlugin\EstateFiles::correctUrl
	 */
	public function testInstance()
	{
		$this->assertInstanceOf(EstateFiles::class, $this->_pInstance);
	}

	/**
	 * @covers \onOffice\WPlugin\EstateFiles::getEstatePictures
	 * @covers \onOffice\WPlugin\EstateFiles::getFilesOfTypeByCallback
	 */
	public function testGetEstatePictures()
	{
		$this->assertEquals(self::$_pictures, $this->_pInstance->getEstatePictures(15));
	}

	/**
	 * @covers \onOffice\WPlugin\EstateFiles::getEstateMovieLinks
	 */
	public function testGetEstateMovieLinks()
	{
		$this->assertEmpty($this->_pInstance->getEstateMovieLinks(15));
	}

	/**
	 * @covers \onOffice\WPlugin\EstateFiles::getEstateFileUrl
	 */
	public function testGetEstateFileUrl()
	{
		$this->assertEquals(esc_url('https://test.url/image/2.jpg'), $this->_pInstance->getEstateFileUrl(2, 15));

		$this->assertEquals(esc_url('https://test.url/image/2.jpg@150x50'),
			$this->_pInstance->getEstateFileUrl(2, 15, ['width' => 150, 'height' => 50]));
	}

	/**
	 * @covers \onOffice\WPlugin\EstateFiles::getEstatePictureTitle
	 */
	public function testGetEstatePictureTitle()
	{
		$this->assertEquals(self::$_pictures[2]['title'], $this->_pInstance->getEstatePictureTitle(2, 15));
	}

	/**
	 * @covers \onOffice\WPlugin\EstateFiles::getEstatePictureText
	 */
	public function testGetEstatePictureText()
	{
		$this->assertEquals(self::$_pictures[3]['text'], $this->_pInstance->getEstatePictureText(3, 15));
	}

	/**
	 * @covers \onOffice\WPlugin\EstateFiles::getEstatePictureValues
	 */
	public function testGetEstatePictureValues()
	{
		$this->assertEquals(self::$_pictures[4], $this->_pInstance->getEstatePictureValues(4, 15));
		$this->assertEmpty($this->_pInstance->getEstatePictureValues(5, 15));
	}
}