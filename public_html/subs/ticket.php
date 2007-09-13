<?
/* BWreg2 Event ticket system
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

/* This file deals with the ticket system of BWreg2:
 */


/* Fetches ticket state */
class Ticket_state
{
	private $force;
	private $period_start;
	private $period_end;
	private $queue_allowed;
	private $seating_start;
	private $seating_end;
	private $seating_group_start;
	private $seating_group_end;
	private $seating_group_size;
	private $init = false;
	private $in_period = false;
	private $eid;
	function Ticket_state ($eid)
	{
		global $me;
		global $db;
		global $page;
		$this->eid = $eid;
		$query = "SELECT force_state,".
				"period_start,".
				"period_end,".
				"NOW() > period_start && NOW() < period_end,".
				"option_queue,".
				"seating_start,".
				"seating_end,".
				"seating_group_start,".
				"seating_group_end,".
				"seating_group_size,".
				"tickets ".
				"FROM ticket_state WHERE ".
				"eid = '". $db->escape($eid) ."';";
		$result = $db->query($query);
		if (!$result) 
		{
			$this->init = false;
			return null;
		}
		$this->force = $result['force_state'];
		$this->period_start = $result['period_start'];
		$this->period_end = $result['period_end'];
		$this->queue_allowed = $result['option_queue'];
		$this->in_period = $result['NOW() > period_start && NOW() < period_end'];
		$this->seating_start = $result['seating_start'];
		$this->seating_end = $result['seating_end'];
		$this->seating_group_start = $result['seating_group_start'];
		$this->seating_group_end = $result['seating_group_end'];
		$this->seating_group_size = $result['seating_group_size'];
		$this->tickets = $result['tickets'];
		$this->init = true;
	}

	/* Checks if ticket registration is CURRENTLY enabled.
	 */
	public function isEnabled()
	{
		if (!$this->init)
			return false;
		if ($this->force == "Enabled")
			return true;
		elseif ($this->force == "Disabled")
			return false;
		if ($this->in_period)
			return true;
		return false;
	}

	/* Counts tickets of $state */
	private function countTickets (array $state)
	{
		global $db;
		$first = true;
		$query = "SELECT count(tickets.ticket_id) FROM tickets WHERE ".
			"eid = '" . $db->escape($this->eid) . "' AND (";
		foreach ($state as $s)
		{
			if (!$first)
				$query .= " OR ";
			else
				$first = false;
			$query .= "state = '" . $db->escape($s) . "'";
		}
		$query .= ");";
		$result = $db->query($query);
		if (!$result) 
			return -1;
		return $result['count(tickets.ticket_id)'];
	}

	/* Checks if there are any free tickets. */
	private function hasFree()
	{
		if ($this->countTickets (array('ordered','payed')) < $this->tickets)
			return true;
		return false;
	}
	
	/* Returns the number of ordered tickets. Does not include waiting list.
	 */
	public function getOrdered ()
	{
		return $this->countTickets(array('ordered','payed'));
	}

	public function getQueue ()
	{
		return $this->countTickets(array('queue'));
	}
	
	public function getTickets ()
	{
		return $this->tickets;
	}

	/* Returns the state of new orders. Returns false if not
	 * enabled. Note that we always return "queue" if someone is
	 * already in it.
	 */
	public function getNewOrderState ()
	{
		if (!$this->isEnabled())
			return false;
		if ($this->getQueue ()) 
			return "queue";
		if ($this->hasFree())
			return "ordered";
		return "queue";
	}
}

/* A ticket - One per user. The database doesn't have to have an entry
 * on the specific user, that would mean the user has yet to attempt to order
 * a ticket.
 */
class Ticket
{
	private $eid;
	private $uid;
	private $ticket_id = null;
	private $in_db = false;
	private $state = "NotOrdered";
	private $arrived;
	private $seat;
	private $seater;
	private $ticket_state;

	function Ticket($uid,$eid)
	{
		$this->eid = $eid;
		$this->uid = $uid;
		$this->ticket_state = new Ticket_state($eid);
		if ($this->ticket_state == null)
			return null;
		$this->setState();
	}

