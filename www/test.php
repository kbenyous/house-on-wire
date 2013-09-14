<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");
	require('php/boxes.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <!--[if IE]>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
        <link rel="stylesheet" type="text/css" href="/css/boxes.css" />
        <title>House On Wire</title>
    </head>
    <body>


<?
	insert_box1($db, '10.A8EB65020800', true, '26.EAB360010000.v', 'PCR800.b', 'PCR800.s');
	insert_box1($db, '10.C0625A020800', true);
	insert_box1($db, '10.D6F865020800');
?>
<br/>
<?
        insert_box2($db, '10.A8EB65020800', true, '26.EAB360010000.v', 'PCR800.b', 'PCR800.s');
        insert_box2($db, '10.C0625A020800', true);
        insert_box2($db, '10.D6F865020800');
?>
<br/>
<?
	insert_box3($db, '28.BB1A53030000', '26.FF9E6E010000.v', '26.0B8D6E010000.v');
?>
<br/>


    </body>
</html>
