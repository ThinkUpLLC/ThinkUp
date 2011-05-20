"SSL certificate problem, verify that the CA cert is OK"
========================================================

This error means that cURL's CA cert bundle on your server is out of date.

Recommended Solution
--------------------

Update cURL's SSL certificates on your server. Read more on how to do this at
`Details on Server SSL Certificates <http://curl.haxx.se/docs/sslcerts.html>`_.

Less Secure Workaround
----------------------

Alternately, you can `set cURL to not verify HTTPS
connections <http://forum.developers.facebook.net/viewtopic.php?pid=258460>`_. To do this in the Facebook library
that ThinkUp uses, go to line 600 in ``webapp/plugins/facebook/extlib/facebook/facebook.php`` and insert the following
lines: ::

   $opts[CURLOPT_SSL_VERIFYHOST] = false;
   $opts[CURLOPT_SSL_VERIFYPEER] = false;


See also the `mailing list thread about SSL certs 
<http://groups.google.com/group/thinkupapp/browse_thread/thread/b86557dbd6747ee7>`_.

