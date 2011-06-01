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