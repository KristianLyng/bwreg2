<?

/* BWReg2 HTML rendering classes
 * Copyright (C) 2007 Kristian Lyngstøl
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
 */


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
 * This in turn makes it trivial to add things to the warn-box without
 * caring about anything except the object that's added in that part of the
 * code. This lets us deal with hiding messages, not the actual box.
 */

function uinfolink($uname)
{
	global $page;
	$url = $page->url() . "?page=Userinfo&amp;action=UserGetInfo&amp;user=" . $uname;
	return $url;
}
class page  extends box
{
	var $top1;
	var $htmltitle = "No title";
	var $top2;
	var $css = false;
	var $top3;
	var $top4;
	var $header = "No header";
	var $top5;
	var $bottom = "</body></html>";
	
	var $content; 
	var $footer;
	var $info1;
	var $info2;
	var $info3;
	var $warn;
	var $ctrl1;
	var $ctrl2;
	var $ctrl3;
	var $ctrl4;
	var $logo;

	var $lp;
	function page($title = "no title", $header = "no header")
	{
		$this->top1 = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
//		$this->top1 = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
		$this->top1 .= "<html><head><title>";
		$this->htmltitle =  "$title";
		$this->top2 = "</title>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
		$this->top4 = "</head><body><div id=\"titl\">\n";
		$this->top5 = "</div>\n";
		$this->header = $header;
		$this->bottom = "</body></html>\n";
		$this->content = new namedbox("id", "content");
		$this->footer = new footer();
		$this->ctrl1 = new ctrl("1");
		$this->ctrl2 = new ctrl("2");
		$this->ctrl3 = new ctrl("3");
		$this->ctrl4 = new ctrl("4");
		$this->info1 = new info("1");
		$this->info2 = new info("2");
		$this->info3 = new info("3");
		$this->warn = new info("warn");
		$this->logo = new logo();
		$this->pf = false;
		global $page; // YES. PHP is this retarded. Try changing it to $this and see it fail.
		$this->lp =& add_action("PrintFriendly",&$page);
	}
	
	function actioncb($action)
	{
		$this->pf = true;
		next_action($action,$this->lp);
	}
	function set_css($css) 
	{
		if(!$this->css)
			$this->css = $css;
	}

	function get_css()
	{
		if (!$this->css)
			$this->css = "css/default.css";
		return "<link href=\"" . $this->css . "\" type=\"text/css\" rel=\"stylesheet\">\n";
	}

	function get_header()
	{
		if(!$this->pf)
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
		$this->add($this->warn);
		$this->add($this->content);
		$this->add($this->footer);
	}
	function setrefresh($target = null, $time = 1)
	{
		if(!$target)
			$target = $this->url();
		$this->top3 = "<META http-equiv=\"refresh\" content=\"$time;URL=$target\">";
	}
	function get() 
	{
		$data = $this->top1 . $this->htmltitle . $this->top2 . $this->get_css() . $this->top3;
		$data .= $this->top4 . $this->get_header() . $this->top5;
		$data .= parent::get();
		$data .= $this->bottom;
		return $data;
	}
	function rss()
	{
		//header('Content-type: application/RSS+xml');
	}
	function output()
	{
		if($this->pf)
		{
			$this->css = "css/printfriendly.css";
			$this->add($this->content);
		} else if (isset($this->rss)) {
			print $this->rss->get();
			return;
		} else
			$this->merge();
		print($this->get());
	}
	function url()
	{
		return $_SERVER['PHP_SELF'];
	}
}

/* This is a basic menu.
 * You can add this recursivly,. They will use the getraw
 */
class menu extends box{
	var $title = "";

	function menu($title = "")
	{
		$this->title = $title;
	}
	function add(&$item)
	{
		parent::addst("\t<li>");
		parent::add($item);
		parent::addst("</li>\n");
	}
	function addst($item)
	{
		parent::addst("\t<li>");
		parent::addst($item);
		parent::addst("</li>\n");
	}
	function get()
	{
		$menu = "<div class=\"menuboks\">\n";
		if ($this->title != "" && $this->title != null)
		{
			$menu .= "<h1>";
			if(is_object($this->title))
				$menu .= $this->title->get();
			else
				$menu .= $this->title;
			$menu .= "</h1>\n";
		}
		$menu .= "<ul>\n";
		$menu .= parent::getraw();
		$menu .= "</ul>\n";
		$menu .= "</div>\n";
		return $menu;
	}