	/* Updates the state of the ticket. Used when ticket object
	 * is created and when the ticket is altered.
	 */
	private function setState()
	{
		global $db;
		$query = "SELECT ticket_id,".
			"state,".
			"arrived,".
			"seat,".
			"seater ".
			"FROM tickets WHERE ".
			"eid = '" . $db->escape($this->eid) . "' AND ".
			"uid = '" . $db->escape($this->uid) . "';";
		$result = $db->query($query);
		if (!$result)
		{
			$this->in_db = false;
			$this->state = "NotOrdered";
			return ;
		}
		$this->ticket_id = $result['ticket_id'];
		$this->state = $result['state'];
		$this->arrived = $result['arrived'];
		$this->seat = $result['seat'];
		$this->seater = $result['seater'];
		$this->in_db = true;
	}

	/* Returns true if the user can not do anything more to order a ticket;
	 * in other words, true if he is in queue or has ordered.
	 */
	public function isOrdered()
	{
		if ($this->state == "queue")
			return true;
		if ($this->state == "ordered")
			return true;
		if ($this->state == "payed")
			return true;
		return false;
	}

	/* Returns a copy of the TicketState to avoid making extra sql queries
	 */
	public function getTicketState ()
	{
		return $this->ticket_state;
	}
	
	public function getTicketId ()
	{
		return $this->ticket_id;
	}
	/* Returns how far down in the queue the ticket is */
	public function getQueue()
	{
		global $db;
		if ($this->state != "queue")
			return 0;
		$query = "SELECT count(*) FROM tickets WHERE state = 'queue' AND ".
			"eid = '". $db->escape($this->eid) ."' AND ".
			"ticket_id <= " . $this->ticket_id . ";";
		$result = $db->query($query);
		if (!$result)
			return -1;
		return $result['count(*)'];
	}

	public function getState ()
	{
		return $this->state;
	}

	public function cancelOrder ()
	{
		global $page;
		global $db;
		if (!$this->isOrdered())
			return false;
		if (!$this->in_db)
			return false;
		$new = 'canceled-not-payed';
		if ($this->state == 'payed')
			$new = 'canceled-payed';
		$query = "UPDATE tickets SET state = '" . $new . "' WHERE ".
			"eid = '" . $db->escape($this->eid) . "' AND ".
			"ticket_id = '". $db->escape($this->ticket_id) . "' LIMIT 1;";
		$db->insert($query);
		$this->setState();
		if (!$this->isOrdered())
			$page->warn->add(h1("Billetten ble avbestillt."));
		else
			return false;
		return true;
	}	
	/* Places an order if possible. Returns FALSE if unsuccessfull, TRUE if 
	 * the order is placed and the user is confirmed as either ordered OR 
	 * in the queue. Returns true if the user already placed an order too.
	 */
	public function placeOrder()
	{
		global $db;
		if ($this->isOrdered())
			return true;
		$new = $this->ticket_state->getNewOrderState ();
		if (!$new)
			return false;
		if ($this->in_db)
		{
			if ($this->state == $new)
				return true;
			$query = "UPDATE tickets SET state = '" . $new . "' WHERE ".
				"eid = '" . $db->escape($this->eid) . "' AND ".
				"ticket_id = '". $db->escape($this->ticket_id) . "' LIMIT 1;";
		}
		else
		{
			$query  = "INSERT INTO tickets (eid,uid,state) VALUES ('".
				$db->escape($this->eid) . "','".
				$db->escape($this->uid) . "','".
				$new . "');";
		}
		$db->insert($query);
		$this->setState();
		if (!$this->in_db)
		{
			print "An error occured";
			return false;
		}
		if ($this->state == $new)
		{
			return true;
		}
		return false;
	}
}

/* Builds a dynamic table.
 */
class Table_Builder
{
	private $cols = 0;
	private $class = false;
	private $rows;
	function Table_Builder($header = null, $class = false)
	{
		$this->class = $class;
		$this->header = $header;
		$this->rows = array();
	}

	public function addRow (array $data)
	{
		$this->rows[] = $data;
		if ($this->cols < count($data))
			$this->cols = count($data);
	}

	public function get()
	{
		$tab = new table($this->cols, $this->class);
		if (is_array($this->header))
		{
			foreach ($this->header as $tmp)
				$tab->add(str($tmp));
		}
		foreach ($this->rows as $row)
		{
			$pad = $this->cols - count($row);
			$counter = count($row);
			foreach ($row as $col)
			{
				if (!is_object($col))
					$col = str($col);
				if ($counter == 0)
					$tab->add($col,$pad);
				$tab->add($col);
			}
		}
		return $tab->get();
	}
}

class Search_Builder
{
	private $callback;
	private $tbuilder;
	function Search_Builder($query, $header = null, $callback = null)
	{
		global $db;
		$this->callback = $callback;
		$this->tbuilder = new Table_Builder($header);
		$db->query($query,&$this);
	}
	private function sqlCleaner($row)
	{
		$newrow = array();
		foreach ($row as $a => $r)
		{
			if (is_int($a))
				continue;
			$newrow[$a] = $r;
		}
		return $newrow;
	}
	public function sqlcb($row)
	{
		$row = $this->sqlCleaner($row);
		if ($this->callback == null)
			$this->tbuilder->addRow($row);
		else
		{
			$r = $this->callback->searchcb($row);
			$this->tbuilder->addRow($r);
		}
	}
	public function get()
	{
		return $this->tbuilder->get();
	}
}
class Search_Criteria
{
	public $state; // AND NOT OR
	public $value;
	public function Search_Criteria ($value,$state = "AND")
	{
		$this->value = $value;
		$this->state = $state;
	}
	public function getSql($name, $first = false)
	{
		if (!$first)
			$q = database::escape($this->state);
		else
			$q = "WHERE";
	    $q .= " $name LIKE '" . database::escape($this->value) . "' ";
		return $q;
	}
}

class Ticket_Criteria
{
	private $firstname;
	private $lastname;
	private $state; // ordered,queue,etc
	private $ticket_id;
	private $orderby = "firstname";

	public function Ticket_Criteria()
	{
		if (isset($_REQUEST['searchfirstname']) && $_REQUEST['searchfirstname'] != "")
			$this->firstname = new Search_Criteria ($_REQUEST['searchfirstname']);
		if (isset($_REQUEST['searchlastname']) && $_REQUEST['searchlastname'] != "")
			$this->lastname = new Search_Criteria ($_REQUEST['searchlastname']);
		if (isset($_REQUEST['searchstate']) && $_REQUEST['searchstate'] != "")
			$this->state = new Search_Criteria ($_REQUEST['searchstate']);
		if (isset($_REQUEST['searchticketid']) && $_REQUEST['searchticket_id'] != "")
			$this->ticketid = new Search_Criteria ($_REQUEST['searchticket_id']);
		if (isset($_REQUEST['searchorderby']) && $_REQUEST['searchorderby'] != "")
			$this->orderby = $_REQUEST['searchorderby'];
	}

	// Fetches the parts after WHERE
	public function getSqlMatch ()
	{
		$query = "";
		$first = true;
		if ($this->firstname != null)
		{
			$query .= $this->firstname->getSql('firstname',$first);
			$first = false;
		}
		if ($this->lastname != null)
		{
			$query .= $this->lastname->getSql('lastname',$first);
			$first = false;
		}
		if ($this->state != null)
		{
			$query .= $this->state->getSql('state',$first);
			$first = false;
		}
		if ($this->ticket_id != null)
		{
			$query .= $this->ticket_id->getSql('ticket_id',$first);
		}
		$query .= "ORDER BY " . database::escape($this->orderby);
		return $query;
	}
}

