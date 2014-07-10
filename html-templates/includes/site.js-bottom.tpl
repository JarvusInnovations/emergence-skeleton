{* more info goo.gl/mZiyb *}
<!--[if lt IE 7 ]>
	{jsmin "dd_belatedpng.js"}
	<script>DD_belatedPNG.fix('img, .png_bg');</script>
<![endif]-->

{if $.get.jsdebug || !Site::resolvePath('site-root/js/pages/common.js')}
    <script src="{Sencha::getVersionedFrameworkPath('ext', 'build/ext-all-debug.js', '5.0.0')}"></script>
    {sencha_bootstrap classPaths=array('sencha-workspace/pages/src', 'ext-library/Jarvus/ext/patch')}
{else}
    <script src="{Site::getVersionedRootUrl('js/pages/common.js')}"></script>
{/if}

<script>
    Ext.scopeCss = true;
    Ext.require('Site.Common');
</script>