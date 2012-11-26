{include file=$tpl_path|cat:'_header.tpl'}

        <div class="insight-attachment-detail post">
                {include file=$tpl_path|cat:"_post.tpl" post=$i->related_data}
        </div>

{include file=$tpl_path|cat:'_footer.tpl'}