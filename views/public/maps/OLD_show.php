<?php head(); ?>

<body>

<?php

echo $this->partial('maps/map.phtml', array( 'params' => $params ));

foot();?>

</body>
