
{include file="_header.tpl"}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">
	<div role="application" class="yui-g" id="tabs">

				<ul>
					<li><a href="#instances">Twitter Accounts</a></li>
					<li><a href="#templates">Templates</a></li>
					<li><a href="#settings">Settings</a></li>
					
				</ul>		


		<div class="section" id="instances">
			<b>Your Twitter accounts</b>
			<br /><br />
			{if $owner->is_admin}<p class="info">You are an administrator so you can see all accounts in the system.</p><br /><br />{/if}
			
			{if count($owner_instances) > 0 }
			<ul>
			{foreach from=$owner_instances key=iid item=i}
			<li><a href="{$cfg->site_root_path}?u={$i->twitter_username}">{$i->twitter_username}</a>  <small>[delete]</small></li>
			{/foreach}
			</ul>
			{else}
			You have no Twitter accounts configured.
			{/if}
			<br /><br />
			<b>Set up a Twitter account</b><br /><br /> <a href="{$oauthorize_link}">Authorize Twitalytic to read your Twitter data&rarr;</a>
			
		</div>

		<div class="section" id="templates">
			Template list goes here
		</div>
		
		<div class="section" id="settings">
			Other settings go here
			<ul>
				<li>Password</li>
				<li>Timezone</li>
			</ul>
		</div>



	</div>
	</div>
	</div>

	<div role="contentinfo" id="keystats" class="yui-b">

	<h2>
		
	</h2>
	<ul>
	</ul>
	</div>


	</div>


	{include file="_footer.tpl"}			