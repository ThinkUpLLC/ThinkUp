<html lang="en" itemscope itemtype="http://schema.org/Article">
<head prefix="og: http://ogp.me/ns#">
    <meta charset="utf-8">
    <title>ThinkUp</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="">
    <meta property="og:site_name" content="ThinkUp" />
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@thinkup">
    <meta name="twitter:domain" content="thinkup.com">
    <meta name="twitter:image:src" content="https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?>">

    <meta itemprop="name" content="Insight Tester">
    <meta name="twitter:title" content="Insight Tester">
    <meta property="og:title" content="Insight Tester" />

    <meta itemprop="description" content="ThinkUp Insight Tester">
    <meta name="description" content="ThinkUp Insight Tester">
    <meta name="twitter:description" content="ThinkUp Insight Tester">

    <meta itemprop="image" content="https://www.thinkup.com/joinassets/ico/apple-touch-icon-144-precomposed.png">
    <meta property="og:image" content="https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?>" />
    <meta property="og:image:secure" content="https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?>" />

    <meta name="og:image:type" content="image/png">

    <!-- styles -->
    <link href="assets/css/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/vendor/font-awesome.min.css" rel="stylesheet">
    <link href='//fonts.googleapis.com/css?family=Libre+Baskerville:400,700,400italic|' rel='stylesheet' type='text/css'>
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
    <a target="_blank"https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?> style="float:right;" href="https://shares.thinkup.com/insight?<?php echo $_SERVER['QUERY_STRING'] ?>">Get image</a>
    <div class="stream stream-permalink">
            <div class="date-group today">
        <div class="date-marker">
                        <div class="relative">N/A ago</div>
            <div class="absolute">Today</div>
                    </div>
        
        <div>
            <form method="post" action="#" class="previewer">
                <div><label>Headline:</label> <input type="text" id="headline" value="I am the headline!" /></div>
                <div><label>Body:</label> 
                  <textarea id="body">I am the body.  Lots more text goes in me, normally.</textarea>
                  <!-- <input type="text" id="body" value="I am the body.  Lots more text goes in me, normally."/> -->
                  <!-- <input type="text" id="body" value="I am the body.  Lots more text goes in me, normally."/> -->
                </div>
                <div><label>Hero image:</label> 
                        <input type="text" style="width: 400px"  id="hero" value="https://www.thinkup.com/assets/images/insights/2014-05/subway.jpg" />
                        Show: <input type="checkbox" id="show-hero" class="cb" />
                </div>
                <div><label>Button:</label> 
                        <input type="text" style="width: 400px"  id="button" value="Action!" />
                        Show: <input type="checkbox" id="show-button" class="cb" />
                </div>
                <div><label>Avatar:</label> 
                        <input type="text" style="width: 400px"  id="avatar" value="https://pbs.twimg.com/profile_images/1096005346/1_normal.jpg" />
                        Show: <input type="checkbox" id="show-avatar" class="cb" />
                </div>
                <div><label>High emphasis:</label>
                    <input type="checkbox" id="show-emphasis" class="cb" />
                </div>
                <div><label>Embed:</label> 
                  <input type="radio" name="embeds" id="embed-none" class="cb" checked />None
                  <input type="radio" name="embeds" id="show-post" class="cb" />Post
                  <input type="radio" name="embeds" id="show-posts" class="cb" />Posts
                  <input type="radio" name="embeds" id="show-user" class="cb" />User
                  <input type="radio" name="embeds" id="show-users" class="cb" />Users
                  <input type="radio" name="embeds" id="show-bar" class="cb" />Bar chart
                  <input type="radio" name="embeds" id="show-line" class="cb" />Line chart
                  <input type="radio" name="embeds" id="show-pie" class="cb" />Pie chart
                </div>
                <button class="editToggle hideEditor btn btn-default btn-action" value="Hide editor">Hide editor</button>
            </form>
        </div>

