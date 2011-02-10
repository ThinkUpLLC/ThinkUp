UserNotFoundException
=====================
This exception is thrown when you have queried for a user that does not exist in ThinkUp's database.

=======
Example
=======

``api/v1/post.php?type=user_posts&username=samwhat``::

    {
        "error":{
            "type":"UserNotFoundException",
            "message":"The user that you specified could not be found in our database."
        }
    }