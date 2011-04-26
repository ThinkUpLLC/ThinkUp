APICallTpyeNotRecognizedException
=================================
This exception is thrown when you make an API call with a type that is not recognized.

=======
Example
=======

``api/v1/post.php?type=not_a_recognized_type&post_id=18152896965124096``::

    {
        "error":{
            "type":"APICallTypeNotRecognized",
            "message":"Your API call type not_a_recognized_type was not recognized."
        }
    }