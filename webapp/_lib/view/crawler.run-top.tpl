{include file="_header.tpl" enable_bootstrap=1}

  <style type="text/css">
  {literal}
  body { background-color:white!important;}
  html {background:white!important;}


.control-group.warning {
  color: #c09853;
}
.control-group.error {
  color: #b94a48;
}
.control-group.success {
  color: #468847;
}
.table td.crawl-log-component {
    text-align : right;
    font-weight : bold;
}

  {/literal}
  </style>

<table class="table table-condensed table-striped">
    <thead><th>Time</th><th class="crawl-log-component">Component</th><th>Details</th></thead>
    <tbody>