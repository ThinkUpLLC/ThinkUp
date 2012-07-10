Hi! Your ThinkUp installation is no longer connected to the {$faceboook_user_name} Facebook account. That's probably
normal - Facebook makes the permissions for that connection expire every 60 days, and it can also get disconnected if
you change your password or other authorization settings.

The good news is, it's easy to fix. You'll just need to click on "Add a Facebook User" in ThinkUp's settings,
at this link:

http{if $smarty.server.HTTPS}s{/if}://{$server}{$site_root_path|escape:'urlpathinfo'}account/?p=facebook

(If you're not logged in, it'll ask you to log in to ThinkUp or Facebook, as needed.)

Thanks for using ThinkUp!
