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

class Ticket_Admin
{
	private $event;
	function Ticket_Admin(Event $event)
	{
		$this->event = $event;
		if (!me_perm($event->gname . "Ticket", "w"))
			throw new Error("Not sufficient permission");
	}
	public function get()
	{
		return "hei";
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
