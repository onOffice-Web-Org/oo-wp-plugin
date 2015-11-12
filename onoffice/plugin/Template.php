<?php

/**
 *
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

/**
 *
 */

class Template
{
	/** @var \onOffice\WPlugin\EstateList */
	private $_pEstateList = null;

	/** @var string */
	private $_templateName = null;


	/**
	 *
	 * @param \onOffice\WPlugin\EstateList $pEstates
	 * @param string $templateName
	 *
	 */

	public function __construct( EstateList $pEstates, $templateName ) {
		$this->_pEstateList = $pEstates;
		$this->_templateName = $templateName;

		if ( ! file_exists( $this->getFilePath( 'template.php' ) ) ) {
			$this->_templateName = 'default';
		}
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function render() {
		$pEstateList = $this->_pEstateList;
		$result = $this->getIncludeContents( $pEstateList );

		return $result;
	}


	/**
	 *
	 * @param \onOffice\WPlugin\EstateList $pEstates Used later on in the included template
	 * @param string $templateName
	 * @return string
	 *
	 */

	private function getIncludeContents( EstateList $pEstates ) {
		$filename = $this->getFilePath( 'template.php' );
		if ( file_exists($filename) ) {
			ob_start();
			include $filename;
			return ob_get_clean();
		}

		return '';
	}


	/**
	 *
	 * @param string $fileName
	 * @return string
	 *
	 */

	private function getFilePath( $fileName ) {
		return __DIR__.'/../templates/' . $this->_templateName . '/'. $fileName;
	}
}
