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

class news_category_front
{

    private $cacheRefreshTime;

    private $cacheString = 'news_list_cat';

    private $route = null;
    private $newsPref;
    private $nobody_regexp = '';
    private $category_id;
    private $category_sef;

    private $currentRow = array();
    private $error = NULL;

    private $from = 0;
    private $page = 0;
    private $news_list_limit = 12;  //always full row 3,4,6

    private $pref;
    private $caption;
    private $text;

    function __construct()
    {

        $this->newsPref = e107::pref('news');
        $this->pref = e107::getPref();
        $this->cacheRefreshTime = vartrue($this->newsPref['news_cache_timeout'], false);

        $this->nobody_regexp = "'(^|,)(" . str_replace(",", "|", e_UC_NOBODY) . ")(,|$)'";

        $this->category_id = e107::getParser()->filter($_GET['id'], "int");
        $this->category_sef = e107::getParser()->filter($_GET['sef'], "str");

        $this->news_list_limit = varset($this->newsPref['news_list_limit'], 15);

    }

    function init()
    {
        if ($this->category_id > 0 &&  isset($this->category_sef))
        {

            $this->route = 'news/list/category';
            $this->cacheString .=  $this->category_id;

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

            //e107::getEvent()->trigger('user_news_item_viewed', $this->currentRow);

            $this->setCanonical();

            $this->setBreadcrumb();

            $this->setNewsFrontMeta($this->currentRow);

            $this->setPagination();
        }
        else
        {
            $this->error = 1;
            // $this->renderError($this->error);

        }
    }

    function render()
    {

        $template = e107::getTemplate('news', 'news', 'category');

        if ($this->error > 0)
        {
            $this->renderError($this->error);
        }
        else
        {

            $news = $this->currentRow;

            $caption = $this->getNewsCache($this->cacheString, 'caption');

            if ($caption)
            {

                $this->caption  = $caption;
            }
            else
            {

                $this->caption = $news['category_name'];
                $nsc = e107::getScBatch('news', true);
                $nsc->setScVar('news_item', $this->currentRow);
                $this->caption = e107::getParser()->parseTemplate($template['caption'], TRUE, $nsc);

                $this->setNewsCache($this->cacheString, 'caption', $this->caption);
            }

            $newsCachedPage =  $this->getNewsCache($this->cacheString, 'text');
            if ($newsCachedPage)
            {
                $this->text = $newsCachedPage;
            }
            else
            {

                $wrapperKey =  'news/category';

                $nsc = e107::getScBatch('news', true)->wrapper($wrapperKey);
                $nsc->setScVar('news_category', $this->currentRow);

                $this->text = e107::getParser()->parseTemplate($template['start'], FALSE, $nsc);
                foreach ($this->currentRow['items'] as $news)
                {

                    $nsc->setScVar('news_item', $news);
                    /* this is not parsing LANs 
                    $this->text .= e107::getParser()->parseTemplate($template['item'], FALSE, $nsc);
                    */

                    $this->text .= e107::getParser()->parseTemplate($template['item'], TRUE, $nsc);
                }
                $this->text .= e107::getParser()->parseTemplate($template['end'], FALSE, $nsc);

                $this->setNewsCache($this->cacheString, 'text', $this->text);
            }
            /* fix for not correct magic shortcode */
            e107::getRender()->tablerender($this->caption, "", 'magiccaption');
            $tablerender = varset($template['tablerender'], 'news-category');
            $output = e107::getRender()->tablerender("", $this->text, $tablerender, true);
            echo $output;
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
        e107::getDebug()->log('Retrieving cache string:' . $cachetag);

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

        if (!empty($news['category_name']))
        {
            e107::title(e107::getParser()->toHTML($news['category_name'], false, 'TITLE_PLAIN'));
            e107::meta('og:type', 'article');
            e107::meta('twitter:card', 'summary');
        }

        if ($news['category_description'])
        {
            e107::meta('description', $news['category_description']);
            e107::meta('og:description', $news['category_description']);
            e107::meta('twitter:description', $news['category_description']);
        }

        if ($news['category_meta_description'])
        {
            e107::meta('description', $news['category_meta_description']);
            e107::meta('og:description', $news['category_meta_description']);
            e107::meta('twitter:description', $news['category_meta_description']);
            //define('META_DESCRIPTION', $news['news_meta_description']); // deprecated
        }
        elseif ($news['category_description'])
        {
            e107::meta('description', $news['category_description']);
            e107::meta('og:description', $news['category_description']);
            e107::meta('twitter:description', $news['category_description']);
        }


        // include news-thumbnail/image in meta. - always put this one first.
        if (!empty($news['category_image']))
        {
            $iurl = (substr($news['category_image'], 0, 3) == "{e_") ? $news['category_image'] : SITEURL . e_IMAGE . "newspost_images/" . $news['category_image'];
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

        $category = e107::getDb()->retrieve("news_category", "*", "category_id=" . intval($this->category_id));
        $this->currentRow = $category;
        $this->currentRow['items'] = array();

        $query = "
			SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, u.user_image, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords,
			nc.category_meta_description
			FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			WHERE n.news_category=" . intval($this->category_id) . "
			AND n.news_start < " . time() . " AND (n.news_end=0 || n.news_end>" . time() . ")
			AND n.news_class REGEXP '" . e_CLASS_REGEXP . "' AND NOT (n.news_class REGEXP " . $this->nobody_regexp . ")
			ORDER BY n.news_datestamp DESC
			LIMIT " . $this->from . "," . $this->news_list_limit;

        if ($news = e107::getDb()->retrieve($query, true))
        {

            $this->currentRow['items'] = $news;

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
            /* this way you can let index and follow category pages */
            e107::canonical('news', 'all', array(), $options);
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

        $breadcrumb[] = array('text' => $categoryName, 'url' => null);

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

    private function setPagination()
    {
        if (!empty($_GET['page']))
        {
            $this->page = (int) ($_GET['page']);
        }
        else $this->page = 0;

        // New in v2.3.1 Pagination with "Page" instead of "Record".
        if (!empty($this->pref['news_pagination']) && $this->pref['news_pagination'] === 'page' && !empty($this->page))
        {
            $this->from = (int) ($this->page - 1)  * $this->news_list_limit;
        }

        $this->addDebug('FROM', $this->from);
    }


    function renderError($error = NULL)
    {

        switch ($error)
        {
            case 1:
                $debug = "(1) Wrong GET parameters";
                break;
            case 2:
                $debug = "(2) Wrong ID - no available record / access, dates";
                break;
            default:
                break;
        }

        header("HTTP/1.0 404 Not Found", true, 404);
        require_once(e_LANGUAGEDIR . e_LANGUAGE . "/lan_error.php");

        $text = "";

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

$newsObj = new news_category_front;

$newsObj->init();

require_once(HEADERF);

$newsObj->render();

if (E107_DBG_BASIC && ADMIN)
{
    $newsObj->debug();
}

require_once(FOOTERF);
