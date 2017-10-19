{template dli dt dd url=null default='&mdash;'}
    <div class="dli">
        <dt>{$dt}</dt>
        <dd>{if $url}<a href="{$url|escape}">{/if}{default $dd $default}{if $url}</a>{/if}</dd>
    </div>
{/template}