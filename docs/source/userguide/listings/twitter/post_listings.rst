Tweets
======

ThinkUp's Twitter plugin offers listings for the data related to a given tweet.

Retweets
--------

All the retweets of this tweet. ThinkUp displays both a total number of retweets, and a list of all the retweets it
has captured.

In some cases, ThinkUp may report a larger retweet total than it displays in its list of retweets. For example,
ThinkUp might report a tweet has 58 retweets, but only show 40 of them. This situation can happen when Twitter has
let ThinkUp know how many retweets there are, but ThinkUp has not captured every individual retweet.

**How ThinkUp Counts Retweets**

Twitter users retweet in one of two ways:

*    They post a "native" retweet, where their Twitter client tells Twitter the tweet is a retweet and the retweeted
     tweet is unmodified.
*    They post an "old-style" retweet, where a user or Twitter client simply copies a tweet's contents and adds the
     letters "RT" to the front of it. Sometimes the retweeter adds commentary or edits the content of the original tweet
     when they manually retweet.

Therefore, ThinkUp captures retweets in two ways:

*    ThinkUp captures the total number of native retweets for a particular tweet that the Twitter API reports. The
     Twitter API maxes out its individual tweet's retweet count at 100. 
*    Additionally, ThinkUp uses a simple algorithm to attempt to detect old-style retweets. If a tweet contains the
     letters "RT" and a username and 25 matching characters from a recent tweet by that author, ThinkUp marks it as a
     manual retweet. This method doesn't identify every retweet--for example, if the original tweet has been modified
     so 25 characters of it don't match the original--but the ones it does identify are correct.

ThinkUp displays the sum of the total number of old-style retweets it has counted as well as the number of native
retweets Twitter has reported. 

.. admonition:: Note on Retweet Counts Prior to Beta 13

    Prior to beta 13, some tweets' retweet counts got set at Twitter's max of 100. As of beta 13, retweet counts are
    a more accurate sum of old-style and native retweets.

