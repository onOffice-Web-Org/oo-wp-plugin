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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
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
		$pDetailView = DataDetailViewHandler::getDetailView();
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
				$pDetailView = DataDetailViewHandler::getDetailView();

				if ($pDetailView->getName() === $attributes['view'])
				{
					$pTemplate = new Template($pDetailView->getTemplate(), 'estate', 'default_detail');
					$pEstateDetail = $this->preloadSingleEstate($pDetailView, $attributes['units']);
					$pTemplate->setEstateList($pEstateDetail);
					$result = $pTemplate->render();
					return $result;
				}

				$pListViewFactory = new DataListViewFactory();
				$pListView = $pListViewFactory->getListViewByName($attributes['view']);

				if (is_object($pListView) && $pListView->getName() === $attributes['view'])
				{
					$pTemplate = new Template($pListView->getTemplate(), 'estate', 'default');
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
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function renderFormsSortCodes( $attributesInput )
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
				$pTemplate = new Template( $template, 'form', 'defaultform' );
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
	 * @param int $page
	 * @return string
	 *
	 */

	private function rebuildSlugTaxonomy( $page ) {
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
	 * Filter the user's written page
	 *
	 * @param string $content
	 * @return string
	 *
	 */

	public function filter_the_content( $content ) {
		$content = $this->filterForms( $content );
		return $content;
	}


	/**
	 *
	 * @param string $content
	 * @return string
	 *
	 */

	private function filterForms( $content ) {
		$regexSearch = '!(?P<tag>\[oo_form\s+(?P<name>[0-9a-z]+)?\])!';
		$matches = array();
		preg_match_all( $regexSearch, $content, $matches );

		$matchescount = count( $matches ) - 1;
		$formConfig = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );

		if ( 0 == $matchescount || empty( $matches['name'] ) ) {
			return $content;
		}
		$onofficeTags = $matches['name'];

		foreach ( $onofficeTags as $id => $name ) {
			if ( array_key_exists( $name, $formConfig ) ) {
				$pTemplate = new Template( $name, 'form', 'defaultform' );
				$pForm = new Form( $name );
				$pTemplate->setForm( $pForm );
				$htmlOutput = $pTemplate->render();

				$content = str_replace( $matches['tag'][$id], $htmlOutput, $content );
			}
		}

		return $content;
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

	private function preloadSingleEstate(DataDetailView $pDetailView, $unitsView) {
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

	public function registerScripts() {
		wp_register_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js' );
		wp_register_script( 'gmapsinit', plugins_url( '/js/gmapsinit.js', __DIR__ ), array('google-maps') );
		wp_register_script( 'jquery-latest', 'https://code.jquery.com/jquery-latest.js');
		wp_register_script( 'onoffice-favorites', plugins_url( '/js/favorites.js', ONOFFICE_PLUGIN_DIR.'/index.php' ));
	}


	/**
	 *
	 */

	public function includeScripts() {
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
	}


	/**
	 *
	 * @param string $fileName
	 * @return string
	 *
	 */

	private function getFileUrl( $fileName ) {
		return plugins_url( 'onoffice/templates/default/'. $fileName );
	}
}
