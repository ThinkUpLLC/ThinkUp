
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
			You've configured the following Twitter accounts:
			<br /><br />
			
			<ul>
			{foreach from=$owner_instances key=iid item=i}
			<li><a href="/?u={$i->twitter_username}">{$i->twitter_username}</a>  <small>delete|update</small></li>
			{/foreach}
			</ul>
			<br /><br />
			
			Add an account:<br />
			<form name="form1" method="post" action="add.php" style="padding:5px;">
			<p> 
	          Twitter Username: <input name="twitter_username" type="text" size="10"><br />
			  Twitter Password: <input name="twitter_password" type="password" size="10">
	        </p>
			<p> 
	          <input type="submit" name="Submit" value="Add">
	        </p>
	      </form>
			TODO: make sure fields aren't empty, that username field isn't already in list<br />
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