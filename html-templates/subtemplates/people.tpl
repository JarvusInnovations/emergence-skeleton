{template avatar Person size=32 pixelRatio=2 urlOnly=false forceSquare=true}
    {$pixels = $size}
    {if $pixelRatio}
        {$pixels = $size * $pixelRatio}
    {/if}
    {if $Person->PrimaryPhoto}
        {$src = $Person->PrimaryPhoto->getThumbnailRequest($pixels, $pixels, null, $forceSquare)}
    {elseif $Person->Email}
        {$src = cat("//www.gravatar.com/avatar/", md5(strtolower($Person->Email)), "?s=", $pixels, "&r=g&d=mm")}
    {/if}

    {if $urlOnly}{strip}
        $src
    {/strip}{else}{strip}
        <img alt="{$Person->FullName|escape}" src="{$src}" class="avatar" width="{$size}">
    {/strip}{/if}
{/template}

{template personLink Person photo=no photoSize=64 pixelRatio=2}{strip}
    <a href="{$Person->getURL()}" title="{$Person->FullName|escape}">
        {if $photo}
            {avatar $Person size=$photoSize pixelRatio=$pixelRatio}
        {/if}
        <span class="name">{$Person->FullName|escape}</span>
    </a>
{/strip}{/template}