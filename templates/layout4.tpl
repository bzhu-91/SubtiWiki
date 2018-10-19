<?php $user = User::getCurrent(); ?>
<!DOCTYPE html>
<html>
   <head>
      <title>{{:headerTitle}}</title>
      <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
      <base href="<?php echo $GLOBALS["WEBROOT"];?>/" />

      <!-- fav icon -->
      <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
      <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
      <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
      <link rel="manifest" href="site.webmanifest">
      <link rel="mask-icon" href="img/safari-pinned-tab.svg" color="#5bbad5">
      <meta name="msapplication-TileColor" content="#da532c">
      <meta name="theme-color" content="#ffffff">

      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/4.6.1/papaparse.min.js"></script>
      <script type="text/javascript" src="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script> 
      <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.js"></script> 
      <script type="text/javascript" src="js/libs/md5.js"></script>
      <script type="text/javascript" src="js/libs/Lucille.js"></script>
      <script type="text/javascript" src="js/patch_IE.js"></script>
      <script type="text/javascript" src="js/patch_textarea.js"></script>
      <script type="text/javascript" src="js/patch_AJAX.js"></script>
      <script type="text/javascript" src="js/Editor.js"></script>

      <link rel="stylesheet" type="text/css" href="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" >
      <link rel="stylesheet" type="text/css" href="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.css" >
      <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
      <link rel="stylesheet" type="text/css" href="css/layout2.css" />
      <link rel="stylesheet" type="text/css" href="css/common.css" />
      {{js:jsBeforeContent}}{{css:styles}}
   </head>
   <body>
       {{:content}}
      <script type="text/javascript" src="js/markup.js"></script>
      {{js:jsAfterContent}}
      <script type="text/javascript" src="js/user.js"></script>
   </body>
</html>