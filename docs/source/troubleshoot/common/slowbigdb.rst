My database is so big, every migration takes forever or doesn't complete
=========================================================================

ThinkUp's database grows very quickly, especially if an installation has several active service users set up in
it. If your database is so big and unwieldy that your server slows to a crawl while running migrations--and maybe 
doesn't ever complete them--it's time to reduce the size of your database.

Transfer Active Service Users to Fresh ThinkUp Installation
-----------------------------------------------------------

The easiest way to to reduce the size of a big ThinkUp installation's database is to separate out data-heavy service
users to other databases/installations. To do so, use :doc:`the service user export tool</install/backup>` to
download each individual user's archive.

Then, set up new ThinkUp installations and import that data into them.

Advanced: Tweak Your MySQL Server's Settings to Complete Migrations on Large Tables
-----------------------------------------------------------------------------------

`ThinkUp user Brian Lenz describes how he performance-tuned MySQL for ThinkUp's
upgrade <https://groups.google.com/a/expertlabs.org/group/thinkup-dev/msg/f301c18f3e0fdb58>`_:

    The first issue was that we had the key_buffer_size 
    and myisam_sort_buffer_size set way too low.  We had them both set at
    8MB, I believe (probably just MySQL defaults).  I upped those values
    to 1GB each (on a machine with 4GB RAM).  MySQL isn't guaranteed to
    use the full 1GB of RAM for each, though.
    
    The key_buffer_size should be somewhere between 25% and 50% of the
    total RAM of the machine (assuming that is reasonable based on other
    services on the machine):
    
    http://dev.mysql.com/doc/refman/5.5/en/server-system-variables.html#sysvar_key_buffer_size
    
    I believe that the myisam_sort_buffer_size had a much bigger impact on
    the performance of the alter tables, though:
    
    http://dev.mysql.com/doc/refman/5.0/en/alter-table.html
    
    Specifically, this section:
    
    "If you use any option to ALTER TABLE other than RENAME, MySQL always
    creates a temporary table, even if the data wouldn't strictly need to
    be copied (such as when you change the name of a column). For MyISAM
    tables, you can speed up index re-creation (the slowest part of the
    alteration process) by setting the myisam_sort_buffer_size system
    variable to a high value."
    
    http://dev.mysql.com/doc/refman/5.0/en/server-system-variables.html#sysvar_myisam_sort_buffer_size
    
    Once I had those in place, I could watch the temp table as the index
    built much faster.  Once it got past creating the temp table, however,
    it went into "Repair with keycache", which is un-fast:
    
    http://stackoverflow.com/questions/1067367/how-to-avoid-repair-with-keycache
    
    Ideally, you want MySQL to use "Repair with sorting" (which uses the
    MySQL tmpdir) when doing an index rebuild for an alter.  In order to
    use "Repair with sorting", there must be enough room in the tmpdir to
    store a small factor (2-5?) times the size of the total indexes.  In
    our case, we had the tmpdir set to /tmp, which is only a 2GB
    partition.  2GB wasn't enough space for the index, so it was falling
    back to "Repair with keycache".  I switched our tmpdir to a different
    directory/partition with more space and tried again.  I was still
    running into "Repair with keycache".  This time, the issue was the
    myisam_max_sort_file_size.  It defaults to 2GB, but 2GB wasn't enough
    for our follows table.  Since we have sufficient disk, I upped this
    value up to 32GB.  Then, MySQL was able to properly use "Repair with
    sorting".  At that point, the alter table took 45 minutes vs. the
    unknown amount of time it was going to take prior to making any of
    these changes (10+ hours just to build half of the temp table before I
    killed it).