<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="viewport" content="width=device-width" />

<!--build:include _email-insights.min.css-->
This will be replaced by the content of _email-insights.min.css.
<!--/build-->

</head>
<body>
  <table class="body">
    <tr>
      <td class="center" align="center" valign="top">
        <center>

          <table class="row top-message">
            <tr>
              <td class="center" align="center">
                <center>

                  <table class="container">
                    <tr>
                      <td class="wrapper last">

                        <table class="twelve columns" align="center">
                          <tr>
                              <td class="center">
                                  <center>{if isset($show_welcome_message) and
                                  $show_welcome_message}Welcome to ThinkUp!{else}{$header_text}{/if}</center>
                            </td>
                            <td class="expander"></td>
                          </tr>
                        </table>

                      </td>
                    </tr>
                  </table>

                </center>
              </td>
            </tr>
          </table>

          <table class="row header">
            <tr>
              <td class="center" align="center">
                <center>
                  <table class="container">
                    <tr>
                      <td class="wrapper last">
                        <table class="twelve columns" align="center">
                          <tr>
                            <td class="center">
                              <center><a href="{$application_url}" style="text-decoration:none;"><img class="center" src="https://www.thinkup.com/join/assets/img/thinkup-logo-header.png" alt="ThinkUp" width="70" height="19"></a></center>
                            </td>
                            <td class="expander"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </center>
              </td>
            </tr>
          </table>

{if isset($thinkupllc_email_tout)}
          <table class="row bottom-message">
            <tr>
              <td>
                <center>
                  <table class="container">
                    <tr>
                      <td>
                        <table class="twelve columns" align="center">
                          <tr>
                            <td>
                              <center>{$thinkupllc_email_tout}</center>
                            </td>
                            <td class="expander"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </center>
              </td>
            </tr>
          </table>
{/if}

<br>
<br>
          <table class="container">
            <tr>
              <td>
{if !(isset($show_welcome_message) and $show_welcome_message)}
<table class="row">
  <tr>
    <td class="wrapper last">
      <table class="twelve columns">
        <tr>
          <td class="center">
              <h6 class="center">{$header_text}</h6>
          </td>
          <td class="expander"></td>
        </tr>
      </table>

    </td>
  </tr>
</table>
{/if}
{if isset($show_welcome_message) and $show_welcome_message}
<table class="row insight welcome-insight">
  <tr>
    <td class="wrapper last">

      <table class="twelve columns insight-header">
        <tr>
          <td class="text-pad">
              <h6>Thanks for joining ThinkUp!</h6>
          </td>
          <td class="expander"></td>
        </tr>
      </table>

      <table class="twelve columns insight-body">
        <tr>
            <td class="text-pad">
              <p>Now you can sit back, relax, and keep doing what you&rsquo;re doing. Each day you&rsquo;ll get an email like this with your new insights from ThinkUp.</p>
              <ul>
                <li>Add more networks or update your settings from your
                  <a href="https://thinkup.com/join/user/membership.php">membership page</a>.</li>
                <li>Have questions or need help? Just reply to this email.</li>
              </ul>

              <p>Check out your insight stream at <a href="{$application_url}">{$application_url}</a>.
                This is gonna be great.</p>
            </td>
            <td class="expander"></td>
        </tr>
      </table>
        <table class="twelve columns">
        <tr>
          <td class="insight-footer">
            <img src="https://www.thinkup.com/join/assets/img/chart-landscape.png" alt="Chart graphic">
          </td>
          <td class="expander"></td>
        </tr>
      </table>

    </td>
  </tr>
</table>
{/if}
{if isset($pay_prompt_explainer) and isset($pay_prompt_headline) and isset($pay_prompt_button_label)}
<table class="row insight payment-reminder" style="border-top: 1px solid #1b5c92;border-bottom: 1px solid #1b5c92;">
  <tr>
    <td class="wrapper last">
      <table class="twelve columns insight-header">
        <tr>
          <td class="text-pad">
            <h6>{$pay_prompt_headline}</h6>
            <p style="margin-bottom: 0;">{$pay_prompt_explainer}</p>
            <br>
            <div style="text-align:center;"><!--[if mso]>
              <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{$pay_prompt_url}" style="height:60px;v-text-anchor:middle;width:160px;" arcsize="10%" stroke="f" fillcolor="#24b98f">
                <w:anchorlock/>
                <center>
              <![endif]-->
                  <a href="{$pay_prompt_url}" class="reminder-button"
            style="background-color:#24b98f;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-weight:bold;width:160px;font-size:20px;line-height:10px;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;">&nbsp;<br><span style="line-height:26px;">Join ThinkUp</span><br><span class="second-line" style="font-size:12px;line-height:14px;color:#e9f8f4; font-weight: normal;">{$pay_prompt_button_label}</span><br>&nbsp;</a>
              <!--[if mso]>
                </center>
              </v:roundrect>
            <![endif]--></div>
          </td>
          <td class="expander"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
{/if}

