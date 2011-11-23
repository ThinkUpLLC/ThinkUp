Click on the link below to activate your new account on {$app_title|filter_xss}:

http{if $smarty.server.HTTPS}s{/if}://{$server}{$site_root_path|escape:'urlpathinfo'}session/activate.php?usr={$email}&code={$activ_code}

Thanks for registering!