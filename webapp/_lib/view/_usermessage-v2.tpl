{if isset($field)}
    {if isset($success_msgs.$field)}
        {assign var=msg_type value="success"}
        {assign var=msg_classes value="fa-override-before fa-check-circle"}
        {if isset($success_msg_no_xss_filter)}
            {assign var=msg value=$success_msgs.$field}
        {else}
            {assign var=msg value=$success_msgs.$field|filter_xss}
        {/if}
    {/if}
    {if isset($error_msgs.$field)}
        {assign var=msg_type value="warning"}
        {assign var=msg_classes value="fa-override-before fa-exclamation-triangle"}
        {if isset($error_msg_no_xss_filter)}
            {assign var=msg value=$error_msgs.$field}
        {else}
            {assign var=msg value=$error_msgs.$field|filter_xss}
        {/if}
    {/if}
    {if isset($info_msgs.$field)}
        {assign var=msg_type value="info"}
        {if isset($info_msg_no_xss_filter)}
            {assign var=msg value=$info_msgs.$field}
        {else}
            {assign var=msg value=$info_msgs.$field|filter_xss}
        {/if}
    {/if}
{else}
    {if isset($success_msg)}
        {assign var=msg_type value="success"}
        {assign var=msg_classes value="fa-override-before fa-check-circle"}
        {if isset($success_msg_no_xss_filter)}
            {assign var=msg value=$success_msg}
        {else}
            {assign var=msg value=$success_msg|filter_xss}
        {/if}
    {/if}
    {if isset($error_msg)}
        {assign var=msg_type value="warning"}
        {assign var=msg_classes value="fa-override-before fa-exclamation-triangle"}
        {if isset($error_msg_no_xss_filter)}
            {assign var=msg value=$error_msg}
        {else}
            {assign var=msg value=$error_msg|filter_xss}
        {/if}
    {/if}
    {if isset($info_msg)}
        {assign var=msg_type value="info"}
        {if isset($info_msg_no_xss_filter)}
            {assign var=msg value=$info_msg}
        {else}
            {assign var=msg value=$info_msg|filter_xss}
        {/if}
    {/if}
{/if}

{if isset($msg) and isset($msg_type)}{literal}
<script>
var app_message = {};
app_message.msg = {/literal}"{$msg}"{literal};
app_message.type = {/literal}"{$msg_type}"{literal};
</script>
{/literal}{/if}