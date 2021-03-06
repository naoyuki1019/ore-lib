<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_Volume
 *
 * @package ore
 */
class ORE_Volume extends ORE_Params {

	const msg_default_value = '----___----';

	/**
	 * @var string
	 */
	public $find_fileds = '*';

	/**
	 * @var string
	 */
    public $count_filed = '*';

	/**
	 * This property is deprecated. Use $rs instead.
	 *
	 * @var array
	 */
	public $entries = [];

	/**
	 * This property is deprecated. Use $r instead.
	 *
	 * @var null
	 */
	public $entry = null;

	/**
	 * record set
	 *
	 * @var array
	 */
	public $rs = [];

	/**
	 * @var null
	 */
	public $r = null;

	/**
	 * @var int
	 */
	protected $_page = 1;

	/**
	 * @var int
	 */
	protected $_limit = 20;

	/**
	 * @var int
	 */
	protected $_total = 0;

	/**
	 * 総件数不要フラグ
	 *
	 * @var int
	 */
	public $flg_no_total = 0;

	/**
	 * @var string
	 */
	protected $_sort_ud = 'asc';

	/**
	 * @var int
	 */
	protected $_sort_key = 1;

	/**
	 * @var \string[][]
	 */
	protected $_sort_key_allows = [
		1 => [
			'label' => 'registration date',
			'query' => 'created {sort_ud}',
		],
		2 => [
			'label' => 'update date',
			'query' => 'modified {sort_ud}',
		],
	];

	/**
	 * @var null
	 */
	protected $_result = null;

	/**
	 * @var array
	 */
	protected $_messages = [];

	/**
	 * @var array
	 */
	protected $_errors = [];

	/**
	 * ORE_Volume constructor.
	 *
	 * @param array $params
	 */
	public function __construct($params = []) {
		parent::__construct($params);
	}

	/**
	 * @param array $params
	 */
	public function set($params = [], $value=null) {

		$type = strtolower(gettype($params));
		if ('array' === $type OR 'object' === $type) {
			foreach ($params as $key => $val) {

				if ('_result' === $key OR '_total' === $key OR '_sort_key_allows' === $key) {
					continue;
				}

				if ('_page' === $key) {
					$this->set_page($val);
					continue;
				}

				if ('_limit' === $key) {
					$this->set_limit($val);
					continue;
				}

				if ('_sort_ud' === $key) {
					$this->set_sort_ud($val);
					continue;
				}

				if ('_sort_key' === $key) {
					$this->set_sort_key($val);
					continue;
				}

				$this->{$key} = $val;
			}
		}
		else {
			$this->{$params} = $value;
		}

		return $this;
	}

	/**
	 * @param $find_fileds
	 */
	public function set_find_fileds($find_fileds) {
		$this->find_fileds = $find_fileds;
	}

	/**
	 * @return string
	 */
	public function find_fileds() {

		// $type = gettype($this->find_fileds);
		//
		// if ('array' === $type AND 0 === count($this->find_fileds)) {
		// 	return '*';
		// }
		//
		// if ('string' === $type AND '' === $this->find_fileds) {
		// 	return '*';
		// }

		return $this->find_fileds;
	}

	/**
	 * @return string
	 */
	public function count_filed() {
		return $this->count_filed;
	}

	/**
	 *
	 */
	public function set_result($result) {
		$this->_result = $result;
	}

	/**
	 *
	 */
	public function result() {
		return $this->_result;
	}

	/**
	 *
	 * @param integer $page 明細ページング処理のページ番号
	 */
	public function set_page($page) {
		if (preg_match('/^[1-9]\d*$/', $page)) {
			$this->_page = $page;
		}
		else if (0 > $page) {
			$this->_page = 1;
		}
	}

	/**
	 *
	 */
	public function page() {
		return $this->_page;
	}

	/**
	 *
	 * @param integer $limit 明細ページング処理の明細数
	 */
	public function set_limit($limit) {
		if (preg_match('/^[1-9]\d*$/', $limit)) $this->_limit = $limit;
	}

	/**
	 *
	 */
	public function limit() {
		return $this->_limit;
	}

	/**
	 * 入力値規制
	 */
	public function set_total($total) {
		$this->_total = (preg_match('/^[1-9]\d*$/', $total)) ? $total : 0;
	}

	/**
	 *
	 */
	public function total() {
		return $this->_total;
	}

	/**
	 * 入力値規制
	 */
	public function set_sort_ud($sort_ud) {
		$sort_ud = strtolower($sort_ud);
		if ('desc' !== $sort_ud AND 'asc' !== $sort_ud) {
			$sort_ud = 'asc';
		}
		$this->_sort_ud = $sort_ud;
	}

	/**
	 *
	 */
	public function sort_ud() {
		return $this->_sort_ud;
	}

