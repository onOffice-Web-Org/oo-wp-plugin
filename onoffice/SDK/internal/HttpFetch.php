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

class HttpFetch
{
	/** @var string */
	private $_url = '';

	/** @var string */
	private $_postData = '';


	/**
	 *
	 * @param string $url
	 * @param string $postData as JSON
	 *
	 */

	public function __construct($url, $postData)
	{
		$this->_url = $url;

		$this->_postData = $postData;
	}


	/**
	 *
	 * @return type
	 * @throws \Exception
	 *
	 */

	public function send()
	{
		$curlResource = curl_init($this->_url);
		curl_setopt($curlResource, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curlResource, CURLOPT_POST, true);
		curl_setopt($curlResource, CURLOPT_POSTFIELDS, $this->_postData);
		curl_setopt($curlResource, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
		curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curlResource);

		if (!$result)
		{
			throw new \Exception('http');
		}

		return $result;
	}
}
