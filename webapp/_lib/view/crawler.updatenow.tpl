{include file="_header.tpl"}
{include file="_statusbar.tpl"}


<div class="container">

<div class="container_24 thinkup-canvas clearfix">
<div class="grid_22 prefix_1 alpha omega prepend_20 append_20 clearfix">
<h1>Capture Data</h1>
<div class="alert helpful" style="margin: 20px 0px; padding: 0.5em 0.7em;">
     <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
ThinkUp is capturing your social data. The log below lists the technical details of what's going on.
This could take a while. Leave this page open until it's complete.
     </p>
 </div> 
<br />
<iframe width="850" height="500" src="run.php{if $log == 'full'}?log=full{/if}" style="border:solid black 1px">
</iframe>
<br /><br />
    {include file="_usermessage.tpl"}    </div>
</div>
</div>

{include file="_footer.tpl"}