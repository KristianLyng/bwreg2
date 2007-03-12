<?php 

include "subs/env.php";

/* This page is a sort of template/demo of the 
 * "top page" of bwreg.
 * It will be updated to test/demo functionality, and much of 
 * the functionality this page demonstrates will be used internally
 * in other parts of bwreg2 as they are developed.
 */
$dropdown2 = new dropdown("");
$menu1 = new menu("En undermeny");
$menu2 = new menu("Petter");
$menu3 = new menu("Enda en meny");
$news1 = new news(&$dropdown2, "cirka naa");
$news2 = new news("Jesus", "tusen aar siden");
$news3 = new news("Adam", "Tidenes morgen");
$infoboks1 = new infoboks();
$dropdown = new dropdown("Action");
$somebox = new box();
$user = new user("kristian");
$dropdown2->content->title = $user;
$dropdown2->add($user->userinfo);
$page->content->add(&$infoboks1);
$page->content->add(&$news1);
$page->content->add(&$news2);
$page->content->add(&$news3);
if($event->location->id)
{
	$page->info1->add(p($event->location->name));
	$page->info1->add(p($event->location->address));
}

$page->ctrl1->add($menu3);
$page->footer->add(h1($user));
$page->info4->add(h1("Advarsel"));
$page->info4->add(p("Dette er en viktig infoboks som kan fjernes ved å fjerne det som gjør den skummel"));
$page->info4->add($dropdown);

$somebox->addst("Somepage!");
$somebox->add(img("images/glider.png","glideralt"));
$menu3->add(htlink("somepage",str("foo")));
$menu3->add($menu2);
$menu3->add($menu1);

$infoboks1->add(p("Dette er en informativ infoboks ja."));
$infoboks1->add(p("VELDIG informativ."));

$menu1->add(htlink("foo", str("SomeItem")));
$menu1->add(htlink("foo", str("SomeItem")));

$menu2->add(htlink("foo", str("SomeItem")));
$menu2->add(htlink("abra",$somebox));

$mul = new multiuserlist(false,"K","S");
$news2->add(p("Dette er resultatet av et enkelt søk i databasen:"));
$news2->add($mul);
$news3->add(p("Info om innlogget bruker:"));
$news3->add($me->userinfo);
$dropdown->add(htlink("foo.html",str("Read")));
$dropdown->add(htlink("bar.html",str("Hide")));


$news1->add(p("Fooooooo bar etc etc"));
$news3->add(p("Fooooooo bar etc etc Knut. Dette er litt tamt..."));
$news3->add(p("Fordelen er at dette nesten ikke krever html-kunskap, og php-filene blir html-frie"));


?>

