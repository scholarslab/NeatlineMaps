<?php head(); ?>

<body>

<?php 

echo $this->partial('maps/map.phtml',array('params' => array('testvariable'=>'testvalue'),"layername" => $layername, "serviceaddy" => $serviceaddy, 'proj4js' => $proj4js,
					"minx" => $minx ,'maxx' => $maxx ,'miny' => $miny ,'maxy' => $maxy ,'srs' => $srs 		));

foot();?>
</body>
