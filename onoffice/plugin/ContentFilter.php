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
	/** @var array */
	private $_config = array();


	/**
	 *
	 * @param array $config
	 *
	 */

	public function __construct( array $config ) {
		$this->_config = $config;
	}


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
		foreach ( $this->_config['estate'] as $configname => $config ) {
			$listpagename = $configname;
			if ( array_key_exists('listpagename', $config ) ) {
				$listpagename = $config['listpagename'];
			}
			$views = array_keys($config['views']);
			$listIndex = array_search('list', $views);
			unset($views[$listIndex]);

			$list[$listpagename] = array_values($views);
		}

		return $list;
	}


	/**
	 *
	 * Insert the estate overview list
	 *
	 * @param string $content
	 * @return string
	 *
	 */

	public function filter_the_content( $content ) {
		$regexSearch = '!(?P<tag>\[oo_estate\s+(?P<name>[0-9a-z]+)(?:\s+(?P<view>[0-9a-z]+))?\])!';
		$matches = array();

		preg_match_all( $regexSearch, $content, $matches );

		$matchescount = count( $matches ) - 1;

		if ( 0 == $matchescount || empty( $matches['name'] ) ) {
			return $content;
		}

		$onofficeTags = $matches['name'];

		foreach ( $onofficeTags as $id => $name ) {
			$viewName = 'list';

			if ('' != $matches['view'][$id]) {
				$viewName = $matches['view'][$id];
			}

			if ( ! array_key_exists( $name, $this->_config['estate'] ) ||
				empty( $this->_config['estate'][$name]['views'][$viewName] ) ) {
				continue;
			}

			if ( ! array_key_exists( 'filter', $this->_config['estate'][$name] ) ) {
				$this->_config['estate'][$name]['filter'] = array();
			}

			$configByName = $this->_config['estate'][$name];
			$templateName = 'default';

			if (isset($configByName['views'][$viewName]['template'])) {
				$templateName = $configByName['views'][$viewName]['template'];
			}

			if ( 'list' == $viewName ) {
				$pEstateList = $this->preloadEstateList( $name, $viewName );
			} else {
				$pEstateList = $this->preloadSingleEstate( $name, $viewName );
			}

			$pTemplate = new Template( $pEstateList, $templateName );
			$htmlOutput = $pTemplate->render();

			$content = str_replace( $matches[0][$id], $htmlOutput, $content );
		}

		return $content;
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
		$pEstateList = new EstateList( $this->_config, $configName, $viewName );

		$recordsPerPage = $this->_config['estate'][$configName]['views'][$viewName]['records'];
		$pEstateList->setEstateRecordsPerPage($recordsPerPage);
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

		$pEstateList = new EstateList( $this->_config, $configName, $view );
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

		if (array_key_exists( $configKey, $this->_config['estate'] ) &&
			! is_null( $view ) &&
			isset( $this->_config['estate'][$configKey]['views'][$view] ) &&
			array_key_exists( 'pageid', $this->_config['estate'][$configKey]['views'][$view] ) ) {
			$detailpageId = $this->_config['estate'][$configKey]['views'][$view]['pageid'];
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
		foreach ( $this->_config['estate'] as $index => $config ) {
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

	public function includeScripts() {
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
