<?php
    $pageTitle = 'Chez PY';
    $widgetsData = array(
        array(
            'id' => '10.C0625A020800',
            'title' => 'Palier',
            'unit' => 'C',
            'top' => 233,
            'left' => 594,
            'level' => '1'
        ),
        array(
            'id' => '10.28BD65020800',
            'title' => 'Chambre Justine',
            'unit' => 'C',
            'top' => 226,
            'left' => 375,
            'level' => '1'
        ),
        array(
            'id' => '10.A8EB65020800',
            'title' => 'Chambre Py/Steph',
            'unit' => 'C',
            'top' => 300,
            'left' => 700,
            'level' => '1'
        ),
        array(
            'id' => '10.380166020800',
            'title' => 'Chambre Noémie',
            'unit' => 'C',
            'top' => 195,
            'left' => 745,
            'level' => '1'
        ),
        array(
            'id' => '10.22E465020800',
            'title' => 'Chambre Léane',
            'unit' => 'C',
            'top' => 135,
            'left' => 480,
            'level' => '1'
        ),
        array(
            'id' => '10.D6F865020800',
            'title' => 'Salon/Séjour',
            'unit' => 'C',
            'top' => 300,
            'left' => 450,
            'level' => '0'
        ),
        array(
            'id' => '10.D6D765020800',
            'title' => 'Bureau',
            'unit' => 'C',
            'top' => 430,
            'left' => 350,
            'level' => '0'
        ),
        array(
            'id' => '10.EDFA65020800',
            'title' => 'Cuisine',
            'unit' => 'C',
            'top' => 360,
            'left' => 670,
            'level' => '0'
        ),
        array(
            'id' => '28.DEE652030000',
            'title' => 'Garage',
            'unit' => 'C',
            'top' => 430,
            'left' => 800,
            'level' => '0'
        ),
        array(
            'id' => '28.BB1A53030000',
            'title' => 'Exterieur',
            'unit' => 'C',
            'top' => 250,
            'left' => 120,
            'level' => '0'
        ),
        array(
            'id' => '22.587D2F000000',
            'title' => 'Baie Brassage',
            'unit' => 'C',
            'top' => 480,
            'left' => 760,
            'level' => '0'
        )
    );
?>

<!DOCTYPE html>
<html>
    <head>
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <link rel="stylesheet" type="text/css" href="/css/main.css" />
        <link rel="stylesheet" type="text/css" href="/css/tooltip.css" />
        <link rel="stylesheet" type="text/css" href="/css/tab.css" />
        <link rel="stylesheet" type="text/css" href="/css/popup.css" />
        <link rel="stylesheet" type="text/css" href="/css/widget.css" />

        <title>House On Wire</title>
    </head>
    <body>
        <div id="background">
            <h1 id="pageTitle"><?= $pageTitle; ?></h1>
            <div class="tabs">
                <div class="tabsButtons">
                    <div class="tab level0 selected" data-tab-name="level0">
                        Rez de chaussée
                    </div>
                    <div class="tab level1" data-tab-name="level1">
                        Etage
                    </div>
                    <div class="tab graph" data-tab-name="graph">
                        Graph général
                    </div>
                </div>
                <div class="tabsContainers">
                    <div class="tabBody level0">
                        <div id="level0" class="widgets"></div>
                    </div>
                    <div class="tabBody level1 hidden">
                        <div id="level1" class="widgets"></div>
                    </div>
                    <div class="tabBody graph hidden">
                        <div id="graph">
                            <iframe frameborder="0" scrolling="no" width="1200px" 
                                    height="630px" src="http://home.vitre.info/onewire/chart_full.php">
                            </iframe>
                        </div>
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
