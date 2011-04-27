Click on the link below to activate your new {$app_title} account:

http{if $smarty.server.HTTPS}s{/if}://{$server}{$site_root_path|escape:'urlpathinfo'}session/activate.php?usr={$email}&code={$activ_code}

Thanks for registering!