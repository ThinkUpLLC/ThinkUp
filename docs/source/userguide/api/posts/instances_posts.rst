Instances posts by date
=======================
Get number of posts done for every instance from a social network between two dates.

**API call type slug:** ``instances_posts``

**Example Usage:** ``api/v1/post.php?type=instances_posts&start_date=2013-09-29 00:00:00&end_date=2013-09-29 23:59:59``

==================
Optional Arguments
==================

* **start_date**

    Specify the start date which you want to get the number of posts done. 
    Sample: '2013-09-29 00:00:00'. Default **today** at 00:00:00.

* **end_date**

    Specify the end date which you want to get the number of posts done. 
    Sample: '2013-09-29 23:59:59'. Default **today** at 23:59:59.

* **network**

    The network to use in the call. Default **twitter**.

* **limit**

    The number of top instances posts to display from this API call. Default **no limit**. 
    If you supply something that is not a valid number, this argument will revert to its default.

==============
Example output
==============


``api/v1/post.php?type=instances_posts&start_date=2013-09-29 00:00:00&end_date=2013-09-29 23:59:59``

[
	{
    	"network_user_id": 14866960,
	    "network_username": "esport3",
	    "crawler_last_run": "2013-10-2108: 01: 29",
	    "total_posts_by_owner": 55199,
	    "total_posts_in_system": 6243,
	    "posts_per_day": "25.00",
	    "posts_per_week": "25.00",
	    "percentage_replies": "3.25",
	    "percentage_links": "73.47",
	    "network": "twitter",
	    "posts": 85
	}
]
