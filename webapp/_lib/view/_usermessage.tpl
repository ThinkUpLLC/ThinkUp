{if $field}
    {if $success_msgs.$field}
     <div class="alert helpful">
         <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {$success_msgs.$field}
         </p>
     </div>    
    {/if}
    {if $error_msgs.$field}
     <div class="alert urgent">
         <p>
           <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {$error_msgs.$field}
         </p>
    </div>
    {/if}
    {if $info_msgs.$field}
    {if $success_msgs.$field OR $error_msgs.$field}<br />{/if}
    <div class="alert stats" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
        <p>
             <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
             {$info_msgs.$field}
        </p>
    </div>
    {/if}
{else}
    {if $success_msg}
     <div class="alert helpful" style="">
         <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {$success_msg}
         </p>
     </div>        
    {/if}
    {if $error_msg}
     <div class="alert urgent" style="">
         <p>
           <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {$error_msg}
         </p>
    </div>
    {/if}
    {if $info_msg}
    {if $success_msg OR $error_msg}<br />{/if}
    <div class="alert helpful" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
        <p>
             <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
             {$info_msg}
        </p>
    </div>
    {/if}
{/if}