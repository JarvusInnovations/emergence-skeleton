{extends migration.tpl}

{block title}Executed {$dwoo.parent}{/block}

{block content}
    {$dwoo.parent}
    <h3>Output</h3>
    <pre>{print_r($output, true)|escape}</pre>
    <h3>Log</h3>
    <pre>{print_r($log, true)|escape}</pre>
{/block}