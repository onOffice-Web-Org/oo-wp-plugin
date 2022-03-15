<?php

namespace onOffice\WPlugin;

class EstateListHandle
{

	/**
	 * @var array[]
	 */
	private $_listCustomField;
	public function __construct()
	{
		$this->_listCustomField = [
							'EN' => [
										'objekttitel' => apply_filters('get_post_metadata','onoffice_title'),
										'objektbeschreibung' => apply_filters('get_post_metadata','onoffice_description'),
										'ort' => apply_filters('get_post_metadata','onoffice_city'),
										'plz' => apply_filters('get_post_metadata','onoffice_postal_code'),
										'objektart' => apply_filters('get_post_metadata','onoffice_property_class'),
										'vermarktungsart' => apply_filters('get_post_metadata','onoffice_marketing_method'),
										'Id' => apply_filters('get_post_metadata','onoffice_id')
									],
							'DEU' => [
										'objekttitel' => apply_filters('get_post_metadata','onoffice_titel'),
										'objektbeschreibung' => apply_filters('get_post_metadata','onoffice_beschreibung'),
										'ort' => apply_filters('get_post_metadata','onoffice_ort'),
										'plz' => apply_filters('get_post_metadata','onoffice_plz'),
										'objektart' => apply_filters('get_post_metadata','onoffice_objektart'),
										'vermarktungsart' => apply_filters('get_post_metadata','onoffice_vermarktungsart'),
										'Id' => apply_filters('get_post_metadata','onoffice_datensatznr')
									]
						];
	}
	public function handleRecord($recordModified)
	{
		foreach ($recordModified as $key => $record) {
			$lang = Language::getDefault();
			if ($lang == 'DEU')
			{
				if (array_key_exists($key,$this->_listCustomField[$lang]))
				{
					$recordModified[$key] = $this->_listCustomField[$lang][$key];
				}
			}
			else
			{
				if (array_key_exists($key,$this->_listCustomField['EN']))
				{
					$recordModified[$key] = $this->_listCustomField['EN'][$key];
				}
			}
		}
		return $recordModified;
	}

	/**
	 * @param array $listCustomField
	 */
	public function setListCustomField(array $listCustomField)
	{
		$this->_listCustomField = $listCustomField;
	}

	/**
	 * @return array[]
	 */
	public function getListCustomField()
	{
		return $this->_listCustomField;
	}
}