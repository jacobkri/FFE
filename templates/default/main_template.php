<?php
// Main template file
// Contains the standard HTML parts for all pages,
// such as: <!doctype html>, <html>, <head>, <body>. Etc.

$template = <<<LOADTEMPLATE
<!doctype html>
<html lang="da">

 <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{$this->HTML_CONTENT['description']}">

    <title>{$this->HTML_CONTENT['title']}</title>
    <link rel="stylesheet" href="templates/default/main.css" type="text/css">
    <link rel="stylesheet" href="fonts/open-sans.css" type="text/css">
    <link rel="stylesheet" href="fonts/roboto.css" type="text/css">
    
  </head>

  <body>
    <header>
      <div class="width_control">
        <div class="text_logo">
         <h1>Fonden For Entreprenørskab</h1>
         <p style="padding:0.2rem 0 0;font-weight: bold;">
         Region Hovedstaden
         </p>
        </div>

      {$this->HTML_CONTENT['navigation']}
      </div>
    </header>

     {$this->HTML_CONTENT['presenter']}

    <article id="ffe_main" class="width_control">
     {$this->HTML_CONTENT['main_content']}
    </article>
    
    <footer>
      <div class="width_control flexbox">
        <div class="flexer">
        {$this->HTML_CONTENT['address_section']}
        {$this->HTML_CONTENT['SoMe_link_section']}
        </div>
        {$this->HTML_CONTENT['region_section']}
      </div>
      <p id="admin_link" class="width_control"><a href="wordpress/wp-admin">Admin</a></p>
    </footer>
  </body>
 </html>
 
LOADTEMPLATE;

// Yay :-D