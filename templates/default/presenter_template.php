<?php

if (!empty($this->HTML_CONTENT['presenter_image'])) {
  // $presenter_bg = " style=\"background-image:url('".$this->HTML_CONTENT['presenter_image']."');\"";
  $presenter_bg = '<img class="thumbnail" src="'.$this->HTML_CONTENT['presenter_image'].'" alt="">';
}

// The presenter block
$presenter_template = <<<LOADTEMPLATE
<div class="presenter">
{$presenter_bg}
  <h2>{$this->HTML_CONTENT['presenter_title']}</h2>
</div>
<script src="https://cdn.jsdelivr.net/npm/simple-parallax-js@4.2.1/dist/simpleParallax.min.js"></script>
<script>
let image = document.getElementsByClassName('thumbnail');
new simpleParallax(image);
</script>
LOADTEMPLATE;

// Yay :-D