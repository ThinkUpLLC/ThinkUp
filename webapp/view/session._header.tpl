<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <title>ThinkTank {$title}</title>
  <link rel="shortcut icon" href="{$cfg->site_root_path}assets/img/favicon.ico">
  <style type="text/css">{literal}
html {
  background: #EEEEEA;
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size:14.5px;
}

a,
a:link,
a:visited {
  text-decoration: none;
  color: #0060E0;
}

a:hover {
  text-decoration: underline;
  color: red;
}

a:visited {
  color: #000;
  text-decoration: underline;
}

h1 {
  font-size: x-large;
}

h3 {
  font-weight: 800;
}

.tweet {
  padding: 10px;
}

.content {
  width: 600px;
  background-color: white;
  border: solid 1px grey;
  text-align: left;
}

.tweetmeta {
  text-align: right;
}

small {
  color: grey;
}

small a:visited {
  color: grey;
  text-decoration: underline;
}

/******* Tweet Formatting ********/

.individual-tweet {
  padding: 10px;
  margin-top: 10px;
}

.reply {
  padding-left: 85px;
}

.private {
  border: 1px dotted #666;
  background-color: #EEE;
}

.person-info {
  float: left;
  margin-right: 10px;
  width: 80px;
  text-align: center;
}

li {
  list-style: none;
}

li.individual-tweet h3 a {
  font-size: x-small;
  color: #666;
}

li.individual-tweet h4,
li.individual-tweet form {
  font-size: xx-small;
  /* visibility:hidden; */
}

li.individual-tweet:hover h4,
li.individual-tweet:hover form {
  visibility: visible;
  color: #666;
}

li.individual-tweet h4.reply-count {
  font-size: medium;
  padding: 0;
  margin: 0;
}
 
li.individual-tweet:hover h3 a {
  color: #0060E0;
}

li.individual-tweet h3 a.most-popular {
  font-size: medium;
  font-weight: bold;
}

.avatar {
  border: solid 1px #CCC;
}

.error {
  background-color: #FF8080;
  padding: 10px;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  color: #FFF;
  text-align: center;
  font-weight: bold;
}

.success {
  background-color: #BFDFBF;
  padding: 10px;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  text-align: center;
  font-weight: bold;
}

.info {
  background-color: #FFFFAD;
  padding: 10px;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  color: #FFF;
  text-align: center;
  font-weight: bold;
}

/******* /Tweet Formatting ********/

  {/literal}</style>

</head>

<body>
<center>