{foreach from=$insights item=insight}
{capture name=permalink assign="permalink"}{$application_url}?u={$insight->instance->network_username|urlencode_network_username}&amp;n={$insight->instance->network|urlencode}&amp;d={$insight->date|date_format:'%Y-%m-%d'}&amp;s={$insight->slug}{/capture}
{if $insight->instance->network eq 'twitter'}
  {capture name="share_link" assign="share_link"}
    <a href="https://twitter.com/intent/tweet?related=thinkup&amp;text={$insight->headline|strip_tags:true|strip|truncate:100}&amp;url={$permalink|html_entity_decode|escape:'url'}&amp;via=thinkup">Tweet this</a>
  {/capture}
{elseif $insight->instance->network eq 'facebook'}
  {capture name="share_link" assign="share_link"}
    <a href="https://www.facebook.com/sharer.php?u={$permalink|html_entity_decode|escape:'url'}">Share on Facebook</a>
  {/capture}
{/if}
{math equation="x % 10" x=$insight->id assign=random_color_num}
{if $i->slug eq 'posts_on_this_day_popular_flashback' | 'favorites_year_ago_flashback'}
  {assign var='color_name' value='sepia'}
  {assign var='color_dark' value='A19F8B'}
  {assign var='color' value='C0BDAF'}
  {assign var='color_light' value='efefeb'}
{elseif $random_color_num eq '0'}
  {assign var='color_name' value='pea'}
  {assign var='color_dark' value='5fac1c'}
  {assign var='color' value='9dd767'}
  {assign var='color_light' value='e7f5d9'}
{elseif $random_color_num eq '1'}
  {assign var='color_name' value='creamsicle'}
  {assign var='color_dark' value='FF8F41'}
  {assign var='color' value='FFBB4E'}
  {assign var='color_light' value='ffeed3'}
{elseif $random_color_num eq '2'}
  {assign var='color_name' value='purple'}
  {assign var='color_dark' value='8E69C2'}
  {assign var='color' value='B690E2'}
  {assign var='color_light' value='ede3f8'}
{elseif $random_color_num eq '3'}
  {assign var='color_name' value='mint'}
  {assign var='color_dark' value='24B98F'}
  {assign var='color' value='41DAB3'}
  {assign var='color_light' value='d0f6ec'}
{elseif $random_color_num eq '4'}
  {assign var='color_name' value='bubblegum'}
  {assign var='color_dark' value='B3487C'}
  {assign var='color' value='F576B5'}
  {assign var='color_light' value='fddded'}
{elseif $random_color_num eq '5'}
  {assign var='color_name' value='seabreeze'}
  {assign var='color_dark' value='198A9C'}
  {assign var='color' value='44C9D7'}
  {assign var='color_light' value='d0f2f5'}
{elseif $random_color_num eq '6'}
  {assign var='color_name' value='dijon'}
  {assign var='color_dark' value='C59301'}
  {assign var='color' value='E4BF28'}
  {assign var='color_light' value='f8efc9'}
{elseif $random_color_num eq '7'}
  {assign var='color_name' value='sandalwood'}
  {assign var='color_dark' value='D13A0A'}
  {assign var='color' value='FD8560'}
  {assign var='color_light' value='ffe1d7'}
{elseif $random_color_num eq '8'}
  {assign var='color_name' value='caramel'}
  {assign var='color_dark' value='9E5E14'}
  {assign var='color' value='DD814B'}
  {assign var='color_light' value='f7e0d2'}
{else}
  {assign var='color_name' value='salmon'}
  {assign var='color_dark' value='DA6070'}
  {assign var='color' value='FC939E'}
  {assign var='color_light' value='fee4e7'}
{/if}

