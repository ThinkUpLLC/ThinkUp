Enable the Slow SQL Log
=======================

The slow SQL log is a developer tool which lets devs see what queries
are slow. You enable it in config.inc.php file (usually under /var/www/thinkup) using these two values
(explanations in the comments):

1.  **Run an editor( vi or some other editor you use)**

      vi config.inc.php    

2.  **If the following two lines are not in the file, then add all four lines to the bottom of config.inc.php, change the null to the path you want to put the logs into and save the file** 

         // Full server path to sql.log. To not log queries, set to null.
             *$THINKUP_CFG['sql_log_location']          = null;*

         // How many seconds does a query take before it gets logged as a slow query?
             *$THINKUP_CFG['slow_query_log_threshold']  = 2.0;*

---------------------------------------------------------------------

vi instructions...
""""""""""""""""""
      in vi:   

               Type  /sql_log_location

               If nothing is found then

               Type   Control-G  to go to the bottom of the file.

               Type   i     to go into insert mode.

               Highlight the four lines above and hit Control-C.

               Then in the terminal window hit Control-v and the lines should paste in the terminal window.

               Hit the Esc key   which takes you out of insert mode

               then Type     wq:
