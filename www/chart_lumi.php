<html>
<head>
     	<title>House-On-Wire</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<script type="text/javascript" src="js/dygraph-combined.js"></script>
</head>
<body>
<div id="graphdiv" style="width:800px; height:600px; "></div>
<div id="graphdiv2" style="width:800px; height:600px; "></div>


<script type="text/javascript">

  g = new Dygraph(

    // containing div
    document.getElementById("graphdiv"),
"get_data_csv.php",
{
title: 'Test',
legend: 'always',
labelsSeparateLines: true
} 
  );

  g1 = new Dygraph(

    // containing div
    document.getElementById("graphdiv2"),
"luminosite2.csv",
{
title: 'Test',
legend: 'always',
labelsSeparateLines: true
} 
  );


</script>
</body>
</html>