<table class="row insight insight-{$color_name}">
  <tr>
    <td class="wrapper last">

      <table class="twelve columns insight-header">
        <tr>
          <td class="text-pad">
              <h6><a href="{$permalink}">{$insight->headline}</a></h6>
          </td>
          <td class="expander"></td>
        </tr>
        </table>
{if isset($insight->related_data.hero_image.url) && isset($insight->related_data.hero_image.alt_text) && isset($insight->related_data.hero_image.credit)}
      <table class="twelve columns insight-image">
        <tr>
          <td>
              <img src="{$insight->related_data.hero_image.url}" alt="{$insight->related_data.hero_image.alt_text}" class="center">
              <small class="text-pad">{$insight->related_data.hero_image.credit}</small>
          </td>
          <td class="expander"></td>
        </tr>
        </table>
{/if}

{if $insight->text ne '' or isset($insight->related_data.posts) or isset($insight->related_data.people)
or isset($insight->related_data.changes)}
    <table class="twelve columns insight-body">
        {if $insight->text ne ''}
        <tr>
            <td class="text-pad">
                {$insight->text|strip_tags:false}
            </td>
            <td class="expander"></td>
        </tr>
        {/if}
        {if isset($insight->related_data.people)}
        {foreach from=$insight->related_data.people key=uid item=user}
        {if isset($user->network) and isset($user->user_id) and isset($user->avatar)}
        <tr>
            <td class="sub-grid object user text-pad">
                <table>
                    <tr>
                        <td class="two sub-columns center">
                            <a href="{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={elseif $user->network eq 'facebook'}https://facebook.com/{/if}{$user->user_id}" title="{$user->user_fullname}"><img src="{$user->avatar|use_https}" alt="{$user->user_fullname}" width="60" height="60" class="img-circle"></a>
                        </td>
                        <td class="ten sub-columns">
                            <div class="user-name"><a href="{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={elseif $user->network eq 'facebook'}https://facebook.com/{/if}{$user->user_id}" title="{$user->user_fullname}">{$user->full_name}</a></div>
                            <div class="user-text">
                                <p>{if $user->network eq 'twitter'}
                                    {$user->follower_count|number_format} followers
                                {else}
                                    {if isset($user->other.total_likes)}
                                    {$user->other.total_likes|number_format} likes
                                    {/if}
                                {/if}</p>
                                {if $user->description neq ''}
                                    <p>{$user->description}</p>
                                {/if}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="expander"></td>
        </tr>
        {/if}
        {/foreach}
        {/if}
        {if isset($insight->related_data.changes)}
        {foreach from=$insight->related_data.changes item=change name=changed }
        {assign var='user' value=$change.user}
        {insert name="string_diff" from_text=$change.before to_text=$change.after assign="bio_diff" is_email=true}
        {if isset($user->network) and isset($user->user_id) and isset($user->avatar)}
        <tr>
            <td class="sub-grid object user text-pad">
                <table>
                    <tr>
                        <td class="two sub-columns center">
                            <a href="{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={elseif $user->network eq 'facebook'}https://facebook.com/{/if}{$user->user_id}" title="{$user->user_fullname}"><img src="{$user->avatar|use_https}" alt="{$user->user_fullname}" width="60" height="60" class="img-circle"></a>
                        </td>
                        <td class="ten sub-columns">
                            <div class="user-name"><a href="{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={elseif $user->network eq 'facebook'}https://facebook.com/{/if}{$user->user_id}" title="{$user->user_fullname}">{$user->full_name}</a></div>
                            <div class="user-text">
                                <p>{if $user->network eq 'twitter'}
                                    {$user->follower_count|number_format} followers
                                {else}
                                    {if isset($user->other.total_likes)}
                                    {$user->other.total_likes|number_format} likes
                                    {/if}
                                {/if}</p>
                                {if $bio_diff neq ''}
                                    <p class="text-diff">{$bio_diff}</p>
                                {/if}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="expander"></td>
        </tr>
        {/if}
        {/foreach}
        {/if}
        {if isset($insight->related_data.posts)}
        {foreach from=$insight->related_data.posts key=uid item=post name=bar}
        {if isset($post->network) and isset($post->author_user_id) and isset($post->author_avatar)}
        <tr>
            <td class="sub-grid object tweet text-pad">
                <table>
                    <tr>
                        <td class="two sub-columns center">
                            <a href="{if $post->network eq 'twitter'}https://twitter.com/intent/user?user_id={elseif $post->network eq 'facebook'}https://facebook.com/{/if}{$post->author_user_id}" title="{$post->author_username}"><img src="{$post->author_avatar|use_https}" alt="{$post->author_username}" width="60" height="60" class="img-circle"></a>
                        </td>
                        <td class="ten sub-columns last">
                            <div class="byline"><a href="{if $post->network eq 'twitter'}https://twitter.com/intent/user?user_id={elseif $post->network eq 'facebook'}https://facebook.com/{/if}{$post->author_user_id}" title="{$post->author_username}"><strong>{$post->author_fullname}</strong> {if $post->network eq 'twitter'}<span class="username">@{$post->author_username}</span>{/if}</a></div>
                            <div class="tweet-body">{$post->post_text|filter_xss|link_urls|link_usernames_to_twitter|color_html_email_links}</div>
                            <div class="tweet-actions">
                              <a href="{if $post->network eq 'twitter'}https://twitter.com/{$post->author_username}/status/{/if}{if $post->network eq 'facebook'}https://www.facebook.com/{$post->author_user_id}/posts/{/if}{$post->post_id}"
                                class="tweet-action tweet-action-permalink">{$post->pub_date|date_format:'%b %e, %Y'}</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="expander"></td>
        </tr>
        {/if}

        {if isset($post->links[0]->image_src) and $post->links[0]->image_src neq ""}
        <tr class="insight-image-inline">
          <td class="sub-grid">
            <table class="twelve sub-columns last">
              <tr>
                <td>
                    <a href="{$post->links[0]->url}"><img src="{$post->links[0]->image_src}" alt="{$post->author_fullname}" class="center"></a>
                </td>
                <td class="expander"></td>
              </tr>
            </table>
          </td>
        </tr>
        {/if}
        {/foreach}
        {/if}

        {if isset($insight->related_data.posts) or isset($insight->related_data.people)}
        <tr><td>&nbsp;</td></tr>
        {/if}
    </table>
{/if}
        <table class="twelve columns">
        <tr>
          <td class="insight-footer sub-grid">
              <table>
                  <tr>
                      <td class="six sub-columns permalink">
                          <img src="https://www.thinkup.com/join/assets/img/icons/{$insight->instance->network}-gray.png" alt="{$insight->instance->network}"><a href="{$permalink}">{$insight->date|date_format:"%b %d"}</a>
                      </td>
                      <td class="six sub-columns date">
                        {$share_link}
                      </td>
                  </tr>
              </table>
          </td>
          <td class="expander"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
{/foreach}

