<?php

namespace onOffice\WPlugin;

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;

class EstateListHandle
{

	/**
	 * @var array[]
	 */
	private $_listCustomField;
	public function __construct()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pEnvironment = new EstateListEnvironmentDefault($pContainer);
		$pageId = $pEnvironment->getDataDetailViewHandler()
			->getDetailView()->getPageId();
		$this->_listCustomField = [
							'EN' => [
										'objekttitel' => get_metadata('post', $pageId, 'onoffice_title', true),
										'objektbeschreibung' => get_metadata('post', $pageId, 'onoffice_description', true),
										'ort' => get_metadata('post', $pageId, 'onoffice_city', true),
										'plz' => get_metadata('post', $pageId, 'onoffice_postal_code', true),
										'objektart' => get_metadata('post', $pageId, 'onoffice_property_class', true),
										'vermarktungsart' => get_metadata('post', $pageId, 'onoffice_marketing_method', true),
										'Id' => get_metadata('post', $pageId, 'onoffice_id', true),
									],
							'DEU' => [
										'objekttitel' => get_metadata('post', $pageId, 'onoffice_titel', true),
										'objektbeschreibung' => get_metadata('post', $pageId, 'onoffice_beschreibung', true),
										'ort' => get_metadata('post', $pageId, 'onoffice_ort', true),
										'plz' => get_metadata('post', $pageId, 'onoffice_plz', true),
										'objektart' => get_metadata('post', $pageId, 'onoffice_objektart', true),
										'vermarktungsart' => get_metadata('post', $pageId, 'onoffice_vermarktungsart', true),
										'Id' => get_metadata('post', $pageId, 'onoffice_datensatznr', true),
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
					$recordModified[$key] = $this->_listCustomField['DEU'][$key] ?? $recordModified[$key];
				}
			}
			else
			{
				if (array_key_exists($key,$this->_listCustomField['EN']))
				{
					$recordModified[$key] = $this->_listCustomField['EN'][$key] ?? $recordModified[$key];
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