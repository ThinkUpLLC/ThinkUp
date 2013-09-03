<a href="?p=twitter" class="btn btn-mini"><i class="icon-chevron-left icon-muted"></i> Back to Twitter plugin</a>

{include file="_usermessage.tpl"}

<div class="plugin-info">
    <span class="pull-right">{insert name="help_link" id='twitterhashtag'}</span>
    <h2>
        <i class="icon-twitter icon-muted"></i>@{$user}'s Saved Searches 
    </h2>
</div>

{if count($hashtags) > 0 }
<table class="table">
    <tr>
        <th><h4 class="pull-left">Keyword</h4></th>
        <th><h4 class="pull-left">Count</h4></th>
        <th><i class="icon-trash icon-2x icon-muted"></i></th>
    </tr>

    {foreach from=$hashtags key=iid item=h name=foo}
    <tr>
        <td>
            <h3 class="lead"><a href="{$site_root_path}search.php?u={$user}&n=twitter&c=searches&k={$h->hashtag|urlencode}&q={$h->hashtag|urlencode}">{$h->hashtag}</a></h3>
        </td>
        <td>
            <h3 class="lead">{$h->count_cache}</h3>
        </td>
        <td class="action-button">
            <span id="delete{$h->id}">
            <form method="post" action="{$site_root_path}account/?p=twitter&u={$user}&n=twitter">
                <input type="hidden" name="instance_id" value="{$instance->id}">
                {insert name="csrf_token"}
                <input type="hidden" name="hashtag_id" value="{$h->id}">
                <input onClick="return confirm('Do you really want to delete this saved search?');"
            type="submit" name="action" class="btn btn-danger" value="Delete" /></form></span>
        </td>
    </tr>
    {/foreach}
</table>
{/if}

{if count($hashtags) == 0 }
<table class="table">
    <tr>
        <td>@{$user} has no saved searches.</td>
    </tr>
</table>
{/if}

<h3><i class="icon-tag icon-muted"></i> Add a Saved Search</h3>
  <form name="newhashtag" id="newhashtag" class="form-horizontal" method="post" 
  action="{$site_root_path}account/?p=twitter&u={$user}&n=twitter">
    <div class="control-group input-prepend">
      <label for="hashtag" class="control-label">Keyword or hashtag:</label>
      <div class="controls">
        {insert name="csrf_token"}
        <input type="hidden" name="instance_id" value="{$instance->id}">
        <input name="new_hashtag_name" type="new_hashtag_name" id="new_hashtag_name" {literal}pattern="^[\S]*$"{/literal}  required 
        data-validation-required-message="<i class='icon-exclamation-sign'></i>Please enter a keyword or hashtag."
        data-validation-pattern-message="<i class='icon-exclamation-sign'></i>Enter an individual keyword or hashtag, not a phrase.">
        <span class="help-block"></span>
        
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <input type="submit" id="hashtag-new" name="action" value="Save search" class="btn btn-primary">
      </div>
    </div>
  </form>

