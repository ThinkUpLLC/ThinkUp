
{include file="_header.tpl" body_type="settings menu-open"}
{include file="_navigation.tpl"}

<div id="main">

{if isset ($smarty.get.p)}

        <div class="section" id="plugins">

            {include file="_usermessage-v2.tpl" field="account"}

            {if $body}
              {$body}
            {/if}

        </div> <!-- end #plugins -->

{/if}

{include file="_footer.tpl"}
