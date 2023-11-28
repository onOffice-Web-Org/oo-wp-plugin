<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\GenerateMetaDataSocial;
use WP_UnitTestCase;

class TestClassGenerateMetaDataSocial extends WP_UnitTestCase
{

	/** @var array */
	private $_estateData = [
		'title' => 'Demo Test Title Open Graph and Twitter Cards',
		'description' => 'Demo Test Class GenerateMetaDataSocial',
		'image' => 'https://link-cover-image/photo/house.jpg',
		'url' => 'https://www.example.com/estate-detail'
	];

	/**
	 *
	 */
	public function testGenerateOpenGraphData()
	{
		$pGenerateMetaData = new GenerateMetaDataSocial();
		$openGraphData = $pGenerateMetaData->generateMetaDataSocial($this->_estateData, [GenerateMetaDataSocial::OPEN_GRAPH_KEY]);
		$this->assertCount(7, $openGraphData);
		$this->assertEquals($this->_estateData['title'], $openGraphData['title']);
		$this->assertEquals($this->_estateData['description'], $openGraphData['description']);
		$this->assertEquals($this->_estateData['image'], $openGraphData['image']);
		$this->assertEquals($this->_estateData['url'], $openGraphData['url']);
		$this->assertEquals('website', $openGraphData['type']);
		$this->assertEquals(get_locale(), $openGraphData['locale']);
	}

	/**
	 *
	 */
	public function testGenerateTwitterCardData()
	{
		$pGenerateMetaData = new GenerateMetaDataSocial();
		$openGraphData = $pGenerateMetaData->generateMetaDataSocial($this->_estateData, [GenerateMetaDataSocial::TWITTER_KEY]);
		$this->assertCount(3, $openGraphData);
		$this->assertEquals($this->_estateData['title'], $openGraphData['title']);
		$this->assertEquals($this->_estateData['description'], $openGraphData['description']);
		$this->assertEquals($this->_estateData['image'], $openGraphData['image']);
	}
}