Instances hashtags by date
==========================
Get number of posts saved with a hashtag for every instance from a social network between two dates.

**API call type slug:** ``instances_hashtags``

**Example Usage:** ``api/v1/post.php?type=instances_hashtags&start_date=2013-09-29 00:00:00&end_date=2013-09-29 23:59:59``

==================
Optional Arguments
==================

* **start_date**

    Specify the start date which you want to get the number of posts saved with a hashtag.  
    Sample: '2013-09-29 00:00:00'. Default **today** at 00:00:00.

* **end_date**

    Specify the end date which you want to get the number of posts saved with a hashtag. 
    Sample: '2013-09-29 23:59:59'. Default **today** at 23:59:59.

* **network**

    The network to use in the call. Default **twitter**.

* **limit**

    The number of top instances posts saved with a hashtag to display from this API call. Default **no limit**. 
    If you supply something that is not a valid number, this argument will revert to its default.

==============
Example output
==============


``api/v1/post.php?type=instances_posts&start_date=2013-09-29 00:00:00&end_date=2013-09-29 23:59:59``

[
	{
	    "network_user_id": 1513446199,
	    "network_username": "happyTV3",
	    "crawler_last_run": "2013-10-21 01:04:46",
	    "network": "twitter",
	    "hashtag": "#ohdTV3",
	    "count_cache": 6828,
	    "period_number_posts": 2510
	}
]
