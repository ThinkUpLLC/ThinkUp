APICallTpyeNotRecognisedException
=================================
This exception is thrown when you make an API call with a type that is not recognised.

=======
Example
=======

``webapp/api/v1/post.php?type=not_a_recognised_type&post_id=18152896965124096``::

    {
        "error":{
            "type":"APICallTypeNotRecognised",
            "message":"Your API call type not_a_recognised_type was not recognised."
        }
    }