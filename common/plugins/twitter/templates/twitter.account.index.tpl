                    <b>Twitter Configuration</b>
                    <br/>
                    <br/>
                    {if $owner->is_admin}
                    <p class="info">
                        You are an administrator so you can see all accounts in the system.
                    </p>
                    <br />
                    <br />
                    {/if}

                    {if count($owner_instances) > 0 }
                    <ul>
                        {foreach from=$owner_instances key=iid item=i}
                        <li>
                            <a href="{$cfg->site_root_path}?u={$i->network_username}">{$i->network_username}</a>
                            <span id="div{$i->network_username}"><input type="submit" name="submit" class="{if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->network_username}" value="{if $i->is_public}remove from public timeline{else}include on public timeline{/if}" /></span>
							<span id="divactivate{$i->network_username}"><input type="submit" name="submit" class="{if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->network_username}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
                        </li>{/foreach}
                    </ul>{else}
                    You have no Twitter accounts configured.
                    {/if}
                    <br/>
                    <br/>
                    <b>Add a Twitter account</b>: <a href="{$oauthorize_link}">Authorize ThinkTank on Twitter&rarr;</a>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