	function getraw()
	{
		if(is_object($this->title))
				$string = $this->title->get();
		else
				$string = $this->title;

		return $string . "<ul>\n" . parent::get() . "</ul>";
	}

	function output()
	{
		print($this->get());
	}
}
class menuboks extends namedbox {
	function menuboks($title)
	{
		parent::namedbox("class","menuboks");
	}
}
class dropdown {
	var $root;
	var $content;

	function dropdown($title)
	{
		$this->root = new menu("");
		$this->content = new menu($title);
		$this->root->add($this->content);
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
		return $this->root->get();
	}
	function getraw()
	{
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
		if(isset($this->items))
			return parent::get();
	}
	function getraw() 
	{
		if(isset($this->items))
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
		if(isset($this->items))
			return parent::get();
	}
	function getraw() 
	{
		if(isset($this->items))
			return parent::getraw();
	}
}


class userinfoboks extends namedbox {
	function userinfoboks()
	{
		$this->namedbox("class","userinfo");
	}
}

class htmlnews extends box {
	var $header1;
	var $header2;
	function htmlnews($header1,$header2)
	{
		if(!is_object($header1))
			$this->header1 = str($header1);
		else
			$this->header1 = $header1;
		if(!is_object($header2))
			$this->header2 = str($header2);
		else
			$this->header2 = $header2;
	}
	function get()
	{	
		$data = "<div class=\"news\">";
		$data .= "<h1>" . $this->header1->get() . "</h1>";
		$data .= "<div class=\"h2\">" . $this->header2->get() . "</div>";
		$data .= "<div class=\"newscontent\">\n";
		$data .= parent::get();
		$data .= "</div>\n";
		
		return $data . "</div>\n";
	}
	function getraw()
	{
		return $this->get();
	}
}

class footer extends namedbox {
	function footer()
	{
		$this->namedbox("id", "footer");
	}
	function get() 
	{
		if(isset($this->items))
			return parent::get();
	}
	function getraw() 
	{
		if(isset($this->items))
			return parent::getraw();
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

	function htmlobject($open, $ctrl, &$content,$end = false)
	{
		$this->open = $open;
		$this->ctrl = $ctrl;
		$this->content =& $content;
		$this->end = $end;
	}

	function get()
	{
		$string = "<" . $this->open;
		if ($this->ctrl != "")
			$string .= " " . $this->ctrl;
		$string .= ">";
		if(is_object($this->content))
			$string .= $this->content->get();
		else
			$string .= $this->content;
			
		if($this->content != null || $this->end)
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
		$this->ctrl = $ctrl;
	}
	function content($content)
	{
		$this->content = $content;
	}
}
class form extends htmlobject
{
	var $fcontent;
	function form($action = null,$method = "post")
	{
		global $page;
		$this->fcontent = new box();
		if($action == null)
			$action = $page->url();
		
		$ctrl = "action=\"$action\" method=\"$method\"";
		$this->htmlobject("form", $ctrl,&$this->fcontent);
	}
	function add(&$data)
	{
		$this->fcontent->add($data);
	}
}
class selectbox extends box
{
	function selectbox($name)
	{
		$this->name = $name;
	}

