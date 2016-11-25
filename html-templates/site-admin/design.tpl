<!DOCTYPE html>
<html lang="en">
    <head>
        {block meta}
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="icon" href="/img/emergence/favicon.ico">
        {/block}

        <title>{block title}Emergence Site Administrator{/block}</title>

        {block css}
            {cssmin "bootstrap3/bootstrap.css+bootstrap3/bootstrap-theme.css+site-admin.css"}
        {/block}
    </head>

    <body>
        {block nav}
            {if $.task && !$activeSection}
                {$activeSection = tasks}
            {/if}

            <nav class="navbar navbar-inverse navbar-fixed-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="#" class="navbar-left emr-logo"><img src="/img/emergence/logo.png"></a>
                        <a class="navbar-brand" href="/site-admin/">Emergence Site Administrator</a>
                    </div>
                    <div id="navbar" class="collapse navbar-collapse">

                        <ul class="nav navbar-nav">
                            {foreach item=item key=key from=Emergence\SiteAdmin\Navigation::getItems()}
                                <li class="{tif $activeSection == $key ? active}">
                                    <a href="{$item.url|escape}">
                                        {$item.label|escape}

                                        {if $item.badge}
                                            <span class="badge">{$item.badge|number_format}</span>
                                        {/if}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
{*
                        <ul class="nav navbar-nav navbar-right">
                            <li><a href="#">Load Average</a></li>
                            {$CPULoad = sys_getloadavg()}
                            <li class="active {if $CPULoad.0>1}load-yellow{else if $CPULoad.0>4}load-red{else}load-green{/if}"><a href="#">{$CPULoad.0}<sub>1</sub></a></li>
                            <li class="active {if $CPULoad.0>1}load-yellow{else if $CPULoad.0>4}load-red{else}load-green{/if}"><a href="#">{$CPULoad.1}<sub>5</sub></a></li>
                            <li class="active {if $CPULoad.0>1}load-yellow{else if $CPULoad.0>4}load-red{else}load-green{/if}"><a href="#">{$CPULoad.2}<sub>15</sub></a></li>
                        </ul>
*}
                    </div>
                </div>
            </nav>
        {/block}

        <div class="container main-content">
            {strip}
            <ol class="breadcrumb">
                {block breadcrumbs}
                    {if $.task}
                        <li><a href="/site-admin/tasks">Tasks</a></li>
                        <li class="active">
                            <span class="glyphicon glyphicon-{$.task.icon}" aria-hidden="true"></span>
                            &nbsp;
                            {$.task.title|escape}
                        </li>
                    {/if}
                {/block}
            </ol>
            {/strip}

            {block content}{/block}
        </div>

        {block js-bottom}
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
            {jsmin "bootstrap3/bootstrap.js+bootbox.js"}
        {/block}
  </body>
</html>
