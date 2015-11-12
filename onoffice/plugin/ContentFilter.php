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
	}


	/**
	 *
	 */

	public function addCustomRewriteRules() {
		$lists = $this->getEstateListPageList();

		foreach ( $lists as $list ) {
			add_rewrite_rule( '('.preg_quote($list).')/([^/]*)/?$',
				'index.php?pagename=$matches[1]&estate_id=$matches[2]','top' );
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getEstateListPageList() {
		$list = array();
		foreach ( $this->_config['estate'] as $config ) {
			if ( array_key_exists('listpagename', $config ) ) {
				$list[] = $config['listpagename'];
			}
		}

		$list = array_unique( $list );
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
		$regexSearch = '!(\[oo_estate ([0-9a-z]*)\])!';
		$matches = array();

		preg_match_all( $regexSearch, $content, $matches );

		$matchescount = count( $matches ) - 1;

		if ( 0 == $matchescount ) {
			return $content;
		}

		$onofficeTags = array_pop( $matches );

		foreach ( $onofficeTags as $id => $name ) {
			if ( ! array_key_exists( $name, $this->_config['estate'] ) ||
				 ! array_key_exists( 'data', $this->_config['estate'][$name] ) ) {
				continue;
			}

			if ( ! array_key_exists( 'filter', $this->_config['estate'][$name] ) ) {
				$this->_config['estate'][$name]['filter'] = array();
			}

			$filter = $this->_config['estate'][$name]['filter'];
			$data = $this->_config['estate'][$name]['data'];

			$pEstateList = new EstateList( $this->_config );
			$pEstateList->loadEstates( $data, $filter );

			$pTemplate = new Template( $pEstateList, $name );
			add_action( 'wp_enqueue_scripts', array($pTemplate, 'inquireIncludes' ) );
			$htmlOutput = $pTemplate->render();

			$content = str_replace( $matches[0][$id], $htmlOutput, $content );
		}

		return $content;
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

		if ( empty( $posts[0] ) ) {
			return $posts;
		}

		$configKey = $this->getConfigKeyByPostname( $posts[0]->post_name );
		$detailpageId = null;

		if (array_key_exists( $configKey, $this->_config['estate'] ) &&
			array_key_exists( 'detailpageid', $this->_config['estate'][$configKey] ) ) {
			$detailpageId = $this->_config['estate'][$configKey]['detailpageid'];
		}

		if ( ! empty( $wp_query->query_vars['estate_id'] ) && ! is_null( $detailpageId ) ) {
			$newPost = get_post( $detailpageId );
			if ( ! is_null( $newPost ) ) {
				$post = $newPost;
				$post->post_content = $this->filter_the_content( $post->post_content );
				$post->post_status = 'publish';
				$post->post_name = 'detailansicht';

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
