<!DOCTYPE html>
{load_templates designs/site.subtemplates.tpl}
<html class="no-js" lang="en">

<head>
	<meta charset="utf-8">

	{* now use {$dwoo.parent} on subpages to automatically fill in the site name *}
	<title>{block "title"}{$.Site.title|escape}{/block}</title>

	{block "meta-info"}
        {include includes/site.meta-info.tpl}
	{/block}

	{block "meta-rendering"}
        {include includes/site.meta-rendering.tpl}
	{/block}

	{block "favicons"}
        {include includes/site.favicons.tpl}
	{/block}

	{block "css"}
        {include includes/site.css.tpl}
	{/block}

	{block "css-ie"}
		<!--[if IE 6]><link rel="stylesheet" href="/css/ie6.css"><![endif]-->
		<!--[if IE 7]><link rel="stylesheet" href="/css/ie7.css"><![endif]-->
		<!--[if IE 8]><link rel="stylesheet" href="/css/ie8.css"><![endif]-->
	{/block}

	{block "js-top"}
        {include includes/site.js-top.tpl}
	{/block}
</head>

{* using the responseID as a class on the body can help with subpage-specific styles *}
<body class="{block 'body-class'}{str_replace('/', '_', $.responseId)}-tpl{/block}">
	<div class="wrapper site">
	<header class="site clearfix">
	{block "header"}
		{* the things in here should probably be set up as configurable subtemplates in some way (especially nav) *}
		<h1 id="logo">
			<a href="/"><img src="http://placehold.it/300x40" alt="{$.Site.title|escape}" width="300" height="40"></a>
		</h1>

		<section id="user-info">
			{if $.User}
				{avatar $.User size=10} <a href="/profile" id="current-user">{personName $.User}</a> - <a href="/logout">Logout</a>
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
    			<li><a href="/blog">Blog</a></li>
    			<li><a href="/pages">Pages</a></li>
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

	{block "js-bottom"}
        {include includes/site.js-bottom.tpl}
	{/block}

    {block "js-analytics"}
        {include includes/site.analytics.tpl}
    {/block}

	{* enables site developers to dump the internal session log here by setting ?log_report=1 on any page *}
	{log_report}
</body>

</html>