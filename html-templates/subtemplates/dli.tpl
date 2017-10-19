{template dli dt dd url=null}
    <div class="dli">
        <dt>{$dt}</dt>
        <dd>{if $url}<a href="{$url}">{/if}{default $dd '&mdash;'}{if $url}</a>{/if}</dd>
    </div>
{/template}