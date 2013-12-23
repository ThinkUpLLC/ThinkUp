<script type="text/javascript">
{if $user_is_admin}
var option_elements = {$option_elements_json};
var option_not_required = {$option_not_required_json};
var option_required_message = {$option_required_message_json};
var plugin_id = '{$plugin_id}';
var site_root = '{$site_root_path}';
{/if}
var is_admin = {if $user_is_admin}true;{else}false;{/if}
{assign var='required_values_set' value=true}
{foreach from=$option_elements key=option_name item=option_obj}
    {if ! $option_not_required.$option_name && ! $option_obj.value && $required_values_set}
        {assign var='required_values_set' value=false}
    {/if}
{/foreach}
var required_values_set = {if $required_values_set}true{else}false{/if};

{literal}
var advanced_visible = false;
function show_advanced() {
    if(advanced_visible) {
        $(".advanced-option-label").hide();
        $(".advanced-option-input").hide();
        $('#adv-flip-prompt').html('Show');
        advanced_visible = false;
        $("#advanced-icon").removeClass('fa-chevron-up').addClass('fa-chevron-down');
    } else {
        $(".advanced-option-label").show();
        $(".advanced-option-input").show();
        $('#adv-flip-prompt').html('Hide');
        advanced_visible = true;
        $("#advanced-icon").removeClass('fa-chevron-down').addClass('fa-chevron-up');
    }
}
{/literal}
</script>

<form id="plugin_option_form" onsubmit="return false;">

<div class="alert alert-success"  id="plugin_options_success" style="display: none;">
     <p>
       <span class="fa fa-check"></span>
       Saved!
     </p>
 </div> 


<div class="alert urgent" id="plugin_option_server_error" style="display: none;">
    <p>
        <span class="fa fa-warning"></span>
        <span id="plugin_option_server_error_message"></span>
    </p>
</div>

<div id="plugin_option_error" 
    class="alert alert-error" style="margin: 20px 0px; padding: 0.5em 0.7em; display: none;">
    <p>
        <span class="fa fa-warning"></span>
        Please complete all required fields
    </p>
</div>

{if $user_is_admin}
<!-- plugin options form elements -->
{foreach from=$option_elements key=option_name item=option_obj}
    {if $option_obj.advanced and !isset($advanced_options)}
        <p>
            <a href="#" onclick="show_advanced(); return false" class="btn btn-small">
            <i id="advanced-icon" class="fa fa-chevron-down"></i> <span id="adv-flip-prompt">Show</span>
            Advanced Options
            </a>
        </p>
        {assign var=advanced_options value=1}
    {/if}
    {if $option_headers.$option_name}
        <div id="plugin_options_{$option_obj.name}_header" style="font-weight: bold; margin: 10px 0px 0px 0px;">
            {$option_headers.$option_name}
        </div>
    {/if}

<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em; display: none;" 
    id="plugin_options_error_{$option_obj.name}">
    <p>
        <span class="fa fa-info-circle"></span>
        <span id="plugin_options_error_message_{$option_obj.name}">&nbsp;</span>
    </p>
</div>

<div style="float: left; margin-top: 10px; width: 200px;{if $option_obj.advanced}display: none;{/if}" 
{if $option_obj.advanced}class="advanced-option-label"{/if}>
    <label id="plugin_options_{$option_obj.name}_label">
    {if $option_obj.label}
        {$option_obj.label}:
    {else}
        {$option_obj.name}:
    {/if}
    {if $option_not_required.$option_name}<i>(optional)</i>{/if}
    </label>
</div>

<div style="float: left; margin: 10px 0px 0px 5px;{if $option_obj.advanced}display: none;{/if}"
{if $option_obj.advanced}class="advanced-option-input"{/if}>

    {if $option_obj.type eq 'text_element'}
        <input type="text" 
        value="{if isset($option_obj.value)}{$option_obj.value|filter_xss}{/if}"
            name="plugin_options_{$option_obj.name}" id="plugin_options_{$option_obj.name}"
            {if isset($option_obj.size)}size="{$option_obj.size}" {/if}{if ! $user_is_admin} disabled="true"{/if} />
    {/if}
    {if $option_obj.type eq 'radio_element'}
    
        <div id="plugin_options_{$option_obj.name}">
        
            {foreach from=$option_obj.values key=radio_name item=radio_value}
                <div style="float: left;">
                    <input type="radio" name="plugin_options_{$option_obj.name}" value="{$radio_value|escape:'html'}" 
                        {if ! $user_is_admin} disabled="true"{/if} 
                        {if  isset($option_obj.value) && $option_obj.value == $radio_value} checked="true"{/if} 
                        /> {$radio_name|escape:'html'} &nbsp;
                </div>
            {/foreach}

            <div style="clear: both;"></div>

        </div>
    {/if}
    {if $option_obj.type eq 'select_element'}
        <div style="float: left;">
        <select name="plugin_options_{$option_obj.name}" id="plugin_options_{$option_obj.name}" 
        {if ! $user_is_admin} disabled="true"{/if} >
        {foreach from=$option_obj.values key=select_name item=select_value}
                <option value="{$select_value|escape:'html'}"
                {if isset($option_obj.value) && $option_obj.value == $select_value}selected="true"{/if}>
                {$select_name}</option>
        {/foreach}
        </select>
        </div>
        <div style="clear: both;"></div>
    {/if}

</div>

<div style="clear: both;"></div>
{/foreach}

{/if}

<p style="margin-top: 10px;" id="plugin_option_submit_p">
{if $user_is_admin}
<input type="submit" value="Save Settings" class="btn btn-primary"/>
{/if}
</p>

<!--{if $option_not_required|@count > 0}
<p>
    <i style="font-size: 12px;">* optional</i>
</p>
{/if}-->

</form> 