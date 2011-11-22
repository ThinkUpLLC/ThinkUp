Premature end of script headers
===============================

If you're seeing an error like "Premature end of script headers" in ThinkUp's Apache error logs, it means your web
server is timing out before ThinkUp's action (usually the crawler output page) can complete.

To fix this problem, contact your web hosting provider and ask them for two things:

1.  To increase the amount of memory available for ThinkUp's use so that the application can run faster. For example,
    Dreamhost's cheapest shared hosting package may time out when gathering data for busy accounts, but Dreamhost's
    300MB VPS server (which costs around $15/month) will not.

2.  To increase the PHP script timeout for PHP pages served for your account.

