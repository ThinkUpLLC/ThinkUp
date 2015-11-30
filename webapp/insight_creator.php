<html lang="en" itemscope itemtype="http://schema.org/Article">
<head prefix="og: http://ogp.me/ns#">
    <meta charset="utf-8">
    <title><?php echo empty($_GET['headline']) ? 'ThinkUp Insight Creator' : $_GET['headline'] ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="">
    <meta property="og:site_name" content="ThinkUp" />
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@thinkup">
    <meta name="twitter:domain" content="thinkup.com">
    <meta name="twitter:image:src" content="https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?>">

    <meta itemprop="name" content="Insight Tester">
    <meta name="twitter:title" content="<?php echo empty($_GET['headline']) ? 'ThinkUp Insight Creator' : $_GET['headline'] ?>">
    <title></title>
    <meta property="og:title" content="<?php echo empty($_GET['headline']) ? 'ThinkUp Insight Creator' : $_GET['headline'] ?>" />

    <meta itemprop="description" content="<?php echo empty($_GET['body']) ? 'ThinkUp Insight Creator' : $_GET['headline'] ?>">
    <meta name="description" content="<?php echo empty($_GET['body']) ? 'ThinkUp Insight Creator' : $_GET['headline'] ?>">
    <meta name="twitter:description" content="<?php echo empty($_GET['body']) ? 'ThinkUp Insight Creator' : $_GET['headline'] ?>">

    <meta itemprop="image" content="https://www.thinkup.com/joinassets/ico/apple-touch-icon-144-precomposed.png">
    <meta property="og:image" content="https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?>" />
    <meta property="og:image:secure" content="https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?>" />

    <meta name="og:image:type" content="image/png">

    <!-- styles -->
    <link href="assets/css/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/vendor/font-awesome.min.css" rel="stylesheet">
    <link href='//fonts.googleapis.com/css?family=Libre+Baskerville:400,700,400italic|' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="//use.typekit.net/xzh8ady.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
    <link href="assets/css/thinkup.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript"> var site_root_path = '/'; </script>

    <!-- google chart tools -->
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
</head>
<body class="insight-stream">
    <div id="menu"><ul class="list-unstyled menu-options"></ul></div>
    <div id="page-content">
      <nav class="navbar navbar-default" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button class="btn menu-trigger">
            <i class="fa fa-bars"></i>
          </button>
          <a class="navbar-brand" href="/"><strong>Think</strong>Up</span></a>
        </div>
      </nav>
<div class="container">
<center><h4>Welcome to ThinkUp's Insight Creator</h4>
<p>Fill out the fields below to propose a ThinkUp insight.</p></center>
    <div class="stream stream-permalink">
            <div class="date-group today">
        <div class="date-marker">
                        <div class="relative">N/A ago</div>
            <div class="absolute">Today</div>
                    </div>

        <div>
            <form method="post" action="#" class="previewer">
                <div><label class="blue">Network</label>
                  <select class="insight-network">
                    <option value="twitter">Twitter</option>
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                  </select>
                </div>

                <div><label class="blue">Headline:</label> <input type="text" id="headline" value="@catlady99 rescued twice the kittens this week" /></div>
                <div><label class="blue">Body:</label>
                  <textarea id="body">@catlady99 shared <strong>68</strong> rescue kittens this week. That's compared to 32 felines last week. Way to save the kitties!</textarea>
                  <!-- <input type="text" id="body" value="I am the body.  Lots more text goes in me, normally."/> -->
                  <!-- <input type="text" id="body" value="I am the body.  Lots more text goes in me, normally."/> -->
                </div>
                <div><label class="blue">Hero image <input type="checkbox" id="show-hero" class="cb" /></label>
                        <input type="text" id="hero" value="https://farm4.staticflickr.com/3170/3110862090_0aab78c4d1_b.jpg" />
                </div>
                <div><label class="blue">Action button <input type="checkbox" id="show-button" class="cb" /></label>
                        <input type="text" id="button" value="Share a kitten" />
                </div>
                <div><label class="blue">Header image <input type="checkbox" id="show-avatar" class="cb" checked="true" /></label>
                        <input type="text" id="avatar" value="https://farm7.staticflickr.com/6146/5976784449_4fe7c02760_q.jpg" />
                </div>
                <div><label class="blue">High emphasis <input type="checkbox" id="show-emphasis" class="cb" /></label>
                </div>
                <div><label class="blue">Full width <input type="checkbox" id="show-wide" class="cb" /></label>
                </div>
                <div><label class="blue">Embed</label>
                  <input type="radio" name="embeds" id="embed-none" class="cb" />None
                  <input type="radio" name="embeds" id="show-post" class="cb" /><label for="show-post" class="embedLabel">Post</label>
                  <input type="radio" name="embeds" id="show-posts" class="cb" /><label for="show-posts" class="embedLabel">Posts</label>
                  <input type="radio" name="embeds" id="show-user" class="cb" /><label for="show-user" class="embedLabel">User</label>
                  <input type="radio" name="embeds" id="show-users" class="cb" /><label for="show-users" class="embedLabel">Users</label>
                  <input type="radio" name="embeds" id="show-bar" class="cb" /><label for="show-bar" class="embedLabel">Bar chart</label>
                  <input type="radio" name="embeds" id="show-line" class="cb" checked /><label for="show-line" class="embedLabel">Line chart</label>
                  <input type="radio" name="embeds" id="show-pie" class="cb" /><label for="show-pie" class="embedLabel">Pie chart</label>
                </div>
                <div><label class="blue">Color</label>
                  <select class="insight-color">
                    <option value="salmon">Salmon</option>
                    <option value="creamsicle">Creamsicle</option>
                    <option value="pea">Pea</option>
                    <!--<option value="sepia">Sepia</option>-->
                    <option value="purple">Purple</option>
                    <option value="mint">Mint</option>
                    <option value="bubblegum">Bubblegum</option>
                    <option value="seabreeze">Seabreeze</option>
                    <option value="dijon">Dijon</option>
                    <option value="sandalwood">Sandalwood</option>
                    <option value="caramel">Caramel</option>
                  </select>
                </div>
                <button class="editToggle hideEditor btn btn-default btn-action" value="Hide editor">Hide editor</button>
            </form>
        </div>