	/**
	 * @param $allows
	 */
	public function set_sort_key_allows($allows) {
		if ('ALL' === $allows) {
			$this->_sort_key_allows = 'ALL';
		}
		else if (is_array($allows)) {
			$this->_sort_key_allows = $allows;
		}
	}

	/**
	 * @param $allows
	 */
	public function sort_key_allows() {
		return $this->_sort_key_allows;
	}

	/**
	 * @param $sort_key
	 * @param string $sort_ud
	 * @return mixed|null
	 */
	public function sort_query($sort_key, $sort_ud = 'asc') {

		if (true !== ctype_digit((string)$sort_key)) {
			return null;
		}

		$sort_ud = strtolower($sort_ud);
		if ('desc' !== $sort_ud AND 'asc' !== $sort_ud) {
			$sort_ud = 'asc';
		}

		if (is_array($this->_sort_key_allows)) {
			if (array_key_exists($sort_key, $this->_sort_key_allows)) {
				if (is_array($this->_sort_key_allows[$sort_key])) {
					if (array_key_exists('query', $this->_sort_key_allows[$sort_key])) {
						$query = $this->_sort_key_allows[$sort_key]['query'];
						$query = str_replace('{sort_ud}', $sort_ud, $query);
						return $query;
					}
				}
				else {
					return $this->_sort_key_allows[$sort_key];
				}
			}
		}
		return null;
	}

	/**
	 * ソート
	 *
	 * @param $sort_key
	 */
	public function set_sort_key($sort_key, $sort_ud = null) {
		$bk = $this->_sort_key;
		$this->_sort_key = [];
		$this->add_sort_key($sort_key, $sort_ud);
		if ((TRUE !== is_object($this->_sort_key) AND TRUE !== is_array($this->_sort_key) AND '' !== strval($this->_sort_key))
			OR (TRUE === is_array($this->_sort_key) AND 0 < count($this->_sort_key))) {
			return;
		}
		$this->_sort_key = $bk;
	}

	/**
	 * ソートキー制限
	 *
	 * @param $sort_key
	 */
	public function add_sort_key($sort_key, $sort_ud = null) {
		if (is_array($sort_key)) {
			foreach ($sort_key as $tmp => $sort_ud) {
				$this->_set_sort_key($tmp, $sort_ud);
			}
		}
		else {
			$this->_set_sort_key($sort_key, $sort_ud);
		}
	}

