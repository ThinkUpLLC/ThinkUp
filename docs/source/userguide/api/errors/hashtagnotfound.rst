HashtagNotFoundException
========================
This exception is thrown when you have queried for a hashtag search that does not exist in ThinkUp's database.

=======
Example
=======

``api/v1/post.php?type=hashtag_posts&hashtag_name=mwc2013``::

	{
	  "error": {
		"type": "HashtagNotFoundException",
		"message": "The requested hashtag data is not available."
	  }
	}