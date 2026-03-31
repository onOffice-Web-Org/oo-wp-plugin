<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use onOffice\WPlugin\AddressList;
use WP_UnitTestCase;

/**
 * Unit tests for AddressList::createAddressTitle() method
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 * @covers onOffice\WPlugin\AddressList::createAddressTitle
 *
 */
class TestClassAddressListCreateAddressTitle extends WP_UnitTestCase
{
	/**
	 * Test createAddressTitle with all parameters provided
	 */
	public function testCreateAddressTitleWithAllParameters()
	{
		$result = AddressList::createAddressTitle('Max', 'Mustermann', 'Acme Corp');
		$this->assertEquals('max mustermann acme corp', $result);
	}

	/**
	 * Test createAddressTitle with firstName and lastName only
	 */
	public function testCreateAddressTitleWithFirstNameAndLastName()
	{
		$result = AddressList::createAddressTitle('Max', 'Mustermann', null);
		$this->assertEquals('max mustermann', $result);
	}

	/**
	 * Test createAddressTitle with firstName and company only
	 */
	public function testCreateAddressTitleWithFirstNameAndCompany()
	{
		$result = AddressList::createAddressTitle('Max', null, 'Acme Corp');
		$this->assertEquals('max acme corp', $result);
	}

	/**
	 * Test createAddressTitle with lastName and company only
	 */
	public function testCreateAddressTitleWithLastNameAndCompany()
	{
		$result = AddressList::createAddressTitle(null, 'Mustermann', 'Acme Corp');
		$this->assertEquals('mustermann acme corp', $result);
	}

	/**
	 * Test createAddressTitle with firstName only
	 */
	public function testCreateAddressTitleWithFirstNameOnly()
	{
		$result = AddressList::createAddressTitle('Max', null, null);
		$this->assertEquals('max', $result);
	}

	/**
	 * Test createAddressTitle with lastName only
	 */
	public function testCreateAddressTitleWithLastNameOnly()
	{
		$result = AddressList::createAddressTitle(null, 'Mustermann', null);
		$this->assertEquals('mustermann', $result);
	}

	/**
	 * Test createAddressTitle with company only
	 */
	public function testCreateAddressTitleWithCompanyOnly()
	{
		$result = AddressList::createAddressTitle(null, null, 'Acme Corp');
		$this->assertEquals('acme corp', $result);
	}

	/**
	 * Test createAddressTitle with all parameters null
	 */
	public function testCreateAddressTitleWithAllParametersNull()
	{
		$result = AddressList::createAddressTitle(null, null, null);
		$this->assertEquals('', $result);
	}

	/**
	 * Test createAddressTitle with empty strings
	 */
	public function testCreateAddressTitleWithEmptyStrings()
	{
		$result = AddressList::createAddressTitle('', '', '');
		$this->assertEquals('', $result);
	}

	/**
	 * Test createAddressTitle with mixed empty and null values
	 */
	public function testCreateAddressTitleWithMixedEmptyAndNull()
	{
		$result = AddressList::createAddressTitle('', null, 'Acme Corp');
		$this->assertEquals('acme corp', $result);
	}

	/**
	 * Test createAddressTitle with whitespace-only strings (should be treated as empty)
	 */
	public function testCreateAddressTitleWithWhitespaceOnlyStrings()
	{
		$result = AddressList::createAddressTitle('   ', '  ', '   ');
		$this->assertEquals('', $result);
	}

	/**
	 * Test createAddressTitle with mixed whitespace and values
	 */
	public function testCreateAddressTitleWithMixedWhitespaceAndValues()
	{
		$result = AddressList::createAddressTitle('Max', '  ', 'Acme Corp');
		$this->assertEquals('max acme corp', $result);
	}

	/**
	 * Test createAddressTitle with special characters
	 */
	public function testCreateAddressTitleWithSpecialCharacters()
	{
		$result = AddressList::createAddressTitle('Max', 'Müller', 'Société &Co.');
		$this->assertEquals('max müller société &co.', $result);
	}

	/**
	 * Test createAddressTitle with mixed case characters
	 */
	public function testCreateAddressTitleWithMixedCaseCharacters()
	{
		$result = AddressList::createAddressTitle('MaX', 'MuStErMaNn', 'AcMe CoRp');
		$this->assertEquals('max mustermann acme corp', $result);
	}

	/**
	 * Test createAddressTitle with numeric values as strings
	 */
	public function testCreateAddressTitleWithNumericValues()
	{
		$result = AddressList::createAddressTitle('123', '456', '789');
		$this->assertEquals('123 456 789', $result);
	}

	/**
	 * Test createAddressTitle without company parameter (backward compatibility)
	 */
	public function testCreateAddressTitleWithoutCompanyParameter()
	{
		$result = AddressList::createAddressTitle('Max', 'Mustermann');
		$this->assertEquals('max mustermann', $result);
	}

	/**
	 * Test createAddressTitle with zero values
	 */
	public function testCreateAddressTitleWithZeroValues()
	{
		$result = AddressList::createAddressTitle('0', '0', '0');
		$this->assertEquals('', $result);
	}

	/**
	 * Test createAddressTitle with false values (should be empty)
	 */
	public function testCreateAddressTitleWithFalseStringValue()
	{
		$result = AddressList::createAddressTitle('false', 'false', 'false');
		$this->assertEquals('false false false', $result);
	}

	/**
	 * Test createAddressTitle with leading and trailing whitespace (should be trimmed)
	 */
	public function testCreateAddressTitleWithLeadingTrailingWhitespace()
	{
		$result = AddressList::createAddressTitle('  Max  ', '  Mustermann  ', '  Acme Corp  ');
		$this->assertEquals('max mustermann acme corp', $result);
	}

	/**
	 * Test createAddressTitle with newlines and tabs (should be treated as whitespace)
	 */
	public function testCreateAddressTitleWithNewlinesAndTabs()
	{
		$result = AddressList::createAddressTitle("\t\n", "\n\t", "\t");
		$this->assertEquals('', $result);
	}
}
