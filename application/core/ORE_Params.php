<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * 現在のスコープからアクセスできるプロパティのみ（Publicのみ）取得
 *
 * @param $object
 * @return array
 */
if (! function_exists('get_object_public_vars')) {
	function get_object_public_vars($object) {
		return get_object_vars($object);
	}
}

/**
 * Class ORE_Params
 *
 * @package ore
 */
class ORE_Params {

	// Public変数は定義しない事！

	/**
	 * @var int
	 */
	protected $_delimiter_type = 0; // 1:codeigniter;

	/**
	 * @param int $delimiter_type
	 */
	public function set_delimiter_type($delimiter_type) {
		$this->_delimiter_type = $delimiter_type;
	}

	/**
	 * ORE_Params constructor.
	 *
	 * @param array $params
	 */
	public function __construct($params = []) {

		$this->set($params);
	}

	/**
	 * @param mixed $params
	 * @param mixed $value
	 * @return $this
	 */
	public function set($params = [], $value = null) {
		$type = strtolower(gettype($params));
		if ('array' === $type || 'object' === $type) {
			foreach ($params as $key => $val) {
				$this->{$key} = $val;
			}
		}
		else {
			$this->{$params} = $value;
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		$tmp = [];
		$array = get_object_vars($this);
		$this->_to_array($tmp, $array);
		return $tmp;
	}

	/**
	 * @return array
	 */
	public function to_public_array() {
		$tmp = [];
		$array = get_object_public_vars($this);
		$this->_to_array($tmp, $array);
		return $tmp;
	}

	/**
	 * @param array $tmp
	 * @param mixed $array
	 */
	private function _to_array(&$tmp, &$array) {

		if (is_array($array) || is_object($array)) {

			foreach ($array as $key => $val) {

				if (is_array($val)) {

					$tmp[$key] = [];
					$this->_to_array($tmp[$key], $val);
				}

				else if (is_object($val)) {

					$val = get_object_vars($val);
					$tmp[$key] = [];
					$this->_to_array($tmp[$key], $val);
				}

				else {
					$tmp[$key] = $val;
				}

			}
		}
	}

	/**
	 * @param mixed $remove_keys
	 * @param bool $public
	 * @return string
	 */
	public function to_uri($remove_keys = [], $public = false) {

		if (! is_array($remove_keys)) {
			if ('' === strval($remove_keys)) {
				$remove_keys = [];
			}
			else {
				$remove_keys = [$remove_keys];
			}
		}

		$remove_keys[] = '_delimiter_type';

		$keys = [];
		$tmp = [];
		if (true === $public) {
			$array = $this->to_public_array();
		}
		else {
			$array = $this->to_array();
		}

		foreach ($array as $key => $val) {
			if (in_array($key, $remove_keys)) {
				unset($array[$key]);
			}
		}

		$this->_to_uri($keys, $tmp, $array);

		if (0 < count($tmp)) {
			if (1 == $this->_delimiter_type) {
				return implode('/', $tmp);
			}
			else {
				return implode('&', $tmp);
			}
		}
		else {
			return "";
		}
	}

	/**
	 * @param array $keys
	 * @param array $tmp
	 * @param array $array
	 */
	private function _to_uri(&$keys, &$tmp, &$array) {

		foreach ($array as $key => $val) {

			array_push($keys, $key);

			if (is_array($val)) {

				if (0 < count($val)) {
					$this->_to_uri($keys, $tmp, $val);
				}
			}

			else if (is_object($val)) {

				$val = get_object_vars($val);

				if (0 < count($val)) {
					$this->_to_uri($keys, $tmp, $val);
				}
			}

			else {
				if ("" != $val || 0 === $val) {

					$uri_keys = $keys;

					$uri_key = array_shift($uri_keys);

					foreach ($uri_keys as $uri_key2) {
						$uri_key .= "[".urlencode($uri_key2)."]";
					}

					if (1 == $this->_delimiter_type) {
						$tmp[] = $uri_key;
						$tmp[] = ('' !== strval($val)) ? urlencode($val) : '';
					}
					else {
						$val = ('' !== strval($val)) ? urlencode($val) : '';
						$tmp[] = $uri_key.'='.$val;
					}
				}
			}

			array_pop($keys);
		}
	}

	/**
	 * @param mixed $pointing_keys
	 * @return string
	 */
	public function to_pointing_uri($pointing_keys = []) {

		if (! is_array($pointing_keys)) {
			$pointing_keys = [$pointing_keys];
		}

		$keys = [];
		$tmp = [];
		$array = $this->to_array();
		foreach ($array as $key => $val) {
			if (! in_array($key, $pointing_keys)) {
				unset($array[$key]);
			}
		}

		$this->_to_uri($keys, $tmp, $array);

		if (0 < count($tmp)) {
			if (1 == $this->_delimiter_type) {
				return implode('/', $tmp);
			}
			else {
				return implode('&', $tmp);
			}
		}
		else {
			return "";
		}
	}
}