<div class="panel panel-default insight insight-default insight-facebookprofileprompt
   insight-salmon " id="insight-">
  <div class="panel-heading">
    <h2 class="panel-title"><span class="preview-headline"></span></h2>
    <img src="" alt="" width="50" height="50" class="img-circle userpic userpic-featured preview-avatar">
    <i class="editor editToggle fa fa-pencil-square-o fa-2" title="Click to edit this insight"></i>
    </div>
  <div class="panel-desktop-right">
    <div class="panel-body">
      <figure class="insight-hero-image preview-hero">
        <img src="https://www.thinkup.com/assets/images/insights/2014-05/subway.jpg" alt="New York City subway car" class="img-responsive">
        <figcaption class="insight-hero-credit">Photo: Julian Dunn</figcaption>
      </figure>
            <div class="panel-body-inner">
            <p id="insight-text-"><span class="preview-body"></span></p>


           <!-- single user -->
           <ul class="body-list user-list body-list-show-some all-items-visible preview-user">
             <li class="list-item">

             <div class="user">
               <a href="https://twitter.com/intent/user?user_id=59503113">
                 <img src="https://pbs.twimg.com/profile_images/513778004179165184/p5oQ46TQ_normal.png" alt="mark little" class="img-circle pull-left user-photo">
               </a>
               <div class="user-about">
                 <div class="user-name"><a href="https://twitter.com/intent/user?user_id=59503113">mark little <i class="fa fa-twitter icon icon-network"></i></a></div>
                 <div class="user-text">
                   <p>                    59,803 followers
                   </p>
                   <p>Founder/CEO of @Storyful</p>
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
               <a href="https://twitter.com/intent/user?user_id=59503113">
                 <img src="https://pbs.twimg.com/profile_images/513778004179165184/p5oQ46TQ_normal.png" alt="mark little" class="img-circle pull-left user-photo">
               </a>
               <div class="user-about">
                 <div class="user-name"><a href="https://twitter.com/intent/user?user_id=59503113">mark little <i class="fa fa-twitter icon icon-network"></i></a></div>
                 <div class="user-text">
                   <p>                    59,803 followers
                   </p>
                   <p>Founder/CEO of @Storyful</p>
                 </div>
               </div>
             </div>
             </li>
             <li class="list-item" style="display: list-item;">

             <div class="user">
               <a href="https://twitter.com/intent/user?user_id=478973822">
                 <img src="https://pbs.twimg.com/profile_images/1892959002/greenflower_normal.jpg" alt="Green City Media" class="img-circle pull-left user-photo">
               </a>
               <div class="user-about">
                 <div class="user-name"><a href="https://twitter.com/intent/user?user_id=478973822">Green City Media <i class="fa fa-twitter icon icon-network"></i></a></div>
                 <div class="user-text">
                   <p>                    13,882 followers
                   </p>
                   <p>Social Media and Green Projects. Follow our CEO @aTravelAmerican, Michael Green @Green4h2o. #4h2o</p>
                 </div>
               </div>
             </div>
             </li>
             <li class="list-item" style="display: list-item;">

             <div class="user">
               <a href="https://twitter.com/intent/user?user_id=248711375">
                 <img src="https://pbs.twimg.com/profile_images/459312577198047233/GJ_eZ-P0_normal.png" alt="QVSource" class="img-circle pull-left user-photo">
               </a>
               <div class="user-about">
                 <div class="user-name"><a href="https://twitter.com/intent/user?user_id=248711375">QVSource <i class="fa fa-twitter icon icon-network"></i></a></div>
                 <div class="user-text">
                   <p>                    1,426 followers
                   </p>
                   <p>QVSource - The QlikView API Connector. Making Social Media and other Web API Data easily accessible to QlikView Users. Qlik Technology Partner of the Year 2014.</p>
                 </div>
               </div>
             </div>
             </li>
           </ul>
           <!-- end list of users -->


           <!-- bar chart -->
