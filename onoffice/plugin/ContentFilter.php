<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierFactory;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Utility\__String;
use WP_Query;

/**
 *
 */

class ContentFilter
{
	/**
	 *
	 */

	public function addCustomRewriteTags() {
		add_rewrite_tag('%estate_id%', '([^&]+)');
		add_rewrite_tag('%view%', '([^&]+)');
	}


	/**
	 *
	 */

	public function addCustomRewriteRules() {
		$pDetailView = $this->getEstateDetailView();
		$detailPageId = $pDetailView->getPageId();

		if ($detailPageId != null) {
			$pagename = get_page_uri( $detailPageId );
			$pageUrl = $this->rebuildSlugTaxonomy($detailPageId);
			add_rewrite_rule( '^('.preg_quote( $pageUrl ).')/([0-9]+)/?$',
				'index.php?pagename='.urlencode( $pagename ).'&view=$matches[1]&estate_id=$matches[2]','top' );
		}
	}


	/**
	 *
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function registerEstateShortCodes( $attributesInput )
	{
		global $wp_query;
		$page = 1;
		if ( ! empty( $wp_query->query_vars['page'] ) ) {
			$page = $wp_query->query_vars['page'];
		}

		$attributes = shortcode_atts(array(
			'view' => null,
			'units' => null,
		), $attributesInput);

		if ($attributes['view'] !== null) {
			try {
				$pDetailView = $this->getEstateDetailView();

				if ($pDetailView->getName() === $attributes['view']) {
					$pTemplate = new Template($pDetailView->getTemplate());
					$pEstateDetail = $this->preloadSingleEstate($pDetailView, $attributes['units']);
					$pTemplate->setEstateList($pEstateDetail);
					$result = $pTemplate->render();
					return $result;
				}

				$pListViewFactory = new DataListViewFactory();
				$pListView = $pListViewFactory->getListViewByName($attributes['view']);

				if (is_object($pListView) && $pListView->getName() === $attributes['view']) {
					$this->setAllowedGetParametersEstate($pListView);
					$pTemplate = new Template($pListView->getTemplate());
					$pListViewFilterBuilder = new DefaultFilterBuilderListView($pListView);

					$pEstateList = new EstateList($pListView);
					$pEstateList->setDefaultFilterBuilder($pListViewFilterBuilder);
					$pEstateList->setUnitsViewName($attributes['units']);
					$pTemplate->setEstateList($pEstateList);
					$pEstateList->loadEstates($page);

					$result = $pTemplate->render();
					return $result;
				}
			} catch (Exception $pException) {
				return $this->logErrorAndDisplayMessage($pException);
			}
			return __('Estates view not found.', 'onoffice');
		}
	}


	/**
	 *
	 * @param DataListView $pDataView
	 *
	 */

	private function setAllowedGetParametersEstate(DataListView $pDataView)
	{
		$pFieldNames = new Fieldnames();
		$pFieldNames->loadLanguage();
		$pSearchParameters = SearchParameters::getInstance();

		foreach ($pDataView->getFilterableFields() as $filterableField) {
			$fieldInfo = $pFieldNames->getFieldInformation
				($filterableField, onOfficeSDK::MODULE_ESTATE);

			if ($fieldInfo === null) {
				continue;
			}

			$type = $fieldInfo['type'];

			if (Types\FieldTypes::isNumericType($type) ||
				Types\FieldTypes::isDateOrDateTime($type)) {
				$pSearchParameters->addAllowedGetParameter($filterableField.'__von');
				$pSearchParameters->addAllowedGetParameter($filterableField.'__bis');
			}

			$pSearchParameters->addAllowedGetParameter($filterableField);
		}
	}


