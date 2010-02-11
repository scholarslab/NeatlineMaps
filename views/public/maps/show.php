<?php head(); 

echo js('proj4js/proj4js-compressed');
echo js('OpenLayers-2.8/lib/OpenLayers');
echo js('ext-3.0.0/adapter/ext/ext-base');
echo js('ext-3.0.0/ext-all'); 
echo js('GeoExt/GeoExt');

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
<link
	rel="stylesheet" href="<?php echo css('ext-all'); ?>" />
<link
	rel="stylesheet" href="<?php echo css('geoext-all'); ?>" />
<style type="text/css">
/* work around an Ext bug that makes the rendering
               of menu items not as one would expect */
.ext-ie .x-menu-item-icon {
	left: -24px;
}

.ext-strict .x-menu-item-icon {
	left: 3px;
}

.ext-ie6 .x-menu-item-icon {
	left: -24px;
}

.ext-ie7 .x-menu-item-icon {
	left: -24px;
}
</style>
<script type="text/javascript">
//<!--
Event.observe(window, 'load', Neatline.Maps.showSimple);
//-->
</script>

<h1>Map <?php echo $layername;?></h1>

<div id="layerlist"></div>
<hr style="width: 700px;" />
<div id="map" class="themap"></div>
<hr style="width: 700px;" />
	<?php foot();?>