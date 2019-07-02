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

namespace onOffice\WPlugin\ScriptLoader;

/**
 *
 */

class ScriptLoaderRegistrator
{
	/** @var ScriptLoaderBuilder */
	private $_pBuilder = null;

	/** @var array */
	private $_scriptLoader = [];


	/**
	 *
	 * @param ScriptLoaderBuilder $pBuilder
	 *
	 */

	public function __construct(ScriptLoaderBuilder $pBuilder)
	{
		$this->_pBuilder = $pBuilder;
	}


	/**
	 *
	 * @return $this
	 *
	 */

	public function generate(): self
	{
		$this->_scriptLoader = iterator_to_array($this->_pBuilder->build());
		add_action('wp_enqueue_scripts', function() {
			$this->register();
		}, 9);

		add_action('wp_enqueue_scripts', function() {
			$this->enqueue();
		});
		return $this;
	}


	/**
	 *
	 */

	public function register()
	{
		/* @var $pScriptLoader ScriptLoader */
		foreach ($this->_scriptLoader as $pScriptLoader) {
			$pScriptLoader->register();
		}
	}


	/**
	 *
	 */

	public function enqueue()
	{
		/* @var $pScriptLoader ScriptLoader */
		foreach ($this->_scriptLoader as $pScriptLoader) {
			$pScriptLoader->enqueue();
		}
	}
}
