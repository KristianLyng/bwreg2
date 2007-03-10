<?
/* The basic container type, most other classes extends this.
 * Not sure why it's still based on an array, but oh well.
 */
class box {
	var $items;
	var $nItems=0;

	function box()
	{
	}
	function add($item)
	{
		$this->items[$this->nItems++] = $item;
	}
	function get()
	{
		$menu = "";
		for ($tmp = 0; $tmp < $this->nItems; $tmp++)
		{
			$menu .= $pre . $this->items[$tmp] . $post;
		}
		return $menu;
	}
}

/* The basic page. All pages need this. 
 * The HTML parts can probably be prettier...
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
		if($this->ctrl1->nItems > 0)
			$this->add($this->ctrl1->get());
		if($this->ctrl2->nItems > 0)
			$this->add($this->ctrl2->get());
		if($this->ctrl3->nItems > 0)
			$this->add($this->ctrl3->get());
		if($this->ctrl4->nItems > 0)
			$this->add($this->ctrl4->get());
		if($this->info1->nItems > 0)
			$this->add($this->info1->get());
		if($this->info2->nItems > 0)
			$this->add($this->info2->get());
		if($this->info3->nItems > 0)
			$this->add($this->info3->get());
		if($this->info4->nItems > 0)
			$this->add($this->info4->get());
		$this->add($this->content->get());
		$this->add($this->footer->get());
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
 * Use getraw() to nest them easily.
 */
class menu extends box{
	var $title = "";

	function menu($title)
	{
		$this->title = $title;
	}
	function add($item)
	{
		parent::add("<li>" . $item . "</li>\n");
	}
	function get()
	{
		$menu = "<div class=\"menuboks\">\n";
		if ($this->title != "")
			$menu .= "<h1>" . $this->title . "</h1>\n";
		$menu .= "<ul>";
		$menu .= parent::get();
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

	function add($data)
	{
		$this->content->add($data);
	}
	function get()
	{
		$this->root->add($this->content->getraw());
		return $this->root->get();
	}
	function getraw()
	{
		$this->root->add($this->content->getraw());
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
}

class info extends namedbox {
	function info($id)
	{
		$this->namedbox("id", "info" . $id);
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
		$this->add("<h1>" . $header1 . "</h1>\n" . "<h2>" . $header2 . "</h2>\n");
		$this->add("<div class=\"newscontent\">\n");
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


?>
