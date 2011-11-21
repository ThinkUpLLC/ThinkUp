"SSL certificate problem, verify that the CA cert is OK"
========================================================

This error means that cURL's CA cert bundle on your server is out of date.

Recommended: Update Your Server Certificates
--------------------------------------------

To fix this problem, update cURL's SSL certificates on your server. Contact your web hosting provider with the request.
Read more at `Details on Server SSL Certificates <http://curl.haxx.se/docs/sslcerts.html>`_.

Not Recommended: A Less Secure Workaround
-----------------------------------------

Alternately, you can manually edit ThinkUp's application `set cURL to not verify HTTPS
connections <http://forum.developers.facebook.net/viewtopic.php?pid=258460>`_. Anywhere ThinkUp connects to an SSL
URL, add the following lines before the `curl_exec` call: ::

   $opts[CURLOPT_SSL_VERIFYHOST] = false;
   $opts[CURLOPT_SSL_VERIFYPEER] = false;


See also this `mailing list thread about SSL certs 
<http://groups.google.com/group/thinkupapp/browse_thread/thread/b86557dbd6747ee7>`_.