	/**
	 *
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function renderFormsShortCodes( $attributesInput )
	{
		$attributes = shortcode_atts(array(
			'form' => null,
		), $attributesInput);

		$formName = $attributes['form'];

		try {
			if ($formName !== null) {
				$pFormConfigFactory = new DataFormConfigurationFactory(null);
				$pFormConfig = $pFormConfigFactory->loadByFormName($formName);
				/* @var $pFormConfig DataFormConfiguration */
				$template = $pFormConfig->getTemplate();
				$pTemplate = new Template( $template );
				$pForm = new Form( $formName, $pFormConfig->getFormType() );
				$pTemplate->setForm( $pForm );
				$htmlOutput = $pTemplate->render();
				return $htmlOutput;
			}
		} catch (Exception $pException) {
			return $this->logErrorAndDisplayMessage($pException);
		}
	}


	/**
	 *
	 * @global WP_Query $wp_query
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function renderAddressShortCodes( $attributesInput )
	{
		global $wp_query;
		$page = 1;
		if ( ! empty( $wp_query->query_vars['page'] ) ) {
			$page = $wp_query->query_vars['page'];
		}

		$attributes = shortcode_atts(array(
			'view' => null,
		), $attributesInput);
		$addressListName = $attributes['view'];

		$pDataListFactory = new DataListViewFactoryAddress();
		/* @var $pAddressListView DataListViewAddress */
		$pAddressListView = $pDataListFactory->getListViewByName($addressListName);

		$pFieldnames = new Fieldnames();
		$pFieldnames->loadLanguage();
		$pAddressList = new AddressList($pFieldnames);

		$pAddressList->loadAddresses($pAddressListView, $page);
		$templateName = $pAddressListView->getTemplate();
		$pTemplate = new Template($templateName);
		$pTemplate->setAddressList($pAddressList);
		return $pTemplate->render();
	}


	/**
	 *
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function renderImpressumShortCodes( $attributesInput )
	{
		try {
			$pImpressum = new Impressum();
			if ( count($attributesInput) == 1 ) {
				$attribute = $attributesInput[0];
				$impressumValue = $pImpressum->getDataByKey($attribute);

				return $impressumValue;
			}
		} catch (Exception $pException) {
			return $this->logErrorAndDisplayMessage($pException);
		}
	}


	/**
	 *
	 * @param string $text
	 * @return string
	 *
	 */

	public function renderWidgetImpressum($text)
	{
		add_shortcode( 'oo_basicdata', array($this, 'renderImpressumShortCodes'));
		return do_shortcode($text);
	}


	/**
	 *
	 * @param int $page
	 * @return string
	 *
	 */

	private function rebuildSlugTaxonomy( $page )
	{
		$pPost = get_post( $page );

		if ($pPost === null) {
			return;
		}

		$listpermalink = $pPost->post_name;
		$parent = wp_get_post_parent_id( $page );

		if ( $parent ) {
			$listpermalink = $this->rebuildSlugTaxonomy( $parent ).'/'.$listpermalink;
		}

		return $listpermalink;
	}


	/**
	 *
	 * @param Exception $pException
	 * @return string
	 *
	 */

	public function logErrorAndDisplayMessage( Exception $pException )
	{
		$output = '';

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$output = '<pre>'
					. '<u><strong>[onOffice-Plugin]</strong> Ein Fehler ist aufgetreten:</u><p>'
					.esc_html((string) $pException).'</pre></p>';
		}

		error_log('[onOffice-Plugin]: '.strval($pException));

		return $output;
	}


	/**
	 *
	 * @global WP_Query $wp_query
	 * @param DataDetailView $pDetailView
	 * @param string $unitsView
	 * @return EstateDetail
	 *
	 */

	private function preloadSingleEstate(DataDetailView $pDetailView, $unitsView)
	{
		global $wp_query;

		$estateId = 0;
		if ( ! empty( $wp_query->query_vars['estate_id'] ) ) {
			$estateId = $wp_query->query_vars['estate_id'];
		}

		$pDefaultFilterBuilder = new DefaultFilterBuilderDetailView();
		$pDefaultFilterBuilder->setEstateId($estateId);

		$pEstateDetailList = new EstateDetail($pDetailView);
		$pEstateDetailList->setDefaultFilterBuilder($pDefaultFilterBuilder);
		$pEstateDetailList->setUnitsViewName($unitsView);
		$pEstateDetailList->loadSingleEstate($estateId);

		return $pEstateDetailList;
	}


	/**
	 *
	 */

	public function registerScripts()
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';

		wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js');
		wp_register_script('gmapsinit', plugins_url('/js/gmapsinit.js', $pluginPath), ['google-maps']);
		wp_register_script('jquery-latest', 'https://code.jquery.com/jquery-latest.js');
		wp_register_script('onoffice-favorites', plugins_url('/js/favorites.js', $pluginPath));
		wp_register_script('onoffice-multiselect', plugins_url('/js/onoffice-multiselect.js', $pluginPath));

		wp_register_style('onoffice-multiselect', plugins_url('/css/onoffice-multiselect.css', $pluginPath));
		wp_register_style('onoffice-forms', plugins_url('/css/onoffice-forms.css', $pluginPath));
		wp_register_script('onoffice-leadform', plugins_url('/js/onoffice-leadform.js', $pluginPath), 'jquery', false, true);
	}


	/**
	 *
	 * @global WP_Query $wp_query
	 * @param array $title see Wordpress internal function wp_get_document_title()
	 * @return string
	 *
	 */

	public function setTitle(array $title)
	{
		global $wp_query;

		if (!isset($wp_query->query_vars['estate_id'])) {
			return $title;
		}

		$pDetailView = $this->getEstateDetailView();
		$pEstateList = $this->preloadSingleEstate($pDetailView, null);
		$modifier = EstateViewFieldModifierTypes::MODIFIER_TYPE_TITLE;
		$pEstateIterator = $pEstateList->estateIterator($modifier);
		$pViewFieldModifierFactory = new ViewFieldModifierFactory(onOfficeSDK::MODULE_ESTATE);
		$pEstateFieldModifier = $pViewFieldModifierFactory->create($modifier);
		$fieldsForTitle = $pEstateFieldModifier->getVisibleFields();

		if ($pEstateIterator) {
			$fetchedValues = array_map(array($pEstateIterator, 'getValueRaw'), $fieldsForTitle);
			$values = array_combine($fieldsForTitle, $fetchedValues);

			$pEstateList->resetEstateIterator();
			$title['title'] = $this->buildEstateTitle($values);
		}

		return $title;
	}


	/**
	 *
	 * @param array $values
	 * @return string
	 *
	 */

	private function buildEstateTitle(array $values)
	{
		$pageTitle = null;
		$estateTitle = $values['objekttitel'];
		$titleLength = __String::getNew($estateTitle)->length();

		if ($titleLength > 0 && $titleLength < 70) {
			$pageTitle = $estateTitle;
		} else {
			// Objektart (Vermarktungsart) in Ort - Objektnummer
			$estateKind = $values['objektart'];
			$estateMarketing = $values['vermarktungsart'];
			$estateCity = $values['ort'];
			$estateNo = $values['objektnr_extern'];
			$format = __('%1$s (%2$s) in %3$s - %4$s', 'onoffice');
			$pageTitle = sprintf($format, $estateKind, $estateMarketing, $estateCity, $estateNo);
		}

		return $pageTitle;
	}


	/**
	 *
	 */

	public function includeScripts()
	{
		wp_enqueue_script( 'gmapsinit' );

		if ( is_file( plugin_dir_path( __FILE__ ).'../templates/default/style.css' ) ) {
			wp_enqueue_style( 'onoffice-template-style.css', $this->getFileUrl( 'style.css' ) );
		}

		if ( is_file( plugin_dir_path( __FILE__ ).'../templates/default/script.js' ) ) {
			wp_enqueue_style( 'onoffice-template-script.js', $this->getFileUrl( 'script.js' ) );
		}

		if (Favorites::isFavorizationEnabled()) {
			wp_enqueue_script( 'onoffice-favorites' );
		}

		wp_enqueue_script('jquery-latest');

		wp_enqueue_script('onoffice-multiselect', '', [], false, true);
		wp_enqueue_style('onoffice-multiselect');
		wp_enqueue_script('onoffice-leadform');
		wp_enqueue_style('onoffice-forms');
	}


	/**
	 *
	 * @param string $fileName
	 * @return string
	 *
	 */

	private function getFileUrl( $fileName )
	{
		return plugins_url( 'onoffice/templates/default/'. $fileName );
	}


	/**
	 *
	 * @return DataDetailView
	 *
	 */

	private function getEstateDetailView(): DataDetailView
	{
		$pDataDetailViewHandler = new DataDetailViewHandler();
		return $pDataDetailViewHandler->getDetailView();
	}
}
