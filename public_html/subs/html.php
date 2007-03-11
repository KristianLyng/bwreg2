<?

/* These are some basic, generic elements used all over the place. Some of these
 * might get moved at a later date depending on the rest of the code.
 *
 * This marks the generic part of html.php
 * =========================================================================
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
	var $nItems=0;

	function box()
	{
	}
	function add(&$item)
	{
		if(!is_object($item) || $item == NULL)
		{
			print "WARNING! \"" . $item . "\" added as object! ";
			print "last object: " . $this->items[$this->nItems - 1]->get() . "\n";
			$this->addst($item);
		}
		$this->items[$this->nItems++] =& $item;
	}
	function addst($item)
	{
		$this->items[$this->nItems++] =& new str($item);
	}
	function get()
	{
		$menu = "";
		for ($tmp = 0; $tmp < $this->nItems; $tmp++)
			$menu .= $this->items[$tmp]->get();
		return $menu;
	}
	function getraw()
	{
		$menu = "";
		for ($tmp = 0; $tmp < $this->nItems; $tmp++)
		{
			if(get_class($this->items[$tmp]) == "menu")
			{
				$menu .= $this->items[$tmp]->getraw();
			}
			else
				$menu .= $this->items[$tmp]->get();
		}
		return $menu;
	}
	function output()
	{
		for ($tmp = 0; $tmp < $this->nItems; $tmp++)
			print($this->items[$tmp]->get());
	}
}


/* The following are the classes that actually make up the individual parts
 * of a web page. They are not elementary, because they usually contain
 * more than one html tag, or have the ability to store other advanced
 * objects. Menus, diffrent layoout parts, the actual top-page class, 
 * news-class, etc.
 * This marks the core of the html.php
 * ====================================================================
 */

/* The basic page. All pages need this. 
 * This could probably be a lot prettier, but this is generic and
 * works quite well.
 * The idea is that diffrent parts of bwreg will simply do 
 * $page->content->add(foo) to add something to the content
 * part, and that it's first come first serve, or CSS handles the order.
 * This will only print info and controlboxes if they have actual content.
 * This in turn makes it trivial to add things to the info4 warn-box without
 * caring about anything except the object that's added in that part of the
 * code. This lets us deal with hiding messages, not the actual box.
 */
class page extends box
{
	var $top1;
	var $htmltitle = "No title";
	var $top2;
	var $css = "default.css";
	var $top3;
	var $header = "No header";
	var $top4;
	var $bottom = "</body></html>";
	var $content; 
	var $footer;
	var $info1;
	var $info2;
	var $info3;
	var $info4;
	var $ctrl1;
	var $ctrl2;
	var $ctrl3;
	var $ctrl4;
	var $logo;
	
	function page($title, $header)
	{
		$this->top1 = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
		$this->top1 .= "<html><head><title>";
		$this->htmltitle =  "$title";
		$this->top2 = "</title>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
		$this->top3 = "</head><body><div id=\"titl\">\n";
		$this->top4 = "</div>\n";
		$this->header = $header;
		$this->bottom = "</body></html>\n";
		$this->content = new content();
		$this->footer = new footer();
		$this->ctrl1 = new ctrl("1");
		$this->ctrl2 = new ctrl("2");
		$this->ctrl3 = new ctrl("3");
		$this->ctrl4 = new ctrl("4");
		$this->info1 = new info("1");
		$this->info2 = new info("2");
		$this->info3 = new info("3");
		$this->info4 = new info("4");
		$this->logo = new logo();
	}

	function set_css($css) 
	{
		$this->css = $css;
	}

	function get_css()
	{
		return "<link href=\"css/" . $this->css . "\" type=\"text/css\" rel=\"stylesheet\" />\n";
	}

	function get_header()
	{
		return "<h1>" . $this->header . "</h1>\n";
	}

	function set_header($header)
	{
		$this->header = $header;
	}
	function merge()
	{
		$this->add($this->logo);
		$this->add($this->ctrl1);
		$this->add($this->ctrl2);
		$this->add($this->ctrl3);
		$this->add($this->ctrl4);
		$this->add($this->info1);
		$this->add($this->info2);
		$this->add($this->info3);
		$this->add($this->info4);
		$this->add($this->content);
		$this->add($this->footer);
	}

	function get() 
	{
		$data = $this->top1 . $this->htmltitle . $this->top2 . $this->get_css() . $this->top3;
		$data .= $this->get_header() . $this->top4;
		$data .= parent::get();
		$data .= $this->bottom;
		return $data;
	}
	function output()
	{
		$this->merge();
		print($this->get());
	}
}

/* This is a basic menu.
 * You can add this recursivly,. They will use the getraw
 */
class menu extends box{
	var $title = "";

