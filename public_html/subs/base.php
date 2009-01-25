<?
/*
 * BWReg2 Base classes
 * Copyright (C) 2007 Kristian Lyngstol <kristian@bohemians.org>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 * 
 */

/* Basic string class, need to have get() and getraw()
 * since this is what these classes end up using.
 * getraw() is get(), except when a menu is fetched. 
 */
class str {
	var $data;
	function str($data)
	{
		$this->data = $data;
	}

	function get()
	{
		return $this->data;
	}
	
	function getraw()
	{
		return $this->data;
	}
}
function &str($str)
{
	return new str($str);
}
/* The basic container type, most other classes extends this.
 */
class box {
	var $items;

	function box()
	{
	}
	function add(&$item)
	{
		if(!is_object($item) || $item == NULL)
		{
			print "WARNING! \"" . $item . "\" added as object! ";
			$this->addst($item);
		} else 
			$this->items[] =& $item;
	}
	function addst($item)
	{
		$this->items[] =& new str($item);
	}
	function get()
	{
		$menu = "";
		if(!isset($this->items))
				return $menu;
		foreach ($this->items as $item)
			$menu .= $item->get();
		return $menu;
	}
	function getraw()
	{
		$menu = "";
		foreach ($this->items as $item)
		{
			if(get_class($item) == "menu")
				$menu .= $item->getraw();
			else
				$menu .= $item->get();
		}
		return $menu;
	}
	function output()
	{
		foreach ($this->items as $item)
			print($item->get());
	}
}

class dateStuff 
{
	var $year = 1970;
	var $month = 0;
	var $day = 0;
	var $hour = 0;
	var $minute = 0;
	var $second = 0;

	function dateStuff($year = -1, $month = -1, $day = -1, $hour = -1, $minute = -1, $second = -1)
	{
		$this->year = $year;
		$this->month = $month;
		$this->day = $day;
		$this->hour = $hour;
		$this->minute = $minute;
		$this->second = $second;
	}
	function gettime()
	{
		if ($this->hour < 0)
			return "";
		$string = $this->hour;
		if ($this->minute < 0)
			return "kl" . $string . ", ";
		$string .= ":" . $this->minute;
		if ($this->second < 0)
			return $string . ", ";
		$string .= ":" . $this->second;
		return $string . ", ";
	}
	
	function getdate()
	{
		if ($this->day > 0)
			$string = $this->day . ".";
		if ($this->month > 0)
			$string .= $this->month;
		if ($this->year < 0)
			return $string;
		$string .= " " .  $this->year;
		return $string;
	}
	
	function get()
	{
		$string = $this->gettime();
		$string .= $this->getdate();
		return $string;
	}
}

/* Handles errors in a generic fashion.
 */
class Error extends Exception
{
    public function __construct($message, $code = 0) {
	parent::__construct($message, $code);
    }
    public function get()
    {
	return $this->message;
    }
}

function BWlog($level,$message,$module = "")
{
	global $db;
	if (!is_object($db))
		return false;
	global $event;
	global $me;
	$query = "INSERT INTO log (uid,eid,level,module,message) VALUES('" . database::escape($me->uid) . "','" . $event->eid . "','" . database::escape($level) . "','" . database::escape($module) . "','" . database::escape($message) . "');";
	$db->insert($query);
	
}
?>
