<?php

// The container element for multiple units
$region_container = <<<LOADTEMPLATE
<div id="departments" class="flexer">
  <h2>REGIONER</h2>
  <ol class="flexbox space_between">
   [REPEATED]
  </ol>
</div>
LOADTEMPLATE;


// Code for each individual unit 
$region_template = '<li><a href="[PLACEHOLDER=url]">[PLACEHOLDER=title]</a></li>';