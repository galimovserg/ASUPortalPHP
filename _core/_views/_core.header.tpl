<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>{$page_title} - Официальный портал кафедры АСУ</title>
    {foreach $js as $j}
        <script src="{$web_root}scripts/{$j}"></script>
    {/foreach}
    {foreach $css as $c}
        <LINK href="{$web_root}css/{$c}" rel="stylesheet" type="text/css">
    {/foreach}
    {if $_dojo_enabled}
        <script src="{$web_root}scripts/{$_dojo_path}dojo/dojo.js" data-dojo-config="parseOnLoad: true"></script>
        <LINK href="{$web_root}scripts/{$_dojo_path}dijit/themes/{$_dojo_theme}/{$_dojo_theme}.css" rel="stylesheet" type="text/css">
    {/if}
    {if ((count($jsInline) > 0) || (count($jqInline) > 0))}
        <script>
            {foreach $jsInline as $i}
                {$i}
            {/foreach}

            {if (count($jqInline) > 0)}
                $(document).ready(function(){
                    {foreach $jqInline as $i}
                        {$i}
                    {/foreach}
                });
            {/if}
        </script>
    {/if}
    {foreach $jsIe as $version=>$scripts}
		<!--[if IE {$version}]>
		{foreach $scripts->getItems() as $script}
			<script src="{$web_root}scripts/{$script}"></script>
		{/foreach}
		<![endif]-->
 	{/foreach}
</head>
<body class="{$_dojo_theme}">