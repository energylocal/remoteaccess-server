<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EmonLocal</title>
    <?php foreach ($stylesheets as $url) printf('<link href="%s" rel="stylesheet">'."\n\t", $url); ?>

  </head>

  <body>

    <nav class="navbar sticky-top navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php _url('') ?>">EmonLocal</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <?php 
                    _navlink('Feeds', 'feeds');
                    _navlink('Minimal', 'minimal');
                    _navlink('Vue Test', 'vuetest');
                    _navlink('Graph', 'graph');

                    if($session["valid"]===true){
                        _navlink('Logout','logout');
                    } else {
                        _navlink('Login','login');
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container pt-3">
        <?php echo $content; ?>
        
    </main>

    <!-- JAVASCRIPTS -->
    <?php foreach ($scripts as $url) printf('<script src="%s"></script>'."\n\t", $url); ?>
    <?php foreach ($ie_scripts as $url) printf('<!--[if IE]><script src="%s"></script><![endif]-->'."\n\t", $url); ?>

    <!-- SVG ICONS -->
    <svg id="svgs" xmlns="http://www.w3.org/2000/svg" class="d-none">
      <symbol id="lock-locked"><path d="M3 0c-1.1 0-2 .9-2 2v1h-1v4h6v-4h-1v-1c0-1.1-.9-2-2-2zm0 1c.56 0 1 .44 1 1v1h-2v-1c0-.56.44-1 1-1z" transform="translate(1)" /></symbol>
      <symbol id="lock-unlocked"><path d="M3 0c-1.1 0-2 .9-2 2h1c0-.56.44-1 1-1s1 .44 1 1v2h-4v4h6v-4h-1v-2c0-1.1-.9-2-2-2z" transform="translate(1)" /></symbol>
      <symbol id="edit"><path d="M6 0l-1 1 2 2 1-1-2-2zm-2 2l-4 4v2h2l4-4-2-2z" /></symbol>
      <symbol id="delete"><path d="M3 0c-.55 0-1 .45-1 1h-1c-.55 0-1 .45-1 1h7c0-.55-.45-1-1-1h-1c0-.55-.45-1-1-1h-1zm-2 3v4.81c0 .11.08.19.19.19h4.63c.11 0 .19-.08.19-.19v-4.81h-1v3.5c0 .28-.22.5-.5.5s-.5-.22-.5-.5v-3.5h-1v3.5c0 .28-.22.5-.5.5s-.5-.22-.5-.5v-3.5h-1z" /></symbol>
      <symbol id="download"><path d="M3 0v3h-2l3 3 3-3h-2v-3h-2zm-3 7v1h8v-1h-8z" /></symbol>
      <symbol id="view"><path d="M7.03 0l-3.03 3-1-1-3 3.03 1 1 2-2.03 1 1 4-4-.97-1zm-7.03 7v1h8v-1h-8z" /></symbol>
      <symbol id="close"><path d="M1.41 0l-1.41 1.41.72.72 1.78 1.81-1.78 1.78-.72.69 1.41 1.44.72-.72 1.81-1.81 1.78 1.81.69.72 1.44-1.44-.72-.69-1.81-1.78 1.81-1.81.72-.72-1.44-1.41-.69.72-1.78 1.78-1.81-1.78-.72-.72z" /></symbol>
      <symbol id="search"><path d="M3.5 0c-1.93 0-3.5 1.57-3.5 3.5s1.57 3.5 3.5 3.5c.59 0 1.17-.14 1.66-.41a1 1 0 0 0 .13.13l1 1a1.02 1.02 0 1 0 1.44-1.44l-1-1a1 1 0 0 0-.16-.13c.27-.49.44-1.06.44-1.66 0-1.93-1.57-3.5-3.5-3.5zm0 1c1.39 0 2.5 1.11 2.5 2.5 0 .66-.24 1.27-.66 1.72-.01.01-.02.02-.03.03a1 1 0 0 0-.13.13c-.44.4-1.04.63-1.69.63-1.39 0-2.5-1.11-2.5-2.5s1.11-2.5 2.5-2.5z" /></symbol>
    </svg>

  </body>
</html>
