<?
 /* This file handles the basic configuration before we have a
  * database up and running.
  */

class config
{
	var $db;
	function config()
	{
		$this->db = new dbconfig();
	}
}

class dbconfig
{
	var $user = "bwreg2";
	var $pass = "bwreg2";
	var $host = "localhost";
	var $db = "bwreg2";
}
?>
