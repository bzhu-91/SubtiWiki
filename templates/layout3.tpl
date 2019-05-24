<!DOCTYPE html>
<html>
<head>
	<title>{{:pageTitle}}</title>
	<base href="<?php echo $GLOBALS["WEBROOT"];?>/" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
	{{css:css}}
	<link rel="stylesheet" type="text/css" href="css/layout3.css">
	<link rel="stylesheet" type="text/css" href="css/common.css">
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	<script type="text/javascript" src="js/libs/Lucille.js"></script>
	<script type="text/javascript" src="js/libs/md5.js"></script>
    {{js:jsBeforeContent}}
</head>
<body>
<div id="center">
	<div style="text-align: left; padding: 20px; background: white">{{:content}}</div>
</div>
{{js:jsAfterContent}}
<script type="text/javascript" src="js/user.js"></script>
</body>
</html>