<div class="panel panel-default insight insight-default insight-facebookprofileprompt
   insight-salmon " id="insight-">
  <div class="panel-heading">
    <h2 class="panel-title"><span class="preview-headline"></span></h2>
    <img src="" alt="" width="50" height="50" class="img-circle userpic userpic-featured preview-avatar">
    </div>
  <div class="panel-desktop-right">
    <div class="panel-body">
      <figure class="insight-hero-image preview-hero">
        <img src="https://www.thinkup.com/assets/images/insights/2014-05/subway.jpg" alt="New York City subway car" class="img-responsive">
        <figcaption class="insight-hero-credit">Photo: Robert Wiśniewski</a></figcaption>
      </figure>
            <div class="panel-body-inner">
            <p id="insight-text-"><span class="preview-body"></span></p>


           <!-- single user -->
           <ul class="body-list user-list body-list-show-some all-items-visible preview-user">
             <li class="list-item">

             <div class="user">
               <a href="#">
                 <img src="https://pbs.twimg.com/profile_images/550825678673682432/YRqb4FJE.png" alt="Gina Trapani" class="img-circle pull-left user-photo">
               </a>
               <div class="user-about">
                 <div class="user-name"><a href="#">Gina Trapani <i class="fa fa-twitter icon icon-network"></i></a></div>
                 <div class="user-text">
                   <p>                    327,175 followers
                   </p>
                   <p>Co-founder of @ThinkUp. Founder of @Lifehacker. Working on building a customer-supported, sustainable, web business. Join us: http://thinkup.com</p>
                 </div>
               </div>
             </div>
             </li>
           </ul>


           <!-- end single user -->


           <!-- list of users -->
           <ul class="body-list user-list body-list-show-some all-items-visible preview-users">
             <li class="list-item">

             <div class="user">
               <a href="#">
                 <img src="https://pbs.twimg.com/profile_images/550825678673682432/YRqb4FJE.png" alt="Gina Trapani" class="img-circle pull-left user-photo">
               </a>
               <div class="user-about">
                 <div class="user-name"><a href="#">Gina Trapani <i class="fa fa-twitter icon icon-network"></i></a></div>
                 <div class="user-text">
                   <p>                    327,175 followers
                   </p>
                   <p>Co-founder of @ThinkUp. Founder of @Lifehacker. Working on building a customer-supported, sustainable, web business. Join us: http://thinkup.com</p>
                 </div>
               </div>
             </div>
             </li>
             <li class="list-item" style="display: list-item;">

             <div class="user">
               <a href="#">
                 <img src="https://pbs.twimg.com/profile_images/529664614863101952/yBQgCUMW.png" alt="Anil" class="img-circle pull-left user-photo">
               </a>
               <div class="user-about">
                 <div class="user-name"><a href="#">Anil <i class="fa fa-twitter icon icon-network"></i></a></div>
                 <div class="user-text">
                   <p>                    511,173 followers
                   </p>
                   <p>Cofounder @thinkup & @activateinc • Writer @Medium & @Wired • Blog: http://dashes.com  • anil@dashes.com • 646 833-8659 • Be the change you want to see &c.</p>
                 </div>
               </div>
             </div>
             </li>
             <li class="list-item" style="display: list-item;">

             <div class="user">
               <a href="#">
                 <img src="https://pbs.twimg.com/profile_images/458683475629838336/9FSA_3UQ_400x400.jpeg" alt="Matt Jacobs" class="img-circle pull-left user-photo">
               </a>
               <div class="user-about">
                 <div class="user-name"><a href="#">Matt Jacobs <i class="fa fa-twitter icon icon-network"></i></a></div>
                 <div class="user-text">
                   <p>                    1,444 followers
                   </p>
                   <p>I design and build internet things. These days, I’m building @kidpostapp and working with @thinkup. Also, cheeseburgers.</p>
                 </div>
               </div>
             </div>
             </li>
           </ul>
           <!-- end list of users -->


           <!-- bar chart -->
