<table class="row" style="border-spacing: 0; border-collapse: collapse; vertical-align: top; text-align: left; width: 100%; position: relative; padding: 0px;"><tr style="vertical-align: top; text-align: left; padding: 0;" align="left">
<td class="wrapper last" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; position: relative; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 14px; margin: 0; padding: 10px 0px 0px;" align="left" valign="top">
      <table class="twelve columns" style="border-spacing: 0; border-collapse: collapse; vertical-align: top; text-align: left; width: 580px; margin: 0 auto; padding: 0;"><tr style="vertical-align: top; text-align: left; padding: 0;" align="left">
<td class="center" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: center; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 14px; margin: 0; padding: 0px 0px 10px;" align="center" valign="top">
              <h6 class="center" style="text-align: center; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 1.3; word-break: normal; font-size: 20px; margin: 0; padding: 0;" align="center">Today&rsquo;s Insights</h6>
          </td>
          <td class="expander" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; visibility: hidden; width: 0px; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 14px; margin: 0; padding: 0;" align="left" valign="top"></td>
        </tr></table>
</td>
  </tr></table>
{foreach from=$insights item=insight}
{capture name=permalink assign="permalink"}{$application_url}?u={$insight->instance->network_username}&amp;n={$insight->instance->network}&amp;d={$insight->date|date_format:'%Y-%m-%d'}&amp;s={$insight->slug}{/capture}
<table class="row insight insight-pea" style="border-spacing: 0; border-collapse: collapse; vertical-align: top; text-align: left; width: 100%; position: relative; border-top-width: 5px; border-top-color: #5fac1c; border-top-style: solid; border-bottom-style: solid; border-bottom-color: #9dd767; border-bottom-width: 2px; margin-bottom: 14px; background: #9dd767; padding: 0px;" bgcolor="#9dd767"><tr style="vertical-align: top; text-align: left; padding: 0;" align="left">
<td class="wrapper last" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; position: relative; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 14px; margin: 0; padding: 10px 0px 0px;" align="left" valign="top">

      <table class="twelve columns insight-header" style="border-spacing: 0; border-collapse: collapse; vertical-align: top; text-align: left; width: 580px; margin: 0 auto; padding: 0;"><tr style="vertical-align: top; text-align: left; padding: 0;" align="left">
<td class="text-pad" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 14px; margin: 0; padding: 0px 10px 10px;" align="left" valign="top">
              <h6 style="color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; text-align: left; line-height: 1.3; word-break: normal; font-size: 20px; margin: 0; padding: 0;" align="left"><a href="{$permalink}" style="color: #fff !important; text-decoration: none; font-weight: bold; font-size: 18px;">{$insight->headline|replace:":":""}</a></h6>
          </td>
          <td class="expander" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; visibility: hidden; width: 0px; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 14px; margin: 0; padding: 0;" align="left" valign="top"></td>
        </tr></table>
{if $insight->text ne ''}
      <table class="twelve columns insight-body" style="border-spacing: 0; border-collapse: collapse; vertical-align: top; text-align: left; width: 580px; background: #fff; margin: 0 auto; padding: 0;" bgcolor="#fff"><tr style="vertical-align: top; text-align: left; padding: 0;" align="left">
<td class="text-pad" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; color: #222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 18px; font-size: 14px; margin: 0; padding: 10px;" align="left" valign="top">
              {$insight->text|strip_tags:false}
          </td>
          <td class="expander" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; visibility: hidden; width: 0px; color: #222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 18px; font-size: 14px; margin: 0; padding: 0;" align="left" valign="top"></td>
        </tr></table>
{/if}
        <table class="twelve columns" style="border-spacing: 0; border-collapse: collapse; vertical-align: top; text-align: left; width: 580px; margin: 0 auto; padding: 0;"><tr style="vertical-align: top; text-align: left; padding: 0;" align="left">
<td class="insight-footer sub-grid" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 14px; border-top-style: solid; border-top-color: #dbdbdb; border-top-width: 1px; background: #fff; margin: 0; padding: 4px 10px;" align="left" bgcolor="#fff" valign="top">
              <table style="border-spacing: 0; border-collapse: collapse; vertical-align: top; text-align: left; width: 100%; padding: 0;"><tr style="vertical-align: top; text-align: left; padding: 0;" align="left">
<td class="six sub-columns permalink" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; min-width: 0px; color: #999; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 13px; margin: 0; padding: 0;" align="left" valign="top">
                          <img src="{$site_root_path}assets/img/icons/{$insight->instance->network}-gray.png" alt="twitter" style="outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; width: auto; max-width: 100%; float: left; clear: both; display: block; margin-right: 5px;" align="left"><a href="{$permalink}" style="color: #999; text-decoration: none;">Jan 12</a>
                      </td>
                      <td class="six sub-columns date" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: right; min-width: 0px; color: #999; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 13px; margin: 0; padding: 0;" align="right" valign="top">
                          <a href="{$permalink}" style="color: #46bcff; text-decoration: none;">View this insight</a>
                      </td>
                  </tr></table>
</td>
          <td class="expander" style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; text-align: left; visibility: hidden; width: 0px; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: normal; line-height: 19px; font-size: 14px; margin: 0; padding: 0;" align="left" valign="top"></td>
        </tr></table>
</td>
  </tr></table>
{/foreach}
