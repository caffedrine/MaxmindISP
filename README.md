## MaxMind ISP Database - No API

Use MaxMind databases to correlate IP Addresses with their ISP Organization!

This version uses CSV databases imported to a local MySql server!

No official API are used so this works very SLOW compared to versions which are using official APIs. The main advantage is that there are no external dependencies and is very compact!

Database used with PHP script: https://dev.maxmind.com/geoip/legacy/geolite/ (.CSV version)

## Import database

Because of it's size, importing database may be tricky from phpmyadmin as you may have ti split it in multiple parts! So I would do this from terminal:

1. Create an empty database with name **Maxmind_GeoLite_ASN**:

	```sql
	$ mysql -u root -p
	Enter password: 
	mysql> CREATE DATABASE Maxmind_GeoLite_ASN;
	mysql> USE Maxmind_GeoLite_ASN;
	```

2. Create a table inside database called **blocks** with 3 fields: ipNumStart - int(11), ipNumEnd - int(11), isp - varcher(128):

	```sql
	mysql> CREATE TABLE blocks ( ipNumStart int(11), ipNumEnd int(11), isp varchar(128) );
	```

3. To import database faster:

	```sql
	mysql> LOAD DATA LOCAL INFILE '~/GeoIPASNum2.csv' INTO TABLE Maxmind_GeoLite_ASN.blocks FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n';
	```


## Database structure:

Database must have the following structure and names:

```sql
mysql> SELECT DATABASE();
+---------------------+
| DATABASE()          |
+---------------------+
| Maxmind_GeoLite_ASN |
+---------------------+
```

```sql
mysql> SHOW TABLES;
+-------------------------------+
| Tables_in_Maxmind_GeoLite_ASN |
+-------------------------------+
| blocks                        |
+-------------------------------+
```

```sql
mysql> DESCRIBE blocks;
+------------+--------------+------+-----+---------+-------+
| Field      | Type         | Null | Key | Default | Extra |
+------------+--------------+------+-----+---------+-------+
| ipNumStart | int(11)      | NO   |     | NULL    |       |
| ipNumEnd   | int(11)      | NO   |     | NULL    |       |
| isp        | varchar(128) | NO   |     | NULL    |       |
+------------+--------------+------+-----+---------+-------+
```

## Usage

Make sure you have Apache2, PHP and MySQL services installed and started!

Just access http://localhost/MaxmindISP/index.php file and upload IP Addresses file (one per line)!