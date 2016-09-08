{extends "designs/site.tpl"}
{*<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Site Admin for {$.Site.title|escape}</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2em;
            padding: 0;
        }

        ul {
            display: inline-block;
            margin: 0;
            padding: 0;
        }

        li {
            display: block;
            list-style: none;
            margin-bottom: 1px;
        }

        a {
            background: #eee;
            display: block;
            padding: .75em 1.5em;
            text-decoration: none;
        }

        a:hover,
        a:focus {
            background: #ccc;
        }
    </style>
</head>

<body>*}
{block "header"}{/block}
{block "content"}
    <header class="page-header">
        <h2 class="header-title">Site Admin for {$.Site.title|escape}</h2>
    </header>

    <ul>
        <li><a class="button" href="/develop">/develop</a></li>
        <li><a class="button" href="/app/EmergencePullTool/production">Emergence Pull Tool</a></li>
        {foreach item=script from=$scripts}
            {$scriptName = $script->Handle|basename:'.php'}
            <li><a class="button" href="/site-admin/{$scriptName}">{$scriptName}</a></li>
        {/foreach}
    </ul>
{/block}
{*</body>
</html>*}