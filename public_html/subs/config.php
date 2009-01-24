<?
 /* This file handles the basic configuration before we have a
  * database up and running.
  */

class config
{
	var $db;
	var $base_path = "/index.php";
	function config()
	{
		$this->db = new dbconfig();
	}
}

class dbconfig
{
	var $user = "bwreg2";
	var $pass = "bwreg2";
	var $host = "db.lyngstol.int";
	var $db = "bwreg2";
}
?>
