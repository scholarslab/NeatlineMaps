<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Add server.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */
?>

<?php
head(array('content_class' => 'neatline'));
?>

<?php echo $this->partial('servers/_header.php', array(
    'subtitle' => 'Create Server',
    'add_button_uri' => 'neatline-maps/add',
    'add_button_text' => 'Create a Server'
)); ?>

<div id="primary">
    <?php echo $form; ?>
</div>

<?php foot(); ?>
