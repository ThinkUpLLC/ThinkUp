{if $field}
    {if $success_msgs.$field}
    <p class="success">
        {$success_msgs.$field}
    </p>    
    {/if}
    {if $error_msgs.$field}
     <div class="ui-state-error ui-corner-all" style="margin: 20px 0px; padding: 0.5em 0.7em;">
         <p>
           <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {$error_msgs.$field}
         </p>
    </div>
    {/if}
    {if $info_msgs.$field}
    {if $success_msgs.$field OR $error_msgs.$field}<br />{/if}
    <div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
        <p>
             <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
             {$info_msgs.$field}
        </p>
    </div>
    {/if}
{else}
    {if $success_msg}
    <p class="success">
        {$success_msg}
    </p>    
    {/if}
    {if $error_msg}
     <div class="ui-state-error ui-corner-all" style="margin: 20px 0px; padding: 0.5em 0.7em;">
         <p>
           <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {$error_msg}
         </p>
    </div>
    {/if}
    {if $info_msg}
    {if $success_msg OR $error_msg}<br />{/if}
    <div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
        <p>
             <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
             {$info_msg}
        </p>
    </div>
    {/if}
{/if}