<?php
    // Lecture du fichier de conf
    $config = parse_ini_file('/etc/house-on-wire/house-on-wire.ini', true);
    $db = pg_connect(
        'host='.$config['bdd']['host'].' '.
        'port='.$config['bdd']['port'].' '.
        'dbname='.$config['bdd']['dbname'].' '.
        'user='.$config['bdd']['username'].' '.
        'password='.$config['bdd']['password'].' '.
        'options="--client_encoding=UTF8"'
    ) or die('Erreur de connexion au serveur SQL');
    $result = pg_query(
        $db,
        'select * from onewire_meta join onewire using (id)'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    $widgetsData = array();
    while($row = pg_fetch_array($result)) {
        $widgetsData[] = array(
            'id' => $row['id'],
            'title' => $row['name'],
            'unit' => $row['unity'],
            'top' => $row['top'],
            'left' => $row['left'],
            'level' => $row['level']
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
        <link rel="stylesheet" type="text/css" href="/css/widget.css" />

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
                    <div class="tab global selected" data-tab-name="global">
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
                    <div class="tabBody global">
                        <div id="global" class="widgets"></div>
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
                            height="630px" src="/php/chart_full.php"></iframe>
                        </div>
                    </div>
                    <div class="tabBody graph_papp hidden">
                        <div id="graph_papp">
                            <!--iframe frameborder="0" scrolling="no" width="1200px"
                            height="630px" src="/php/chart_papp.php">
                            </iframe-->
                        </div>
                    </div>
                    <div class="tabBody graph_lumi hidden">
                        <div id="graph_lumi">
                            <iframe frameborder="0" scrolling="no" width="1200px"
                            height="630px" src="/php/chart.php?id=26.24AE60010000"></iframe>
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
        <script type="text/javascript" src="/js/log.js"></script>
        <script type="text/javascript" src="/js/dashboard.js"></script>
        <script type="text/javascript" src="/js/widget.js"></script>
    </body>
</html>