<?php

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */


namespace onOffice\WPlugin\Region;

/**
 *
 */

class Region {
	/** @var string */
	private $_id = null;

	/** @var string */
	private $_name = null;

	/** @var string */
	private $_description = null;

	/** @var string */
	private $_language = null;

	/** @var int[] */
	private $_postalCodes = array();

	/** @var string */
	private $_state = null;

	/** @var string */
	private $_country = null;

	/** @var Region[] */
	private $_children = array();


	/**
	 *
	 * @param string $id
	 * @param string $language
	 *
	 */

	public function __construct($id, $language) {
		$this->_id = $id;
		$this->_language = $language;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getId() {
		return $this->_id;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getName() {
		return $this->_name;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getDescription() {
		return $this->_description;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getLanguage() {
		return $this->_language;
	}


	/**
	 *
	 * @return int[]
	 *
	 */

	public function getPostalCodes() {
		return $this->_postalCodes;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getState() {
		return $this->_state;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getCountry() {
		return $this->_country;
	}


	/**
	 *
	 * @return Region[]
	 *
	 */

	public function getChildren() {
		return $this->_children;
	}


	/**
	 *
	 * @param string $name
	 *
	 */

	public function setName($name) {
		$this->_name = $name;
	}


	/**
	 *
	 * @param string $description
	 *
	 */

	public function setDescription($description) {
		$this->_description = $description;
	}


	/**
	 *
	 * @param int[] $postalcodes
	 *
	 */

	public function setPostalCodes(array $postalcodes) {
		$this->_postalCodes = $postalcodes;
	}


	/**
	 *
	 * @param string $state
	 *
	 */

	public function setState($state) {
		$this->_state = $state;
	}


	/**
	 *
	 * @param string $country
	 *
	 */

	public function setCountry($country) {
		$this->_country = $country;
	}


	/**
	 *
	 * @param Region[] $children
	 *
	 */

	public function setChildren(array $children) {
		$this->_children = $children;
	}


	/**
	 *
	 * @param \onOffice\WPlugin\Region\Region $pChild
	 * 
	 */

	public function addChild(Region $pChild) {
		$this->_children[] = $pChild;
	}
}
