KeywordNotFoundException
========================
This exception is thrown when you have queried for a keyword saved search that does not exist in ThinkUp's database.

=======
Example
=======

``api/v1/post.php?type=keyword_posts&keyword=mwc2013&network=twitter``::

	{
	  "error": {
		"type": "KeywordNotFoundException",
		"message": "The requested keyword data is not available."
	  }
	}