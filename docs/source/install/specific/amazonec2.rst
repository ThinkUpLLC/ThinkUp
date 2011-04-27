Installing ThinkUp on Amazon EC2
================================

Installing ThinkUp on Amazon's Elastic Computing Cloud (EC2) is one
of the cheapest and easiest ways to get started with ThinkUp. The
entire thing can be done in a web browser, start to finish, in
about ten minutes. No downloads, installations, or commandline
knowledge required. Plus, Amazon's Micro instances (their smallest
available server) are `free for a
year <http://aws.amazon.com/free/>`_ for new Amazon Web Services
users, and about $15/month for everyone else.

Here's a step-by-step walkthrough, using Amazon's Web Services
Dashboard to create and configure a new instance running the latest
version of Ubuntu and ThinkUp.

**1.** Sign up for an EC2 account. New users should `start
here <http://aws.amazon.com/free/>`_ to get the first year free,
existing users can `sign up for EC2 <http://aws.amazon.com/ec2/>`_
here. A valid credit card is required for registration, even with
the free trial.

**2.** When you're finished, sign in to the `EC2
Dashboard <https://console.aws.amazon.com/ec2/home>`_. See the text
underneath the big "Launch Instance" button that says which region
your instance will launch in? Remember that for Step 4.

.. image :: https://img.skitch.com/20110104-8qtwafd4fcwhyujewpqdnc6pim.jpg

**3.** Click the big "Launch Instance" button, and click the
"Community AMIs" tab to see all the user-submitted disk images. It
looks like this:

.. image :: https://img.skitch.com/20110104-c4ge6xwniittfws7ctf6719xsm.jpg

**4.** We want to install the latest official 64-bit Ubuntu 10.10
image in your area. Go to `this
page <http://uec-images.ubuntu.com/releases/10.10/release/>`_
maintained by Ubuntu, and look up the AMI ID for the 64-bit EBS
image in your area (as shown in step 2). For example, my instances
are in us-east, so my 64-bit EBS ID is "ami-cef405a7". Now, search
for that ID in the Community AMIs tab, and select the image that's
returned. Your search form should look something like this:

.. image :: https://img.skitch.com/20110104-xsgscjrhefku22yqnis2iebjd8.jpg

**5.** On the Instance Details page, use the following settings.
Number of Instances => 1, Availability Zone => No Preference,
Instance Type => "Micro." Choose "Launch Instances" and click
Continue.

.. image :: https://img.skitch.com/20110104-grjbybta71je4wk824kc1wredh.jpg

**6.** On the next screen, under Advanced Instance Options, you can
leave all the existing defaults. In "User Data," we're going to
supply Amazon with all the commands to run to get ThinkUp (and all
its prerequisites) up-and-running after the instance is created.
Copy the contents of `this install
script <https://gist.github.com/764396>`_ into the "User Data"
field.

**Important:** Be sure to replace both "NEWPASSWORDHERE"
placeholders at the end of the script with the root password you'd
like to set for MySQL, and *remember that password!* (You'll need
it to configure ThinkUp.) When you're done, it should look
something like this:

.. image :: https://img.skitch.com/20110104-jwr7qgmxbmeeiatb4ckmku1c7y.jpg

**7.** On the next screen, skip defining tags for your instance by
clicking "Continue." Create and download your keypair. Save this
for later, it will be required if you ever need to SSH to your
server. When you're finished, click "Continue."

**8.** On the next screen, you can configure your firewall. Click
"Create A New Security Group," and name it "web" (or whatever you
like) with the description "web." Using the "Select..." box, add
rules for SSH, HTTP, and HTTPS. When you're done, it should roughly
look like this.

.. image :: https://img.skitch.com/20110104-c5uw8fpt5h9wkmny1xmr5hpe37.jpg

**9.** Review all your changes, and click the "Launch" button! You
can now watch as your instance spins up. When the status goes from
"Pending" to "Running," you're alive! (It generally takes 1-3
minutes for PHP, MySQL, and other prerequisites to install, behind
the scenes.)

**10.** Click on your new instance, and scroll down in the bottom
frame to "Public DNS." That hostname is your new webserver! (It
should look something like
"ec2-184-73-51-39.compute-1.amazonaws.com".) You can now configure
your ThinkUp install at
http://yourhostnamehere.amazonaws.com/thinkup/install/ **Note:**
When configuring ThinkUp, your database username is "root" and the
password is whatever you set in Step 6.

**11.** Advanced user? To SSH to your new server, set the
permission of the private key you saved in Step 7 to be readable by
you alone (e.g. ``chmod 600 your-downloaded-pem-key.pem``). You can
log into your EC2 instance as the 'ubuntu' user:
``ssh -v -i your-downloaded-pem-key.pem ubuntu@hostname.compute-1.amazonaws.com``

Good luck, and have fun! (Special thanks to Sophia for the initial
`Ubuntu install
script <http://sproke.blogspot.com/2010/12/install-script-for-thinkup-07-on-ubuntu.html>`_.)

