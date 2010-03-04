<?php head(); ?>

<body onload="init()">

<?php 

echo $this->partial('maps/map.phtml',array("layername" => $layername, "serviceaddy" => $serviceaddy, 'proj4js' => $proj4js,
					"minx" => $bb['minx'] ,'maxx' => $bb['maxx'] ,'miny' => $bb['miny'] ,'maxy' => $bb['maxy'] ,'srs' => $bb['SRS'] 		));



foot();?>
</body>
