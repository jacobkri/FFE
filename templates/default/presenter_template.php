<?php

if (!empty($this->HTML_CONTENT['presenter_image'])) {
  $presenter_bg = " style=\"background-image:url('".$this->HTML_CONTENT['presenter_image']."');\"";
}

// The presenter block
$presenter_template = <<<LOADTEMPLATE
<div class="presenter"{$presenter_bg}>
  <h2>{$this->HTML_CONTENT['presenter_title']}</h2>
</div>
LOADTEMPLATE;

// Yay :-D