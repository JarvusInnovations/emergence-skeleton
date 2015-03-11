{extends designs/site.tpl}

{block title}Migration &mdash; {$dwoo.parent}{/block}

{block content}
    <h2>Migration {$data.key|escape}</h2>
    <pre>{print_r($data, true)|escape}</pre>
{/block}