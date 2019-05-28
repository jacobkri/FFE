<?php

// The container element for multiple units
$some_link_container = <<<LOADTEMPLATE
<ol id="social" class="flexbox">
  [REPEATED]
</ol>
LOADTEMPLATE;


// Code for each individual unit
$some_link_template = '<li><a href="[PLACEHOLDER=url]"><img src="[PLACEHOLDER=ikon_url]" alt="[PLACEHOLDER=alt]"></a></li>';