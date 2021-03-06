<div class="dashboard">
    {foreach $items->getItems() as $item}
        <div class="dashboard_item item-{$item->getId()}">
            <div class="item_icon">
                {if $item->icon !== ""}
                    <img src="{$web_root}images/{$icon_theme}/64x64/{$item->icon}">
                {/if}
            </div>
            <div class="item_content">
                <h4>
                    {if $item->getLink() !== ""}
                        <a href="{$web_root}{$item->getLink()}">{$item->title}</a>
                    {else}
                        {$item->title}
                    {/if}
                </h4>
                {if ($item->children->getCount() > 0)}
                    <ul>
                        {foreach $item->children->getItems() as $child}
                            <li>
                                {if $child->getLink() !== ""}
                                    <a href="{$web_root}{$child->getLink()}">{$child->title}</a>
                                {else}
                                    {$child->title}
                                {/if}
                            </li>
                        {/foreach}
                    </ul>
                {/if}
            </div>
            <div style="clear: both;"></div>
        </div>
    {/foreach}
    {if $items@index eq 0}
    {foreach $settings->reports->getItems() as $report}
        <div class="dashboard_item dashboard_report" report_id="{$report->report->getId()}">
            <div class="item_hover"></div>
            <div class="item_icon">
                <img src="{$web_root}images/{$icon_theme}/64x64/apps/devhelp.png">
            </div>
            <div class="item_content">
                <h4>{$report->report->title}</h4>

            </div>
        </div>
    {/foreach}
    {/if}
</div>