<?php
    $pageTitle = 'House On Wire';

// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");
$widgetsData = array();
$result = pg_query( $db, "select * from onewire_meta join onewire using (id)" ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );
while ($row = pg_fetch_array($result))
{
	$tmp = array();
	
	$tmp['id'] = $row['id'];
        $tmp['title'] = $row['name'];
        $tmp['unit'] = $row['unity'];
        $tmp['top'] = $row['top'];
        $tmp['left'] = $row['left'];
        $tmp['level'] = $row['level'];

	array_push($widgetsData, $tmp);
}

        $tmp = array();

        $tmp['id'] = 'RdC';
        $tmp['title'] = 'Rez de Chaussée';
        $tmp['unit'] = 'C';
        $tmp['top'] = 340;
        $tmp['left'] = 510;
        $tmp['level'] = 2;

        array_push($widgetsData, $tmp);

        $tmp = array();

        $tmp['id'] = 'Etage';
        $tmp['title'] = 'Etage';
        $tmp['unit'] = 'C';
        $tmp['top'] = 265;
        $tmp['left'] = 610;
        $tmp['level'] = 2;

        array_push($widgetsData, $tmp);

?>

<!DOCTYPE html>
<html>
    <head>
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
        <link rel="stylesheet" type="text/css" href="/css/main.css" />
        <link rel="stylesheet" type="text/css" href="/css/tooltip.css" />
        <link rel="stylesheet" type="text/css" href="/css/tab.css" />
        <link rel="stylesheet" type="text/css" href="/css/popup.css" />
        <link rel="stylesheet" type="text/css" href="/css/widget.css" />

        <title>House On Wire</title>
    </head>
    <body>
        <div id="background">
    <h1 id="pageTitle"><img src="/image/house.png"/><?= $pageTitle; ?></h1>
            <div class="tabs">
                <div class="tabsButtons">
    

                    <div class="tab level2 selected" data-tab-name="level2">
                        Vue d'ensemble
                    </div>
	            <div class="tab level0" data-tab-name="level0">
                        Rez de chaussée
                    </div>
                    <div class="tab level1" data-tab-name="level1">
                        Etage
                    </div>
                    <div class="tab graph" data-tab-name="graph">
                        Graph général Température
                    </div>
                    <div class="tab graph_papp" data-tab-name="graph_papp">
                        Graph Consommation
                    </div>
                    <div class="tab graph_lumi" data-tab-name="graph_lumi">
                        Graph Luminosite
                    </div>
                    <div class="tab logs" data-tab-name="logs">
                        Logs
                    </div>
		

                </div>
                <div class="tabsContainers">
                    <div class="tabBody level2">
                        <div id="level2" class="widgets">

<div id="widget999" style="top: 450px; left: 10px;" class="widget">
	<span class="widgetTitle">Rez de Chaussée</span><span> : </span><span class="widgetTemperatureValue">23.06</span><span>&nbsp;</span><span>C</span>
	<div class="tooltipAnchor">
		<img class="tooltipHandle" src="/image/info.png">
		<div class="tooltipContent">
			<p class="widgetContentTitle">Rez de Chaussée : <span class="widgetUpdate">07-09-2012 15:38</span></p>
			<div class="widgetContent">
				<div class="widgetDelta">
					<p class="widgetDeltaTitle">Moyenne :</p>
					<div class="widgetDeltaPlusOneHour">
						<p class="widgetDeltaFrequency">1h</p>
						<p class="widgetDeltaImage increase">&nbsp;</p>
						<p class="widgetDeltaValue">22.96</p>
						<p class="widgetDeltaUnit">C</p>
					</div>
					<div class="widgetDeltaPlusOneDay">
						<p class="widgetDeltaFrequency">1j</p>
						<p class="widgetDeltaImage decrease">&nbsp;</p>
						<p class="widgetDeltaValue">23.19</p>
						<p class="widgetDeltaUnit">C</p>
					</div>
					<p class="widgetDeltaTitle">Min / Max 24H :</p>
					<div class="widgetDeltaPlusOneDay">
						<p class="widgetDeltaImageMax increase">&nbsp;</p>
						<p class="widgetDeltaValueMax">26</p>
						<p class="widgetDeltaUnit">C</p>
					</div>
					<div class="widgetDeltaPlusOneDay">
						<p class="widgetDeltaImageMin decrease">&nbsp;</p>
						<p class="widgetDeltaValueMin">20.06</p>
						<p class="widgetDeltaUnit">C</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<img class="popupLink" data-type="graph" data-parameters="%7B%22id%22%3A%22RdC%22%7D" src="/image/graph.png">
	<br/>
        <span class="widgetTitle">Rez de Chaussée</span><span> : </span><span class="widgetTemperatureValue">23.06</span><span>&nbsp;</span><span>C</span>

</div>


			</div>
                    </div>
                    <div class="tabBody level0 hidden">
                        <div id="level0" class="widgets"></div>
                    </div>
                    <div class="tabBody level1 hidden">
                        <div id="level1" class="widgets"></div>
                    </div>
                    <div class="tabBody graph hidden">
                        <div id="graph">
                            <iframe frameborder="0" scrolling="no" width="1200px" 
                                    height="630px" src="/chart_full.php">
                            </iframe>
                        </div>
                    </div>
                    <div class="tabBody graph_papp hidden">
                        <div id="graph_papp">
                            <!--iframe frameborder="0" scrolling="no" width="1200px" 
                                    height="630px" src="/chart_papp.php">
                            </iframe-->
                        </div>
                    </div>
                    <div class="tabBody graph_lumi hidden">
                        <div id="graph_lumi">
                            <iframe frameborder="0" scrolling="no" width="1200px" 
                                    height="630px" src="/chart.php?id=26.24AE60010000">
                            </iframe>
                        </div>
                    </div>
                    <div class="tabBody logs hidden">
                    <textarea id="txt_log" style="width: 1180px; height: 630px; "></textarea>
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
        <script type="text/javascript" src="/js/widget.js"></script>
    </body>
</html>
