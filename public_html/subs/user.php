<?
require_once("subs/base.php");
require_once("subs/html.php");
class userinfo 
{
	var $firstname = "";
	var $lastname = "";
	var $phone = "";
	var $mail = "";
	var $extra = "";
	var $born = null;
	function get()
	{
		$box = new userinfoboks();
		$box->add(h1(htlink("mailto:" . $this->mail, str($this->firstname . " " . $this->lastname))));
		$box->add(p("phone: " . $this->phone));
		$box->add(p("extra: " . $this->extra));
		if($this->born != null)
		$box->add(p("born: " . $this->born->get()));
		for($tmp = 0; $tmp < $this->nItems; $tmp++)
		{
			$box->add($this->items[$tmp]);
		}
		return $box->get();
	}
}


?>