class Ticket_Admin
{
	private $event;
	private $criteria;
	private $admin = false;
	private $DEFAULTDISPLAY = array('ticket_id','state');
	function Ticket_Admin(Event $event)
	{
		$this->event = $event;
		if (!me_perm($event->gname . "Ticket", "r"))
			throw new Error("Not sufficient permission");
		if (me_perm($event->gname . "Ticket", "w"))
			$this->admin = True;
		$this->dispatchEvent ();
	}

	/* Lists tickets based on criteria */
	private function displayList ()
	{
		if (is_array($_REQUEST['searchshow']))
			$display = $_REQUEST['searchshow'];
		else
			$display = $this->DEFAULTDISPLAY;
		$query = "SELECT ticket_id,seat,users.uname,users.firstname,users.lastname,users.private,users.phone,users.extra,users.mail,users.adress,users.birthyear,";
		$first = true;
		foreach ($display as $d)
		{
			if ($d == "pass")
				continue;
			if (!$first)
			{
				$query .= ",";
			}
			else
			{
				$first = false;
			}
			$query .= database::escape($d);
		}
		$this->display = $display;

		$query .= " FROM users join tickets on users.uid = tickets.uid ";
		$this->criteria = new Ticket_Criteria();
		$query .= $this->criteria->getSqlMatch();
		$foo = array("|","State","Seat","Ticket","","User");
		$ignore = array('uname','firstname','private','lastname','phone','extra','mail','adress','birthyear');
		foreach ($display as $d)
		{
			if ($d == 'ticket_id' || $d == 'seat' || $d == 'state')
				continue;
			if (in_array($d,$ignore))
				continue;
			$foo[] = $d;
		}
		$sb = new Search_Builder($query,$foo,&$this);
		$form = new form();
		$b = $this->rebuildSearchForm();
		$form->add($b);
		$form->add(fsubmit("Search",'searchaction'));
		$form->add($sb);
		$form->add(fsubmit("Save All",'searchaction'));
		$form->add(fhidden('TicketAdmin'));
		$this->sb = $form;
	}
	
	/* Rebuilds the search form supplied using fhidden(), so submitting will
	 * reproduce the last search.
	 */
	private function rebuildSearchForm ()
	{
		$box = new box();
		$temptable = new table(3);
		$tm = array ('firstname','lastname','seat','seater','ticket_id','state','uname','users.uid');
		$tmp = array('searchfirstname','searchlastname','searchticket_id','searchstate');
		$state = array('','queue','ordered','payed','canceled-not-payed','canceled-payed','canceled-refunded');
		$orderbox = new selectbox('searchorderby');
		foreach ($tm as $t)
		{
			$temptable->add(str($t));
			if (in_array($t, $this->display))
				$true = true;
			else
				$true = false;
			if (in_array('search' . $t, $tmp))
			{
				$temptable->add(fcheck('searchshow',$t, $true));
				if ($t != 'state') 
				{
					if (isset($_REQUEST['search' . $t]))
						$temptable->add(ftext('search' . $t, $_REQUEST['search' . $t]));
					else
						$temptable->add(ftext('search' . $t, ""));
				}
				else
				{
					$s = new selectbox('searchstate');
					foreach ($state as $onestate)
					{
						if ($_REQUEST['searchstate']  == $onestate)
						{
							$s->add(foption($onestate,$onestate,true));
						}
						else
						{
							$s->add(foption($onestate,$onestate,false));
						}
					}
					$temptable->add($s);


				}
			} else
				$temptable->add(fcheck('searchshow',$t, $true),2);
			if ($_REQUEST['searchorderby'] == $t) 
				$orderbox->add(foption($t,$t,true));
			else
				$orderbox->add(foption($t,$t,false));
		}
		$box->add($temptable);
		$box->add(str("Sorter etter:"));
		$box->add($orderbox);
		return $box;
	}
	function searchcb($row)
	{
		$newrow = array();
		$newrow[] = fcheck("searchcommit",$row['ticket_id']);
		$s = new selectbox("searchstatecommit" . $row['ticket_id']);
		$state = array('queue','ordered','payed','canceled-not-payed','canceled-payed','canceled-refunded');
		foreach ($state as $st)
		{
			if ($row['state'] == $st)
				$s->add(foption($st,$st,true));
			else
				$s->add(foption($st,$st,false));
		}
		$newrow[] = $s;
		$newrow[] = ftext('commitseat' . $row['ticket_id'],$row['seat'],2,4);
		$tmpbox = new box();
		$tmpbox->add(fradio("saveoneid",$row['ticket_id']));
		$tmpbox->add(str($row['ticket_id']));
		$newrow[] = $tmpbox;
		$newrow[] = fsubmit("Save One","searchaction" );

		$userinfo = new userinfo($row);
		$ubox = new dropdown($userinfo->get_name());
		$ubox->add($userinfo);
		$newrow[] = $ubox;
		$ignore = array('uname','firstname','private','lastname','phone','extra','mail','adress','birthyear');
		foreach ($row as $a => $item)
		{
			if ($a == 'ticket_id' || $a == 'seat' || $a == 'state')
				continue;
			if (in_array($a, $ignore))
				continue;
			$newrow[] = $item;
		}

		return $newrow;
	}

