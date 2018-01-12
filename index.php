<?php

# Display all kind of errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

# It may take a while for big lists - set execution time to unlimited
ini_set('max_execution_time', 0);
ignore_user_abort(false);

# Buffered output - print while script is running
ob_implicit_flush(true);
ob_start();

# Download databases:
# IP Numbers: https://dev.maxmind.com/geoip/legacy/geolite/
# or
# IP Blocks:  https://dev.maxmind.com/geoip/geoip2/geolite2/
# and premium database:
# Info: https://www.maxmind.com/en/geoip2-isp-database
# To import easily:

/**
 * Database structure must be:
 *
 *  mysql> SELECT DATABASE();
 *  +---------------------+
 *  | DATABASE()          |
 *  +---------------------+
 *  | Maxmind_GeoLite_ASN |
 *  +---------------------+
 *
 *  mysql> SHOW TABLES;
 *  +-------------------------------+
 *  | Tables_in_Maxmind_GeoLite_ASN |
 *  +-------------------------------+
 *  | blocks                        |
 *  +-------------------------------+
 *
 *  mysql> DESCRIBE blocks;
 *  +------------+--------------+------+-----+---------+-------+
 *  | Field      | Type         | Null | Key | Default | Extra |
 *  +------------+--------------+------+-----+---------+-------+
 *  | ipNumStart | int(11)      | NO   |     | NULL    |       |
 *  | ipNumEnd   | int(11)      | NO   |     | NULL    |       |
 *  | isp        | varchar(128) | NO   |     | NULL    |       |
 *  +------------+--------------+------+-----+---------+-------+
 */

# Database connection credentials
$db_name = "Maxmind_GeoLite_ASN";
$db_user = "maxmind";
$db_pass = "maxmindmaxmind123!";
$db_host = "localhost";

# Connect and select database then provide a handler
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

# Check connection
if($conn->connect_error)
{
	die("Database connection failed: " . $conn->connect_error);
}

# Function used to extract IS by IP from DB
function get_isp(mysqli $conn, $ip)
{

	$ipnum = ip2long($ip);

	$query_str = "SELECT * FROM `blocks` WHERE '$ipnum' >= `ipNumStart` AND '$ipnum' <= `ipNumEnd`";

	$result = $conn->query($query_str);

	if(!$result)
		return "QUERY_FAIL";

	if($result->num_rows <= 0)
		return "unknown";

	if($row = $result->fetch_assoc())
		return $row['isp'];
}

# Special function to print even when script is running
function output($str)
{
	echo $str;
	ob_flush();
	flush();
}

# Global variable used to store as an array() all IPs uploaded from file
$IPs_LIST = array();

# Handle file uploading
if(isset($_FILES['fileToUpload'], $_REQUEST['action']))
{
	if($_REQUEST['action'] != "upload")
	{
		echo "Invalid action!";
		die();
	}

	$fileContent = file_get_contents($_FILES['fileToUpload']['tmp_name']);

	$handle = @fopen($_FILES['fileToUpload']['tmp_name'], "r");
	if($handle)
	{
		while(($buffer = fgets($handle, 4096)) !== false)
		{
			// Assuming that each line is an valid ip address, IP are pushed back into an array
			array_push($IPs_LIST, trim($buffer) );	// trim() is very important!
		}
		fclose($handle);
	}
	else
	{
		die("PHP fopen() failed :(");
	}

	# Counter to print current IP
	$i = 1;

	# Check every ip then print it
	foreach($IPs_LIST as $IP)
	{
		# Get ISP
		$ISP = get_isp($conn, $IP);

		# Print result
		output($i++ . "->" . $IP . "->" . $ISP . "<br>");
	}

	# Final
	ob_end_flush();
	$conn->close();
	exit();
}
?>

<!DOCTYPE html>
<html>
<body>

<form action="index.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="hidden" name="action" value="upload">
    <input type="submit" value="Upload" name="submit">
</form>

</body>
</html>
