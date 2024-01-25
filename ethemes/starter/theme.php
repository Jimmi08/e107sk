<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2022 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Minimal version 
 *
*/

if (!defined('e107_INIT'))
{
    exit();
}


class theme implements e_theme_render
{

    public function init()
    {

        e107::lan('theme');

        e107::meta('viewport', 'width=device-width, initial-scale=1');

        define("CORE_CSS", false);

        $this->css();

        e107::css('theme', 'e107.css');


    }

    /**
     * Override how THEME_STYLE is loaded. Duplicates will be automatically removed.
     * @return void
     */
    function css()
    {

    }


    function remove_ptags($text = '') // FIXME this is a bug in e107 if this is required.
    {

        $text = str_replace(array("<!-- bbcode-html-start --><p>", "</p><!-- bbcode-html-end -->"), "", $text);

        return $text;
    }


    function tablestyle($caption, $text, $mode = '', $options = array())
    {

        $style = varset($options['setStyle'], 'default');
        

        // Override style based on mode.
        switch ($mode)
        {
            default:
                $style = $mode;
                break;
        }

        if (deftrue('e_DEBUG'))
        {
            echo "\n<!-- \n";
            echo json_encode($options, JSON_PRETTY_PRINT);
            echo "\n-->\n\n";
        }

        switch ($style)
        {

            case 'bare':
            case 'none':
            case 'nocaption':
                
                echo $this->remove_ptags($text);
                break;



            // default style
		    // only if this always work, play with different styles

            default:
            
                if (!empty($caption))
                {
                    echo '<h4>' . $caption . '</h4>';
                }
                echo $text;

                return;

        }    


    }
    
}




