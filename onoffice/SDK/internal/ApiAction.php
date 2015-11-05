<?php

/**
 *
 * @author Jakob Jungmann <j.jungmann@onoffice.de>
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */


namespace onOffice\SDK\internal;


/**
 *
 */

class ApiAction
{
	/** @var array */
	private $_actionParameters = array();


	/**
	 *
	 * @param type $actionid
	 * @param type $resourceType
	 * @param type $parameters
	 * @param type $resourceId
	 * @param type $identifier
	 *
	 */

	public function __construct($actionid, $resourceType, $parameters, $resourceId = '', $identifier = '')
	{
		$this->_actionParameters['actionid'] = $actionid;
		$this->_actionParameters['resourcetype'] = $resourceType;
		$this->_actionParameters['parameters'] = $parameters;
		$this->_actionParameters['resourceid'] = $resourceId;
		$this->_actionParameters['identifier'] = $identifier;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getActionParameters()
	{
		return $this->_actionParameters;
	}
}