	/**
	 * @param $sort_key
	 * @return bool
	 */
	public function is_allowed_key($sort_key) {

		$type = gettype($sort_key);
		if ('object' === $type OR 'array' === $type OR '' === strval($sort_key)) {
			return false;
		}

		if ('string' === gettype($this->_sort_key_allows)) {
			if ('ALL' === strtoupper($this->_sort_key_allows)) {
				return true;
			}
			else {
				if ($sort_key === $this->_sort_key_allows) {
					return true;
				}
			}
		}
		else {
			if (is_array($this->_sort_key_allows)) {
				if (TRUE === array_key_exists($sort_key, $this->_sort_key_allows)) {
					return true;
				}
				else if (TRUE === in_array($sort_key, $this->_sort_key_allows, true)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param $sort_key
	 * @param $sort_ud
	 */
	private function _set_sort_key($sort_key, $sort_ud) {
		if ($this->is_allowed_key($sort_key)) {
			if (is_null($sort_ud)) {
				$this->_sort_key = $sort_key;
			}
			else {
				$sort_ud = strtolower($sort_ud);
				if ('desc' !== $sort_ud AND 'asc' !== $sort_ud) {
					$sort_ud = 'asc';
				}
				if (! is_array($this->_sort_key)) {
					$this->_sort_key = [];
				}
				$this->_sort_key[$sort_key] = $sort_ud;
			}
		}
	}

	/**
	 * @return string
	 */
	public function sort_key() {
		return $this->_sort_key;
	}

	/**
	 * @return string
	 */
	public function sort_key_query($no = null) {
		if (is_null($no)) {
			$no = $this->sort_key();
		}
		$sort_key_allows = $this->sort_key_allows();
		if (is_array($sort_key_allows)) {
			if (array_key_exists($no, $sort_key_allows)) {
				if (array_key_exists('query', $sort_key_allows[$no])) {
					$query = $sort_key_allows[$no]['query'];
					$query = str_replace('{sort_ud}', $this->sort_ud(), $query);
					return $query;
				}
			}
		}
		return '';
	}

	/**
	 * @return float|int
	 */
	public function offset() {

		// ページがリンクに存在しないページ番号の時は最終ページとする
		$offset = ($this->_page - 1) * $this->_limit;
		if ($this->_total <= $offset) {
			$this->set_page(ceil($this->_total / $this->_limit));
		}

		return ($this->_page - 1) * $this->_limit;
	}

	/**
	 * @return float
	 */
	public function lastpage() {
		return (ceil($this->_total / $this->_limit));
	}

	/**
	 * @param $key
	 * @param $msg
	 */
	public function add_error($key, $msg = self::msg_default_value) {
		if (is_array($key)) {
			foreach ($key as $key2 => $msg2) {
				$this->add_error($key2, $msg2);
			}
		}
		else {
			if (is_array($msg)) {
				if (! empty($msg)) {
					if (! array_key_exists($key, $this->_errors)) {
						$this->_errors[$key] = [];
					}
					foreach ($msg as $m) {
						if (! in_array($m, $this->_errors[$key])) {
							$this->_errors[$key][] = $m;
						}
					}
				}
			}
			else {
				if (self::msg_default_value === $msg) {
					$msg = $key;
					$key = 'error';
				}
				if (! array_key_exists($key, $this->_errors)) {
					$this->_errors[$key] = [];
				}
				if (! in_array($msg, $this->_errors[$key])) {
					$this->_errors[$key][] = $msg;
				}
			}
		}
	}

	/**
	 * @return int
	 */
	public function error_count() {
		return count($this->_errors);
	}

	/**
	 * @return bool
	 */
	public function has_error() {
		return (bool)(0 < $this->error_count());
	}

	/**
	 * @params array $errors
	 */
	public function set_errors($errors) {
		$this->_errors = $errors;
	}

	/**
	 * @return array
	 */
	public function errors($keys=null) {
		if (is_null($keys)) {
			return $this->_errors;
		}

		$errors = [];

		$type = strtolower(gettype($keys));
		if ('array' === $type OR 'object' === $type) {
			foreach ($keys as $key) {
				if (array_key_exists($key, $this->_errors)) {
					$errors[$key] = $this->_errors[$key];
				}
			}
		}
		else {
			if (array_key_exists($keys, $this->_errors)) {
				$errors = $this->_errors[$keys];
			}
		}

		return $errors;
	}

	/**
	 * @param $key
	 * @param $msg
	 */
	public function add_message($key, $msg = self::msg_default_value) {
		if (is_array($key)) {
			foreach ($key as $key2 => $msg2) {
				$this->add_message($key2, $msg2);
			}
		}
		else {
			if (is_array($msg)) {
				if (! empty($msg)) {
					if (! array_key_exists($key, $this->_messages)) {
						$this->_messages[$key] = [];
					}
					foreach ($msg as $m) {
						if (! in_array($m, $this->_messages[$key])) {
							$this->_messages[$key][] = $m;
						}
					}
				}
			}
			else {
				if (self::msg_default_value === $msg) {
					$msg = $key;
					$key = 'message';
				}
				if (! array_key_exists($key, $this->_messages)) {
					$this->_messages[$key] = [];
				}
				if (! in_array($msg, $this->_messages[$key])) {
					$this->_messages[$key][] = $msg;
				}
			}
		}
	}

	/**
	 * @return int
	 */
	public function message_count() {
		return count($this->_messages);
	}

	/**
	 * @return bool
	 */
	public function has_message() {
		return (bool)(0 < $this->message_count());
	}

	/**
	 * @params array $messages
	 */
	public function set_messages($messages) {
		$this->_messages = $messages;
	}

	/**
	 * @return array
	 */
	public function messages() {
		return $this->_messages;
	}

	/**
	 * @param $from
	 * @param $to
	 */
	public function error_copy($from, $to) {
		$errors = $this->errors();
		if (array_key_exists($from, $errors) AND ! array_key_exists($to, $errors)) {
			$this->add_error($to, $errors[$from]);
		}
	}

	/**
	 * @return array
	 */
	public function message_string($glue = "\n") {
		return $this->__string('messages', $glue);
	}

	/**
	 * @param string $glue
	 * @return mixed
	 */
	public function error_string($glue = "\n", $open='', $close='') {
		return $open.$this->__string('errors', $glue).$close;
	}

	/**
	 * @param $type
	 * @param $glue
	 * @return string
	 * @throws \Exception
	 */
	public function __string($type, $glue) {

		$key = '_'.$type;

		if (! property_exists($this, $key)) {
			throw new \Exception('key not found');
		}

		$arr = [];
		foreach ($this->{$key} as $msg) {
			if (is_array($msg)) {
				foreach ($msg as $m) {
					$arr[] = $m;
				}
			}
			else {
				$arr[] = $msg;
			}
		}
		return implode($glue, $arr);
	}

	/**
	 * for valx
	 *
	 * @var array
	 */
	public $arr_key = '';
	public $arr_label = '';
	public $arr_label_suffix = '';
}
