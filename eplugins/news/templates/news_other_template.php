<?php


### Related 'start' - Options: Core 'single' shortcodes including {SETIMAGE}
### Related 'item' - Options: {RELATED_URL} {RELATED_IMAGE} {RELATED_TITLE} {RELATED_SUMMARY}
### Related 'end' - Options:  Options: Core 'single' shortcodes including {SETIMAGE}
/*
$NEWS_TEMPLATE['related']['start'] = "<hr><h4>".defset('LAN_RELATED', 'Related')."</h4><ul class='e-related'>";
$NEWS_TEMPLATE['related']['item'] = "<li><a href='{RELATED_URL}'>{RELATED_TITLE}</a></li>";
$NEWS_TEMPLATE['related']['end'] = "</ul>";*/

$NEWS_OTHER_TEMPLATE['related']['caption']    = '{LAN=RELATED}';
$NEWS_OTHER_TEMPLATE['related']['start']      = '{SETIMAGE: w=350&h=350&crop=1}<div class="row">';
$NEWS_OTHER_TEMPLATE['related']['item']       = '<div class="col-md-4"><a href="{RELATED_URL}">{RELATED_IMAGE}</a><h3><a href="{RELATED_URL}">{RELATED_TITLE}</a></h3></div>';
$NEWS_OTHER_TEMPLATE['related']['end']        = '</div>';



// Navigation/Pagination
$NEWS_OTHER_TEMPLATE['nav']['previous'] = '<a rel="prev" href="{NEWS_URL}">{GLYPH=fa-chevron-left}<span class="mx-1">{NEWS_TITLE}</span></a>';
$NEWS_OTHER_TEMPLATE['nav']['current'] = '<a class="text-center" href="{NEWS_NAV_URL}">{LAN=BACK}</a>';
$NEWS_OTHER_TEMPLATE['nav']['next'] = '<a rel="next" class="text-right" href="{NEWS_URL}"><span class="mx-1">{NEWS_TITLE}</span>{GLYPH=fa-chevron-right}</a> ';

$NEWS_OTHER_TEMPLATE['comments']['layout']     = '<h5>{COMMENTCAPTION}</h5> <br> {COMMENTFORM}{COMMENTS}<br>{MODERATE} ';
