How to build an insight for ThinkUp
===================================

First run the makeinsight script to generate the basic files required to create an insight:

./extras/dev/makeplugin/makeinsight NameOfInsight

The crawler will call your insight's generateInsight function to create and store your insight.

In this function you get a week's worth of posts and an instance object with data related to the user.

You then need to use this data to determine interesting things about the user's posts.

If you need access to more information from the database then you can query it using the standard data access objects.

Also remember that in the count_history table you have access to more fine-grained data such as per day counts for
various attributes such as follower counts on Twitter and Facebook and video statistics for YouTube.

Each insight will need to have:

1) Headline - A short string describing the generic idea about your insight. E.g.  "Nudge, Nudge"

2) Text - A string describing what interesting insight you have noticed. E.g. "You tweeted 30 times less this week."

With your insight generated you will need to insert it into the database using the insertInsight() method of the
insight data access object.

This method requires the name of the insight, the instance id, the date, the headline, the text, the filename and the
emphasis. Optionally you can insert related data as the final parameter, this could be something like the tweet the
insight is about.


The types of emphasis are:

EMPHASIS_LOW

EMPHASIS_MEDIUM

EMPHASIS_HIGH

You may also want to store some baselines such as averages to help determine if events are significant.

Baselines should be calculated and stored in the database in a insight class that will use them, baselines can be
retrieved using the getInsightBaseline() method in the InsightBaseLine and inserted using the insertInsightBaseline()
method.

To show the insight to the user you will need to edit the template in the view folder that will be called pluginname.tpl

If you want to show a standard object to the user note that there are some templates you can include in the views folder
such as _post.tpl and _users.tpl.


You also need to test your insight and the template for this was generated in the insightsgenerator/tests folder under
TestOfInsightName.php

Here you will want to insert dummy data into the database and then run your insight and check that the expected insights
were inserted into the database.
