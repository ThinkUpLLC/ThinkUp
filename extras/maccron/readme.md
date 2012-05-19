# Mac OS X cron script

This is a simple configuration file for setting up crons with the [launchd deamon in Mac OS X](https://developer.apple.com/library/mac/#documentation/Darwin/Reference/ManPages/man8/launchd.8.html). The config as currently set up will log directly to the system log.

## Installation
- Edit the com.thinkupapp.plst in this directory according to commenting
- Copy com.thinkupapp.plst into /System/Library/LaunchAgents/ to make the job load on system boot
- Run "launchctl load /system/Library/LaunchAgents/com.thinkupapp.plst" to load the job

## Limitations
- May not be able to run properly as arbitrary user.
