<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Provides information about external libraries.
 */


/**
 * Class theme_library.
 */
class theme_library
{

	/**
	 * Provides information about external libraries.
	 */
	function config()
	{
		return array();
	}

	/**
	 * Alters library information before detection and caching takes place.
	 */
	function config_alter(&$libraries)
	{
		// Bootstrap (CDN).
		$libraries['cdn.bootstrap5']['library_path'] = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2';
		$libraries['cdn.bootstrap5']['path'] = '';
		$libraries['cdn.bootstrap5']['version_arguments']['pattern'] = '/(\d\.\d\.\d\-[a-z]*)/';

		// Bootstrap (local).
		$libraries['bootstrap5']['library_path'] = '{THEME}';
		$libraries['bootstrap5']['path'] = '';
		$libraries['bootstrap5']['theme'] = '';
		$libraries['bootstrap5']['version']	= '5.3.2';		
		$libraries['bootstrap5']['version_arguments']['pattern'] = '/(\d\.\d\.\d\-[a-z]*)/';
	}

}
