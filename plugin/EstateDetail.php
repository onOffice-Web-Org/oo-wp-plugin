<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin;

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\WPlugin\Controller\EstateViewSimilarEstates;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataSimilarEstatesSettingsHandler;
use onOffice\WPlugin\Types\LinksTypes;
use onOffice\WPlugin\Types\MovieLinkTypes;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\SDK\onOfficeSDK;
use WP_Embed;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class EstateDetail
	extends EstateList
{
	/** @var int */
	private $_estateId = null;

	/**
	 *
	 * @return int
	 *
	 */

	protected function getNumEstatePages()
	{
		return 1;
	}


	/**
	 *
	 * @param int $id
	 *
	 */

	public function loadSingleEstate($id)
	{
		$this->_estateId = $id;
		$this->loadEstates(1);
	}


	/**
	 *
	 * @return array
	 * @throws Exception
	 *
	 */

	protected function getPreloadEstateFileCategories()
	{
		$fileCategories = parent::getPreloadEstateFileCategories();
		$pDataView = $this->getDataView();

		if (!$pDataView instanceof DataDetailView) {
			throw new Exception('DataView must be instance of DataDetailView!');
		}

		$movieLinksActive = $pDataView->getMovieLinks() !== MovieLinkTypes::MOVIE_LINKS_NONE;

		$oguloLinksActive = $pDataView->getOguloLinks() !== LinksTypes::LINKS_DEACTIVATED;

		$objectLinksActive = $pDataView->getObjectLinks() !== LinksTypes::LINKS_DEACTIVATED;

		$linksActive = $pDataView->getLinks() !== LinksTypes::LINKS_DEACTIVATED;

		if ($movieLinksActive) {
			$fileCategories []= MovieLinkTypes::FILE_TYPE_MOVIE_LINK;
		}

		if ($oguloLinksActive) {
			$fileCategories []= LinksTypes::FILE_TYPE_OGULO_LINK;
		}

		if ($objectLinksActive) {
			$fileCategories []= LinksTypes::FILE_TYPE_OBJECT_LINK;
		}

		if ($linksActive) {
			$fileCategories []= LinksTypes::FILE_TYPE_LINK;
		}

		return $fileCategories;
	}


	/**
	 *
	 * @return array Returns an array if Movie Links are active and displayed as Link
	 *
	 */

	public function getEstateMovieLinks(): array
	{
		$result = array();
		$estateId = $this->getCurrentEstateId();

		if ($this->getDataView()->getMovieLinks() === MovieLinkTypes::MOVIE_LINKS_LINK) {
			$result = $this->getEstateFiles()->getEstateMovieLinks($estateId);
		}

		return $result;
	}

	/**
	 *
	 * @return array Returns an array if Movie Links are active and displayed as Link
	 *
	 */

	public function getEstateLinks($type): array
	{
		$result = array();
		$estateId = $this->getCurrentEstateId();
		if (
			($type === 'ogulo' && $this->getDataView()->getOguloLinks() === LinksTypes::LINKS_LINK) ||
			($type === 'object' && $this->getDataView()->getObjectLinks() === LinksTypes::LINKS_LINK) ||
			($type === 'link' && $this->getDataView()->getLinks() === LinksTypes::LINKS_LINK)
		) {
			$result = $this->getEstateFiles()->getEstateLinks($estateId, $type);
		}

		return $result;
	}

	/**
	 *
	 * @return string
	 *
	 */

	public function getShortCodeForm(): string
	{
		$result = '';

		if ($this->getDataView()->getShortCodeForm() == '') {
			return '';
		}

		$result = $this->getDataView()->getShortCodeForm();

		return  '[oo_form form="' . $result . '"]';

	}


	/**
	 *
	 * @param array $options key-value array of options (supports width and/or height)
	 * @return array
	 *
	 */

	public function getMovieEmbedPlayers(array $options = []): array
	{
		$result = array();
		$estateId = $this->getCurrentEstateId();

		if ($this->getDataView()->getMovieLinks() === MovieLinkTypes::MOVIE_LINKS_PLAYER) {
			$pWpEmbed = new WP_Embed();
			$movieLinks = $this->getEstateFiles()->getEstateMovieLinks($estateId);
			$allowedOptions = array_flip(['width', 'height']);
			$newOptions = array_intersect_key($options, $allowedOptions);

			foreach ($movieLinks as $linkId => $properties) {
				$player = $pWpEmbed->shortcode($newOptions, $properties['url']);
				$newProperties = $properties;
				$newProperties['player'] = $player;
				$result[$linkId] = $newProperties;
			}
		}

		return $result;
	}

	public function getLinkEmbedPlayers($type, array $options = [])
	{
		$result = array();
		$estateId = $this->getCurrentEstateId();

		if (
			($type === 'ogulo' && $this->getDataView()->getOguloLinks() === LinksTypes::LINKS_EMBEDDED) ||
			($type === 'object' && $this->getDataView()->getObjectLinks() === LinksTypes::LINKS_EMBEDDED) ||
			($type === 'link' && $this->getDataView()->getLinks() === LinksTypes::LINKS_EMBEDDED)
		) {
			$links = $this->getEstateFiles()->getEstateLinks($estateId, $type);
			$allowedOptions = array_flip(['width', 'height']);
			$newOptions = array_intersect_key($options, $allowedOptions);
			foreach ($links as $linkId => $properties) {
				$player = '<iframe width="' . esc_attr($newOptions['width']) . '" height="' . esc_attr($newOptions['height']) . '" src="' . esc_attr($properties['url']) .'" style="border: none"
							allowfullscreen=""></iframe>';
				$newProperties = $properties;
				$newProperties['player'] = $player;
				$result[$linkId] = $newProperties;
			}
		}

		return $result;
	}


	/**
	 *
	 * @param $pContainer
	 * @return string
	 * @throws API\APIEmptyResultException
	 * @throws API\ApiClientException
	 * @throws DataView\UnknownViewException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws HttpFetchNoResultException
	 */

	public function getSimilarEstates(): string
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pDataSimilarEstatesSettings = $pContainer->get(DataSimilarEstatesSettingsHandler::class);
		$pDataSimilarSettings = $pDataSimilarEstatesSettings->getDataSimilarEstatesSettings();
		if (!$pDataSimilarSettings->getDataSimilarViewActive()) {
			return '';
		}
		$pDataViewSimilarEstates = $pDataSimilarSettings->getDataViewSimilarEstates();

		$pSimilarEstates = new EstateViewSimilarEstates($pDataViewSimilarEstates);
		$pCopyThis = clone $this;
		$pCopyThis->setFormatOutput(false);
		$pCopyThis->loadEstates();
		$pSimilarEstates->loadByMainEstates($pCopyThis);
		return $pSimilarEstates->generateHtmlOutput($this->_estateId);
	}


	/**
	 *
	 * @return int
	 *
	 */

	protected function getRecordsPerPage()
	{
		return 1;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function addExtraParams(): array
	{
		return [];
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getEstateId(): int
	{
		return $this->_estateId;
	}


	/**
	 *
	 * @param int $estateId
	 *
	 */

	public function setEstateId(int $estateId)
	{
		$this->_estateId = $estateId;
	}

	/**
	 * @return bool
	 */
	public function getShowEstateMarketingStatus(): bool
	{
		return $this->getDataView()->getShowStatus();
	}

	/**
	 * @param string $field
	 * @return string
	 */
	public function getFieldLabel($field): string
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pLanguage = $pContainer->get(Language::class)->getLocale();
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$dataDetailView = $pDataDetailViewHandler->getDetailView();
		$dataDetailCustomLabel = $dataDetailView->getCustomLabels();
		if(!empty($dataDetailCustomLabel[$field]->$pLanguage) || !empty($dataDetailCustomLabel[$field]->native)){
			$fieldNewName = $dataDetailCustomLabel[$field]->$pLanguage ?? $dataDetailCustomLabel[$field]->native;
		}else {
			$recordType = onOfficeSDK::MODULE_ESTATE;
			$fieldNewName = $this->getEnvironment()->getFieldnames()->getFieldLabel($field, $recordType);
		}
		return $fieldNewName;
	}
}
