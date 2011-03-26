class {$object_name} {literal}{{/literal}
{foreach from=$fields item=field}
    /**
     * @var {$field.PHPType}{if $field.Comment} {$field.Comment}{/if}

     */
    var ${$field.Field}{if $field.PHPType eq 'bool'} = false{/if};
{/foreach}
    public function __construct($row = false) {literal}{{/literal}
        if ($row) {literal}{{/literal}
{foreach from=$fields item=field}
{if $field.PHPType eq "bool"}
            $this->{$field.Field} = PDODAO::convertDBToBool($row['{$field.Field}']);
{else}
            $this->{$field.Field} = $row['{$field.Field}'];
{/if}
{/foreach}
        {literal}}{/literal}
    {literal}}{/literal}
{literal}}{/literal}

