<!doctype html>
<?php
	
	// including FusionCharts PHP wrapper
	include ("fusioncharts/fusioncharts.php");

	$cluster = Cassandra::cluster()->build();
	
	$keyspace = 'marathons';

	// creating session with cassandra scopped by keyspace
	$session = $cluster->connect($keyspace);

	// verifying connection with database
	if(!$session) {
		echo "Error - Unable to connect to database";
	}

?>
<html>
	<head>
		<title>Creating Dynamic Charts using PHP and Cassandra</title>
		<!-- including FusionCharts core package JS files -->
		<script src="https://static.fusioncharts.com/code/latest/fusioncharts.js"></script>
		<script src="https://static.fusioncharts.com/code/latest/fusioncharts.charts.js"></script>
		<style>
			@import url('https://fonts.googleapis.com/css?family=Assistant');

			body {
				text-align: center;
				background-color: #729BDF;
				font-family: Assistant;
			}

			hr {
				margin-top: 5px;
			}
		</style>
	</head>	
	<body>
		<?php

			$statement = new Cassandra\SimpleStatement( 'SELECT id, name, entry_cost, permile_cost, finisher_count FROM topten' );

			// query execution - fully asynchronous
			$exec = $session->executeAsync($statement);  

			// getting query result in a variable
			$result = $exec->get();

			if($result) {
				
				// creating an associative array to store the chart attributes    	
				$arrData = array(
			        "chart" => array(
			          	"caption"=> "World's Top Marathons",
			          	"captionFontBold"=> "1",
			          	"captionFontSize"=> "24",
			          	"captionFont"=> "Assistant",
			          	"subcaption"=> "By Entry Cost (In Pounds)",
			          	"subCaptionFontBold"=> "0",
			          	"subCaptionFontSize"=> "19",
			          	"subCaptionFont"=> "Assistant",
			          	"captionPadding"=> "20",
			          	"numberPrefix"=> "£",
			          	"canvasBgColor"=> "#729BDF",
			          	"bgColor"=> "#729BDF",
			          	"canvasBgAlpha"=> "0",
			          	"bgAlpha"=> "100",
			          	"showBorder"=> "0",
			          	"showCanvasBorder"=> "0",
			          	"showPlotBorder"=> "0",
			          	"paletteColors"=> "#FED34B",
			          	"showValues"=> "0",
			          	"decimals"=> "2",
			          	"usePlotGradientColor"=> "0",
			          	"baseFontColor"=> "#FFFFFF",
			          	"baseFont"=> "Assistant",
			          	"baseFontSize"=> "16",
			          	"showAlternateVGridColor"=> "0",
			          	"divLineColor"=> "#DBEAF8",
			          	"divLineThickness"=> "0.9",
			          	"divLineAlpha"=> "60",
			          	"toolTipPadding"=> "7",
			          	"toolTipBgColor"=> "#000000",
			          	"toolTipBorderAlpha"=> "0",
			          	"toolTipBorderRadius"=> "3"
			          )
			    );

				$arrData["data"] = array();
						// iterating over each data and pushing it into $arrData array
						foreach ($result as $row) {
						array_push($arrData["data"], array(
						"label" => $row["name"],
						"value" => $row["entry_cost"]->value(),
						"toolText" => "<b>" . $row["name"] . "</b><hr>Entry Cost: £" . number_format((float)$row["entry_cost"]->value(), 2, '.', '') . "<br> Per-mile Cost: £" . number_format((float)$row["permile_cost"]->value(), 2, '.', '') . "<br>Finishers: " . $row["finisher_count"]->value()
						)
					);
				}

  				$jsonEncodedData = json_encode($arrData);

				// creating FusionCharts instance
				$toptenChart = new FusionCharts("bar2d", "topChart" , '600', '450', "chart-container", "json", $jsonEncodedData);
    
				// FusionCharts render method
  				$toptenChart->render();				

			}

		?>
		<div id="chart-container"></div>
	</body>	
</html>
