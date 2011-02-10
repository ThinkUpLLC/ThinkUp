RequiredArgumentMissingException
================================
This exception is thrown when a required argument is missing from your API call. Required arguments are listed with
each API type in the `ThinkUp Post API wiki <The ThinkUp Post API>`_.

=======
Example
=======

``webapp/api/v1/post.php?type=user_posts``::

    {
        "error":{
            "type":"RequiredArgumentMissingException",
            "message":"A request of type user_posts requires a user_id or username to be specified."
        }
    }