	private function saveId($id)
	{
		if (!$this->admin)
			throw new Error ("Ikke tilstrekkelig rettigheter til &aring; lagre endringer");
		$state = $_REQUEST['searchstatecommit' . $id];
		if (isset($_REQUEST['commitseat' . $id]))
		{
			$seat = $_REQUEST['commitseat' . $id];
			$query = "UPDATE tickets SET state = '" . database::escape($state) . "',seat='" . database::escape($seat) . "' WHERE eid='" . $this->event->eid . "' AND ticket_id = '" . database::escape($id) . "';";
		}
		else
		{
			$seat = "no change";
			$query = "UPDATE tickets SET state = '" . database::escape($state) . "' WHERE eid = '" . $this->event->eid . "' AND ticket_id = '" . database::escape($id) . "';";
		}
		global $db;
		global $page;
		$page->warn->add(p("TicketAdmin: Lagrer endring for ticket_id $id"));
		$db->insert($query);
		BWlog('info',"Changing state($state) and seat($seat) for ticket $id");
	}
	private function saveAll()
	{
		if (!is_array($_REQUEST['searchcommit']))
			throw new Error ("No ID submitted");
		foreach ($_REQUEST['searchcommit'] as $id)
			$this->saveId($id);

	}
	private function saveOne()
	{
		if (!isset($_REQUEST['saveoneid']))
			throw new Error("No ID submitted");
		$this->saveId($_REQUEST['saveoneid']);
	}

	/* Handles the "internal" ticket admin event/action dispatching.
	 */
	private function dispatchEvent ()
	{
		$event = $_REQUEST['searchaction'];
		try
		{
			switch ($event)
			{
				case 'Save All':
					$this->saveAll();
					break;
				case 'Save One':
					$this->saveOne();

					break;
				default:
			}
		} catch (Error $e)
		{
			global $page;
			$page->warn->add($e);
		}
		$this->displayList();
	}
	public function get()
	{
		if (is_object($this->sb))
			return $this->sb->get();
		else
			return "";
	}
}

/* The user-facing class, this presents the user with an interface to the Ticket
 * class. Both from a sysadmin and normal user perspective.
 */
class Ticket_System 
{
	private $admin = false; 
	private $crew = false;
	private $perm = false;
	private $loggedin = false;
	private $last = null;
	private $self_ticket = null;
	private $ticket_state = null;
	private $eid;
	public function Ticket_System(Event $event)
	{
		global $me;
		$this->perm = $event->gname . "Ticket";
		if (me_perm($this->perm, "w"))
			$this->admin = true;
		if (me_perm($this->perm, "r"))
			$this->crew = true;
		if ($me->uid <= 1)
		{
			$this->loggedin = false;
			$this->ticket_state = new Ticket_state ($event->eid);
		}
		else
		{
			$this->self_ticket = new Ticket($me->uid, $event->eid);
			$this->ticket_state = $this->self_ticket->getTicketState();
			$this->loggedin = true;
		}
		$this->event = $event;
		$this->register_actioncb();
	}
	
