# news
Experimental -  core e107 news without legacy stuff


# JUST BACKUP 
## Don't use it if you don't know what you are doing 

/*  steps to do to replace e107 core news */

you must delete: 

- delete folder e107_core/urls/news
- delete cache folder e107_system/cache/
- delete e107_core/shortcodes/news_shortcodes.php
- delete e107_core/shortcodes/news_legacy_shortcodes.php


resave 
Preferences/URL configuration / Profiles
Preferences/URL configuration / Profiles Alliases

You should
add "end" template with shortcode {NEWS_PAGINATION}, no legacy pagination is supported

 
- change category of news plugin from menu to content - to be able to unistall and install it


Version 2.1, notes:
- removed render_newscats() functionality
- separated rendering of category template from list
- globals $NEWSLISTSTYLE doesn't supported anymore