<div id="chart_40194" class="preview-bar weekly_chart" style="position: relative;"><div dir="ltr" style="position: relative; width: 520px; height: 250px;"><div style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%;"><svg width="520" height="250" aria-label="A chart." style="overflow: hidden;"><defs id="defs"><clipPath id="_ABSTRACT_RENDERER_ID_0"><rect x="200" y="0" width="320" height="250"></rect></clipPath></defs><g><rect x="200" y="0" width="320" height="250" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g clip-path="url(#_ABSTRACT_RENDERER_ID_0)"><g><rect x="200" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect><rect x="280" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect><rect x="360" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect><rect x="439" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect><rect x="519" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect></g><g class="change-me"><rect x="201" y="5" width="5" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="30" width="3" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="55" width="2" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="80" width="2" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="200.858875" y="105" width="1.4354999999999905" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="200.938625" y="129" width="1.7545000000000073" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="154" width="2" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="179" width="6" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="204" width="16" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="200.9785" y="229" width="1.9140000000000157" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="207" y="5" width="73" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="205" y="30" width="151" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="204" y="55" width="63" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="204" y="80" width="112" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="203" y="105" width="81" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="203" y="129" width="78" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="204" y="154" width="76" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="208" y="179" width="24" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="218" y="204" width="2" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="203" y="229" width="13" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="281" y="5" width="90" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="357" y="30" width="110" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="268" y="55" width="87" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="317" y="80" width="76" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="285" y="105" width="41" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="282" y="129" width="55" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="281" y="154" width="54" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="233" y="179" width="29" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="221" y="204" width="8" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="217" y="229" width="36" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect></g><g><rect x="200" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#333333"></rect></g></g><g></g><g><g><text text-anchor="end" x="198" y="17.15" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Lifehack: Turn a 5-minute tas...</text><rect x="2" y="6.949999999999999" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="42.05" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">It's plain as day that the cops ...</text><rect x="2" y="31.849999999999994" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="66.95" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Sometimes I want to wrap my...</text><rect x="2" y="56.75" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="91.85" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Mike Brown was a man. He liv...</text><rect x="2" y="81.64999999999999" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="116.75" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">An important, cogent breakd...</text><rect x="2" y="106.55" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="141.64999999999998" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Take off the helmets, holster t...</text><rect x="2" y="131.45" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="166.54999999999998" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Basic crowd management for ...</text><rect x="2" y="156.35" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="191.45" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Let's be clear: The threat of vi...</text><rect x="2" y="181.25" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="216.34999999999997" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">The ice bucket challenge has ...</text><rect x="2" y="206.14999999999998" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="241.24999999999997" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">I don't have anything left, I ha...</text><rect x="2" y="231.04999999999998" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g></g></g><g></g></svg></div></div><div style="display: none; position: absolute; top: 260px; left: 530px; white-space: nowrap; font-family: Arial; font-size: 12px; font-weight: bold;">456</div><div></div></div>
           <!-- end bar chart -->

           <!-- pie chart -->
<div class="preview-pie" id="chart_44344" style="position: relative;"><div dir="ltr" style="position: relative; width: 300px; height: 250px;"><div style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%;"><svg width="300" height="250" aria-label="A chart." style="overflow: hidden;"><defs id="defs"></defs><rect x="0" y="0" width="300" height="250" stroke="none" stroke-width="0" fill="#ffffff"></rect><g><rect x="188" y="48" width="55" height="26" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g><rect x="188" y="48" width="55" height="10" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g><text text-anchor="start" x="202" y="56.5" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#222222">One thing</text></g><rect x="188" y="48" width="10" height="10" class="prime" stroke="none" stroke-width="0" fill="#f576b5"></rect></g><g><rect x="188" y="64" width="55" height="10" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g><text text-anchor="start" x="202" y="72.5" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#222222">Another thing</text></g><rect x="188" y="64" width="10" height="10" class="sec" stroke="none" stroke-width="0" fill="#b3487c"></rect></g></g><g><path class="prime" d="M115,126L115,69A57,57,0,0,1,164.36344,154.5L115,126A0,0,0,0,0,115,126" stroke="#ffffff" stroke-width="1" fill="#f576b5"></path><text text-anchor="start" x="128.0845073354202" y="113.57407726443193" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#ffffff">33.3%</text></g><g><path class="sec" d="M115,126L164.363448015713,154.5A57,57,0,1,1,115,69L115,126A0,0,0,1,0,115,126" stroke="#ffffff" stroke-width="1" fill="#b3487c"></path><text text-anchor="start" x="72.9154926645798" y="145.42592273556804" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#ffffff">66.7%</text></g><g></g></svg></div></div><div style="display: none; position: absolute; top: 260px; left: 310px; white-space: nowrap; font-family: Arial; font-size: 10px; font-weight: bold;">2 (66.7%)</div><div></div></div>
            <!-- end pie chart -->


            <!-- line chart -->
<div id="chart_" class="chart preview-line" style="position: relative;"><div dir="ltr" style="position: relative; width: 290px; height: 200px;"><div style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%;"><svg width="290" height="200" aria-label="A chart." style="overflow: hidden;"><defs id="defs"><clipPath id="_ABSTRACT_RENDERER_ID_4"><rect x="56" y="38" width="179" height="124"></rect></clipPath></defs><rect x="0" y="0" width="290" height="200" stroke="none" stroke-width="0" fill="#ffffff"></rect><g><rect x="56" y="38" width="179" height="124" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g clip-path="url(#_ABSTRACT_RENDERER_ID_4)"><g><rect x="74" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="95" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="115" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="136" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="157" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="178" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="198" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="161" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="130" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="100" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="69" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="38" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect></g><g><rect x="56" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="161" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect></g><g><path class="change-me" d="M56.5,148.175L77.26666666666667,143.05L98.03333333333333,137.925L118.80000000000001,132.8L139.56666666666666,127.67500000000001L160.33333333333331,122.55000000000001L181.10000000000002,117.42500000000001L213.73333333333335,117.42500000000001L234.5,69.25000000000001" stroke="#fc939e" stroke-width="2" fill-opacity="1" fill="none"></path></g></g><g class="change-me"><circle cx="56.5" cy="148.175" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="77.26666666666667" cy="143.05" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="98.03333333333333" cy="137.925" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="118.80000000000001" cy="132.8" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="139.56666666666666" cy="127.67500000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="160.33333333333331" cy="122.55000000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="181.10000000000002" cy="117.42500000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="213.73333333333335" cy="117.42500000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="234.5" cy="69.25000000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle></g><g><g><text text-anchor="end" x="76.05" y="175.36121593216774" font-family="Arial" font-size="10" transform="rotate(-30 76.05 175.36121593216774)" stroke="none" stroke-width="0" fill="#999999">Jul 15</text></g><g><text text-anchor="end" x="117.58333333333334" y="175.36121593216774" font-family="Arial" font-size="10" transform="rotate(-30 117.58333333333334 175.36121593216774)" stroke="none" stroke-width="0" fill="#999999">Jul 29</text></g><g><text text-anchor="end" x="159.11666666666667" y="175.36121593216774" font-family="Arial" font-size="10" transform="rotate(-30 159.11666666666667 175.36121593216774)" stroke="none" stroke-width="0" fill="#999999">Aug 12</text></g><g><text text-anchor="end" x="200.65" y="175.36121593216774" font-family="Arial" font-size="10" transform="rotate(-30 200.65 175.36121593216774)" stroke="none" stroke-width="0" fill="#999999">Aug 26</text></g><g><text text-anchor="end" x="46" y="165" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">760</text></g><g><text text-anchor="end" x="46" y="134.25" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">820</text></g><g><text text-anchor="end" x="46" y="103.5" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">880</text></g><g><text text-anchor="end" x="46" y="72.75000000000001" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">940</text></g><g><text text-anchor="end" x="46" y="42.000000000000014" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">1,000</text></g></g></g><g></g></svg></div></div><div style="display: none; position: absolute; top: 210px; left: 300px; white-space: nowrap; font-family: Arial; font-size: 10px; font-weight: bold;">846</div><div></div></div>
            <!-- end line chart -->


            <!-- single post -->
            <ul class="body-list tweet-list body-list-show-all preview-post">
                <li class="list-item">
                    <blockquote class="tweet">
                       <a href="https://twitter.com/intent/user?user_id=7861312" title="feliciaday"><img src="https://pbs.twimg.com/profile_images/521350369268355073/LT5IlZFI.jpeg" alt="feliciaday" width="60" height="60" class="img-circle pull-left tweet-photo user-photo"></a>
                       <div class="byline"><a href="https://twitter.com/intent/user?user_id=7861312" title="feliciaday"><strong>Felicia Day</strong> <span class="username">@feliciaday</span></a></div>
                       <div class="tweet-body">I just backed Word Realms on <a href="https://twitter.com/intent/user?screen_name=Kickstarter">@Kickstarter</a><span> <a href="http://t.co/YZvO0yW3">http://t.co/YZvO0yW3</a></span></div>
                       <div class="tweet-actions">
                           <a href="https://twitter.com/feliciaday/status/212418013163552768" class="tweet-action tweet-action-permalink">Jun 12, 2012</a>
                           <a href="http://twitter.com/intent/tweet?in_reply_to=212418013163552768" class="tweet-action"><i class="fa fa-reply icon"></i></a>
                          <a href="http://twitter.com/intent/retweet?tweet_id=212418013163552768" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
                          <a href="http://twitter.com/intent/favorite?tweet_id=212418013163552768" class="tweet-action"><i class="fa fa-heart icon"></i></a>
                       </div>
                    </blockquote>
                </li>
            </ul>
            <!-- end single post -->




            <!-- list of posts -->
<ul class="body-list preview-posts tweet-list body-list-show-some all-items-visible">

    <li class="list-item">

<blockquote class="tweet">
  <a href="https://twitter.com/intent/user?user_id=16347964" title="shl"><img src="https://pbs.twimg.com/profile_images/2374496291/y9a7z6e8xau88v4t6ovh_normal.png" alt="shl" width="60" height="60" class="img-circle pull-left tweet-photo user-photo"></a>
  <div class="byline"><a href="https://twitter.com/intent/user?user_id=16347964" title="shl"><strong>Sahil Lavingia</strong> <span class="username">@shl</span></a></div>
  <div class="tweet-body"><span>Gumroad + Twitter = in-Tweet purchases, finally! <a href="https://t.co/b8njUwOhDJ">https://t.co/b8njUwOhDJ</a></span></div>

    <div class="tweet-actions">
    <a href="https://twitter.com/shl/status/508969172722675712" class="tweet-action tweet-action-permalink">Sep  8, 2014</a>
      <a href="http://twitter.com/intent/tweet?in_reply_to=508969172722675712" class="tweet-action"><i class="fa fa-reply icon"></i></a>
    <a href="http://twitter.com/intent/retweet?tweet_id=508969172722675712" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
    <a href="http://twitter.com/intent/favorite?tweet_id=508969172722675712" class="tweet-action"><i class="fa fa-heart icon"></i></a>
    </div>
</blockquote>
    </li>

    <li class="list-item" style="display: list-item;">

<blockquote class="tweet">
  <a href="https://twitter.com/intent/user?user_id=6981492" title="ftrain"><img src="https://pbs.twimg.com/profile_images/3363818792/c90e33ccf22e3146d5cd871ce561795a_normal.png" alt="ftrain" width="60" height="60" class="img-circle pull-left tweet-photo user-photo"></a>
  <div class="byline"><a href="https://twitter.com/intent/user?user_id=6981492" title="ftrain"><strong>Paul Ford</strong> <span class="username">@ftrain</span></a></div>
  <div class="tweet-body"><span><a href="http://t.co/gqMrHhqpu0">http://t.co/gqMrHhqpu0</a></span></div>

    <div class="tweet-actions">
    <a href="https://twitter.com/ftrain/status/505416713580867585" class="tweet-action tweet-action-permalink">Aug 29, 2014</a>
      <a href="http://twitter.com/intent/tweet?in_reply_to=505416713580867585" class="tweet-action"><i class="fa fa-reply icon"></i></a>
    <a href="http://twitter.com/intent/retweet?tweet_id=505416713580867585" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
    <a href="http://twitter.com/intent/favorite?tweet_id=505416713580867585" class="tweet-action"><i class="fa fa-heart icon"></i></a>
    </div>
</blockquote>
    </li>

    <li class="list-item" style="display: list-item;">

<blockquote class="tweet">
  <a href="https://twitter.com/intent/user?user_id=666073" title="trammell"><img src="https://pbs.twimg.com/profile_images/479778647444692992/inahCiKL_normal.png" alt="trammell" width="60" height="60" class="img-circle pull-left tweet-photo user-photo"></a>
  <div class="byline"><a href="https://twitter.com/intent/user?user_id=666073" title="trammell"><strong>Trammell</strong> <span class="username">@trammell</span></a></div>
  <div class="tweet-body"><a href="https://twitter.com/intent/user?screen_name=anildash">@anildash</a><span> <a href="http://t.co/WBeIYfNIXm">http://t.co/WBeIYfNIXm</a> #blessed</span></div>

    <div class="tweet-actions">
    <a href="https://twitter.com/trammell/status/501926686527479808" class="tweet-action tweet-action-permalink">Aug 20, 2014</a>
      <a href="http://twitter.com/intent/tweet?in_reply_to=501926686527479808" class="tweet-action"><i class="fa fa-reply icon"></i></a>
    <a href="http://twitter.com/intent/retweet?tweet_id=501926686527479808" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
    <a href="http://twitter.com/intent/favorite?tweet_id=501926686527479808" class="tweet-action"><i class="fa fa-heart icon"></i></a>
    </div>
</blockquote>
    </li>
    </ul>
            <!-- end list of posts -->


            <span class="preview-button"><a href="#" class="btn btn-default btn-action">Edit Facebook Profile</a><span>
          </div>
    </div>
    <div class="panel-footer">
      <div class="insight-metadata">
        <i class="fa fa-twitter-square icon icon-network"></i>
        <a class="permalink" href="#"><?php date_default_timezone_set('America/New_York'); echo date('M d'); ?></a>
      </div>
<!--      <div class="share-menu">
        <a class="twitter" href="https://twitter.com/intent/tweet?related=thinkup&amp;text=::headline::&amp;url=::url::&amp;via=thinkup">Share this</a>
      </div>
-->
    </div>
  </div>
</div>
  <div class="share-or-edit">
    <button class="generate-issue btn btn-default btn-action btn-bottom"><i class="fa fa-github"></i> File a ticket</button>
    <a class="twitter" href="https://twitter.com/intent/tweet?related=thinkup&amp;text=::headline::&amp;url=::url::&amp;via=thinkup">
      <button class="btn btn-bottom btn-action btn-primary"><i class="fa fa-twitter"></i> Tweet this</button>
    </a>
    <a class="get-image" target="_blank"https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?> style="float:right;" href="https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?>">
      <button class="btn btn-bottom btn-action"><i class="fa fa-picture-o"></i> Get image</button>
    </a>
    <button class="editor editToggle btn btn-action btn-bottom"><i class="fa fa-edit"></i> Edit</button>
  </div>

  </div><!-- end stream -->
</div><!-- end container -->

</div><!-- end page-content -->

<div class="issue-template" style="display:none;">
# One-liner

Describe what this insight is in a single sentence. This is the description that appears in ThinkUp's insights list, so phrase it like the others are. Current insights single sentences are:

* Post activity spikes for the past 7, 30, and 365 days.
* How often you referred to yourself ("I", "me", "myself", "my") in the past week.
* How many more users a message has reached due to your reshare or retweet.

:insight_tester:

# Full explainer

How does it make the user feel? What is the goal of this insight?

# Audience for the insight

Which networks, if any, are excluded from this insight?

Does this insight serve users with less or more activity?

First-run: does this insight show up on a user's first crawl?

# How often this insight runs

- [ ] Always (triggered by a data event, check the data and update insight on every crawl)
- [ ] Daily (check the data once a day, don't regenerate every crawl)
- [ ] Weekly on a fixed day (specify day of week per network to space these out)
- [ ] Monthly on a fixed day with bonus magic day within first 2 weeks of use (specify day of month per network to space these out)
- [ ] Annual on a fixed date (specify day of year)
- [ ] Annual every 365 days from first appearance


# Headline

:your_headline:

Include multiple variations, use third person and variables for localized network terminology.

For example:

* %username has passed %total %followers!
* More than %total people are %following %username

# Body

:your_body:

Include multiple variations whenever possible.

For example:

* That's more than the population of Belize.
* That's more people than can fit in Yankee Stadium.

# Tout

:your_tout:

Explain how ThinkUp can help potential users, basing copy on the insight.

For example:

* Want a handy list of the links you've favorited? We can help!
* Get a look back at what you were doing on this day in years past.

# Criteria and logic

Describe the rules for when this insight runs.

* What data does this insight need? Last week's posts? Last month's? Just the user's posts?
* Does the insight count replies as well as non-replies?
* Are there baseline comparisons? What is the logic around the comparison?
* Are there special copy cases? (for example, if the baseline comparison matches)
* Is there a minimum threshold for any bit of data before the insight should get generated?

## Emphasis

- [ ] High
- [ ] Medium
- [ ] Low

# Included elements

- [x] Headline
- [x] Text
- [ ] Header image (image off to the left in side-by-side style insights)
- [ ] Hero image (giant image on top)
- [ ] List of user(s)
- [ ] List of post(s)
- [ ] List of link(s)
- [ ] Action button (please specify button label and URL button should link to)
- [ ] Line chart
- [ ] Bar chart
- [ ] Other viz
- [ ] Other graphic treatment

## Action button

Specify the following action button attributes:

* 'label' => 'Edit Facebook Profile',
* 'url' => 'https://www.facebook.com/me?sk=info&edit=eduwork&ref=update_info_button',


## Hero image

If your insight includes a hero image, we encourage you to use public domain or CC-licensed images. Hero images should be at least 540px wide and be landscape. (They can be portrait, but then they're quite tall.)

Specify the following hero image attributes:

* 'url' => 'https://www.thinkup.com/assets/images/insights/2014-03/oscars2014.jpg',
* 'alt_text' => 'Ellen DeGeneres posted the most popular tweet of all time',
* 'credit' => 'Photo: @TheEllenShow',
* 'img_link' => 'https://twitter.com/TheEllenShow/status/440322224407314432'
</div>

<style>
    .share-or-edit {
      margin: 20px auto;
      height: 50px;
      width: 540px;
    }
    .btn-bottom {
      float: right;
      margin-left: 10px;
      height: 34px;
    }
    .preview { margin-right: 120px; }
    .previewer label.blue { width: 130px; background-color: #46BCFF; color: white; height: 20px; padding-left: 5px; margin-top: 5px; }
    .previewer label.embedLabel { font-weight: normal; }
    .previewer label .cb { float: right; }
    .previewer input, .previewer textarea { width: 538px; }
    .previewer textarea { margin-bottom: 5px; }
    .previewer div .cb { width: 20px; margin-left: 5px; }
    .hideEditor {
      margin-left: 125px;
      margin-top: 10px;
    }
</style>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="assets/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
<script type="text/javascript">
  /*!
      query-string
      Parse and stringify URL query strings
      https://github.com/sindresorhus/query-string
      by Sindre Sorhus
      MIT License
  */
  (function () {
      'use strict';
      var queryString = {};

      queryString.parse = function (str) {
          if (typeof str !== 'string') {
              return {};
          }

          str = str.trim().replace(/^\?/, '');

          if (!str) {
              return {};
          }

          return str.trim().split('&').reduce(function (ret, param) {
              var parts = param.replace(/\+/g, ' ').split('=');
              var key = parts[0];
              var val = parts[1];

              key = decodeURIComponent(key);
              // missing `=` should be `null`:
              // http://w3.org/TR/2012/WD-url-20120524/#collect-url-parameters
              val = val === undefined ? null : decodeURIComponent(val);

              if (!ret.hasOwnProperty(key)) {
                  ret[key] = val;
              } else if (Array.isArray(ret[key])) {
                  ret[key].push(val);
              } else {
                  ret[key] = [ret[key], val];
              }

              return ret;
          }, {});
      };

      queryString.stringify = function (obj) {
          return obj ? Object.keys(obj).map(function (key) {
              var val = obj[key];

              if (Array.isArray(val)) {
                  return val.map(function (val2) {
                      return encodeURIComponent(key) + '=' + encodeURIComponent(val2);
                  }).join('&');
              }

              return encodeURIComponent(key) + '=' + encodeURIComponent(val);
          }).join('&') : '';
      };

      queryString.push = function (key, new_value) {
        var params = queryString.parse(location.search);
        params[key] = new_value;
        var new_params_string = queryString.stringify(params)
        history.pushState({}, "", window.location.pathname + '?' + new_params_string);
      }

      if (typeof module !== 'undefined' && module.exports) {
          module.exports = queryString;
      } else {
          window.queryString = queryString;
      }
  })();
  function searchToObject() {
    var pairs = window.location.search.substring(1).split("&"),
      obj = {},
      pair,
      i;

    for ( i in pairs ) {
      if ( pairs[i] === "" ) continue;

      pair = pairs[i].split("=");
      obj[ decodeURIComponent( pair[0] ) ] = decodeURIComponent( pair[1] );
    }

    return obj;
  }
</script>
<script type="text/javascript">
  $(document).ready(function() {

        var inputs = ['headline', 'body', 'button', 'avatar', 'hero'];
        var checks = [
        'button', 'avatar',
        'emphasis', 'hero',
        'wide'
        ];
        var embeds = [
          'post', 'posts', 'user', 'users',
          'bar', 'line', 'pie'
        ]

        var chart_colors = [
          '#eeeeee',
          '#f576b5',
          '#8f3963'
        ];

        var update_url = function() {
          for (var i=0; i < inputs.length; i++) {
            var input = inputs[i];
            var value = $('#'+input).val()
            // if (value !== '') {
              queryString.push(input, value);
            // }
          }

          for (var i=0; i < checks.length; i++) {
            var check = 'show-' + checks[i];
            queryString.push(check, $('#'+check).prop('checked'));
          }

          queryString.push('color', $('.insight-color').val());
          queryString.push('network', $('.insight-network').val());

          var embed = $("input:radio[name=embeds]:checked").attr('id');
          queryString.push('embed', embed);

          queryString.push('preview', '1');
        }
        var update_preview = function () {
          update_color($('.insight-color')[0]);
          update_network($('.insight-network')[0]);
          for (var i=0; i < inputs.length; i++) {
            var input = inputs[i];
            var value = $('#'+input).val()
            if ($.inArray(input, checks) === -1) {
              $('.preview-'+input).html(value);
            }
            else {
              if (input === 'button') {
                $('.preview-'+input+' .btn').html(value);
              }
              else if (input === 'avatar') {
                $('.preview-'+input).attr('src', value);
              }
              else if (input === 'hero') {
                $('.preview-'+input+' img').attr('src', value);
              }
            }
          }
          for (var i=0; i < checks.length; i++) {
            var check = checks[i];
            var $elem = $('#show-'+check+':checked');
            if ($elem.length) {
              $('.preview-'+check).show();
              $('#'+check).show();
            }
            else {
              $('.preview-'+check).hide();
              $('#'+check).hide();
            }
          }
          if ($('#show-emphasis:checked').length) {
            $('.insight').addClass('insight-hero');
          }
          else {
            $('.insight').removeClass('insight-hero');
          }

          if ($('#show-wide:checked').length) {
            $('.insight').addClass('insight-wide');
          }
          else {
            $('.insight').removeClass('insight-wide');
          }

          for (var i=0; i < embeds.length; i++) {
            var embed = embeds[i];
            var $elem = $('#show-'+embed+':checked');
            if ($elem.length) {
              $('.preview-'+embed).show();
              // update its color
              var primaryColor = $('.insight').css('background-color');
              var secColor = $('.insight').css('border-top-color');
              var tertColor = secColor.substr(4, secColor.length-5).split(', ').map(function(numString) {
                var num = parseInt(numString);
                return Math.round(Math.min(255, num * 0.8));
              });
              tertColor = 'rgb(' + tertColor.join(',') + ')';
              var tmpColor = $('.change-me rect').css('fill');
              var colorIndex = 0;
              var colorUpdates = [primaryColor, secColor, tertColor];
              // first update bar chart
              $('.change-me rect').each(function(index) {
                if (tmpColor !== $(this).css('fill')) {
                  tmpColor = $(this).css('fill');
                  colorIndex++;
                }
                $(this).css('fill', colorUpdates[colorIndex]);
              });
              // now update line chart
              tmpColor = $('.change-me circle').css('fill');
              colorIndex = 0;
              $('path.change-me').css('stroke', colorUpdates[colorIndex]);
              $('.change-me circle').each(function(index) {
                if (tmpColor !== $(this).css('fill')) {
                  tmpColor = $(this).css('fill');
                  colorIndex++;
                }
                $(this).css('fill', colorUpdates[colorIndex]);
              });
              // now update pie chart
              $('.prime').css('fill', colorUpdates[0]);
              $('.sec').css('fill', colorUpdates[1]);
            }
            else {
              $('.preview-'+embed).hide();
            }
          }
        };

        var update_color = function (element) {
          var _ref = element.options;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            var option = _ref[_i];
            $('.insight').removeClass("insight-" + option.value);
          }
          $('.insight').addClass("insight-" + element.value);
        }
        var update_network = function (element) {
          var _ref = element.options;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            var option = _ref[_i];
            $('.icon-network').removeClass("fa-" + option.value + "-square");
            $('.icon-network').removeClass("fa-" + option.value);
          }
          if (element.value == 'instagram') {
            $('.icon-network').addClass("fa-" + element.value);
          } else {
            $('.icon-network').addClass("fa-" + element.value + "-square");
          }
        }
        $('.previewer :input').change(update_preview).keyup(update_preview);
        $('.insight-color').change(function() {
          update_color(this);
        });
        $('.insight-network').change(function() {
          update_network(this);
        });
        $('.previewer :input').blur(update_url);
        $('.editToggle').click(function() {
          $('.previewer').toggle();
          $('.editor').toggle();
          return false;
        });
        $('a.twitter').click(function() {
          var url = "https://twitter.com/intent/tweet?related=thinkup&amp;text=::headline::&amp;url=::url::&amp;via=thinkup";
          url = url.replace('::headline::', encodeURIComponent($('#headline').val()));
          url = url.replace('::url::', encodeURIComponent(window.location.href));
          this.href = url;
        });
        $('a.get-image').click(function() {
          var url = "https://shares.thinkup.com/insight" + window.location.search;
          this.href = url;
        });

        if (window.location.search) {
          var params = searchToObject();
          $('.insight-color').val(params['color']);
          for (var i=0; i < inputs.length; i++) {
            var input = inputs[i];
            if (params[input] !== undefined) {
              $('#'+input).val(params[input]);
            }
          }
          for (var i=0; i < checks.length; i++) {
            var check = 'show-'+checks[i];
            if (params[check] !== undefined) {
              var checkIt = params[check] === "true" ? true : false;
              $('#'+check).prop('checked', checkIt);
            }
          }
          if (params['embed'] !== undefined) {
            var embed = params['embed'];
            $('#'+embed).prop('checked', true);
          }
          $('.previewer').hide();
          $('.editor').show();
          // set date
          var d = new Date();
          var dateString = "" + (d.toString().split(" ")[1]) + " " + (d.getDate());
          $('.insight-metadata a.permalink').text(dateString);
        }
        else {
          $('.previewer').show();
          $('.editor').hide();
          var insightColor = $('.insight-color')[0];
          insightColor.selectedIndex = Math.round(Math.random() * insightColor.options.length);
        }
        update_preview();
        if (window.callPhantom != null) {
          window.callPhantom("hello");
        }
  });
</script>

<script type="text/javascript">
// Make a new issue from template
$('.generate-issue').on('click', function() {
  var text = $('.issue-template').text();

  var headline = $('#headline').val();
  text = text.replace(':your_headline:', headline);
  text = text.replace(':your_body:', $('#body').val());
  text = text.replace(':your_tout:', $('#tout').val());
  var imgUrl = "https://shares.thinkup.com/insight" + window.location.search;
  var tester = "![Insight preview](" + imgUrl + ")\n[Insight preview](" + window.location.href + ")";
  text = text.replace(':insight_tester:', tester);
  var $embed = $('input[type=radio]:checked');
  if ($embed.prop('id') !== 'embed-none') {
    var embed_type = $embed.prop('id').split('-')[1];
    embed_type =  embed_type.substr(0, embed_type.length-1);
    var to_find = '[ ] List of ' + embed_type;
    text = text.replace(to_find, '[x] List of ' + embed_type);

    embed_type = embed_type[0].toUpperCase() + embed_type.substr(1, embed_type.length);
    to_find = '[ ] ' + embed_type;
    text = text.replace(to_find, '[x] ' + embed_type);
  }
  $('input[type=checkbox].cb:checked').each(function(index) {
    var embed_type = this.id.split('-')[1];
    if (embed_type === 'hero') {
      embed_type = embed_type[0].toUpperCase() + embed_type.substr(1, embed_type.length);
      to_find = '[ ] ' + embed_type;
      text = text.replace(to_find, '[x] ' + embed_type);
      text = text.replace(/'hero_url'.*jpg'/, "'hero_url' => '" + $('#hero').val() + "'");
    }
    else if (embed_type === 'button') {
      to_find = '[ ] Action ' + embed_type;
      text = text.replace(to_find, to_find.replace('[ ]', '[x]'));
      text = text.replace(/'label'.*Profile'/, "'label' => '" + $('#button').val() + "'");
    }
    else if (embed_type === 'avatar') {
      to_find = '[ ] Header'
      text = text.replace(to_find, to_find.replace('[ ]', '[x]'));
      text = text.replace('image off to the left in side-by-side style insights', 'image off to the left in side-by-side style insights: ' + $('#avatar').val());
    }
    else if (embed_type === 'emphasis') {
      to_find = '[ ] High'
      text = text.replace(to_find, to_find.replace('[ ]', '[x]'));
    }
  });
  text = encodeURIComponent(text);
  var title = encodeURIComponent("New Insight: [Name this insight]");
  var url = 'https://github.com/ThinkUpLLC/ThinkUp/issues/new?title=' + title + '&body='+ text;
  var win = window.open(url, '_blank');
  if(win){
    //Browser has allowed it to be opened
    win.focus();
  }else{
    //Broswer has blocked it
    alert('Please allow popups for this site');
  }
});

</script>

<script src="assets/js/vendor/bootstrap.min.js"></script>
<script src="assets/js/vendor/jpanelmenu.js"></script>
<script src="//platform.twitter.com/widgets.js"></script>
<script src="assets/js/thinkup.js "></script>
<script type="text/javascript" src="assets/js/linkify.js"></script>
</body>
</html>
