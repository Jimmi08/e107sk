<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

if (!defined('e107_INIT')) { exit; }

// $front_page['news'] = array('page' => 'news.php', 'title' => ADLAN_0); 

 
class news_frontpage  
{
	// simple
	function config()
	{

		$frontPage = array(
			'page' => e107::url("news", "index"), 
			'title' => ADLAN_0
		);

		return $frontPage;
	}

}