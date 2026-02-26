<?php

declare(strict_types=1);

namespace onOffice\WPlugin\WP;

use wpdb;

class WpdbReadCacheProxy
{
	/** @var wpdb */
	private $_wpdb;

	/** @var array<string, mixed> */
	private static $_cache = [];

	public function __construct(wpdb $wpdb)
	{
		$this->_wpdb = $wpdb;
	}

	public static function isEnabled(): bool
	{
		if (defined('OO_DB_REQUEST_CACHE') && OO_DB_REQUEST_CACHE) {
			return true;
		}
		$env = getenv('OO_DB_REQUEST_CACHE');
		return $env !== false && $env !== '' && $env !== '0';
	}

	public static function getWpdb()
	{
		global $wpdb;
		if (self::isEnabled()) {
			static $proxy = null;
			if ($proxy === null) {
				$proxy = new self($wpdb);
			}
			return $proxy;
		}
		return $wpdb;
	}

	private function shouldCache(string $query): bool
	{
		/*
		 * here we can skip caching for some queries.
		 * For example if the quey contains a dynamic part, we should not cache it.
		*/
		return true; //TODO: we will check everything for now. More tests needed!
		//return stripos($query, 'FOUND_ROWS') === false;  // critical (?) but overused FOUND_ROWS
	}

	public function get_row($query = null, $output = null, $y = 0)
	{
		$query = $query ?? $this->_wpdb->last_query;
		if ($query === null || !$this->shouldCache($query)) {
			return $this->_wpdb->get_row($query, $output, $y);
		}
		$key = md5($query . (string)$output . (string)$y);
		if (!array_key_exists($key, self::$_cache)) {
			self::$_cache[$key] = $this->_wpdb->get_row($query, $output, $y);
		}
		return self::$_cache[$key];
	}

	public function get_results($query = null, $output = null)
	{
		$query = $query ?? $this->_wpdb->last_query;
		if ($query === null || !$this->shouldCache($query)) {
			return $this->_wpdb->get_results($query, $output);
		}
		$key = md5($query . (string)$output);
		if (!array_key_exists($key, self::$_cache)) {
			self::$_cache[$key] = $this->_wpdb->get_results($query, $output);
		}
		return self::$_cache[$key];
	}

	public function get_var($query = null, $x = 0, $y = 0)
	{
		$query = $query ?? $this->_wpdb->last_query;
		if ($query === null || !$this->shouldCache($query)) {
			return $this->_wpdb->get_var($query, $x, $y);
		}
		$key = md5($query . (string)$x . (string)$y);
		if (!array_key_exists($key, self::$_cache)) {
			self::$_cache[$key] = $this->_wpdb->get_var($query, $x, $y);
		}
		return self::$_cache[$key];
	}

	public function get_col($query = null, $x = 0)
	{
		$query = $query ?? $this->_wpdb->last_query;
		if ($query === null || !$this->shouldCache($query)) {
			return $this->_wpdb->get_col($query, $x);
		}
		$key = md5($query . (string)$x);
		if (!array_key_exists($key, self::$_cache)) {
			self::$_cache[$key] = $this->_wpdb->get_col($query, $x);
		}
		return self::$_cache[$key];
	}

	public function __call(string $name, array $args)
	{
		return $this->_wpdb->$name(...$args);
	}

	public function __get(string $name)
	{
		return $this->_wpdb->$name;
	}
}
