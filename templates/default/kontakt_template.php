<?php

// The container element for multiple units
$kontakt_container = <<<LOADTEMPLATE
<div class="kontakt_container flexbox space_between">
  [REPEATED]
</div>
<div class="address_box" style="{$this->HTML_CONTENT['address_bgimage']}">
  <div class="address_wrap">
    {$this->HTML_CONTENT['address_section']}
  </div>
</div>
LOADTEMPLATE;


// Code for each individual unit
$kontakt_template = <<<LOADTEMPLATE
  <div class="kontakt">
    <img src="[PLACEHOLDER=billede]" class="profile_picture" alt="[PLACEHOLDER=billede_alt]">
    <p class="about_kontakt">
      <b>[PLACEHOLDER=navn]</b><br>
      [PLACEHOLDER=rolle]
    </p>
    <p class="kontakt_details">
      [PLACEHOLDER=telefon]<br>
      [PLACEHOLDER=email]
    </p>
  </div>
LOADTEMPLATE;

// Yay :-D