	/* Register all the relevant action cb's
	 */
	private function register_actioncb()
	{
		$this->last['OrderTicket'] =& add_action("OrderTicket", &$this);
		if ($this->loggedin)
		{
			$this->last['PaymentInfo'] =& add_action("PaymentInfo", &$this);
			$this->last['TicketCancel'] =& add_action('TicketCancel',&$this);
			$this->last['TicketCancelConfirm'] =& add_action('TicketCancelConfirm',&$this);
			$this->last['TicketAdmin'] =& add_action('TicketAdmin',&$this);
		}
	}

	public function actioncb($action)
	{
		global $page;
		if ($action == "OrderTicket")
		{
			if (!$this->loggedin)
			{
				$page->warn->add(h1("Bestillingen kunne ikke gjennomf&oslash;res."));
				$page->warn->add(p("Du m&aring; logge inn for &aring; bestille en billett."));
			}
			elseif ($this->self_ticket->placeOrder())
			{
				$page->warn->add(h1("Bestillingen er gjennomf&oslash;rt!"));
				$page->warn->add(p("Se p&aring; billettstatus for &aring; se om du er i ventelisten eller har f&aring;tt; tildelt en billett."));
			}
			else
			{
				$page->warn->add(h1("Bestillingen kunne ikke gjenommf&oslash;res"));
			}
		}
		else if ($action == "PaymentInfo")
		{
			$page->content->add(htlink($page->url() . "?action=TicketCancel",str("Avbestill")));
		}
		else if ($action == "TicketCancel")
		{
			$page->content->add(htlink($page->url() . "?action=TicketCancelConfirm",str("Bekreft avbestilling")));
		}
		else if ($action == "TicketCancelConfirm")
		{
			if (is_object($this->self_ticket))
				$this->self_ticket->cancelOrder ();
		}
		else if ($action == "TicketAdmin")
		{
			$page->content->add(new Ticket_Admin($this->event));
		}
		next_action($action,$this->last[$action]);
	}
	
	/* Generates html about the status of the currently logged in
	 * user.
	 */
	private function generateStatus ()
	{
		$box = new htlist();
		global $page;
		if ($this->self_ticket != null)
		{
			$state = $this->self_ticket->getState();
			switch ($state)
			{
				case "queue":
					$box->add(str("Nummer " . $this->self_ticket->getQueue() . " i k&oslash;en"));
					$box->add(htlink($page->url() . "?page=PaymentInfo&amp;action=PaymentInfo",str("Betalingsinformasjon")));
					$box->add(str("Billettnummer: " . $this->self_ticket->getTicketId()));
					break;
				case "ordered":
					$box->add(str("Bestillt, men ikke betalt"));
					$box->add(htlink($page->url() . "?page=PaymentInfo&amp;action=PaymentInfo",str("Betalingsinformasjon")));
					$box->add(str("Billettnummer: " . $this->self_ticket->getTicketId()));
					break;
				case "payed":
					$box->add(str("Bestillt og betalt"));
					$box->add(htlink($page->url() . "?page=PaymentInfo&amp;action=PaymentInfo",str("Betalingsinformasjon")));
					$box->add(str("Billettnummer: " . $this->self_ticket->getTicketId()));
					break;
				default:
					$box->add(str("Ikke bestillt"));
					break;
			}
		}
		$box->add(str("Antall billetter: " . $this->ticket_state->getTickets()));
		$box->add(str("Antall solgt: " . $this->ticket_state->getOrdered()));
		$box->add(str("P&aring; venteliste: " . $this->ticket_state->getQueue()));
		if ($this->ticket_state->isEnabled())
			$box->add(str("Billettbestillingen er &aring;pen"));
		else
			$box->add(str("Billettbestillingen er stengt"));

		return $box;
	}

	public function get ()
	{
		$box = new infoboks();
		$box->add(h1("Billettstatus"));
		$box->add($this->generateStatus());
		return $box->get();
	}
}

?>
