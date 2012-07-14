{if $enable_bootstrap}
    {if $inline}
        
        {if $field}
            {if $success_msgs.$field}
             <span class="label label-success">
 
                   {if $success_msg_no_xss_filter}
                       {$success_msgs.$field}
                   {else}
                       {$success_msgs.$field|filter_xss}
                   {/if}

             </span>
            {/if}
            {if $error_msgs.$field}
             <span class="label label-error">
 
                   {if $error_msg_no_xss_filter}
                       {$error_msgs.$field}
                   {else}
                       {$error_msgs.$field|filter_xss}
                   {/if}

            </span>
            {/if}
            {if $info_msgs.$field}
            {if $success_msgs.$field OR $error_msgs.$field}<br />{/if}
            <span class="label label-info"> 

                     
                     {if $info_msg_no_xss_filter}
                        {$info_msgs.$field|filter_xss}
                     {else}
                        {$info_msgs.$field|filter_xss}
                     {/if}
                </p>
            </span>
            {/if}
        {else}
            {if $success_msg}
             <span class="label label-info" style="">
 
                   {if $success_msg_no_xss_filter}
                       {$success_msg}
                   {else}
                       {$success_msg|filter_xss}
                   {/if}

             </span>
            {/if}
            {if $error_msg}
             <span class="label label-error" style="">
 
                   {if $error_msg_no_xss_filter}
                       {$error_msg}
                   {else}
                       {$error_msg|filter_xss}
                   {/if}

            </span>
            {/if}
            {if $info_msg}
                {if $success_msg OR $error_msg}<br />{/if}
            <span class="label label-success"> 

                     
                     {if $info_msg_no_xss_filter}
                        {$info_msg}
                     {else}
                        {$info_msg|filter_xss}
                     {/if}
                </p>
            </span>
            {/if}
        {/if}


    {else}

        {if $field}
            {if $success_msgs.$field}
             <div class="alert alert-success">
                 <p>
                   {if $success_msg_no_xss_filter}
                       {$success_msgs.$field}
                   {else}
                       {$success_msgs.$field|filter_xss}
                   {/if}
                 </p>
             </div>
            {/if}
            {if $error_msgs.$field}
             <div class="alert alert-error">
                 <p>
                   {if $error_msg_no_xss_filter}
                       {$error_msgs.$field}
                   {else}
                       {$error_msgs.$field|filter_xss}
                   {/if}
                 </p>
            </div>
            {/if}
            {if $info_msgs.$field}
            {if $success_msgs.$field OR $error_msgs.$field}<br />{/if}
            <div class="alert alert-info"> 
                <p>
                     
                     {if $info_msg_no_xss_filter}
                        {$info_msgs.$field|filter_xss}
                     {else}
                        {$info_msgs.$field|filter_xss}
                     {/if}
                </p>
            </div>
            {/if}
        {else}
            {if $success_msg}
             <div class="alert alert-info" style="">
                 <p>
                   {if $success_msg_no_xss_filter}
                       {$success_msg}
                   {else}
                       {$success_msg|filter_xss}
                   {/if}
                 </p>
             </div>
            {/if}
            {if $error_msg}
             <div class="alert alert-error" style="">
                 <p>
                   {if $error_msg_no_xss_filter}
                       {$error_msg}
                   {else}
                       {$error_msg|filter_xss}
                   {/if}
                 </p>
            </div>
            {/if}
            {if $info_msg}
                {if $success_msg OR $error_msg}<br />{/if}
            <div class="alert alert-success"> 
                <p>
                     
                     {if $info_msg_no_xss_filter}
                        {$info_msg}
                     {else}
                        {$info_msg|filter_xss}
                     {/if}
                </p>
            </div>
            {/if}

        {/if}

    {/if}

{else}


{if $field}
    {if $success_msgs.$field}
     <div class="alert helpful">
         <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {if $success_msg_no_xss_filter}
               {$success_msgs.$field}
           {else}
               {$success_msgs.$field|filter_xss}
           {/if}
         </p>
     </div>
    {/if}
    {if $error_msgs.$field}
     <div class="alert urgent">
         <p>
           <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {if $error_msg_no_xss_filter}
               {$error_msgs.$field}
           {else}
               {$error_msgs.$field|filter_xss}
           {/if}
         </p>
    </div>
    {/if}
    {if $info_msgs.$field}
    {if $success_msgs.$field OR $error_msgs.$field}<br />{/if}
    <div class="alert stats" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
        <p>
             <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
             {if $info_msg_no_xss_filter}
                {$info_msgs.$field|filter_xss}
             {else}
                {$info_msgs.$field|filter_xss}
             {/if}
        </p>
    </div>
    {/if}
{else}
    {if $success_msg}
     <div class="alert helpful" style="">
         <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {if $success_msg_no_xss_filter}
               {$success_msg}
           {else}
               {$success_msg|filter_xss}
           {/if}
         </p>
     </div>
    {/if}
    {if $error_msg}
     <div class="alert urgent" style="">
         <p>
           <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
           {if $error_msg_no_xss_filter}
               {$error_msg}
           {else}
               {$error_msg|filter_xss}
           {/if}
         </p>
    </div>
    {/if}
    {if $info_msg}
    {if $success_msg OR $error_msg}<br />{/if}
    <div class="alert helpful" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
        <p>
             <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
             {if $info_msg_no_xss_filter}
                {$info_msg}
             {else}
                {$info_msg|filter_xss}
             {/if}
        </p>
    </div>
    {/if}
{/if}

{/if}