<?php
require 'config.phplib';

$msg="";

if (!isset($_SESSION['hiwa-user'])) || (!isset($_SESSION['hiwa-role'])) {
	Header("Location: login.php");
	exit();
}

$role=$_SESSION['hiwa-role'];
if ($role != 'admin') Header("Location: menu.php");
?>
<html>
<head>
<title>HIWA Manage Users</title>
<link rel="stylesheet" type="text/css" href="style.css"/>
</head>

<body>
<?php require 'header.php';
	//used functions to validate that only valid IPV4 address are entered
	if (array_key_exists("ip", $_REQUEST)) {
	if(filter_var($_REQUEST[ip], FILTER_VALIDATE_IP)){
	echo "<P>pinging target IP address</P>";
	exec("ping -c 3 $_REQUEST[ip]", $out);
	echo "<div><pre>\r\n";
	echo implode("\r\n", $out)."\r\n";
	echo "</pre></div>";
}
	else{
		echo("Invalid IP!")
	}
}
?>

<form>
<table>
<tr>
<td>Check hostname</td>
<td><input type="text" name="ip" placeholder="IP address or hostname" width="50"></td>
</tr>
<tr>
<td colspan="2" style="text-align: right"><input type="submit" value="Check!"/></td>
</tr>
</table>
</form>

</body>
</html>
