ThinkUp's Model View Controller Implementation
==============================================

ThinkUp does not use an MVC framework, but it does employ the
`Model-View-Controller design
pattern <http://en.wikipedia.org/wiki/Model–view–controller>`_. The
Wikipedia page makes the idea sound confusing to new comers so here's a
basic run down of what the MVC design pattern is and how ThinkUp uses
it.

The idea behind the MVC design pattern is to separate programming logic
from the presentation of an application. Ever embedded large amounts of
HTML in strings concatenated with lots of variables? The MVC pattern is
the solution to that problem.

Under the MVC design pattern, an application will have 3 specific types
of component: models, views and controllers. **Models** are designed to
take data and process it, **views** are designed to take processed data
and display it, and **controllers** are designed to decide what data
gets processed by which model and which view is used to display it.

In our case:

-  **Model** - ThinkUp's model objects live in
   /thinkup/webapp/\_lib/model/. Model object filenames start with the
   prefix class.. For example, the Post object is located in the
   class.Post.php file.

-  **View** - ThinkUp's views are Smarty templates, files with the .tpl
   extension, located in the /thinkup/webapp/\_lib/view/ directory. As a
   general rule, HTML markup should never appear in a PHP file, only in
   a template file.

-  **Controller** - ThinkUp's controllers live in
   /thinkup/webapp/\_lib/controller/. Each controller should extend
   either ThinkUpController or ThinkUpAuthController. Extend
   ThinkUpAuthController only if the user should be logged in to perform
   the desired action. PHP pages that are requested in the browser will
   instantiate a controller and echo its go () method. If you are trying
   to figure out how a page works, the PHP file of that page will tell
   you what controller is deciding how the page works.

Understanding Controllers
-------------------------

As mentioned before, all controllers in the ThinkUp application extend
either ThinkUpController or ThinkUpAuthController. The reason for this
is that both of those classes provide a lot of the nitty gritty code
that goes into keeping the user interface consistent and secure so that
you don't have to. They also have a few cool tricks up their sleeves.

If you extend the ThinkUpAuthController, for example, your user will
need to be logged in to access the page. If they are not logged in, the
page realises this and displays an appropriate, consistent error
message. You, as the developer, do not need to worry about handling
guest users trying to access your page.

The ThinkUpController class also handles the template that you want to
use. Setting a template and sending data to the template is all handled
for you and abstracted into a handful of easy to use methods that I will
explain in more detail in the next section.

Remember those cool tricks I mentioned earlier? One of them is
profiling. If you turn on profiling in your config file, the controllers
are what handle that. All of the queries that you make get logged by the
classes that access the database and the controller automatically
displays them at the bottom of the page. Cool, right?

Writing your own controller is really easy. All you need to do is extend
either ThinkUpController or ThinkUpAuthController and override a method
called control (). The control method is an abstract method inside
ThinkUpController (ThinkUpAuthController extends ThinkUpController) that
gets called as part of the go () method. So as the page is generated,
the control () method is called to allow you to do all of the processing
that you need to do, then the controller uses the data that you
processed to generate the page that gets displayed to the user.

Understanding Views
-------------------

We know that a view is what presents data to us in to a front end user
interface, but how does it achieve this? Enter Smarty.

ThinkUp uses a templating engine called
`Smarty <http://www.smarty.net/>`_. Smarty is a very versatile and very
easy to use templating engine that allows a developer to send key=value
pairs to a .tpl file that contains HTML code (and possibly some Smarty
syntax that you can read about on their site). Let's take a look at the
UpdateNowController class.

The UpdateNowController class is quite small and will tell you just
about everything you need to know about the basics of how controllers
send data to views. You will see a line in this controller that looks
like this: $this->setViewTemplate (‘crawler.updatenow.tpl');. This is an
important line of code and needs to be present in all of the controllers
you write; it tells the controller which view to use. You will notice in
the views folder that there is a file called crawler.updatenow.tpl. This
is the view that the UpdateNowController uses.

About half way down the crawler.updatenow.tpl file you will see this
line of code

::

  <iframe width="850" height="500" src="run.php{if $log == 'full'}?log=full{/if}" style="border:solid black 1px">

Notice the strange “if” statement in {curly braces}? That's Smarty
syntax. It's telling the page that if the $log variable equals the
string “full”, print ?log=full to the page. But where did the $log
variable come from? Near the bottom of UpdateNowController you will see
this line of code: $this->addToView (‘log', ‘full');. This is how
controllers pass key=value pairs to Smarty templates.

That just about covers the basics. If you have any further questions
don't hesitate to ping the :doc:`mailing list </contact>` or ask in the :doc:`IRC channel </contact>`.