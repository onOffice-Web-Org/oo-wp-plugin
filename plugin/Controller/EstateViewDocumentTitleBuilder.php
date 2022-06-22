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

namespace onOffice\WPlugin\Controller;

use Exception;
use onOffice\WPlugin\WP\WPQueryWrapper;

class EstateViewDocumentTitleBuilder
{
	/** @var EstateTitleBuilder */
	private $_pEstateTitleBuilder;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper;

	/**
	 * @param EstateTitleBuilder $pEstateTitleBuilder
	 * @param WPQueryWrapper $pWPQueryWrapper
	 */
	public function __construct(EstateTitleBuilder $pEstateTitleBuilder, WPQueryWrapper $pWPQueryWrapper)
	{
		$this->_pEstateTitleBuilder = $pEstateTitleBuilder;
		$this->_pWPQueryWrapper = $pWPQueryWrapper;
	}

	/**
	 * @param string $title see Wordpress internal function wp_get_document_title()
	 * @param string $format
	 * @return string
	 * @throws Exception
	 */
	public function buildDocumentTitleField(string $format): string
	{
		$estateId = (int)$this->_pWPQueryWrapper->getWPQuery()->get('estate_id', 0);
		if ($estateId === 0) {
			return '';
		}

		return $this->_pEstateTitleBuilder->buildCustomFieldTitle($estateId, $format);
	}

	/**
	 * @param  array  $title  see Wordpress internal function wp_get_document_title()
	 *
	 * @return array
	 * @throws Exception
	 */
	public function buildDocumentTitle( array $title ): array
	{
		$estateId = (int) $this->_pWPQueryWrapper->getWPQuery()->get( 'estate_id', 0 );
		if ( $estateId === 0 ) {
			return $title;
		}
		$title['title'] = $this->_pEstateTitleBuilder->buildTitle( $estateId, '%1$s' );

		return $title;
	}
}
