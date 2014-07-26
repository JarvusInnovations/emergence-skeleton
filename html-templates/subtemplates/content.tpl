{template contentBlock Content contentClass=null extraClass=null accountLevelEdit=Staff}
    {$handle = tif(is_string($Content) ? $Content : $Content->Handle)}
    {$Content = tif(is_string($Content) ? Content::getByHandle($Content) : $Content)}

    <div class="content-editable {tif $contentClass ? $contentClass : $handle} {$extraClass}" {if $.User->hasAccountLevel($accountLevelEdit)}data-content-endpoint="/contents" data-content-id="{$handle}" {if !$Content}data-content-phantom="true"{/if} data-content-field="Content" data-content-value="{$Content->Content|escape}" data-content-renderer="{$Content->Renderer|default:Content::getFieldOptions('Renderer', 'default')}"{/if}>
        {tif $Content ? $Content->getHtml()}
    </div>
{/template}