The ThinkUp API
===============

===============
What is an API?
===============

In the simplest of terms, ThinkUp's API is a mechanism that allows other people to get access to the public data in your
ThinkUp installation. Currently supporting only posts, people can query your ThinkUp installation for data about the
publicly available posts and the API returns that information in a machine readable format called JSON.

Examples of what this looks like are available on every section of the Post API documentation.

=========================================
What can people do with this information?
=========================================

All information revealed by the ThinkUp API is public. There's currently no way for people to get at your private data.
That being said, people can do all manner of cool things with your information. The term for using APIs to make
interesting new applications is "mashup" and Andy Baio was the first to demonstrate the ThinkUp API being used in a
mashup, `click here to see it <https://github.com/waxpancake/Twitter-Time-Capsule>`_.

Contents:

.. toctree::
   :maxdepth: 3
   
   posts/index