<table class="row email-settings">
  <tr>
    <td class="wrapper last">
      <table class="twelve columns">
        <tr>
          <td class="center">
              <small class="center">You receive new insights from ThinkUp once a {if $weekly_or_daily eq 'Daily'}day{else}week{/if}.<br>To get insights once a {if $weekly_or_daily eq "Daily"}week{else}day{/if} or unsubscribe altogether, <a href="{$unsub_url}">change your email settings.</a><br>If you reply to this email, an actual human will read it.</small>
          </td>
          <td class="expander"></td>
        </tr>
      </table>

    </td>
  </tr>
</table>


              <!-- container end below -->
              </td>
            </tr>
          </table>

          <table class="row footer">
            <tr>
              <td class="center" align="center">
                <center>

                  <table class="container">
                    <tr>
                      <td class="wrapper last">

                        <table class="twelve columns">
                          <tr>
                          <td class="footer sub-grid">
                              <table>
                                  <tr>
                                      <td class="three sub-columns privacy">
                                    &copy;2014 ThinkUp, LLC<br>
                                        <a class="privacy" href="https://github.com/ThinkUpLLC/policy">Privacy and stuff</a>
                                      </td>
                                      <td class="six sub-columns links">
                                          <a href="https://twitter.com/thinkup"><img src="https://www.thinkup.com/join/assets/img/icons/twitter-blue.png" width="20" height="20"/></a><a href="https://facebook.com/thinkupapp"><img src="https://www.thinkup.com/join/assets/img/icons/facebook-blue.png" width="20" height="20"/></a><a href="https://plus.google.com/109397312975756759279"><img src="https://www.thinkup.com/join/assets/img/icons/google-plus-blue.png" width="20" height="20"/></a><a href="https://github.com/ginatrapani/ThinkUp"><img src="https://www.thinkup.com/join/assets/img/icons/github-blue.png" width="20" height="20"/></a>
                                      </td>
                                      <td class="three sub-columns motto">
                                          It is nice to be nice.
                                      </td>
                                  </tr>
                              </table>
                          </td>
                            <td class="expander"></td>
                          </tr>
                        </table>

                      </td>
                    </tr>
                  </table>

                </center>
              </td>
            </tr>
          </table>


        </center>
      </td>
    </tr>
  </table>
</body>
</html>
