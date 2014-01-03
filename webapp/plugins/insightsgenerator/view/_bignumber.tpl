<div class="big-numbers {$class} big-numbers-per-row-1
  big-numbers-label-{$milestone_label_type}">
  <figure class="big-number insight-color-text">
    <div class="big-number-number">{if $milestone.number}{$milestone.number}{else}0{/if}</div>
    <figcaption class="big-number-label">{if $milestone_label_type eq "icon"}<i class="fa fa-{$i->instance->network}{if $i->instance->network eq 'facebook'}-square{/if}">{else}{$milestone.label}{/if}</i></figcaption>
  </figure>
</div>