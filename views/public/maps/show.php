<?php head(); ?>

<body onload="init()">

<?php
echo js('proj4js/proj4js-compressed');
echo js('OpenLayers-2.8/lib/OpenLayers');
//echo js('ext-3.0.0/adapter/ext/ext-base');
//echo js('ext-3.0.0/ext-all'); 
//echo js('GeoExt/GeoExt');
echo js('show');
?>

<script type="text/javascript"><?php echo $proj4js ?></script>
<script type="text/javascript">
//<!--

	layername = "<?php echo $layername;?>";
	serviceaddy = "<?php echo $serviceaddy;?>";
	srs = "<?php echo $srs;?>";
	//features = <?php echo $features;?>;
	bbox = new OpenLayers.Bounds(<?php echo $minx;?>,
	<?php echo $miny;?>,
	<?php echo $maxx;?>,
	<?php echo $maxy;?>);

//-->
</script>

<link
	rel="stylesheet" href="<?php echo css('neatline'); ?>" />


<h1>Map <?php echo $layername;?></h1>

<hr style="width: 700px;" />

       <!--   <div id="controlToggle">
                <input type="radio" name="type" value="none" id="noneToggle"
                       onclick="toggleControl(this);" checked="checked" />
                <label for="noneToggle">navigate</label>
                <input type="radio" name="type" value="line" id="lineToggle" onclick="toggleControl(this);" />
                <label for="lineToggle">measure distance</label>
                <input type="radio" name="type" value="polygon" id="polygonToggle" onclick="toggleControl(this);" />
                <label for="polygonToggle">measure area</label>
                Measurement: <span id="output"/>
        </div> -->


<div id="map" class="themap"></div>
<hr style="width: 700px;" />
	<?php foot();?>
	</body>