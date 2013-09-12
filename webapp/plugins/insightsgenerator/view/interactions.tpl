{include file=$tpl_path|cat:'_header.tpl'}

{if  !$expand}
<div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-user"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->prefix}</a></span>

<i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

{if !$expand}
<div class="collapse in" id="chart-{$i->id}">
{/if}

    <div id="interactions_{$i->id}">&nbsp;</div>
    <script type="text/javascript">
        {literal}
        (function(d3) {
          var InteractionChart = function(placeholder_id, chart_size, interaction_data) {
            var vis = d3.select('#'+placeholder_id)
            .append("svg")
            .attr("width", chart_size)
            .attr("height", chart_size)
            .style("display", "block")
            .style("margin", "0 auto");

            var min_val = getMinCount(interaction_data);
            var max_val = getMaxCount(interaction_data);

            var rad = d3.scale.linear()
            .domain([min_val, max_val])
            .range([(min_val == max_val ? 52 : 26), 78]);

            var bubble = d3.layout.pack()
            .sort(null)
            .size([chart_size, chart_size])
            .radius(rad);

            var node = vis.selectAll("g.node")
            .data(bubble.nodes(dataset(interaction_data)).filter(function(d) { return !d.children; }))
            .enter()
            .append("g")
            .attr("class", "interaction-node")
            .attr("transform", function(d) { return "translate("+d.x+","+d.y+")"; });

            var ring = d3.svg.arc()
            .innerRadius(function(d) { return (d.r - 2); })
            .outerRadius(function(d) { return (d.r); })
            .startAngle(0)
            .endAngle(2 * Math.PI);

            node.append("title")
            .text(function(d) { return "Mentioned " + d.name+" "+d.value+" "+((d.value == 1)?'once':(d.value == 2)?'twice':'times'); });

            node.append("circle")
            .attr("r", function(d) { return d.r; })
            .style("fill", "#d5f0fc");

            node.append("path")
            .attr("d", ring)
            .attr("fill", "#00aeef");

            node.append("text")
            .attr("text-anchor", "middle")
            .attr("dy", ".3em")
            .style("font-size", ".8em")
            .text(function(d) { return (d.avatar == null) ? truncate(d.name, (d.r / 4)) : ""; });

            node.append("clipPath")
            .attr("id", function(d) { return "avatar_clip_"+placeholder_id+"_"+d.index; })
            .append("circle")
            .attr("transform", "translate(24, 24)")
            .attr("r", "24");

            node.append("image")
            .attr("xlink:href", function(d) { return d.avatar; })
            .attr("width", "48")
            .attr("height", "48")
            .attr("transform", "translate(-24, -24)")
            .attr("clip-path", function(d) { return "url(#avatar_clip_"+placeholder_id+"_"+d.index+")"; });

            function getMaxCount(data) {
              var max = 0;

              for (var i = 0; i < data.length; i++) {
                max = Math.max(max, data[i].count);
              }

              return max;
            }

            function getMinCount(data) {
              var min = Number.MAX_VALUE;

              for (var i = 0; i < data.length; i++) {
                min = Math.min(min, data[i].count);
              }

              return min;
            }

            function truncate(text, max_length) {
              return text.length > max_length ? text.substring(0, (max_length - 1))+"..." : text;
            }

            function dataset(data) {
              var nodes = [];

              for (var i = 0; i < data.length; i++) {
                nodes.push({
                  index: i,
                  name: data[i].mention.substring(1),
                  value: data[i].count,
                  avatar: (data[i].user != null) ? data[i].user.avatar : null
                });
              }

              return {children: nodes};
            }
          };
          {/literal}
          var dataset = {$i->related_data|@json_encode};
          new InteractionChart("interactions_{$i->id}", (dataset.length < 5 ? 400 : 600), dataset);
          {literal}
        })(d3);
        {/literal}
    </script>

{if  !$expand}
</div>
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}