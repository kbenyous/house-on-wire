<?php

    // Lecture du fichier de conf
    $config = parse_ini_file('/etc/house-on-wire/house-on-wire.ini', true);
    $db = pg_connect(
        'host='.$config['bdd']['host'].' '.
        'port='.$config['bdd']['port'].' '.
        'dbname='.$config['bdd']['dbname'].' '.
        'user='.$config['bdd']['username'].' '.
        'password='.$config['bdd']['password'].' '.
        'options=\'--client_encoding=UTF8\''
    ) or die('Erreur de connexion au serveur SQLfgsdf');

    // Récuperation des levels
    $result = pg_query(
        $db,
        'select level, name, image, position from level order by position'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    $css = "";
    $tabsbuttons = "";
    $tabscontainers = "";
    $levels = array();
    while($row = pg_fetch_array($result)) {
        $levels[] = array(
            'level' => $row['level'],
            'name' => $row['name'],
            'image' => $row['image'],
            'position' => $row['position']
        );

        $css .= ".widgets#".$row['level']." {\n";
        $css .= "background: url('".$row['image']."') left top no-repeat transparent; !important\n";
        $css .= "}\n";

        if($row["position"] == 1)
        {
	    $selected = "selected";
	    $hidden = "";
        }
        else
        {
 	    $selected = "";
	    $hidden = "hidden";
        }
        $tabsbuttons .= "<div class=\"tab ".$row['level']." $selected\" data-tab-name=\"".$row['level']."\">\n";
        $tabsbuttons .= $row['name']."\n";
        $tabsbuttons .= "</div>\n";

        $tabscontainers .= "<div class=\"tabBody ".$row['level']." $hidden\">\n";
        $tabscontainers .= "<div id=\"".$row['level']."\" class=\"widgets\"></div>\n";

		if($row['level']=="meteo")
		{
			$tabscontainers .= "<a href='http://house.vitre.info/php/chart_rain_total.php?year=2013-01-01'>Graph pluviometrie</a><br/>";
		}
        $tabscontainers .= "</div>\n";

    }

    // Récupération des sondes
    $result = pg_query(
        $db,
	'select id, coalesce(o.name, om.name) as name, coalesce(o.unity, om.unity) as unity , top, "left", level, coalesce(o.type, om.type) as type from onewire_meta om left join onewire o using (id)'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    $widgetsData = array();
    while($row = pg_fetch_array($result)) {
        $widgetsData[] = array(
            'id' => $row['id'],
            'title' => $row['name'],
            'unit' => $row['unity'],
            'top' => $row['top'],
            'left' => $row['left'],
            'level' => $row['level'],
	    'type' => $row['type']
        );
    }


?>

<!DOCTYPE html>
<html>
    <head>
        <!--[if IE]>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
        <link rel="stylesheet" type="text/css" href="/css/main.css" />
        <link rel="stylesheet" type="text/css" href="/css/tooltip.css" />
        <link rel="stylesheet" type="text/css" href="/css/tab.css" />
        <link rel="stylesheet" type="text/css" href="/css/popup.css" />
        <link rel="stylesheet" type="text/css" href="/css/dashboard.css" />
        <link rel="stylesheet" type="text/css" href="/css/widget.css">
        <style type="text/css">
            <? echo $css; ?>
        </style>
        <title>House On Wire</title>
    </head>
    <body>
        <div id="background">
            <h1 id="pageTitle">
                <img src="/image/house.png"/>
                <span>House On Wire</span>
            </h1>
            <div class="tabs">
                <div class="tabsButtons">
		    <?
			echo $tabsbuttons;
		    ?>
                    <div class="tab stats" data-tab-name="stats">
                        Statistiques
                    </div>
                   <div class="tab logs" data-tab-name="logs">
                        Logs
                    </div>

                </div>
                <div class="tabsContainers">
                    <?
                        echo $tabscontainers;
                    ?>
                    <div class="tabBody stats hidden">
                        <div id="statistiques">
                            <iframe frameborder="0" scrolling="yes" width="1200px" 
                                    height="630px" src="php/statistiques.php">
                            </iframe>

			</div>
                    </div>

                    <div class="tabBody logs hidden">
                        <div id="logConsole"></div>
                    </div>

                </div>
            </div>
        </div>
        <script type="text/javascript">
            var widgetsData = <?= json_encode($widgetsData); ?>;
        </script>
        <script type="text/javascript" src="/js/class.js"></script>
        <script type="text/javascript" src="/js/jquery.min.js"></script>
        <script type="text/javascript" src="/js/jquery.ui.min.js"></script>
        <script type="text/javascript" src="/js/utils/viewport.js"></script>
        <script type="text/javascript" src="/js/utils/xhrRequest.js"></script>
        <script type="text/javascript" src="/js/utils/xDomainRequest.js"></script>
        <script type="text/javascript" src="/js/tooltip.js"></script>
        <script type="text/javascript" src="/js/tab.js"></script>
        <script type="text/javascript" src="/js/popup.js"></script>
        <script type="text/javascript" src="/js/popup/abstract.js"></script>
        <script type="text/javascript" src="/js/popup/graph.js"></script>
        <script type="text/javascript" src="/js/popup/graphPuissanceApparente.js"></script>
        <script type="text/javascript" src="/js/popup/graphFull.js"></script>
        <script type="text/javascript" src="/js/popup/graphConsoElect.js"></script>
        <script type="text/javascript" src="/js/log.js"></script>
        <script type="text/javascript" src="/js/dashboard.js"></script>
        <script type="text/javascript" src="/js/widget.js"></script>
	<script type="text/javascript" src="/js/dygraph-combined.js"></script>

	<? 
	//echo file_get_contents("php/chart_papp_live.php");
	include "php/chart_papp_live.php";
	?>
    </body>
</html>
