<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

namespace onOffice\WPlugin\Controller\ContentFilter;

use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\DataView\UnknownViewException;
use function add_shortcode;

/**
 *
 */

class ContentFilterShortCodeRegistrator
{
	/** @var ContentFilterShortCodeBuilder */
	private $_pBuilder = null;


	/**
	 *
	 * @param ContentFilterShortCodeBuilder $pBuilder
	 *
	 */

	public function __construct(ContentFilterShortCodeBuilder $pBuilder)
	{
		$this->_pBuilder = $pBuilder;
	}


	/**
	 *
	 */

	public function register()
	{
		foreach ($this->_pBuilder->buildAllContentFilterShortCodes() as $pInstance) {
			/* @var $pInstance ContentFilterShortCode */
			add_shortcode($pInstance->getTag(), function($attributesInput, $content, $tag) use ($pInstance) {
				try {
					return $pInstance->replaceShortCodes((array)$attributesInput);
				} catch (UnknownViewException | UnknownFormException $e) {
					return self::buildShortcodeString($tag, (array)$attributesInput);
				}
			});
		}
	}

	private static function buildShortcodeString(string $tag, array $attributes): string
	{
		$parts = [];
		foreach ($attributes as $name => $value) {
			if (!is_string($name) || $name === '' || $value === null || $value === '') {
				continue;
			}
			$parts[] = esc_html($name) . '="' . esc_html((string) $value) . '"';
		}
		$inner = $parts === [] ? '' : ' ' . implode(' ', $parts);
		return '[' . esc_html($tag) . $inner . ']';
	}
}