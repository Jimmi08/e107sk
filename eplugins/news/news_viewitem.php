<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


// news item rewrite for v2.x


if (!defined('e107_INIT'))
{
    require_once(__DIR__ . '/../../class2.php');
}

e107::corelan("news");
e107::lan("news");

class news_front
{

    private $cacheRefreshTime;

    private $cacheString = 'news_item_';

    private $route = null;
    private $newsPref;
    private $nobody_regexp = '';
    private $news_id;
    private $news_sef;

    private $currentRow = array();

    private $error = NULL;

    private $caption;
    private $text;

    function __construct()
    {

        $this->newsPref = e107::pref('news');

        $this->cacheRefreshTime = vartrue($this->newsPref['news_cache_timeout'], false);

        $this->nobody_regexp = "'(^|,)(" . str_replace(",", "|", e_UC_NOBODY) . ")(,|$)'";

        $this->news_id = e107::getParser()->filter($_GET['id'], "int");
        $this->news_sef = e107::getParser()->filter($_GET['sef'], "str");
    }

    function init()
    {
        if ($this->news_id > 0 &&  isset($this->news_sef))
        {

            $this->route = 'news/view/item';
            $this->cacheString .=  $this->news_id;

            //get data 
            $row = $this->getNewsCache($this->cacheString, 'rows');
            if ($row)
            {

                $this->currentRow = $row;
            }
            else
            {
                $this->setRow();  //cache string, 

            }

            e107::getEvent()->trigger('user_news_item_viewed', $this->currentRow);

            $this->setCanonical();

            $this->setBreadcrumb();

            $this->setNewsFrontMeta($this->currentRow);
        }
        else
        {
            $this->error = 1;
            $this->renderError($this->error);
        }
    }

    function render()
    {

        if ($this->error > 0)
        {
            $this->renderError($this->error);
        }
        else
        {

            $news = $this->currentRow;

            $caption = $this->getNewsCache($this->cacheString, 'caption');
            $caption = false;

            if ($caption)
            {

                $this->caption  = $caption;
            }
            else
            {
                $this->caption = $news['news_title'];

                $this->setNewsCache($this->cacheString, 'caption', $this->caption);
            }

            $newsCachedPage =  $this->getNewsCache($this->cacheString, 'text');
            if ($newsCachedPage)
            {
                $this->text = $newsCachedPage;
            }
            else
            {

                $newsViewTemplate = !empty($news['news_template']) ? $news['news_template'] : 'default';
                $template = e107::getTemplate('news', 'news_view', $newsViewTemplate);
                $wrapperKey =  'news_view/' . $newsViewTemplate . '/item';
                $editable = array(
                    'table' => 'news',
                    'pid'   => 'news_id',
                    'vars'  => 'news_item',
                    'perms' => 'H|H4',
                    'shortcodes'    => array(
                        'news_title'        => array('field' => 'news_title', 'type' => 'text', 'container' => 'span'),
                        'news_description'  => array('field' => 'news_meta_description', 'type' => 'text', 'container' => 'span'),
                        'news_body'         => array('field' => 'news_body', 'type' => 'html', 'container' => 'div'),
                        'news_summary'      => array('field' => 'news_summary', 'type' => 'text', 'container' => 'span'),
                    )

                );
                $nsc = e107::getScBatch('news', true)->wrapper($wrapperKey);
                $nsc->setScVar('news_item', $this->currentRow);
                $nsc->editable($editable);

                $this->text = e107::getParser()->parseTemplate($template['item'], TRUE, $nsc);

                $this->setNewsCache($this->cacheString, 'text', $this->text);
            }


            $tablerender = varset($template['tablerender'], 'news-item');
            $output = e107::getRender()->tablerender($this->caption, $this->text, $tablerender, true);
            echo $output;

            // fix for not correct magic shortcode 
            // temp workaround e107::getRender()->tablerender($this->caption, "", 'magiccaption');

        }
    }

