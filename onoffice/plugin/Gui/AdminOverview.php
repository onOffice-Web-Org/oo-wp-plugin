<?php

/**
 *
 * @version $Id: $
 *
 * @author Jakob Jungmann <j.jungmann@onoffice.de>
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) Software AG
 *
 */

/**
 *
 */

namespace onOffice\WPlugin\Gui;

class AdminOverview
{
	/** @var string */
	private $_pageSlug = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pageSlug = 'onoffice';
	}


	/**
	 *
	 */

	public function renderSettings()
	{
		add_settings_section('oo-overview', __('API settings'),
				function(){}, $this->_pageSlug.'-settings');

		register_setting( 'oo-overview', 'testOption', array(
			'type' => 'bool',
			'description' => 'test something in here',
			'default' => false,
		) );

		add_settings_field( 'testOption', __(''), function() {
			echo '<input type="text">';
		}, $this->_pageSlug.'-settings', 'oo-overview', array() );


		do_settings_sections( $this->_pageSlug.'-settings' );
	}


	/**
	 *
	 * @param string $title
	 *
	 */

	public function pageTitle($title)
	{
		echo '<h1>'.esc_html_x('onOffice', 'onoffice');

		if ($title != '')
		{
			echo ' › '.esc_html_x($title, 'onoffice');
		}

		echo '</h1>';
	}


	/**
	 *
	 */

	public function register_menu()
	{
		add_menu_page( __('onOffice', 'onoffice'), __('onOffice', 'onoffice'), 'edit_pages', $this->_pageSlug, array($this, 'pageTitle') );
		add_submenu_page( $this->_pageSlug, __('Estates', 'onoffice'), __('Estates', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-estates', function() {});
		add_submenu_page( $this->_pageSlug, __('Forms', 'onoffice'), __('Forms', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-forms', function() {});
		add_submenu_page( $this->_pageSlug, __('Modules', 'onoffice'), __('Modules', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-modules', function() {});
		add_submenu_page( $this->_pageSlug, __('Settings', 'onoffice'), __('Settings', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-settings', array($this, 'renderSettings'));
	}
}
