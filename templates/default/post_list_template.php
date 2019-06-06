<?php

// The container element for multiple units
$post_list_container = <<<LOADTEMPLATE
<style type="text/css">

</style>
<ol id="post_list" class="flexbox space_around">
  [REPEATED]
</ol>
LOADTEMPLATE;


// Code for each individual unit
$post_li_template = '<li><a href="?page=[PLACEHOLDER=post_url]&type=post"><div class="post_li_title">[PLACEHOLDER=post_title]</div><div class="thumb_box"><img src="[PLACEHOLDER=post_thumbnail]" alt="" class="post_thumb"></div></a></li>';