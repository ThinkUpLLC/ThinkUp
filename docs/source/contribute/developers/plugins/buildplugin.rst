How to Build a ThinkUp Plugin
=============================

First, run the script which will automatically generate the required folder structure and default files with some
standard methods every plugin will need.

Navigate to ThinkUp's root directory in a terminal and type the following command:

::

    ./extras/dev/makeplugin/makeplugin NameOfYourPlugin

Where NameOfYourPlugin is the name of the plugin you want to create, e.g. Twitter, Facebook etc. Once the script is
done, navigate to the webapp/plugins/ folder, and you'll see a newly-created folder there which contains all your
new plugin's files.

If you load ThinkUp and go to the Settings area, you'll see your plugin listed there with the default plugin icon.
Activate your new plugin and click on its name to view its settings page.

From here, modify the default plugin files for your purposes. First make sure your plugin has all the settings it needs.
Then, implement whatever actions your plugin should take when it crawls data. Finally, set up the view of the data
your plugin captured.

TODO: Flesh out the step-by-step instructions for each line in the preceding paragraph.
