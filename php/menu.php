<?php
if (!array_key_exists('hiwa-user', $_COOKIE) ||
    !array_key_exists('hiwa-role', $_COOKIE)) {
	Header("Location: login.php");
	exit();
}
$role=$_COOKIE['hiwa-role'];
?>
<html>
<head>
<title>HIWA Main Menu</title>
<link rel="stylesheet" type="text/css" href="style.css"/>
</head>

<body>
<?php require 'header.php';?>
<div class="title">HIWA Main Menu</div>
<div class="subtitle">Logged in as <?php echo .htmlentities($_COOKIE['hiwa-user']);?>
	(<?php echo .htmlentities($role); ?>)
</div>

<div class="menu">
	<a href="orders.php">Orders</a>
	<p/>
	<?php if ($role == "manager" || $role == "admin") {
		echo '<a href="customers.php">Customers</a>';
		echo '<p/>';
	}?>
	<a href="products.php">Products</a>
	<p/>
	<?php if ($role == "admin") {
		echo '<a href="users.php">System users</a>';
		echo '<p/>';
	}?>
	<a href="logout.php">Logout</a>
	<p/>
	Flag: <i>4cb2d2569de028c5fab3301a7ef5a679</i>
</div>
	
</body>
</html>

