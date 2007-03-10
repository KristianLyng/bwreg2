<?php 

include "html.php";

/* Create the objects */
$page = new page("BWReg2 raw dev page", "This is the header of the page");

$menu1 = new menu("En undermeny");
$menu2 = new menu("Petter");
$menu3 = new menu("Enda en meny");
$news1 = new news("Kristian Lyngstol", "cirka naa");
$news2 = new news("Jesus", "tusen aar siden");
$news3 = new news("Adam", "Tidenes morgen");
$infoboks1 = new infoboks();

/* Populate them */
$infoboks1->add("<p>Dette er en informativ infoboks ja.</p>");
$infoboks1->add("<p>VELDIG informativ.</p>");

$menu1->add("<a href=\"foo\">Some item</a>");
$menu1->add("<a href=\"foo\">Some item</a>");
$menu1->add("<a href=\"foo\">Some item</a>");

$menu2->add("<a href=\"foo\">Some item</a>");
$menu2->add("<a href=\"foo\">Some item</a>");
$menu2->add("<a href=\"foo\">Some item</a>");

/* Populate the last menu with one normal item, and the two
 * other menus.
 */
$menu3->add("<a href=\"somepage\">somepage</a>");
$menu3->add($menu2->getraw());
$menu3->add($menu1->getraw());
$dropdown = new dropdown("Action");
$dropdown->add("<a href=\"foo.html\">Read</a>");
$dropdown->add("<a href=\"foo.html\">Hide this</a>");

/* Same menus on all the boxes */
$page->footer->add("<h1>copyright ME</h1>");
$page->ctrl1->add($menu3->get());
$page->ctrl2->add($menu3->get());
$page->info4->add("<h1>Warning!</h1>");
$page->info4->add("<p>This is very very scary</p>");
$page->info4->add($dropdown->get());
$page->info1->add("<h1>Tam info</h1>");
$page->info1->add("<p>This is quite boring</p>");

$news1->add("<p>Fooooooo bar etc etc</p>");
$news2->add("<p>Fooooooo bar etc etc</p>");
$news3->add("<p>Fooooooo bar etc etc</p>");

/* Popluate the content */
$page->content->add($infoboks1->get());
$page->content->add($news1->get());
$page->content->add($news2->get());
$page->content->add($news3->get());

/* Output the page */
$page->output();
?>

