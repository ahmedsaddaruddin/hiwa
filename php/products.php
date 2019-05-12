<?php
require 'config.phplib';

$msg="";
if (!isset($_SESSION['hiwa-user'])) || (!isset($_SESSION['hiwa-role'])) {
	Header("Location: login.php");
	exit();
}

$role=$_['hiwa-role'];

$nextAction = "blank";
if (array_key_exists('action', $_REQUEST) && array_key_exists('prodid', $_REQUEST)) {
	if ($_REQUEST['action'] == 'delete') {
		$conn = pg_connect('user='.$CONFIG['username'].
			' dbname='.$CONFIG['database']);
		$res = pg_query($conn, "DELETE FROM products WHERE 
			productid='".(int)$_REQUEST['prodid']."'"); //make sure that the id is always an integer
		if ($res === FALSE) {
			$msg = "Unable to remove customer";
		}
	} else if ($_REQUEST['action'] == 'edit') {
		$nextAction = "update";
		$conn = pg_connect('user='.$CONFIG['username'].
			' dbname='.$CONFIG['database']);
		// used prepare to protect against SQL injection
		$res = pg_prepare($conn,"edit_statement","select productid,productname,productdescr,msrp,imageurl from products where productid='".
			$retrieved_id."'");
		$result=pg_execute($conn,"edit_statement"); 
		$cache = pg_fetch_assoc($result);
		pg_free_result($res);
		pg_close($conn);
	}
} 

if (array_key_exists("a", $_REQUEST)) {
	if ($_REQUEST['a'] == 'Add Product') {
		if ($_FILES['prodimg']['tmp_name'] != "") {
			$imgname=$_FILES['prodimg']['name'];
			// && $imgname!='hiwa.png' will check if the uploaded image name is the same as of the 
			//application's logo, it won't allow it to upload.
			if (mime_content_type($_FILES['prodimg']['tmp_name']) != 'text/x-php' && $imgname!='hiwa.png')
			copy($_FILES['prodimg']['tmp_name'],
				$CONFIG['uploads'].'/'.$_FILES['prodimg']['name']);
		} else {
			$imgname='';
		}
			
		$conn = pg_connect('user='.$CONFIG['username'].
			' dbname='.$CONFIG['database']);
		$res = pg_query($conn, "INSERT INTO products
			(productid, productname, productdescr, msrp, imageurl)
			VALUES
			('".$_REQUEST['prodid']."', '".
			$_REQUEST['prodname']."', ".
			"'".$_REQUEST['proddesc']."', ".
			$_REQUEST['msrp'].", ".
			"'".$imgname."');");
		if ($res === FALSE) {
			$msg="Unable to create product.";
		}
	} elseif ($_REQUEST['a'] == 'Update product') {
		if ($_FILES['prodimg']['tmp_name'] != "") {
			$imgname=$_FILES['prodimg']['name'];
			copy($_FILES['prodimg']['tmp_name'],
				$CONFIG['uploads'].'/'.$_FILES['prodimg']['name']);
		} else {
			$imgname='';
		}
		$conn = pg_connect('user='.$CONFIG['username'].
			' dbname='.$CONFIG['database']);
		$res = pg_query($conn, "update products ".
			"set productname='".$_REQUEST['prodname']."',".
			"    productdescr='".$_REQUEST['proddesc']."',".
			"    msrp=".$_REQUEST['msrp'].",".
			"    imageurl='".$imgname."'".
			"where productid='".$_REQUEST['prodid']."'");
		$res = pg_query($conn, "commit;");
		if ($res === FALSE) {
			$msg="Unable to update product.";
		}
	}
}

?>

<html>
<head>
<title>HIWA Manage Products</title>
<link rel="stylesheet" type="text/css" href="style.css"/>
</head>

<body>
<?php require 'header.php';?>
<div class="title">HIWA Manage Products</div>
<div class="subtitle">Logged in as <?php echo $_COOKIE['hiwa-user'];?>(<?php 
	echo $role; ?>)
</div>

<?php
$conn = pg_connect("user=".$CONFIG['username']." dbname=".$CONFIG['database']);
if (array_key_exists("filter", $_REQUEST)) {
	$filter = "WHERE $_REQUEST[filter]";
} else {
	$filter = '';
}
$query = "SELECT * FROM products $filter";
echo "<!-- set request variable filter to manipulate table filter -->\n";
echo "<!-- $query -->";
$res = pg_query($query);
?>
<table class="users">
<tr>
	<th>ID</th>
	<th>Name</th>
	<th>Description</th>
	<th>MSRP</th>
	<th>Action</th>
</tr>
<?php
$count=1;
while (($row = pg_fetch_assoc($res)) != FALSE) {
	if ($count % 2 == 0) $class="even"; else $class="odd";
	$count++;
	echo "<tr class=\"$class\">";
	echo "<td>".$row['productid']."</td>";
	echo "<td>".$row['productname']."</td>";
	echo "<td>".$row['productdescr']."</td>";
	echo "<td>".$row['msrp']."</td>";
	echo "<td>";
	if ($row['imageurl'] != '') {
		echo '<img src="'.$CONFIG['images'].'/'.$row['imageurl'].'"'.
		' width="75">';
	}
	echo "</td>";
	echo "<td><a href=\"".$_SERVER['SCRIPT_NAME'].
		"?action=delete&prodid=".$row['productid']."\">delete</a>
		<a href=\"".$_SERVER['SCRIPT_NAME'].
		"?action=edit&prodid=".$row['productid']."\">edit</a>
	</td>";
	echo "</tr>";
}
pg_free_result($res);
pg_close($conn);
?>
</table>	
<p>
<?php if ($msg != "") echo '<div class="err">'.$msg.'</div>'; ?>
<form method="post" enctype="multipart/form-data"
	 action="<?php echo $_SERVER['SCRIPT_NAME'];?>">
<div class="section">Product</div>
<table>
<tr>
	<td>Product ID:</td>
	<td><input type="text" name="prodid" size="25"
	<?php if ($nextAction=="update") echo 'value="'.$cache['productid'].'"';?>
	></td>
</tr>
<tr>
	<td>Product Name:</td>
	<td><input type="text" name="prodname" size="25"
	<?php if ($nextAction=="update") echo 'value="'.$cache['productname'].'"';?>
	></td>
</tr>
<tr>
	<td>Product Description:</td>
	<td><textarea cols="60" rows="5" name="proddesc"><?php 
		if ($nextAction=="update") echo $cache['productdescr'];
	?></textarea></td>
</tr>
<tr>
	<td>Suggested Retail Price:</td>
	<td><input type="text" name="msrp" size="25"
	<?php if ($nextAction=="update") echo 'value="'.$cache['msrp'].'"';?>
	></td>
</tr>
<tr>
	<td>Upload product image:</td>
	<td><input type="file" name="prodimg"></td>
</tr>
</table>
<p>
<?php if ($nextAction == "update") $name="Update product"; 
else $name="Add Product";?>
<input type="submit" name="a" value="<?php echo $name;?>">
</form>
</body>
</html>
