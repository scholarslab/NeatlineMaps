<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Delete server.
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
    'subtitle' => 'Delete Server "' . $server->name . '"',
    'add_button_uri' => 'neatline-maps/add',
    'add_button_text' => 'Create a Server'
)); ?>

<div id="primary" class="neatline-delete-confirm-static">

    <h1>Are you sure?</h1>
    <p>This will permanently delete the "<?php echo $server->name; ?>" server.</p>

    <div class="alert-actions">
        <form method="post">
            <input type="submit" name="delete-neatline" id="delete-neatline" value="Delete" class="neatline btn delete large">
        </form>
    </div>

</div>

<?php
foot();
?>
