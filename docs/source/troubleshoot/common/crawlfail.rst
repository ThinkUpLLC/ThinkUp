Something's going wrong during crawls, but the log on the "Capture Data" page doesn't give enough information
=============================================================================================================

To closely troubleshoot crawler activity, enable the crawler's verbose developer log,
which provides detailed information like memory usage, class and method names, and line numbers.

To do that, log in as an administrator. In Settings > Application check the "Developer log" checkbox.

If you know that the problem is confined to a specific crawler, skip all the other service users crawls by deactivating
all the plugins except the one you're having trouble with.

Then, click on the "Capture Data" link. The log output will contain more detailed debugging information. :doc:`Send
details to the community to troubleshoot </contact>`.

If you're automating ThinkUp's data capture, here's how to :doc:`capture the output of the verbose developer
log </troubleshoot/common/advanced/crawlerlog>` for troubleshooting after the fact.