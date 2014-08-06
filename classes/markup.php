<?php
namespace Markup;

class Markup
{

	public static $inputs = array();
	public static $errors = array();

	/**
	 * 文字列の切り捨て
	 *
	 * @param $str 処理対象の文字列
	 * @param $len 切り捨てずに表示する文字列の長さ(半角:1, 全角2 にて指定)
	 * @param $html htmlタグが含まれている場合はtrue
	 * @param $addition 切り捨てを行なった場合に表示する代替文字
	 */
	public static function abbreviate($str, $len, $html = false, $addition = '…') {
		$count = 0;

		if ($html)
		{
			if (preg_match_all('|(<.*?>)|is', $str, $matches))
			{
				$count = count($matches[0]);
				for ($i = 1; $i <= $count; $i++)
				{
					$str = preg_replace('|(<.*?>)|is', '<'.sprintf('%03d', $i).'>', $str, 1);
				}
			}
		}

		for ($i = 0; $i < mb_strlen($str); $i++)
		{
			$char = mb_substr($str, $i, 1);
			if ($html)
			{
				if ($char == '<')
				{
					$i += 4;
					continue;
				}
			}
			if (strlen($char) == mb_strlen($char))
			{
				$len -= 1;
			}
			else
			{
				$len -= 2;
			}
			if ($len < 0)
			{
				$str = mb_substr($str, 0, $i).$addition;
				break;
			}
		}

		if ($html)
		{
			for ($i = 1; $i <= $count; $i++)
			{
				$str = str_replace('<'.sprintf('%03d', $i).'>', $matches[1][$i - 1], $str);
			}
		}

		return $str;
	}

	public static function get_input($key)
	{
		//return isset(static::$inputs[$key]) ? static::$inputs[$key] : '';
		return is_null(\Input::post($key)) ? '' : \Input::post($key);
	}

	public static function get_error($key)
	{
		return empty(static::$errors[$key]) ? '' : static::$errors[$key]->get_message();
	}

	public static function checked($key, $needle, $default = null)
	{
		$ret = '';
		$data = static::get_input($key);
		if (isset($data))
		{
			if (is_array($data))
			{
				in_array($needle, $data) and $ret = ' checked="ckecked" ';
			}
			else
			{
				$needle == $data and $ret = ' checked="ckecked" ';
			}
		}
		elseif ($needle === $default)
		{
			$ret = ' checked="ckecked" ';
		}
		return $ret;
	}

	public static function selected($key, $needle)
	{
		$ret = '';
		$data = static::get_input($key);
		if (isset($data))
		{
			$needle === $data and $ret = ' selected="selected" ';
		}
		return $ret;
	}

	public static function value($key)
	{
		return static::get_input($key);
	}

	public static function error_exists($key)
	{
		return ! ! static::get_error($key);
	}

	public static function error_message($key)
	{
		return static::get_error($key);
	}

	public static function flash_exists($key)
	{
		$str = \Session::get_flash($key);
		if (empty($str))
		{
			return false;
		}
		return true;
	}

	public static function flash_message($key)
	{
		return \Session::get_flash($key);
	}

	public static function get_sorter_sort_value($default = null)
	{
		$sort = \Input::get(\Config::get('markup.sorter.keys.sort', 'sort'));
		if (is_null($sort)) $sort = $default;
		return $sort;
	}

	public static function get_sorter_order_value($default = null)
	{
		$order = \Input::get(\Config::get('markup.sorter.keys.order', 'order'));
		if (is_null($order)) $order = $default;
		return $order;
	}

	public static function get_sorter_query_string($excludes)
	{
		$params = array();

		foreach (\Input::get() as $key => $value)
		{
			if (in_array($key, $excludes)) continue;
			$params[] = "{$key}={$value}";
		}

		$ret = implode('&', $params);

		return $ret;
	}

	public static function sorter($uri, $text, $key, $default = null)
	{
		$sort_key = \Config::get('markup.sorter.keys.sort', 'sort');
		$order_key = \Config::get('markup.sorter.keys.order', 'order');
		$order_val_asc = \Config::get('markup.sorter.values.order.asc', 'asc');
		$order_val_desc = \Config::get('markup.sorter.values.order.desc', 'desc');

		$sort_val = $key;
		$order_val = $order_val_asc;
		$class = \Config::get('markup.sorter.class.base', '');

		if (\Input::get($sort_key) == $key or
			(is_null(\Input::get($sort_key)) and ! is_null($default))) {
			if (\Input::get($order_key) != $order_val_desc and $default != $order_val_desc) {
				$order_val = $order_val_desc;
				$class .= ' '.\Config::get('markup.sorter.class.active.asc', '');
			} else {
				$class .= ' '.\Config::get('markup.sorter.class.active.desc', '');
			}
		}

		$uri = \Uri::create($uri);

		if (strpos($uri, '?') === false) {
			$uri .= "?";
		} else {
			$uri .= "&";
		}

		$uri .= "{$sort_key}={$sort_val}&{$order_key}={$order_val}";
		$ret = '<a href="'.$uri.'" class="'.$class.'">'.$text.'</a>';

		return $ret;
	}

	public static function pagination_item_first($name = null)
	{
		return \Pagination::instance($name)->__get('offset') + 1;
	}

	public static function pagination_item_last($name = null)
	{
		$total_last = \Pagination::instance($name)->__get('total_items');
		$page_last = \Pagination::instance($name)->__get('per_page') * \Pagination::instance($name)->__get('current_page');
		return ($total_last < $page_last) ? $total_last : $page_last;
	}

}