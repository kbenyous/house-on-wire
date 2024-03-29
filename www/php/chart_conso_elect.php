<html>
    <head>
        <title>House-On-Wire</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="/js/dygraph-combined.js"></script>
    </head>
    <body>
        <div id="graphdiv" style="width:1150px; height:300px;"></div>
        <div id="graphdiv2" style="width:1150px; height:300px;"></div>

        <?php
            // Lecture du fichier de conf
            $config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
            $db = pg_connect("host=" . $config['bdd']['host'] . " port=" . $config['bdd']['port'] . " dbname=" . $config['bdd']['dbname'] . " user=" . $config['bdd']['username'] . " password=" . $config['bdd']['password'] . " options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");
    
            $result = pg_query($db, "SELECT extract(epoch from current_timestamp)*1000  as finish_date, extract( epoch from current_date - interval '18 day')*1000 as start_date") or die("Erreur SQL sur recuperation des valeurs: " . pg_error());
            $date = pg_fetch_array($result);
        ?>

        <script type="text/javascript">
            new Dygraph(
                // containing div
                document.getElementById('graphdiv'),
                '/php/get_data_csv.php?type=conso_elect',
                {
                    title: 'Consommation Electrique',
                    ylabel: 'kW',
                    legend: 'always',
                    labelsDivStyles: { 'textAlign': 'right' },
                    showRangeSelector: true,
                    stepPlot: true,
                    fillGraph: true,
                    stackedGraph: true,
                    dateWindow:[<?=$date['start_date'] ?>, <?=$date['finish_date'] ?>]
                }
            );

            new Dygraph(
                // containing div
                document.getElementById('graphdiv2'),
                '/php/get_data_csv.php?type=conso_elect_euro',
                {
                    title: 'Consommation Electrique',
                    ylabel: '€',
                    legend: 'always',
                    labelsDivStyles: { 'textAlign': 'right' },
                    showRangeSelector: true,
                    stepPlot: true,
                    fillGraph: true,
                    stackedGraph: true,
                    dateWindow:[<?=$date['start_date'] ?>, <?=$date['finish_date'] ?>]
                }
            );

        </script>
    </body>
</html>
