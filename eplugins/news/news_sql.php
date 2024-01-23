 
CREATE TABLE news (
news_id int(10) unsigned NOT NULL auto_increment,
news_title varchar(255) NOT NULL default '',
news_sef varchar(255) NOT NULL default '',
news_body longtext NOT NULL,
news_extended longtext NOT NULL,
news_meta_title varchar(255) NOT NULL default '',
news_meta_keywords varchar(255) NOT NULL default '',
news_meta_description text NOT NULL,
news_meta_robots varchar(255) default '',
news_datestamp int(10) unsigned NOT NULL default '0',
news_modified int(10) unsigned NOT NULL default '0',
news_author int(10) unsigned NOT NULL default '0',
news_category tinyint(3) unsigned NOT NULL default '0',
news_allow_comments tinyint(3) unsigned NOT NULL default '0',
news_start int(10) unsigned NOT NULL default '0',
news_end int(10) unsigned NOT NULL default '0',
news_class varchar(255) NOT NULL default '0',
news_render_type varchar(20) NOT NULL default '0',
news_comment_total int(10) unsigned NOT NULL default '0',
news_summary text NOT NULL,
news_thumbnail text NOT NULL,
news_sticky tinyint(3) unsigned NOT NULL default '0',
news_template varchar(50) default NULL,
PRIMARY KEY (news_id),
KEY news_category (news_category),
KEY news_start_end (news_start,news_end),
KEY news_datestamp (news_datestamp),
KEY news_sticky (news_sticky),
KEY news_render_type (news_render_type),
KEY news_class (news_class)
) ENGINE=MyISAM;

 
CREATE TABLE news_category (
category_id tinyint(3) unsigned NOT NULL auto_increment,
category_name varchar(200) NOT NULL default '',
category_sef varchar(200) NOT NULL default '',
category_meta_description text NOT NULL,
category_meta_keywords varchar(255) NOT NULL default '',
category_manager tinyint(3) unsigned NOT NULL default '254',
category_icon varchar(250) NOT NULL default '',
category_order tinyint(3) unsigned NOT NULL default '0',
category_template varchar(50) default NULL,
PRIMARY KEY (category_id),
KEY category_order (category_order)
) ENGINE=MyISAM;
 