<div class="section">
    <ol class="body-list url-list
    {if count($i->related_data.url_counts) gt 2}body-list-show-some{else}body-list-show-all{/if}">
    {foreach from=$i->related_data.url_counts key=url item=count name=bar}
        <li class="list-item">
            <div class="link">
                <div class="link-title">
                  <img src="//getfavicon.appspot.com/http://{$url}" alt="http://{$url}" width="16" height="16" />
                  <a href="http://{$url}">{$url}</a>
                </div>
                <div class="link-metadata">{$count} link{if $count gt 1}s{/if}</div>
            </div>
        </li>

    {/foreach}

    </ol>

    {if count($i->related_data.url_counts) gt 2}
    <button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all {$i->related_data.url_counts|@count} links</span> <i class="fa fa-chevron-down icon"></i></button>
    {/if}

</div>