<div id="chart_40194" class="preview-bar weekly_chart" style="position: relative;"><div dir="ltr" style="position: relative; width: 520px; height: 250px;"><div style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%;"><svg width="520" height="250" aria-label="A chart." style="overflow: hidden;"><defs id="defs"><clipPath id="_ABSTRACT_RENDERER_ID_0"><rect x="200" y="0" width="320" height="250"></rect></clipPath></defs><g><rect x="200" y="0" width="320" height="250" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g clip-path="url(#_ABSTRACT_RENDERER_ID_0)"><g><rect x="200" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect><rect x="280" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect><rect x="360" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect><rect x="439" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect><rect x="519" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#cccccc"></rect></g><g><rect x="201" y="5" width="5" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="30" width="3" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="55" width="2" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="80" width="2" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="200.858875" y="105" width="1.4354999999999905" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="200.938625" y="129" width="1.7545000000000073" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="154" width="2" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="179" width="6" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="201" y="204" width="16" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="200.9785" y="229" width="1.9140000000000157" height="15" stroke="none" stroke-width="0" fill="#f576b5"></rect><rect x="207" y="5" width="73" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="205" y="30" width="151" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="204" y="55" width="63" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="204" y="80" width="112" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="203" y="105" width="81" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="203" y="129" width="78" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="204" y="154" width="76" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="208" y="179" width="24" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="218" y="204" width="2" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="203" y="229" width="13" height="15" stroke="none" stroke-width="0" fill="#b3487c"></rect><rect x="281" y="5" width="90" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="357" y="30" width="110" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="268" y="55" width="87" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="317" y="80" width="76" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="285" y="105" width="41" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="282" y="129" width="55" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="281" y="154" width="54" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="233" y="179" width="29" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="221" y="204" width="8" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect><rect x="217" y="229" width="36" height="15" stroke="none" stroke-width="0" fill="#8f3963"></rect></g><g><rect x="200" y="0" width="1" height="250" stroke="none" stroke-width="0" fill="#333333"></rect></g></g><g></g><g><g><text text-anchor="end" x="198" y="17.15" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Lifehack: Turn a 5-minute tas...</text><rect x="2" y="6.949999999999999" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="42.05" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">It's plain as day that the cops ...</text><rect x="2" y="31.849999999999994" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="66.95" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Sometimes I want to wrap my...</text><rect x="2" y="56.75" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="91.85" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Mike Brown was a man. He liv...</text><rect x="2" y="81.64999999999999" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="116.75" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">An important, cogent breakd...</text><rect x="2" y="106.55" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="141.64999999999998" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Take off the helmets, holster t...</text><rect x="2" y="131.45" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="166.54999999999998" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Basic crowd management for ...</text><rect x="2" y="156.35" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="191.45" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">Let's be clear: The threat of vi...</text><rect x="2" y="181.25" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="216.34999999999997" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">The ice bucket challenge has ...</text><rect x="2" y="206.14999999999998" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g><g><text text-anchor="end" x="198" y="241.24999999999997" font-family="Libre Baskerville, georgia, serif" font-size="12" stroke="none" stroke-width="0" fill="#000000">I don't have anything left, I ha...</text><rect x="2" y="231.04999999999998" width="196" height="12" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect></g></g></g><g></g></svg></div></div><div style="display: none; position: absolute; top: 260px; left: 530px; white-space: nowrap; font-family: Arial; font-size: 12px; font-weight: bold;">456</div><div></div></div>
           <!-- end bar chart -->

           <!-- pie chart -->
<div class="preview-pie" id="chart_44344" style="position: relative;"><div dir="ltr" style="position: relative; width: 300px; height: 250px;"><div style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%;"><svg width="300" height="250" aria-label="A chart." style="overflow: hidden;"><defs id="defs"></defs><rect x="0" y="0" width="300" height="250" stroke="none" stroke-width="0" fill="#ffffff"></rect><g><rect x="188" y="48" width="55" height="26" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g><rect x="188" y="48" width="55" height="10" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g><text text-anchor="start" x="202" y="56.5" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#222222">One thing</text></g><rect x="188" y="48" width="10" height="10" stroke="none" stroke-width="0" fill="#f576b5"></rect></g><g><rect x="188" y="64" width="55" height="10" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g><text text-anchor="start" x="202" y="72.5" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#222222">Another thing</text></g><rect x="188" y="64" width="10" height="10" stroke="none" stroke-width="0" fill="#b3487c"></rect></g></g><g><path d="M115,126L115,69A57,57,0,0,1,164.36344,154.5L115,126A0,0,0,0,0,115,126" stroke="#ffffff" stroke-width="1" fill="#f576b5"></path><text text-anchor="start" x="128.0845073354202" y="113.57407726443193" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#ffffff">33.3%</text></g><g><path d="M115,126L164.363448015713,154.5A57,57,0,1,1,115,69L115,126A0,0,0,1,0,115,126" stroke="#ffffff" stroke-width="1" fill="#b3487c"></path><text text-anchor="start" x="72.9154926645798" y="145.42592273556804" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#ffffff">66.7%</text></g><g></g></svg></div></div><div style="display: none; position: absolute; top: 260px; left: 310px; white-space: nowrap; font-family: Arial; font-size: 10px; font-weight: bold;">2 (66.7%)</div><div></div></div>
            <!-- end pie chart -->


            <!-- line chart -->
<div id="chart_" class="chart preview-line" style="position: relative;"><div dir="ltr" style="position: relative; width: 290px; height: 200px;"><div style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%;"><svg width="290" height="200" aria-label="A chart." style="overflow: hidden;"><defs id="defs"><clipPath id="_ABSTRACT_RENDERER_ID_4"><rect x="56" y="38" width="179" height="124"></rect></clipPath></defs><rect x="0" y="0" width="290" height="200" stroke="none" stroke-width="0" fill="#ffffff"></rect><g><rect x="56" y="38" width="179" height="124" stroke="none" stroke-width="0" fill-opacity="0" fill="#ffffff"></rect><g clip-path="url(#_ABSTRACT_RENDERER_ID_4)"><g><rect x="74" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="95" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="115" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="136" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="157" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="178" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="198" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="161" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="130" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="100" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="69" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="38" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect></g><g><rect x="56" y="38" width="1" height="124" stroke="none" stroke-width="0" fill="#eeeeee"></rect><rect x="56" y="161" width="179" height="1" stroke="none" stroke-width="0" fill="#eeeeee"></rect></g><g><path d="M56.5,148.175L77.26666666666667,143.05L98.03333333333333,137.925L118.80000000000001,132.8L139.56666666666666,127.67500000000001L160.33333333333331,122.55000000000001L181.10000000000002,117.42500000000001L213.73333333333335,117.42500000000001L234.5,69.25000000000001" stroke="#fc939e" stroke-width="2" fill-opacity="1" fill="none"></path></g></g><g><circle cx="56.5" cy="148.175" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="77.26666666666667" cy="143.05" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="98.03333333333333" cy="137.925" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="118.80000000000001" cy="132.8" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="139.56666666666666" cy="127.67500000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="160.33333333333331" cy="122.55000000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="181.10000000000002" cy="117.42500000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="213.73333333333335" cy="117.42500000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle><circle cx="234.5" cy="69.25000000000001" r="3" stroke="none" stroke-width="0" fill="#fc939e"></circle></g><g><g><text text-anchor="end" x="76.05" y="175.36121593216774" font-family="Arial" font-size="10" transform="rotate(-30 76.05 175.36121593216774)" stroke="none" stroke-width="0" fill="#999999">Jul 15</text></g><g><text text-anchor="end" x="117.58333333333334" y="175.36121593216774" font-family="Arial" font-size="10" transform="rotate(-30 117.58333333333334 175.36121593216774)" stroke="none" stroke-width="0" fill="#999999">Jul 29</text></g><g><text text-anchor="end" x="159.11666666666667" y="175.36121593216774" font-family="Arial" font-size="10" transform="rotate(-30 159.11666666666667 175.36121593216774)" stroke="none" stroke-width="0" fill="#999999">Aug 12</text></g><g><text text-anchor="end" x="200.65" y="175.36121593216774" font-family="Arial" font-size="10" transform="rotate(-30 200.65 175.36121593216774)" stroke="none" stroke-width="0" fill="#999999">Aug 26</text></g><g><text text-anchor="end" x="46" y="165" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">760</text></g><g><text text-anchor="end" x="46" y="134.25" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">820</text></g><g><text text-anchor="end" x="46" y="103.5" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">880</text></g><g><text text-anchor="end" x="46" y="72.75000000000001" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">940</text></g><g><text text-anchor="end" x="46" y="42.000000000000014" font-family="Arial" font-size="10" stroke="none" stroke-width="0" fill="#999999">1,000</text></g></g></g><g></g></svg></div></div><div style="display: none; position: absolute; top: 210px; left: 300px; white-space: nowrap; font-family: Arial; font-size: 10px; font-weight: bold;">846</div><div></div></div>
            <!-- end line chart -->


            <!-- single post -->
            <ul class="body-list tweet-list body-list-show-all preview-post">
                <li class="list-item">
                    <blockquote class="tweet">
                       <a href="https://twitter.com/intent/user?user_id=7861312" title="feliciaday"><img src="https://pbs.twimg.com/profile_images/429050041500590080/8Sn16xxH_normal.jpeg" alt="feliciaday" width="60" height="60" class="img-circle pull-left tweet-photo user-photo"></a>
                       <div class="byline"><a href="https://twitter.com/intent/user?user_id=7861312" title="feliciaday"><strong>Felicia Day</strong> <span class="username">@feliciaday</span></a></div>
                       <div class="tweet-body">I just backed Word Realms on <a href="https://twitter.com/intent/user?screen_name=Kickstarter">@Kickstarter</a><span> <a href="http://t.co/YZvO0yW3">http://t.co/YZvO0yW3</a></span></div>
                       <div class="tweet-actions">
                           <a href="https://twitter.com/feliciaday/status/212418013163552768" class="tweet-action tweet-action-permalink">Jun 12, 2012</a>
                           <a href="http://twitter.com/intent/tweet?in_reply_to=212418013163552768" class="tweet-action"><i class="fa fa-reply icon"></i></a>
                          <a href="http://twitter.com/intent/retweet?tweet_id=212418013163552768" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
                          <a href="http://twitter.com/intent/favorite?tweet_id=212418013163552768" class="tweet-action"><i class="fa fa-star icon"></i></a>
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
    <a href="http://twitter.com/intent/favorite?tweet_id=508969172722675712" class="tweet-action"><i class="fa fa-star icon"></i></a>
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
    <a href="http://twitter.com/intent/favorite?tweet_id=505416713580867585" class="tweet-action"><i class="fa fa-star icon"></i></a>
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
    <a href="http://twitter.com/intent/favorite?tweet_id=501926686527479808" class="tweet-action"><i class="fa fa-star icon"></i></a>
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
        <i class="fa fa--square icon icon-network"></i>
        <a class="permalink" href="http:///?u=&n=&d=2014-06-16&s=facebookprofileprompt">Jun 16</a>
      </div>
      <div class="share-menu">
          <i class="fa fa-lock icon icon-share text-muted" title="This  account and its insights are private."></i>
      </div>
    </div>
  </div>
</div>

  </div><!-- end stream -->
</div><!-- end container -->

</div><!-- end page-content -->

<style>
    .previewer label { width: 118px; background-color: #46BCFF; color: white; height: 20px; padding-left: 10px}
    .previewer input, .previewer textarea { width: 600px; }
    .previewer textarea { margin-bottom: 5px; }
    .previewer div .cb { width: 20px; margin-left: 5px; }
    .editor {
      position: absolute;
      top: 5px;
      right: 5px;
      background: white;
      border-radius: 3px;
      width: 20px;
      height: 20px;
      font-size: 25px;
      cursor: pointer;
    }
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
        'emphasis', 'hero'
        ];
        var embeds = [
          'post', 'posts', 'user', 'users',
          'bar', 'line', 'pie'
        ]

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

          var embed = $("input:radio[name=embeds]:checked").attr('id');
          queryString.push('embed', embed);

          queryString.push('preview', '1');
        }
        var update_preview = function () {
          for (var i=0; i < inputs.length; i++) {
            var input = inputs[i];
            var value = $('#'+input).val()
            // if (value !== '') {
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
            // }
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
            }
            else {
              $('.preview-'+embed).hide();
            }
          }
        };

        $('.previewer :input').change(update_preview).keyup(update_preview);
        $('.previewer :input').blur(update_url);
        $('.editToggle').click(function() {
          $('.previewer').toggle();
          $('.editor').toggle();
          return false;
        });

        if (window.location.search) {
          var params = searchToObject();
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
        }
        else {
          $('.previewer').show();
          $('.editor').hide();
        }
          update_preview();
  });
</script>

<script src="assets/js/vendor/bootstrap.min.js"></script>
<script src="assets/js/vendor/jpanelmenu.js"></script>
<script src="//platform.twitter.com/widgets.js"></script>
<script src="assets/js/thinkup.js "></script>
<script type="text/javascript" src="assets/js/linkify.js"></script>
</body>
</html>
