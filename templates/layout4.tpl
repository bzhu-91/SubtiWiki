<?php $user = User::getCurrent(); ?>
<!DOCTYPE html>
<html>
   <head>
      <title>{{:headerTitle}}</title>
      <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
      <base href="<?php echo $GLOBALS["WEBROOT"];?>/" />
      <link rel="stylesheet" type="text/css" href="css/layout2.css" />
      <link rel="stylesheet" type="text/css" href="css/common.css" />
      <link rel="stylesheet" type="text/css" href="css/jquery-ui.min.css" />
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        
      <script type="text/javascript" src="js/libs/md5.js"></script>
      <script type="text/javascript" src="js/libs/Lucille.js"></script>
      <script type="text/javascript" src="js/patch_IE.js"></script>
      <script type="text/javascript" src="js/patch_textarea.js"></script>
      <script type="text/javascript" src="js/patch_AJAX.js"></script>
      <script type="text/javascript" src="js/Editor.js"></script>
      {{js:jsBeforeContent}}{{css:styles}}
   </head>
   <body>
       {{:content}}
      <script type="text/javascript" src="js/markup.js"></script>
      {{js:jsAfterContent}}
      <script type="text/javascript" src="js/user.js"></script>
   </body>
</html>