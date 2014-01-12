{*
Renders an insight with an array of links embedded in posts in related_data.

Parameters:
$i (required) Insight object
$icon (required) Icon glyph name, from http://twitter.github.com/bootstrap/base-css.html#icons
*}
      {if isset($i->related_data.posts)}
          <ul class="body-list link-list
          {if count($i->related_data.posts) lte 2}body-list-show-all{else}body-list-show-some{/if}">
          {foreach from=$i->related_data.posts key=pid item=p name=bar}

              {foreach from=$p->links key=lid item=l name=lnk}
              <li class="list-item">
                  <div class="link">
                      <div class="link-title">
                          <a href="{$l->url}">
                              {if $l->title}
                                  {$l->title|truncate:100}title
                              {elseif $l->expanded_url}
                                  {$l->expanded_url}
                              {else}
                                  {$l->url}
                              {/if}
                          </a>
                      </div>
                      <div class="link-metadata">
                      {if $p->network eq 'twitter'}
                          Posted by {'@'|cat:$p->author_username|link_usernames_to_twitter}
                          on <a href="http://twitter.com/{$p->author_user_id}/statuses/{$p->post_id}">
                          {$p->adj_pub_date|date_format:"%b %e"}</a>
                      {else}
                          Posted by {$p->author_fullname} on {$p->adj_pub_date|date_format:"%l:%M %p - %d %b %y"}
                      {/if}
                      </div>
                  </div>
              </li>
              {/foreach}

          {/foreach}

          </ul>
          {if count($i->related_data.posts) gt 2}
          <button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all {$i->related_data.posts|@count} links</span> <i class="fa fa-chevron-down icon"></i></button>
          {/if}
    {/if}
