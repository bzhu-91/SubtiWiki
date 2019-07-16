<!doctype html>
<html lang="en">
	<head>
		<title>{{:pageTitle}}</title>
		<base href="<?php echo $GLOBALS["WEBROOT"];?>/" />

		<meta http-equiv="expires" content="0" />
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
		<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />

		<!-- fav icon -->
		<link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
		<link rel="manifest" href="site.webmanifest">
		<link rel="mask-icon" href="img/safari-pinned-tab.svg" color="#5bbad5">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="theme-color" content="#ffffff">

		<!-- html5 + CSS 3 Template created by miss monorom  http://intensivstation.ch 2013 -->
		<link rel="stylesheet" href="css/layout1.css" type="text/css" />
		<link rel="stylesheet" href="css/common.css" type="text/css" />

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="js/libs/md5.js"></script>
		<script type="text/javascript" src="js/libs/Lucille.js"></script>
		<script type="text/javascript" src="js/markup.js"></script>
		<script type="text/javascript" src="js/patch_textarea.js"></script>
		{{css:css}}{{js:jsBeforeContent}}
	</head>
	<body>
		<div id="float_container">
			<a href='javascript:showSearch();'>
			<img src='img/search.svg'/>
			</a>
			{{floatButton:floatButtons}}
			<a href='javascript:window.scrollTo(0, 0);'>
			<img src='img/top.svg'/>
			</a>
		</div>
		<div id="container">
			<div id="top">
				<header>
					<!-- different images for the sizes of the media -->
					<div class="menubutton"></div><!-- menubutton -->
               <a href="index.php" class="maxi">
                  <img src="img/Logo_transparent.png" alt="<?php echo $GLOBALS['SITE_NAME']; ?>" height="100px" />
               </a><!-- 413 x 56 -->
               <a href="index.php" class="mini" style="text-align: center">
                  <img src="./img/Logo_transparent.png" alt="<?php echo $GLOBALS['SITE_NAME']; ?>" height="80px" />
               </a><!-- 154 x 53 -->
				</header>
				<nav id="mainnav">{{navlink:navlinks}}</nav>
			</div><!-- top -->
			<div id="floatTop">
				<span>{{:title}}</span>
				<nav>{{navlink:navlinks}}</nav>
			</div>
			<section id="content">
				<div id="content-wrapper">
					{{banner.php}}
					<h1>
						<span>{{:title}}</span>
						<span style="float:right; font-size: smaller">{{:titleExtra}}</span>
					</h1>
					{{:content}}
					<div class="footnote box" style="margin-top: 50px; display: {{:showFootNote}}">
						<p style="display: none;">{{:bank_id}}</p>
						<p style="display: none;">{{:id}}</p>
						<p><b>Page visits: </b>{{:count}}</p>
						<p><b>Time of last update: </b>{{:lastUpdate}}</p>
						<p><b>Author of last update: </b>{{:lastAuthor}}</p>
					</div>
				</div>
			</section>
			<aside>
				<div id="highlights">
					<h3>Highlights</h3>
					<ul style="padding-left: 0" id="highlights">
					<li><a href="wiki?title=Conferences">Conferences</a></li>
					<li><a href="wiki?title=Paper%20of%20the%20month">Paper of the month</a></li>
					<li><a href="wiki?title=labs">Bacillus labs</a></li>
					<li><a href="category">All categories</a></li>
					<li><a href="gene/random">Random gene</a></li>
					<li><a href="https://academic.oup.com/nar/article/46/D1/D743/4372578" target="_blank" style="color: #E65100">Please cite us ^_^</a></li>
					<li><a href="wiki?title=People">Credits</a></li>
				</div><!-- highlights -->
				<div id="special">
					<h3>Special pages</h3>
					<ul style="padding-left: 0" id="specialPages">
						<li><a href="gene/exporter">Gene export wizard</a></li>
						<li><a href="exports">Exports</a></li>
						<li><a href="user">User list</a></li>
						<li><a href="history">History</a></li>
						<li><a href="statistics">Statistics</a></li>
					</ul>
				</div>
				<div id="searchWrapper">
					<div id="search">
						<h3>Search</h3>
						<p>
                     <input id="searchBox" placeholder="Gene / locus tag" />
                     <button id="send_go">Go</button>
							<button id="send_search">Search</button>
						</p>
						<br />
						<h3>PubMed</h3>
						<p>
                     <input id="searchBox2" type="text" maxlength="100" placeholder="Title, author, id"/>
                     <button id="pubmed_search">Go</button>
                  </p>
					</div>
				</div>
				<div>{{:side}}</div>
			</aside>
			<footer class="footer">
				<div class="footer-segment">
					<h3>Contact</h3>
					<ul>
						<li><a href="">General Microbiology Göttingen</a></li>
						<li><a href="people">People</a></li>
						<li>Web admin: <a href="mailto:bzhu@gwdg.de">bzhu@gwdg.de</a></li>
						<li>Admin: <a href="mailto:jstuekl@gwdg.de">jstuelk@gwdg.de</a></li>
					</ul>
				</div><!-- footer segment -->
				<div class="footer-segment">
					<h3>Special pages</h3>
					<ul>
						<li><a href="user">User list</a></li>
						<li><a href="history">All history</a></li>						
						<li><a href="statistics">Statistics</a></li>						
					</ul>
				</div>
				<div class="footer-segment">
					<h3>Browsers</h3>
					<ul>
						<li><a href="pathway">Pathway browser</a></li>
						<li><a href="expression">Expression browser</a></li>
						<li><a href="interaction">Interaction browser</a></li>
						<li><a href="regulation">Regulation browser</a></li>
						<li><a href="genome">Genome browser</a></li>
					</ul>
				</div>
			</footer>
		</div><!-- container -->
      <script type="text/javascript">{{:jsvars}}</script>
      <script type="text/javascript" src="js/search.js"></script>
		<script type="text/javascript" src="js/user.js"></script>
		<script type="text/javascript" src="js/pubmed.js"></script>
		<script type="text/javascript" src="js/layout1.js"></script>
		{{js:jsAfterContent}}
	</body>
</html>
