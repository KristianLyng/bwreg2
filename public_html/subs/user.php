<?
require_once("subs/base.php");
require_once("subs/html.php");

/* This file deals with users and permissions for said users 
 * It is one of the most important files, and contains the classes for
 * improtant objects such as $me.
 */

/* Userinfo object. Plugins get noticed when this is created and a get
 * is called. 
 */
class userinfo 
{
	var $firstname = "Anonymous";
	var $lastname = "";
	var $phone = "";
	var $mail = "";
	var $extra = "";
	var $adress = "";
	var $born = null;
	var $pluginextra; // Plugins add userinfo stuff here.
	function userinfo()
	{
		global $plugins;
		$this->pluginextra = new box();
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
		$box->add($this->pluginextra);
		return $box->get();
	}
}

/* permissions class contains a list with the permissions a user has
 * on a named resource. It also contains a $keys array with a list of
 * all the named resources the user has access to in a more easily
 * read fashion (checking is done with $list, but listing of resources
 * is done with $keys. This mostly happens through the user object)
 */
class permissions 
{
	var $list;
	var $keys; 

	function permissions($uid)
	{
		global $db;
		if(!isset($uid) or $uid == "")
			return;
		$query = "SELECT " . 
			 "permissions.resource, " . 
			 "permissions.resource_name, " .
			 "permissions.permissions, " .
			 "permissions.eid, " .
			 "permissions.gid " . 
			 "FROM permissions LEFT JOIN groups ON groups.groupid = permissions.groupid " .
			 "LEFT JOIN group_members ON group_members.groupid = groups.groupid " .
			 "WHERE group_members.uid = '";
		if (!is_int($uid))
			$query .= $db->escape($uid);
		else
			$query .= $uid;
		$query .= "';";
		$db->query($query,&$this);
	}
	function sqlcb($row)
	{
		$this->list[$row['resource_name']] = $row['permissions'];
		$this->list[$row['resource']] = $row['permissions'];
		$this->keys[$row['resource']] = $row['resource_name'];
	}
}

/* A generic user.
 * This gets all information about a user.
 */
class user extends box
{
	var $userinfo;
	var $uid;
	var $uname;
	var $debug = true;
	var $perms;
	function user($token = false, $password = null)
	{
		$this->userinfo = new userinfo();
		global $plugins;
		if ($password != null)
			$this->login($token,$password);
		else if (is_string($token))
			$this->c_uname($token);
		else if (is_int($token))
			$this->c_uid($token);
		else
			$this->guest();
		$this->perms =& new permissions($this->uid);
		for($tmp = 0; $tmp < $plugins->nUser; $tmp++)
			$plugins->user[$tmp]->user(&$this);
	}
	function permission($param)
	{
		if (isset($this->perms->list[$param]))
			return $this->perms->list[$param];
		return false;
	}
	function list_perms($checker)
	{
		foreach ($this->perms->keys as $param => $value)
		{
			$string .= "<option value=\"" . $param . "\"";
			if ( $param == $checker )
				$string .= " selected ";
			$string .= ">" . $value . "</option>\n";
			
		}
		return $string;
	}
	function login($user, $password)
	{
		global $db;
		$myuser = $db->escape($user);
		$mypass = $db->escapepass($password);
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE uname = '";
		$query .= $myuser;
		$query .= "' AND pass = ";
		$query .= $mypass;
		$query .= ";";
		$uid = $db->query($query,&$this);
		if (!$uid) {
			$this->uid = 0;
			$this->userinfo = new userinfo();
			return false;
		}
	}

	function guest()
	{
		$this->uid = false;
		$this->uname = false;
		$this->userinfo = new userinfo();
	}

	function c_uname($user)
	{
		global $db;
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE uname = '";
		$query .= $db->escape($user);
		$query .= "';";
		return $db->query($query, &$this);
	}
	function c_uid($uid)
	{
		global $db;
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE uid = '";
		$query .= $db->escape($uid);
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

	function get()
	{
			return $this->userinfo->firstname . " " . $this->userinfo->lastname;
	}

	function getname()
	{
			return $this->userinfo->firstname . " " . $this->userinfo->lastname;
	}
}

/* Class for $me.
 * Contains the current user. Logs in if necesarry and provides
 * the login box etc.
 */
class myuser extends user
{
	var $failed = true; 
	function myuser()
	{	
		parent::user();
		if ($_REQUEST['action'] == 'Logout')
			$this->logout();
		if($_SESSION['uname'] && $_SESSION['pass'] ) 
			$this->login($_SESSION['uname'],$_SESSION['pass']);
		 else if ($_POST['uname'] && $_POST['pass']) {
			$_SESSION['uname'] = $_POST['uname'];
			$_SESSION['pass'] = $_POST['pass'];
			$this->login($_POST['uname'],$_POST['pass']);
		} else
			$this->failed = false;
		if($this->uid == 0)
			$this->logout();
		$this->perms =& new permissions($this->uid);
	}
	function logout()
	{
		$_SESSION['uname'] = null;
		$_SESSION['pass'] = null;
		$this->guest();
	}
	function print_box()
	{
		$form = new form();
		$form->add(ftext("uname","uname",8));
		$form->add(fpass("pass",8));
		$form->add(fsubmit("Login", "action"));
		return $form->get();
	}
	function print_logout()
	{
		global $page;
		$url = $page->url();
		$url .= "?page=Logout&action=Logout";
		$object = htlink($url, str("Logout"));
		return $object->get();
	}
	function get()
	{
		if($this->failed && $this->uid == 0) {
			return "Login failed" . $this->print_box();
		} else if ($this->uid > 0) {
			return parent::get() . " " . $this->print_logout();
		} else {
			return $this->print_box();
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
	function multiuser($any,$fname,$lname)
	{
		global $db;
		$this->userinfo = new userinfo();
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE firstname like '";
		$query .= $db->escape($fname);
		if ($any)
				$query .= "%' or lastname like '";
		else
				$query .= "%' and lastname like '";
		$query .= $db->escape($lname);
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
	function multiuserlist($any, $fname, $lname)
	{
		$this->userinfo = new userinfo();
		$this->multiuser($any,$fname,$lname);
	}

	function get()
	{
		$box = new box();
		for($tmp = 0; $tmp < $this->nItems; $tmp++) 
		{
			$dropdown = new dropdown($this->items[$tmp]->getname());
			$dropdown->add($this->items[$tmp]->userinfo);
			$box->add(&$dropdown);
			unset($dropdown);
		}
		return $box->get();
	}

}

?>
