<?php
/*
 * e107 Bootstrap CMS
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * IMPORTANT: Make sure the redirect script uses the following code to load class2.php:
 *
 * 	if (!defined('e107_INIT'))
 * 	{
 * 		require_once(__DIR__.'/../../class2.php');
 * 	}
 *
 */

if (!defined('e107_INIT')) { exit; }

// v2.x Standard  - Simple mod-rewrite module.

class news_url // plugin-folder + '_url'
{

	public $alias = 'news';

 

	function config()
	{
		$config = array();

		$pref = e107::pref('core','url_aliases'); // [en][news]

		$alias = null;

		if(!empty($pref[e_LAN]))
		{
			foreach($pref[e_LAN] as $k=>$v)
			{
				if($v === 'news' )
				{
					$alias = $k;
					break;
				}
			}
		}


		/* list  -  list/category  id */
		/* cat  - list/short id */
		/* day. month  id  list/day list/month '
		/* item, extend  view/item */
		/* default  list/items */

		/* all  list/all */ 
		/* default  list/items */ 

		/* news/ + route */
		$alias = 'blog';
		
		/* route:  news/list/author */

		$config['author'] = array(
			'alias'         => "{$alias}/author",
			'regex'			=> '^{alias}-(\d*)-(.*)\/(?:\?)(.*)$',
			'sef'			=> '{alias}-{news_author}-{user_name}/',			 
			'redirect'		=> '{e_PLUGIN}news/news.php?file==author&id=$1&sef=$2'
		);


		$config['item'] = array(
			'alias'         => "{$alias}/view",
			'regex'			=> '^{alias}-(\d*)-([\w-]*)\/?\??(.*)',
			'sef'			=> '{alias}-{news_id}-{news_sef}/',
			'redirect'		=> '{e_PLUGIN}news/news_viewitem.php?id=$1&sef=$2'
		);


		/* news/list/short */
		$config['category'] = array(
			'alias'         => "{$alias}/category",
			'regex'			=> '^{alias}-(\d*)-([\w-]*)\/?\??(.*)',
			'sef'			=> '{alias}-{category_id}-{category_sef}/',			 
			'redirect'		=> '{e_PLUGIN}news/news_category.php?id=$1&sef=$2'
		);

  
		$config['tag'] = array(
			'alias'         => "news/tag",
			'regex'         => '^{alias}-(.*)(?:\/)(.*)(?:\/?)(.*)',
			'sef'			=> '{alias}-{tag}/',			 
			'redirect'		=> '{e_PLUGIN}news/news.php?file==tag&tag=$1&$2'
		);


		/* no pagination */
		$config['all'] = array(
			'alias'         => "news/all",
			'regex'			=> '^{alias}/',
			'sef'			=> '{alias}/',
			'redirect'		=> '{e_PLUGIN}news/news.php?file=all'
		);


		/* pagination */
		$config['list'] = array(
			'alias'         => "news",
			'regex'			=> '^{alias}\/?\??page=?(.*)',
			'sef'			=> '{alias}/page={page}',
			'redirect'		=> '{e_PLUGIN}news/news.php?file=list&page=$1'
		);

		/* frontpage */
		$config['index'] = array(
			'alias'         => "news",
			'regex'			=> '^{alias}/$', 	
			'sef'			=> '{alias}/',
			'redirect'		=> '{e_PLUGIN}news/news.php?file=index'
		);

		return $config;
	}
 
}