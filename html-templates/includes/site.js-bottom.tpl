{* more info goo.gl/mZiyb *}
<!--[if lt IE 7 ]>
	{jsmin "dd_belatedpng.js"}
	<script>DD_belatedPNG.fix('img, .png_bg');</script>
<![endif]-->

{if $.get.jsdebug || !Site::resolvePath('site-root/js/pages/common.js')}
    <script src="{Sencha::getVersionedFrameworkPath('ext', 'ext-all-dev.js')}"></script>
{else}
    <script src="{versioned_url('/js/pages/common.js')}"></script>
{/if}

<script>
    // TODO: figure out how to properly scope CSS so this doesn't need to be done
	Ext.onReady(function() {
		Ext.getBody().removeCls(['x-body', 'x-reset']);
	});

	Ext.Loader.setPath({
		Ext: '/app/ext/src'
		,Emergence: '/x/Emergence'
		,Site: '/app/pages/src'
	});

	Ext.require('Site.Common');
</script>