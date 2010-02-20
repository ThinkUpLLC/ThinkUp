{strip}
<html>
<head>
    <link href="styles.css" rel="stylesheet" type="text/css">
</head>
<body>
{if isset($msg)}<div class="msg"> {$msg} </div>{/if}

<p>&nbsp;</p>
<table width="40%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
        <td bgcolor="#d5e8f9" class="mnuheader" >
           <div align="center"><font size="5"><strong>Login Members</strong></font></div>
        </td>
    </tr>
    <tr> 
        <td bgcolor="#e5ecf9" class="mnubody">
            <form name="form1" method="post" action="">
                <p>&nbsp;</p>
                <p align="center">Your Email <input name="email" type="text" id="email"></p>
                <p align="center"> Password: <input name="pwd" type="password" id="pwd"></p>
                <p align="center"><input type="submit" name="Submit" value="Login"></p>
                <p align="center"><a href="register.php">Register</a> | <a href="forgot.php">Forgot</a></p>
            </form>
        </td>
   </tr>
</table>
<br /><br />
<center>
    <p>
        <a href="http://thinktankapp.com">Set up your own ThinkTank instance</a><br /><br />
        Back to <a href="../public.php">the public timeline</a>.
    </p>
</center>
{/strip}
