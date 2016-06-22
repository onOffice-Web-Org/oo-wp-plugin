<?php

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

/**
 *
 */

class ContentFilter
{
	/**
	 *
	 * @param array $config
	 *
	 */

	public function __construct() {}


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
		$pages = $this->getEstateListPageList();

		foreach ( $pages as $listName => $views ) {
			foreach ( $views as $viewName ) {
				add_rewrite_rule( '('.preg_quote($listName).')/('.preg_quote($viewName).')-([^/]*)/?$',
					'index.php?pagename=$matches[1]&view=$matches[2]&estate_id=$matches[3]','top' );
			}
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getEstateListPageList() {
		$list = array();
		$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );

		foreach ( $estateConfig as $configname => $config ) {
			$listpagename = $configname;
			if ( array_key_exists( 'listpagename', $config ) ) {
				$listpagename = $config['listpagename'];
			}
			$views = array_keys( $config['views'] );
			$listIndex = array_search( 'list', $views );
			unset( $views[$listIndex] );

			foreach ( $views as $view ) {
				$listpageid = wp_get_post_parent_id( $config['views'][$view]['pageid'] );
				$listpermalink = get_page_uri($listpageid);
				$list[$listpermalink][] = $view;
			}
		}

		return $list;
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
		$content = $this->filterEstate( $content );
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
				$language = $formConfig[$name]['language'];
				$pTemplate = new Template( $name, 'form', 'defaultform' );
				$pForm = new \onOffice\WPlugin\Form( $name, $language );
				$pTemplate->setForm( $pForm );
				$htmlOutput = $pTemplate->render();

				$content = str_replace( $matches['tag'][$id], $htmlOutput, $content );
			}
		}

		return $content;
	}


	/**
	 *
	 * Insert the estate overview list
	 *
	 * @param string $content
	 * @return string
	 *
	 */

	private function filterEstate( $content ) {
		$regexSearch = '!(?P<tag>\[oo_estate\s+(?P<name>[0-9a-z]+)(?:\s+(?P<view>[0-9a-z]+))?\])!';
		$matches = array();

		preg_match_all( $regexSearch, $content, $matches );
		$matchescount = count( $matches ) - 1;
		$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );

		if ( 0 == $matchescount || empty( $matches['name'] ) ) {
			return $content;
		}

		$onofficeTags = $matches['name'];

		foreach ( $onofficeTags as $id => $name ) {
			$viewName = 'list';

			if ('' != $matches['view'][$id]) {
				$viewName = $matches['view'][$id];
			}

			if ( ! array_key_exists( $name, $estateConfig ) ||
				empty( $estateConfig[$name]['views'][$viewName] ) ) {
				continue;
			}

			if ( ! array_key_exists( 'filter', $estateConfig[$name] ) ) {
				$estateConfig[$name]['filter'] = array();
			}

			$configByName = $estateConfig[$name];
			$templateName = 'default';

			$configByView = $configByName['views'][$viewName];
			$pForm = null;

			if (isset($configByName['views'][$viewName]['template'])) {
				$templateName = $configByName['views'][$viewName]['template'];
			}

			$pTemplate = new Template( $templateName, 'estate', 'default' );

			if ( array_key_exists( 'formname', $configByView ) ) {
				$formName = $configByView['formname'];
				$language = $configByView['language'];

				$pForm = new \onOffice\WPlugin\Form( $formName, $language );
				$pTemplate->setForm($pForm);
			}

			try {
				if ( 'list' == $viewName ) {
					$pEstateList = $this->preloadEstateList( $name, $viewName );
				} else {
					$pEstateList = $this->preloadSingleEstate( $name, $viewName );
				}

				$pTemplate->setEstateList( $pEstateList );
				$htmlOutput = $pTemplate->render();
			} catch (\onOffice\SDK\Exception\SDKException $pSdkException) {
				$htmlOutput = $this->logErrorAndDisplayMessage( $pSdkException );
			}

			$content = str_replace( $matches[0][$id], $htmlOutput, $content );
		}

		return $content;
	}


	/**
	 *
	 * @param \onOffice\SDK\Exception\SDKException $pException
	 * @return string
	 *
	 */

	public function logErrorAndDisplayMessage( \onOffice\SDK\Exception\SDKException $pException ) {
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
	 * @param array $configName
	 *
	 */

	private function preloadEstateList( $configName, $viewName ) {
		global $wp_query;
		$page = 1;
		if ( ! empty( $wp_query->query_vars['page'] ) ) {
			$page = $wp_query->query_vars['page'];
		}

		$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );
		$recordsPerPage = $estateConfig[$configName]['views'][$viewName]['records'];

		$pEstateList = new EstateList( $configName, $viewName );
		$pEstateList->setEstateRecordsPerPage( $recordsPerPage );
		$pEstateList->loadEstates( $page );

		return $pEstateList;
	}


	/**
	 *
	 * @global \WP_Query $wp_query
	 * @param string $configName
	 * @param string $view
	 * @return \onOffice\WPlugin\EstateList
	 *
	 */

	private function preloadSingleEstate( $configName, $view ) {
		global $wp_query;

		$estateId = 0;
		if ( ! empty( $wp_query->query_vars['estate_id'] ) ) {
			$estateId = $wp_query->query_vars['estate_id'];
		}

		$pEstateList = new EstateList( $configName, $view );
		$pEstateList->loadSingleEstate( $estateId );

		return $pEstateList;
	}


	/**
	 *
	 * @global \WP_Query $wp_query
	 * @global \WP_Post $post
	 * @param \WP_Post[] $posts
	 * @return \WP_Post
	 *
	 */

	public function filter_the_posts( $posts ) {
		global $wp_query;
		global $post;

		if ( empty( $posts[0] ) ||
			empty( $wp_query->query_vars['pagename'] ) ) {
			return $posts;
		}

		$view = null;
		$configKey = $this->getConfigKeyByPostname( $wp_query->query_vars['pagename'] );

		if ( isset( $wp_query->query_vars['view'] )) {
			$view = $wp_query->query_vars['view'];
		}

		$detailpageId = null;
		$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );

		if ( array_key_exists( $configKey, $estateConfig ) &&
			! is_null( $view ) &&
			isset( $estateConfig[$configKey]['views'][$view] ) &&
			array_key_exists( 'pageid', $estateConfig[$configKey]['views'][$view] ) ) {
			$detailpageId = $estateConfig[$configKey]['views'][$view]['pageid'];
		}

		if ( ! empty( $wp_query->query_vars['estate_id'] ) &&
			! is_null( $detailpageId ) ) {
			$newPost = get_post( $detailpageId );
			if ( ! is_null( $newPost ) ) {
				$post = $newPost;
				$post->post_content = $this->filter_the_content( $post->post_content );
				$post->post_status = 'publish';
				$post->post_name = 'detailansicht';

				remove_filter ('the_content', 'wpautop');
				return array($post);
			}
		}

		return $posts;
	}


	/**
	 *
	 * @param string $parentname
	 * @return string
	 *
	 */

	private function getConfigKeyByPostname( $parentname ) {
		foreach ( ConfigWrapper::getInstance()->getConfigByKey( 'estate' ) as $index => $config ) {
			if ( array_key_exists( 'listpagename', $config ) &&
				$config['listpagename'] === $parentname ) {
				return $index;
			}
		}

		return null;
	}


	/**
	 *
	 */

	public function registerScripts() {
		wp_register_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js' );
		wp_register_script( 'gmapsinit', plugins_url( '/js/gmapsinit.js', __DIR__ ), array('google-maps') );
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
