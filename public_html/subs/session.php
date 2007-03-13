<?

class session
{
	var $action;
	var $page;
	function session()
	{
		session_start();
		header("Cache-control: private");
		$this->action = $_REQUEST['action'] ? $_REQUEST['action'] : $_SESSION['action'];
		$this->page = $_REQUEST['page'] ? $_REQUEST['page'] : $_SESSION['page'];
	}
}

?>
