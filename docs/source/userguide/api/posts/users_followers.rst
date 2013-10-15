Users Followers
===============
Gets number of followers for every user from a social network in a date.

**API call type slug:** ``followers``

**Example Usage:** ``api/v1/post.php?type=followers&date=2013-10-11&network=twitter&limit=1``


==================
Optional Arguments
==================

* **date**

    Specify the date which you want to get the number of followers. Default **today**.
    Date format must be year-month-day, sample: 2013-10-11 

* **network**

    The network to use in the call. Default **twitter**.

* **limit**

    The number of top followers to display from this API call. Default **no limit**. 
    If you supply something that is not a valid number, this argument will revert to its default.


==============
Example output
==============


``api/v1/post.php?type=followers&date=2013-10-11&network=twitter&limit=1``
(this is getting twitter user  with maximum number of followers at one date )::

[
    {
        "id": 6681,
        "user_id": 108541550,
        "user_name": "APMTV3",
        "full_name": "ALGUNA PREGUNTA",
        "avatar": "http://a0.twimg.com/profile_images/3161679480/a3f476f2fe5f6670a4a5ad4c325c23c3_normal.jpeg",
        "location": "Barcelona",
        "description": "El twitter oficial del programa Alguna Pregunta Més de TV3. Has trobat una pífia? Ajuda'ns! http://www.tv3.cat/apm/html/pifiesAPM.html",
        "url": "http://www.tv3.cat/apm",
        "is_verified": 0,
        "is_protected": 0,
        "follower_count": 184343,
        "friend_count": 32211,
        "post_count": 6102,
        "last_updated": "2013-10-14 07:03:15",
        "found_in": "Friends",
        "last_post": "0000-00-00 00:00:00",
        "joined": "2010-01-26 06:32:05",
        "last_post_id": null,
        "network": "twitter",
        "favorites_count": 53,
        "date": "2013-10-11",
        "followers": 184139
    }
]