    private function setNewsCache($cache_tag, $type = null, $cache_data)
    {
        $e107cache = e107::getCache();
        $e107cache->setMD5($this->news_sef);
        /*
 
        $e107cache->set($cache_tag . "_title", e107::getSingleton('eResponse')->getMetaTitle());
        $e107cache->set($cache_tag . "_diz", defined("META_DESCRIPTION") ? META_DESCRIPTION : '');
*/

        if (!empty($type))
        {
            $type  = "_" . $type;
        }


        if ($type == '_rows')
        {
            $e107cache->set($cache_tag . "_rows", e107::serialize($cache_data, 'json'));
        }
        else
        {
            $e107cache->set($cache_tag . $type, $cache_data);
        }
    }


    /**
     * @param        $cachetag
     * @param string $type 'title' or 'diz' or 'rows' or empty for html.
     * @return array|false|string
     */
    private function getNewsCache($cachetag, $type = null)
    {
        if (!empty($type))
        {
            $cachetag .= "_" . $type;
        }
        $this->addDebug('CacheString lookup', $cachetag);

        $ret =  e107::getCache()->setMD5($this->news_sef)->retrieve($cachetag);

        if (empty($ret))
        {
            $this->addDebug('Possible Issue', $cachetag . " is empty");
        }

        if ($type == 'rows')
        {
            return e107::unserialize($ret);
        }

        return $ret;
    }



    private function setNewsFrontMeta($news)
    {
        /* move this to prefs who wants to display keywords 
        if (!empty($news['news_meta_robots']))
        {
            e107::meta('robots', $news['news_meta_robots']);
        }
      */
        if (!empty($news['news_title']))
        {
            e107::title($news['news_title']);
            e107::meta('title', $news['news_title']);
            e107::meta('og:title', $news['news_title']);
            e107::meta('og:type', 'article');
            e107::meta('twitter:card', 'summary');
        }

        if (!empty($news['news_meta_title'])) // override title with meta title.
        {
            e107::title($news['news_meta_title'], true);
        }

        if ($news['news_meta_description'])
        {
            e107::meta('description', $news['news_meta_description']);
            e107::meta('og:description', $news['news_meta_description']);
            e107::meta('twitter:description', $news['news_meta_description']);
            //define('META_DESCRIPTION', $news['news_meta_description']); // deprecated
        }
        elseif ($news['news_summary']) // BC compatibility
        {
            e107::meta('description', $news['news_summary']);
            e107::meta('og:description', $news['news_summary']);
            e107::meta('twitter:description', $news['news_summary']);
        }

        // include news-thumbnail/image in meta. - always put this one first.
        if (!empty($news['news_thumbnail']))
        {
            $iurl = (substr($news['news_thumbnail'], 0, 3) == "{e_") ? $news['news_thumbnail'] : SITEURL . e_IMAGE . "newspost_images/" . $news['news_thumbnail'];
            $tmp = explode(",", $iurl);

            if (!empty($tmp[0]) && substr($tmp[0], -8) !== '.youtube')
            {
                $mimg = $tmp[0];
                $metaImg = e107::getParser()->thumbUrl($mimg, 'w=1200', false, true);
                e107::meta('og:image', $metaImg);
                e107::meta('og:image:width', 1200);
                e107::meta('twitter:image', $metaImg);
                e107::meta('twitter:card', 'summary_large_image');
            }
        }
        e107::meta('article:author', SITEURL);

        return;
    }




    public function debug()
    {
        $title = e107::getSingleton('eResponse')->getMetaTitle();

        echo "<div class='alert alert-info'>";
        echo "<h4>News Debug Info</h4>";
        echo "<table class='table table-striped table-bordered'>";
        echo "<tr><td><b>action:</b></td><td>" . $this->action . "</td></tr>";
        echo "<tr><td><b>subaction:</b></td><td>" . $this->subAction . "</td></tr>";
        echo "<tr><td><b>route:</b></td><td>" . $this->route . "</td></tr>";
        echo "<tr><td><b>e_QUERY:</b></td><td>" . e_QUERY . "</td></tr>";
        echo "<tr><td><b>e_PAGETITLE:</b></td><td>" . vartrue($title, '(unassigned)') . "</td></tr>";
        echo "<tr><td><b>PAGE_NAME:</b></td><td>" . defset('PAGE_NAME', '(unassigned)') . "</td></tr>";
        echo "<tr><td><b>CacheTimeout:</b></td><td>" . $this->cacheRefreshTime . "</td></tr>";
        echo "<tr><td><b>_GET:</b></td><td>" . print_r($_GET, true) . "</td></tr>";

        foreach ($this->debugInfo as $key => $val)
        {
            echo "<tr><td><b>" . $key . ":</b></td><td>" . $val . "</tr>";
        }

        echo "</table></div>";
    }

