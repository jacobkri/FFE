<?php

$requested_uri_clean = strtok($_SERVER["REQUEST_URI"],'?');

// The container element for multiple units
$navigation_container = <<<LOADTEMPLATE
<nav>
<button id="nav_burger">☰</button>
<div id="burger_presenter">
  <!-- Contains a preview of the page-title and thumbnail (if any) -->
</div>
<ol>
  [REPEATED]
</ol>
</nav>
LOADTEMPLATE;


// Code for each individual unit
$navigation_template = '<li><a href="?page=[PLACEHOLDER=post_name]" data-bgimg-url="[PLACEHOLDER=thumbnail]">[PLACEHOLDER=link_titel]</a></li>';