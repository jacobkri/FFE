<?php

// The container element for multiple units
$video_list_container = <<<LOADTEMPLATE
<style type="text/css">

</style>
<ol id="video_list" class="flexbox">
  [REPEATED]
</ol>
LOADTEMPLATE;


// Code for each individual unit
$video_li_template = '<li><a href="?page=[PLACEHOLDER=video_url]&type=video"><div class="thumb_box"><div class="video_play_symbol">▶</div><img src="[PLACEHOLDER=video_thumbnail]" alt="" class="video_thumb"></div><div class="video_li_title">[PLACEHOLDER=video_title]</div></a></li>';