	function menu($title)
	{
		$this->title = $title;
	}
	function add(&$item)
	{
		parent::addst("<li>");
		parent::add($item);
		parent::addst("</li>\n");
	}
	function addst($item)
	{
		parent::addst("<li>");
		parent::addst($item);
		parent::addst("</li>\n");
	}
	function get()
	{
		$menu = "<div class=\"menuboks\">\n";
		if ($this->title != "")
			$menu .= "<h1>" . $this->title . "</h1>\n";
		$menu .= "<ul>";
		$menu .= parent::getraw();
		$menu .= "</ul>";
		$menu .= "</div>\n";
		return $menu;
	}

	function getraw()
	{
		return $this->title . "<ul>\n" . parent::get() . "</ul>";
	}

	function output()
	{
		print($this->get());
	}
}

class dropdown {
	var $root;
	var $content;

	function dropdown($title)
	{
		$this->root = new menu("");
		$this->content = new menu($title);
	}

	function add(&$data)
	{
		$this->content->add(&$data);
	}
	function addst($data)
	{
		$this->content->addst($data);
	}
	function get()
	{
		$this->root->add($this->content);
		return $this->root->get();
	}
	function getraw()
	{
		$this->root->add($this->content);
		return $this->root->getraw();
	}
}

class namedbox extends box {
	var $type;
	var $name;

	function namedbox($type, $name)
	{
		$this->type = $type;
		$this->name = $name;
	}
	function get() {
		$data = "<div " . $this->type . "=\"" . $this->name . "\">";
		$data .= parent::get();
		$data .= "</div>\n";
		return $data;
	}
	function output()
	{
		print($this->get());
	}
}
/* Control box, essentialy a menu container.
 */
class ctrl extends namedbox {
	function ctrl($id)
	{
		$this->namedbox("id", "ctrl" . $id);
	}
	function get() 
	{
		if($this->nItems > 0)
			return parent::get();
	}
	function getraw() 
	{
		if($this->nItems > 0)
			return parent::getraw();
	}
}

class info extends namedbox {
	function info($id)
	{
		$this->namedbox("id", "info" . $id);
	}
	function get() 
	{
		if($this->nItems > 0)
			return parent::get();
	}
	function getraw() 
	{
		if($this->nItems > 0)
			return parent::getraw();
	}
}

class content extends namedbox {
	function content()
	{
		$this->namedbox("id", "content");
	}
}

class news extends namedbox {
	function news($header1,$header2)
	{
		$this->namedbox("class", "news");
		$this->add(h1($header1));
		$this->add(h2($header2));
		$this->addst("<div class=\"newscontent\">\n");
	}
	function get()
	{
		return parent::get() . "</div>\n";
	}
}

class footer extends namedbox {
	function footer()
	{
		$this->namedbox("id", "footer");
	}
}

class infoboks extends namedbox {
	function infoboks()
	{
		$this->namedbox("class", "infoboks");
	}
}
class logo extends namedbox {
	function logo()
	{
		$this->namedbox("id","logo");
	}
}


/* A basic HTML object. 
 * The create-functions are mostly used, since they are short to write
 * and easy to read afterwards. 
 * This marks the elementary part of html.php
 * ====================================================================
 */
class htmlobject {
	var $open;
	var $ctrl="";
	var $content=NULL;

	function htmlobject($open, $ctrl, &$content)
	{
		$this->open = $open;
		$this->ctrl = $ctrl;
		$this->content =& $content;

	}

	function get()
	{
		$string = "<" . $this->open;
		if ($this->ctrl != "")
			$string .= " " . $this->ctrl;
		if($this->content == "" || $this->content == null)
			$string .= " /";
		$string .= ">";
		if(is_object($this->content))
			$string .= $this->content->get();
		else
			$string .= $this->content;
			
		if($this->content != "" && $this->content != null)
			$string .= "</" . $this->open . ">";
		$string .= "\n";
		return $string;
	}
	function getraw()
	{
		return $this->get();
	}
	function open($open)
	{
		$this->open = $open;
	}
	function ctrl($ctrl)
	{
		$this->open = $ctrl;
	}
	function content($content)
	{
		$this->open = $content;
	}
}

function &htlink($link, &$text)
{
	return new htmlobject("a","href=\"" . $link . "\"", $text);
}

function &img($url, $desc="")
{
	$obj = null;
	return new htmlobject("img","src=\"" . $url . "\" alt=\"" . $desc . "\"",$obj);
}
function &p($content)
{
	return new htmlobject("p","",$content);
}
function &h1($content)
{
	return new htmlobject("h1","",$content);
}
function &h2($content)
{
	return new htmlobject("h2","",$content);
}
function &h3($content)
{
	return new htmlobject("h3","",$content);
}
function &h4($content)
{
	return new htmlobject("h4","",$content);
}
function &h5($content)
{
	return new htmlobject("h5","",$content);
}
function &h6($content)
{
	return new htmlobject("h6","",$content);
}
?>
