<?php

/**
 * @file
 * This file is loaded every time the core of e107 is included. ie. Wherever
 * you see require_once("class2.php") in a script. It allows a developer to
 * modify or define constants, parameters etc. which should be loaded prior to
 * the header or anything that is sent to the browser as output. It may also be
 * included in Ajax calls.
 */

if(!defined('e107_INIT'))
{
	exit;
}
/* this is not working for menus 
e107::setHandlerOverload('news',  'news', '{e_PLUGIN}news/ehandlers/news_class.php');
e107::setHandlerOverload('e_news_tree', 'e_news_tree',  '{e_PLUGIN}news/ehandlers/news_class.php');
e107::setHandlerOverload('e_news_category_item', 'e_news_category_item',  '{e_PLUGIN}news/ehandlers/news_class.php');
e107::setHandlerOverload('e_news_category_tree', 'e_news_category_tree',  '{e_PLUGIN}news/ehandlers/news_class.php');
*/