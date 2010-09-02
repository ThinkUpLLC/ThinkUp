    {if $successmsg}
    <p class="success">
        {$successmsg}
    </p>    
    {/if}
    {if $errormsg}
     <div class="ui-state-error ui-corner-all" style="margin: 20px 0px; padding: 0.5em 0.7em;">
         <p>
           <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {$errormsg}
         </p>
    </div>
    {/if}
    {if $infomsg}
    {if $successmsg OR $errormsg}<br />{/if}
    <div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
        <p>
             <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
             {$infomsg}
        </p>
    </div>
    {/if}
