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
	var $adress = "";
	var $born = null;
	function userinfo()
	{
		global $plugins;
		for($tmp = 0; $tmp < $plugins->nUserinfo; $tmp++)
		{
			$plugins->userinfo[$tmp]->userinfo(&$this);
		}
	}
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

class user extends box
{
	var $userinfo;
	var $uid;
	var $uname;

	function user($token, $password = null)
	{
		$this->userinfo = new userinfo();
		global $plugins;
		for($tmp = 0; $tmp < $plugins->nUser; $tmp++)
		{
			$plugins->user[$tmp]->user(&$this);
		}
		if ($password != null)
			$this->login($token,$password);
		else if (is_string($token))
			$this->c_uname($token);
		else if (is_int($token))
			$this->c_uid($token);
	}

	function login($user, $password)
	{
		
	}
	function c_uname($user)
	{
		global $db;
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE uname = '";
		$query .= $user;
		$query .= "';";
		return $db->query($query, &$this);
	}
	function c_uid($uid)
	{
		global $db;
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE uid = '";
		$query .= $uid;
		$query .= "';";
		return $db->query($query, $this);
	}
	function sqlcb($row)
	{
		$this->uid = $row[0];
		$this->uname = $row[1];
		$this->userinfo->firstname = $row[2];
		$this->userinfo->lastname = $row[3];
		$this->userinfo->mail = $row[4];
		$this->userinfo->born = new dateStuff($row[5]);
		$this->userinfo->adress = $row[6];
		$this->userinfo->phone = $row[7];
		$this->userinfo->extra = $row[8];
		
		for($tmp = 0; $tmp < $this->nItems; $tmp++)
		{
			if(function_exists($this->items[$tmp]->sqlcb))
				$this->items[$tmp]->sqlcb(&$this);
		}
	}
}
/* multiuser lets us search for and find multiple users.
 * It adds one user per result. It will be further extended
 * for modules that add other search-parameters and whatnot.
 */
class oneuser extends user 
{
	function oneuser($data) 
	{ 
		$this->userinfo = new userinfo();
		$this->sqlcb($data);
	}
}
class multiuser extends user 
{
	var $pre;
	function multiuser($fname,$lname)
	{
		global $db;
		$this->userinfo = new userinfo();
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE firstname like '";
		$query .= $fname;
		$query .= "%' or lastname like '";
		$query .= $lname;
		$query .= "%';";
		$db->query($query, &$this);
	}
	function sqlcb($row)
	{
		$this->add(new oneuser(&$row));
	}
	function get()
	{
		$string = "";
		for($tmp=0; $tmp < $this->nItems; $tmp++)
			$string .= $this->items[$tmp]->userinfo->get();
		return $string;
	}
}

class multiuserlist extends multiuser
{
	function multiuserlist($fname, $lname)
	{
		$this->userinfo = new userinfo();
		$this->multiuser($fname,$lname);
	}

	function get()
	{
		$box = new box();
		for($tmp = 0; $tmp < $this->nItems; $tmp++) 
		{
			$dropdown = new dropdown($this->items[$tmp]->userinfo->firstname);
			$dropdown->add($this->items[$tmp]->userinfo);
			$box->add(&$dropdown);
			unset($dropdown);
		}
		return $box->get();
	}

}

?>
