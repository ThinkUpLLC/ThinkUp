{include file="_install.header.tpl"}
  <div id="installer-die" class="container_24 round-all">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
       {include file="_usermessage.tpl"}

        <textarea style="width : 100%; margin-bottom : 30px;" rows="25">{$config_file_contents}</textarea><br>
        
        <form name="form1" class="input" method="post" action="index.php?step=3">
        {foreach from=$_POST key=k item=v}
           <input type="hidden" name="{$k}" value="{$v}" />
        {/foreach}
        <div class="clearfix append_20">
        <div class="grid_10 prefix_7 left">
        <input type="submit" name="Submit" class="linkbutton ui-state-default ui-priority-secondary" value="Next Step &raquo">
        </div></div></form>
      </div>
    </div>
  </div>
{include file="_install.footer.tpl"}