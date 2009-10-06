<?php head(); ?>
<?php echo js('proj4js/proj4js-compressed'); ?>
<script type="text/javascript"><?php echo $proj4js ?></script>
<?php echo js('OpenLayers-2.8/lib/OpenLayers'); ?>
<?php echo js('prototype'); ?>
<script type="text/javascript">
//<!--
	var map;
	layernames = "<?php echo $layernames;?>";
	serviceaddys = "<?php echo $serviceaddys;?>";
	srs = "<?php echo $srs;?>";
	
	bbox = new OpenLayers.Bounds(<?php echo $minx;?>,
	<?php echo $miny;?>,
	<?php echo $maxx;?>,
	<?php echo $maxy;?>);

//-->
</script>
<?php echo js('ext-3.0.0/adapter/ext/ext-base'); ?>
<?php echo js('ext-3.0.0/ext-all'); ?>
<?php echo js('GeoExt'); ?>
<?php echo js('GeoView'); ?>
<?php echo js('compose'); ?>
<link rel="stylesheet" href="<?php echo css('neatline'); ?>" />
<link rel="stylesheet" href="<?php echo css('ext-all'); ?>" />
<link rel="stylesheet" href="<?php echo css('geoext-all'); ?>" />
<link rel="stylesheet" href="<?php echo css('style'); ?>" />
<link rel="stylesheet" href="<?php echo css('app'); ?>" />

<script type="text/javascript">
//<!--
Event.observe(window, 'load', Neatline.Maps.showComposed);
//-->
</script>

<h1>Map <?php echo $layernames;?></h1>


<div id="readout">
	<?php echo $bb ?>
</div>
<hr style="width:700px;"/>
<div id="tree" style="float:left"></div>
<div id="map" class="themap"></div>

<div id="sliders" ></div>

<hr style="width:700px;"/>
<?php foot();?>