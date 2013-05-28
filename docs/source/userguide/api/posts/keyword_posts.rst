Keyword Posts
=============
Gets posts from saved search that contains a keyword.

**API call type slug:** ``keyword_posts``

**Example Keyword Usage:** ``api/v1/post.php?type=keyword_posts&keyword=mwc2013&network=twitter``
**Example Hashtag Usage:** ``api/v1/post.php?type=keyword_posts&keyword=%23conselldeguerra&network=twitter``

==================
Required arguments
==================

* **keyword** and **network**

    The keyword that posts contain to retrieve and save. 
    The network where posts have been searched and saved.

==================
Optional Arguments
==================

* **order_by**

    The column to order the results by. Defaults to chronological order ("date").

* **direction**

    The direction to order the results in. Can be either DESC or ASC. Defaults to DESC.

* **count**

    The number of results to display from this API call. Defaults to 20. If you supply something that is
    not a valid number, this argument will revert to its default value of 20. For performance reasons, the maximum
    number of posts the ThinkUp API returns per call is 200.

* **page**

    The page of results to display for this API call. Defaults to 1. When you get to the end of the pages of results,
    API calls will just return empty JSON. No error is generated.

* **include_entities**

    Whether or not to include `Tweet Entities <http://dev.twitter.com/pages/tweet_entities>`_ in the output. Defaults
    to false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

* **trim_user**

    If set to true, this flag strips the user part of the output to just the user's ID and nothing else. Defaults to
    false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

==============
Example output
==============


``api/v1/post.php?type=keyword_posts&keyword=VIH&network=twitter&count=2``
(this is getting the first 2 posts for saved search of the keyword #VIH :))::

	[
		{
			"id":308554341550272512,
			"author_follower_count":null,
			"source":"web",
			"location":"BDN - BCN - L'H [Catalunya]",
			"place":null,
			"place_id":null,
			"geo":null,
			"in_reply_to_user_id":null,
			"in_reply_to_post_id":null,
			"is_reply_by_friend":false,
			"is_retweet_by_friend":false,
			"reply_retweet_distance":0,
			"in_rt_of_user_id":null,
			"retweet_count_api":0,
			"favlike_count_cache":0,
			"links":[
				{
					"id":10004,
					"url":"http://t.co/kwq1vDIaPB",
					"expanded_url":"",
					"title":"",
					"description":"",
					"image_src":"",
					"caption":"",
					"post_key":19287,
					"error":"",
					"container_post":null,
					"other":[
						
					]
				}
			],
			"favorited":false,
			"all_retweets":0,
			"text":"M\u00e9dicos de EEUU curan por primera vez a un beb\u00e9 con #VIH: http://t.co/kwq1vDIaPB /via @elmundosalud #AIDS",
			"created_at":false,
			"annotations":null,
			"truncated":false,
			"protected":false,
			"thinkup":{
				"retweet_count_cache":0,
				"retweet_count_api":0,
				"reply_count_cache":0,
				"old_retweet_count_cache":0,
				"is_geo_encoded":0
			},
			"user":{
				"id":44361937,
				"location":"BDN - BCN - L'H [Catalunya]",
				"description":"T\u00e9c. superior en Doc. Sanitaria. 3o Enfermer\u00eda #UB. @Alyssa_Milano & @officialtulisa \u2665 ",
				"url":"",
				"friend_count":704,
				"last_updated":"2013-03-04 13:28:28",
				"followers_count":744,
				"profile_image_url":"http://a0.twimg.com/profile_images/3179891390/3a82fbfca564c86a27ff84676e3a52a4_normal.jpeg",
				"name":"Isaac",
				"screen_name":"sack_am",
				"statuses_count":33452,
				"created_at":false,
				"avg_tweets_per_day":24.40,
				"thinkup":{
					"last_post":"0000-00-00 00:00:00",
					"last_post_id":"",
					"found_in":""
				}
			}
		},
		{
			"id":308554204719493120,
			"author_follower_count":null,
			"source":"<a href=\"http://itunes.apple.com/us/app/bbc-news/id364147881?mt=8&uo=4\" rel=\"nofollow\">BBC News on iOS</a>",
			"location":"En la red",
			"place":null,
			"place_id":null,
			"geo":null,
			"in_reply_to_user_id":null,
			"in_reply_to_post_id":null,
			"is_reply_by_friend":false,
			"is_retweet_by_friend":false,
			"reply_retweet_distance":0,
			"in_rt_of_user_id":null,
			"retweet_count_api":0,
			"favlike_count_cache":0,
			"links":[
				{
					"id":10005,
					"url":"http://t.co/mvVdcXf6TC",
					"expanded_url":"",
					"title":"",
					"description":"",
					"image_src":"",
					"caption":"",
					"post_key":19288,
					"error":"",
					"container_post":null,
					"other":[
						
					]
				}
			],
			"favorited":false,
			"all_retweets":0,
			"text":"Curan a beb\u00e9 infectada por virus del VIH con tratamiento normal de f\u00e1rmacos #VIH #salud  http://t.co/mvVdcXf6TC",
			"created_at":false,
			"annotations":null,
			"truncated":false,
			"protected":false,
			"thinkup":{
				"retweet_count_cache":0,
				"retweet_count_api":0,
				"reply_count_cache":0,
				"old_retweet_count_cache":0,
				"is_geo_encoded":0
			},
			"user":{
				"id":86603388,
				"location":"En la red",
				"description":"Periodista - Locutor | Curioso y siempre aprendiz | Entusiasta del Social Media y la Com Digital | MSc en Ciencia Pol\u00edtica #Bostero @ElUniversal",
				"url":"http://t.co/8f9msKRYxn",
				"friend_count":1902,
				"last_updated":"2013-03-04 13:28:28",
				"followers_count":3513,
				"profile_image_url":"http://a0.twimg.com/profile_images/3071772492/cb292667fd39fe188a1c0b560f6cfc25_normal.jpeg",
				"name":"Gustavo  M\u00e9ndez",
				"screen_name":"mendeztavo",
				"statuses_count":17637,
				"created_at":false,
				"avg_tweets_per_day":14.44,
				"thinkup":{
					"last_post":"0000-00-00 00:00:00",
					"last_post_id":"",
					"found_in":""
				}
			}
		}
	]