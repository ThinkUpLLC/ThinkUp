<div class="big-numbers {$class} big-numbers-per-row-{$milestones.per_row}
big-numbers-label-{$milestones.label_type} insight-color-text">
{foreach from=$milestones.items item=milestone name=milestones}
{if $smarty.foreach.milestones.first}
  <div class="big-numbers-row">
{elseif $smarty.foreach.milestones.index % $milestones.per_row == 0}
  </div><!-- end big-numbers-row -->
  <div class="big-numbers-row">
{/if}
    <figure class="big-number">
      <div class="big-number-number">{if $milestone.number}{$milestone.number}{else}0{/if}</div>
      <figcaption class="big-number-label">{if $milestones.label_type eq "icon"}<i class="fa fa-{$i->instance->network}{if $i->instance->network eq 'facebook'}-square{/if}">{else}{$milestone.label}{/if}</i></figcaption>
    </figure>
{if $smarty.foreach.milestones.last}
  </div><!-- end big-numbers-row -->
{/if}
{/foreach}
</div>