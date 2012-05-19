{$siteConfig = Site::$config}

<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
	<meta charset="utf-8">
	
	{* now use {$dwoo.parent} on subpages to automatically fill in the site name *}
	<title>{block "title"}{$siteConfig.label|default:$.server.HTTP_HOST}{/block}</title>
	
	{block "meta-info"}
	{* most other old-school meta tags are now useless; "keywords" is ignored by Google, for example
	 * but "description" is still good, and you can put other microdata-type tags in here if need be
	*}
	<meta name="description" content="">
	{/block}
	
	{block "meta-rendering"}
	{* tell IE to always use the most modern available rendering engine (even if that means invoking Chrome Frame :) *}
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	{* viewport settings for mobile browsers (change these if default rendering/width is undesirable) *}
	<meta name="viewport" content="width=1000">
	{/block}
	
	{block "favicons"}
	{* in most cases, placing a favicon.ico & a high res apple-touch-icon.png into site-root will suffice
	 * in which case you can delete these link tags
	 * but you can uncomment and use them if you need more explicit URLs for some reason
	<link rel="shortcut icon" href="/favicon.ico">
	<link rel="apple-touch-icon" href="/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/touch-icon-ipad.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="/touch-icon-iphone4.png" />
	*}
	{/block}
	
	{block "css"}
	{* increment the ?v= counter when you update the CSS, to ensure browsers will re-download *}
	<link rel="stylesheet" href="/css/base.css">
	<link rel="stylesheet" href="/css/site.css">
	{/block}
	
	{block "css-ie"}
	<!--[if IE 6]><link rel="stylesheet" href="/css/ie6.css?v=1"><![endif]-->
	<!--[if IE 7]><link rel="stylesheet" href="/css/ie7.css?v=1"><![endif]-->
	<!--[if IE 8]><link rel="stylesheet" href="/css/ie8.css?v=1"><![endif]-->
	{/block}
	
	{block "js-top"}
	{* modernizr.com *}
	<script src="/js/modernizr.js"></script>
	{/block}
</head>

{* using the responseID as a class on the body can help with subpage-specific styles *}
<body class="{$responseID|default:'template'}">
	<div class="wrapper site">
	<header class="site clearfix">
	{block "header"}
		{* the things in here should probably be set up as configurable subtemplates in some way (especially nav) *}
		<h1 id="logo">
			<a href="/"><img src="http://placehold.it/300x40" alt="{$siteConfig.label|default:$.server.HTTP_HOST}" width="300" height="40"></a>
		</h1>
		
		<section id="user-info">
			{if $.User}
				<a href="/profile" id="current-user">{$.User->FullName}</a> - <a href="/logout">Logout</a>
			{else}
				<form id="minilogin" action="/login" method="post">
					<fieldset>
						<input type="text" class="text" name="_LOGIN[username]" placeholder="Username or email" id="minilogin-username" autocorrect="off" autocapitalize="off">
						<input type="password" class="text password" name="_LOGIN[password]" placeholder="Password" id="minilogin-password">
						<input type="submit" class="button submit" id="minilogin-submit" value="Log in">
					</fieldset>
				</form>
				or <a href="/register" id="register-link">Register</a> or <a href="/register/recover" id="recover-link">Recover Password</a>
			{/if}
		</section>

		<nav>
			<ul>
				<li class="current"><a href="/">Home</a></li>
				<li><a href="/about">About</a></li>
				<li><a href="/contact">Contact</a></li>
			</ul>
		</nav>
	{/block}
	</header>
	
	<div id="content" class="clearfix" role="main">
	{block "content"}
		<section class="body-text">
			<h1>Lorem ipsum</h1>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce in ligula dolor. Sed pellentesque quam a odio sollicitudin molestie. Nulla vulputate congue elit id dapibus. Nulla sodales, mi sit amet mollis tincidunt, dui velit ultrices felis, eu mattis sem enim pellentesque tellus. Maecenas vel magna enim. Proin commodo, magna in semper laoreet, nisl tellus dignissim odio, vel hendrerit arcu mauris vel mi. Praesent quis sodales nibh. Sed interdum sodales porttitor. Donec ante elit, venenatis non tempor ut, volutpat accumsan nulla. Nunc nunc nisl, vehicula sit amet pharetra non, lacinia at neque.</p>
			<p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed ac neque vitae metus rhoncus commodo eu at risus. Aenean quis auctor neque. Suspendisse ultricies tempor purus, et eleifend leo porta sed. Phasellus sed sapien ac ipsum dignissim eleifend ut in urna. Praesent venenatis, orci eget lacinia accumsan, lectus lorem porta ante, non porttitor purus mauris eu urna. Vestibulum adipiscing interdum cursus. Aenean sed neque ut nisl interdum fermentum in a neque. Duis sit amet nulla ipsum. Sed velit quam, sodales sit amet scelerisque ut, rutrum et nisl. Donec non lobortis metus. Sed id pulvinar risus. Nulla faucibus arcu nec felis bibendum commodo rhoncus turpis elementum. Mauris interdum nulla vel velit dignissim pretium. Ut volutpat libero diam, ut viverra erat. Pellentesque ante tellus, adipiscing id euismod a, consequat ut eros. Donec sagittis vestibulum leo, sed laoreet libero dignissim quis.</p>
		</section>
	{/block}
	</div><!--!end #content -->
	
	<footer class="site clearfix">
	{block "footer"}
		<small>Copyright &copy; {date_format $.now "%Y"}. All rights reserved.</small>
	{/block}
	</footer>
	</div> <!--!end .site.wrapper -->

	{block "js-analytics"}
	{* Optimized Analytics loader (uncomment and change UA-XXXXX-X to be your site's ID) goo.gl/PpmmQ
	<script>
		var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];(function(d,t){
		var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
		g.async=1;g.src='//www.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)
		}(document,'script'))
	</script>
	*}
	{/block}
	
	{block "js-bottom"}
	{* more info goo.gl/mZiyb *}
	<!--[if lt IE 7 ]>
	<script src="js/libs/dd_belatedpng.js"></script>
	<script>DD_belatedPNG.fix('img, .png_bg');</script>
	<![endif]-->
	
	<script src="/x/ext/ext.js"></script>
	{/block}
</body>

</html>