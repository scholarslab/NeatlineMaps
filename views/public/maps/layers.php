<?php
$layerstitles = array();
$layernames = array();
foreach ($layers as $layer) {
	array_push($layerstitles,$layer->Title);
	array_push($layernames,$layer->Name);
}
$options = array_combine($layernames,$layerstitles);
echo $this->formSelect("layerselect", reset($options), array('class'=>'select'), $options);
?>