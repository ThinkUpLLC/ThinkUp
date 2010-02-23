{include file="session._header.tpl" title="Password Recovery"}<h1>Forgot Password</h1>
<div class="content">
    {if isset($errormsg)}
    <div class="error">
        {$errormsg} 
    </div>{/if}
    {if isset($successmsg)}
    <div class="success">
        {$successmsg} 
    </div>{/if}
    <br/>
    <br/>
    <form name="form1" method="post" action="">
        <table border="0" width="100%" cellpadding="5" cellspacing="0">
            <tr>
                <td align="right">
                    Enter your email address:
                </td>
                <td>
                    <input name="email" type="text" id="email"><input type="submit" name="Submit" value="Send">
                </td>
                </td>
            </tr>
        </table>
    </form>
</div>
<br/>
<br/>
<center>
    Already have an account? <a href="login.php">Sign in</a>
</center>
<p>
    Set up your own <a href="http://thinktankapp.com">ThinkTank</a>
</p>
<p>
    It is nice to be nice
</p>
</center>
</body>
</html>
