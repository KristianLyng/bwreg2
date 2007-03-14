<?

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

?>
