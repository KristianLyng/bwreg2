<?

class CompoTemp
{
	private $loggedIn = false;
	private $compos = array('Warcraft 3 - Dota2 - 2on2');
	function CompoTemp()
	{
		global $me;
		if ($me->uid)
		{
			$this->loggedIn = true;
		}
		$this->registerActions();
	}
	private function registerActions()
	{
		$this->last['CompoShow'] =& add_action('CompoShow',&$this);
		$this->last['CompoReg'] =& add_action('CompoReg',&$this);
	}
	private function inCompo($compo)
	{
		foreach ($this->compos as $c)
			if ($c == $compo)
				return true;
		return false;
	}
	private function regCompo()
	{
		$compo = $_REQUEST['compo'];
		$team = $_REQUEST['team'];
		$members = $_REQUEST['members'];
		if (!$this->inCompo($compo))
			throw new Error("$compo er ikke en compo");
		global $db;
		$db->insert("INSERT INTO comporeg_temp VALUES(NULL,'$compo','" . database::escape($team) . "','" . database::escape($members) . "');");
		global $page;
		$page->warn->add(str("Du skal være registrert gitt."));
		$page->setrefresh();
	}
	private function showReg()
	{
		global $page;
		$form = new form();
		$table = new table(2);
		$table->add(str("Compo"));
		$s = new selectbox("compo");
		foreach ($this->compos as $compo)
			$s->add(foption($compo,$compo));
		$table->add($s);
		$table->add(str("Team:"));
		$table->add(ftext("team"));
		$table->add(str("Deltakere"));
		$table->add(ftext("members"));
		$form->add(fhidden("CompoReg"));
		$form->add($table);
		$form->add(fsubmit("Registrer"));
		$this->form = $form;
	}

	public function fakeGet()
	{
		global $page;
		$b = new box();
		if (is_object($this->form))
			$b->add($this->form);
		if (is_object($this->list))
			$b->add($this->list);
		$page->content->add($b);
	}
	private function show()
	{
		$query = "SELECT compo,team,memebers FROM comporeg_temp ORDER BY compo;";
		global $db;
		$this->list = new table(3);
		$this->list->add(str("Compo"));
		$this->list->add(str("Team"));
		$this->list->add(str("Members"));

		$db->query($query,&$this);
	}
	public function sqlcb($row)
	{
		$this->list->add(str($row['compo']));
		$this->list->add(str($row['team']));
		$this->list->add(str($row['memebers']));
	}
	public function actioncb($action)
	{
		if ($action == 'CompoReg')
		{
			$this->regCompo();
			$this->show();
		}
		if ($action == 'CompoShow')
		{
			$this->showReg();
			$this->show();
		}
		$this->fakeGet();
		next_action($action,$this->last[$action]);
	}
}

?>
