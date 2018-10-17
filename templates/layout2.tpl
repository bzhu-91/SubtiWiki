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
        <script type="text/javascript" src="js/libs/md5.js"></script>
        <script type="text/javascript" src="js/libs/Lucille.js"></script>
        <script type="text/javascript" src="js/patch_IE.js"></script>
        <script type="text/javascript" src="js/patch_textarea.js"></script>
        <script type="text/javascript" src="js/patch_AJAX.js"></script>
        <script type="text/javascript" src="js/Editor.js"></script>

        <!-- summer note related -->
        <!-- include libraries(jQuery, bootstrap) -->
        <link href="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" rel="stylesheet">
        <script src="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script> 

        <!-- include summernote css/js -->
        <link href="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.css" rel="stylesheet">
        <script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.js"></script> 

        <link rel="stylesheet" type="text/css" href="css/layout2.css" />
        <link rel="stylesheet" type="text/css" href="css/common.css" />
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
        {{js:jsBeforeContent}}{{css:styles}}
   </head>
   <body>
      <div id="upper">
         <a href="" target="_self">Home</a>
         <a href="category/" target="_self">Categories</a>
         <a href="regulon" target="_self">Regulon list</a>
         <a href="category?id=SW.6.1">Essential Genes</a>
         <a href="http://subtiwiki.uni-goettingen.de/NetVis/" target="_blank">NetVis</a>
         <a href="gene/random">Random gene</a>
         <a href="https://academic.oup.com/nar/article/46/D1/D743/4372578" target="_blank" style="font-weight: bold">Citation</a><?php if ($user): ?>
            <a href="javascript:user.logout()">Hello, <span style='color: white'><?=$user->name?></span></a><?php endif ?>
         <?php if (!$user): ?><a href="javascript:user.login()">Log in</a><?php endif ?>
         <span style="float: right;"><div id="search-wrapper">{{:searchBox}}</div></span>
      </div><!-- upper -->
      <div id="float_container">
         {{floatButton:floatButtons}}<!-- <a href='javascript:window.scrollTo(0, 0);'><img src='img/top.svg'/></a> -->
      </div>
      <div id="middle">
         <h2 class="title">{{:pageTitle}}</h2>
         <div>{{:content}}</div>
      </div><!-- middle -->
      <script type="text/javascript" src="js/pathwaySearch.js"></script>
      <div id="under">
         <p>
            <a href="pathway">Pathway browser</a>
            <a href="expression">Expression browser</a>
            <a href="interaction">Interaction browser</a>
            <a href="regulation">Regulation browser</a>
            <a href="genome">Genome browser</a>
         </p>
         <p>
            <a href="FAQ">FAQ</a>
            <a href="user">User list</a>
            <a href="history">History list</a>
            <a href="statistics">Statistics</a>
            <a href="http://www.minibacillus.org/">Mini<i>bacillus</i></a>
            <a href="people">People</a>
            <a href="wiki?title=Labs">Labs</a>
            <a href="exports">Data</a><a href="">Impressum</a>
         </p>
      </div>
      <script type="text/javascript" src="js/markup.js"></script>{{js:jsAfterContent}}
      <script type="text/javascript" src="js/user.js"></script>
   </body>
</html>