	function get()
	{
		$a = $this->name;
		$str = "<select name=\"$a\" id=\"$a\">\n";
		$str .= parent::get();
		$str .= "</select>";
		return $str;
	}
}

/* Internal for the table class, you shouldn't use this directly. */
class tableelement 
{
	var $content;
	var $cols;
	var $class;
	function tableelement($content, $class, $cols)
	{
		$this->cols = $cols;
		$this->class = $class;
		$this->content = &$content;
		if ($cols == false)
		$this->cols = 1;
	}
	function get()
	{
		$ctrl = "<td";
		if ($this->class != false)
			$ctrl .= " class=\"" . $this->class . "\"";
		if ($this->cols != 1)
			$ctrl .= " colspan=\"" . $this->cols . "\"";
		$ctrl .= ">" . $this->content->get() . "</td>";
		return $ctrl;
	}
}

/* HTML Table class 
 * Define the amount of columns you want and table style lass, and
 * this class will handle the HTML. You can either add to it as you do with
 * any box, or append the colspan or class for the column.
 * This does not output anything if the table is empty. 
 */
class table extends box
{
	var $cols;
	var $class;
	function table($cols, $class = false)
	{
		$this->cols = $cols;
		$this->class = $class;
	}
	function add($object, $cols = false, $class = false)
	{
		parent::add(new tableelement($object,$class,$cols));
	}
	function get()
	{
		$ct = $this->cols;
		$c = 0;
		$set = false;
		$str = "<table";
		if ($this->class != false)
			$str .= " class=\"" . $this->class . "\"";
		$str .= ">\n";
		foreach ($this->items as $item)
		{
			if (!$set)
			{
				$set = true;	
				$str .= "<tr>\n";
			}
			if ($c >= $ct)
			{
				$str .= "</tr>\n<tr>\n";
				$c = 0;
			}
			if (isset($item->cols))
				$c += $item->cols;
			else
				$c++;
			$str .= $item->get();
		}
		if (!$set)
			return "";
		$str .= "</tr></table>\n";
		return $str;
	}
}

class dummy
{
	var $foo="bar";
	function get()
	{
		return;
	}
	function getraw()
	{
	}
}
function htmlbr()
{
	$obj = null;
	return new htmlobject("br","",$obj);
}
function textarea($name, $value = "", $cols="80", $rows = "30")
{
	$ctrl="name=\"$name\" cols=\"$cols\" rows=\"$rows\"";
	return new htmlobject("textarea",$ctrl,$value,true);
}
function flegend($val)
{
	return new htmlobject("legend","",$val,true);
}
function ftext($name,$value = "",$length = false, $maxlength = false)
{
	$obj = null;
	if($length != false)
	{
		if ($maxlength != false)
			$max = $maxlength;
		else
			$max = $length;
		$mylength = "maxlength=\"$max\" size=\"$length\"";
	}
	else
		$mylength = "";
	return new htmlobject("input","type=\"text\" $mylength name=\"$name\" id=\"$name\" value=\"$value\"", $obj);
}

function foption($value,$desc,$check = false)
{
	if (!$check)
		return new htmlobject("option","value=\"$value\"",str($desc));
	return new htmlobject("option","value=\"$value\" selected=\"selected\"",str($desc));

}

function fpass($name,$length = false)
{
	$obj = null;
	if($length != false)
		$mylength = "maxlength=\"$length\" size=\"$length\"";
	else
		$mylength = "";
	return new htmlobject("input","type=\"password\" $mylength name=\"$name\"", $obj);
}

function fcheck($name,$value,$checked = false,$dis=false)
{
	$obj = null;
	if ($checked)
		$add = "CHECKED";
	else 
		$add = "";
	if ($dis)
		$add .= " disabled=\"disabled\"";
	return new htmlobject("input","type=\"checkbox\" id=\"$name$value\" name=\"" . $name . "[]\" value=\"" . $value . "\" $add",$obj);
}

function fsubmit($value = "Submit", $name = "SubmitButton")
{
	$obj = null;
	return new htmlobject("input","type=\"submit\" class=\"submit\" name=\"$name\" value=\"$value\"", $obj);
}

function flabel($for, $content)
{
	$obj = str($content);
	return new htmlobject("label","for=\"$for\"",$obj);
}

function fhidden($action, $name = "action")
{
	$obj = null;
	return new htmlobject("input","type=\"hidden\" name=\"$name\" value=\"$action\"", $obj);
}

function &htlink($link, &$text,$class = false)
{
	if ($class)
		$c = "class=\"$class\"";
	else
		$c = "";
	return new htmlobject("a","href=\"" . $link . "\" $c", $text);
}

function &img($url, $desc="")
{
	$obj = null;
	return new htmlobject("img","src=\"" . $url . "\" alt=\"" . $desc . "\"",$obj);
}
function &fieldset($content)
{
	return new htmlobject("fieldset","",$content);
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
