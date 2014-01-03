{*
Renders an insight with an array of links embedded in posts in related_data.

Parameters:
$i (required) Insight object
$icon (required) Icon glyph name, from http://twitter.github.com/bootstrap/base-css.html#icons
*}


      {if isset($i->related_data[0]->links)}
          <ul class="body-list link-list" style="height: 109px;">
          {foreach from=$i->related_data key=pid item=p name=bar}

              {foreach from=$p->links key=lid item=l name=lnk}
              <li class="list-item">
                  <div class="link">
                      <div class="link-title">
                          <a href="{$l->url}">
                              {if isset($l->title) and $l->title neq ''}
                                  {$l->title|truncate:100}
                              {else}
                                  {$l->expanded_url}
                              {/if}
                          </a>
                      </div>
                      <div class="link-metadata">
                          Posted by {'@'|cat:$p->author_username|link_usernames_to_twitter} 
                          on {if $p->network eq 'twitter'}<a href="http://twitter.com/{$p->author_user_id}/statuses/{$p->post_id}">{/if}
                          {$p->adj_pub_date|date_format:"%l:%M %p - %d %b %y"}
                          {if $p->network eq 'twitter'}</a>{/if}
                      </div>
                  </div>
              </li>
              {/foreach}

          {/foreach}

          </ul>

          <button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all links</span> <i class="fa fa-chevron-down icon"></i></button>
    {/if}
