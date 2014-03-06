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
        <link rel="stylesheet" type="text/css" href="/css/popup.css" />
    
    <title>House On Wire</title>
    </head>
    <body>

<?
        insert_box3($db, 'THGR228N.t', 'THGR228N.h', 'BTHR918N.p','THGR228N.b','THGR228N.s');
        insert_box_rain($db, 'PCR918N');
?>
<br/>
<?
        insert_box2($db, 'BTHR918N.t', true, 'BTHR918N.h','BTHR918N.b','BTHR918N.s');
        insert_box1($db, 'THGR122NX.t', false, 'THGR122NX.h','THGR122NX.b','THGR122NX.s');
        insert_box1($db, 'THN122N.t', true, false, 'THN122N.b','THN122N.s');
?>
<br/>
<br/>
<?
	insert_box_elect($db);
?>

        <script type="text/javascript" src="/js/class.js"></script>
        <script type="text/javascript" src="/js/jquery.min.js"></script>
        <script type="text/javascript" src="/js/jquery.ui.min.js"></script>
        <script type="text/javascript" src="/js/utils/viewport.js"></script>
        <script type="text/javascript" src="/js/utils/xhrRequest.js"></script>
        <script type="text/javascript" src="/js/utils/xDomainRequest.js"></script>


        <script type="text/javascript" src="/js/popup.js"></script>
        <script type="text/javascript" src="/js/popup/abstract.js"></script>
        <script type="text/javascript" src="/js/popup/graph.js"></script>
        <script type="text/javascript" src="/js/popup/graphRain.js"></script>
        <script type="text/javascript" src="/js/popup/graphTemp.js"></script>
        <script type="text/javascript" src="/js/popup/graphPuissanceApparente.js"></script>
        <script type="text/javascript" src="/js/popup/graphConsoElect.js"></script>

</body>
</html>
