<?php

    $header = array('title' => html_escape('Neatline Maps | ' . $subtitle), 'content_class' => 'horizontal-nav');
    head($header);

?>

<h1 class="neatline-nowrap"><?php echo $header['title']; ?></h1>
<ul id="section-nav" class="navigation">
<?php echo nav(array(
    'Maps' => uri('neatline-maps/maps'),
    'Servers' => uri('neatline-maps/servers')
))?>
</ul>