    function setRow()
    {

        $query = "
		    SELECT n.*, u.user_id, u.user_name, u.user_customtitle, u.user_image, u.user_login, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords,
			nc.category_meta_description
		    FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			WHERE n.news_class REGEXP '" . e_CLASS_REGEXP . "'
			AND NOT (n.news_class REGEXP " . $this->nobody_regexp . ")
			AND n.news_start < " . time() . "
			AND (n.news_end=0 || n.news_end>" . time() . ")
			AND n.news_id=" . intval($this->news_id);

        if ($news = e107::getDb()->retrieve($query))
        {

            e107::getEvent()->trigger('user_news_item_viewed', $news);
            $this->addDebug("Event-triggered:user_news_item_viewed", $news);

            $this->currentRow = $news;
            $this->setNewsCache($this->cacheString, 'rows', $this->currentRow);
        }
        else
        {

            $this->error = 2;
        }
    }

    function setCanonical()
    {

        $options = array('mode' => 'full');

        if (!defined("e_FRONTPAGE"))
        {
            e107::canonical('news', 'item', $this->currentRow, $options);
        }
    }


    private function setBreadcrumb()
    {
        $this->addDebug('setBreadcrumb', 'complete');
        $breadcrumb = array();

        $breadcrumb[] = array('text' => LAN_PLUGIN_NEWS_NAME, 'url' => e107::url('news', 'index'));

        if (empty($this->currentRow['category_name']))
        {
            $this->addDebug("Possible Issue", "missing category_name on this->currentRow");
        }

        $categoryName = e107::getParser()->toHTML($this->currentRow['category_name'], true, 'TITLE');


        $itemName = e107::getParser()->toHTML($this->currentRow['news_title'], true, 'TITLE');

        $breadcrumb[] = array('text' => $categoryName, 'url' => e107::url('news', 'category', $this->currentRow));
        $breadcrumb[] = array('text' => $itemName, 'url' => null);


        e107::breadcrumb($breadcrumb);
    }


    private function addDebug($key, $message)
    {
        if (is_array($message))
        {
            $this->debugInfo[$key] = print_a($message, true);
        }
        else
        {
            $this->debugInfo[$key] = $message;
        }
    }


    function renderError($error = NULL)
    {

        switch ($error)
        {
            case 1:
                $debug = "(1) Wrong GET parameters";
            case 2:
                $debug = "(2) Wrong ID - no available record / access, dates";
                break;
        }

        header("HTTP/1.0 404 Not Found", true, 404);
        require_once(e_LANGUAGEDIR . e_LANGUAGE . "/lan_error.php");
        if (e_DEBUG or ADMIN)
        {
            $text = e107::getMessage()->addError($debug)->render();
        }

        $text .= "<div class='news-view-error'>" .
            e107::getMessage()->setTitle(LAN_ERROR_7, E_MESSAGE_INFO)->addInfo(LAN_NEWS_308)->render(); // Perhaps you're looking for one of the news items below?
        $text .= "</div>";

        $text .= e107::getParser()->parseTemplate("{MENU: path=news/latestnews}");


        e107::getRender()->tablerender(LAN_ERROR_7, "", "magiccaption");

        e107::getRender()->tablerender("", $text, $tablerender);

        return;
    }
}

$newsObj = new news_front;

$newsObj->init();

require_once(HEADERF);

$newsObj->render();

if (E107_DBG_BASIC && ADMIN)
{
    $newsObj->debug();
}

require_once(FOOTERF);
