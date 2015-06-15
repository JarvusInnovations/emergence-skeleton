{extends designs/site.tpl}

{* Uncomment this block to replace or prepend/append the title in site.tpl *}
{*block title}Home &mdash; {$dwoo.parent}{/block*}

{* Uncomment this block to replace the style-guide content block from site.tpl with something specific to the home page *}
{*block content}
    <header class="page-header">
        <h1 class="header-title title-1">Home Page</h1>
    </header>
    
    <p class="lead reading-width">Lorem ipsum&hellip;</p>
{/block*}