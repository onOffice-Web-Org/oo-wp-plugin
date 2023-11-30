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
		'description' => 'The sun-kissed beach was a veritable haven. Soft, white sand stretched lazily from one end of the coastline to the other, inviting visitors to take off their shoes and dip their toes in the cool water.Warm sunshine beat down on my skin as I walked along the smooth shoreline, enjoying the salty ocean breeze that greeted me with each step. In the distance, I could see boats anchored in the harbour, their masts swaying gracefully with the rhythm of the waves.',
		'image' => 'https://link-cover-image/photo/house.jpg',
		'url' => 'https://www.example.com/estate-detail'
	];

	/**
	 *
	 */
	public function testGenerateOpenGraphData()
	{
		$expectOutput = [
			'title' => 'Demo Test Title Open Graph and Twitter Cards',
			'description' => 'The sun-kissed beach was a veritable haven. Soft, white sand stretched lazily from one end of the coastline to the other, inviting visitors to take off their shoes and dip their toes in the cool water.Warm sunshine beat down on my skin as I walked along the smooth shoreline, enjoying the salty ocean',
			'image' => 'https://link-cover-image/photo/house.jpg',
			'url' => 'https://www.example.com/estate-detail'
		];

		$pGenerateMetaData = new GenerateMetaDataSocial();
		$openGraphData = $pGenerateMetaData->generateMetaDataSocial($this->_estateData, [GenerateMetaDataSocial::OPEN_GRAPH_KEY]);
		$this->assertCount(9, $openGraphData);
		$this->assertEquals($expectOutput['title'], $openGraphData['title']);
		$this->assertEquals($expectOutput['description'], $openGraphData['description']);
		$this->assertEquals($expectOutput['image'], $openGraphData['image']);
		$this->assertEquals($expectOutput['url'], $openGraphData['url']);
		$this->assertEquals('website', $openGraphData['type']);
		$this->assertEquals(get_locale(), $openGraphData['locale']);
	}

	/**
	 *
	 */
	public function testGenerateTwitterCardData()
	{
		$expectOutput = [
			'title' => 'Demo Test Title Open Graph and Twitter Cards',
			'description' => 'The sun-kissed beach was a veritable haven. Soft, white sand stretched lazily from one end of the coastline to the other, inviting visitors to take off their shoes and dip their toes in the cool',
			'image' => 'https://link-cover-image/photo/house.jpg'
		];
		
		$pGenerateMetaData = new GenerateMetaDataSocial();
		$openGraphData = $pGenerateMetaData->generateMetaDataSocial($this->_estateData, [GenerateMetaDataSocial::TWITTER_KEY]);
		$this->assertCount(3, $openGraphData);
		$this->assertEquals($expectOutput['title'], $openGraphData['title']);
		$this->assertEquals($expectOutput['description'], $openGraphData['description']);
		$this->assertEquals($expectOutput['image'], $openGraphData['image']);
	}
}