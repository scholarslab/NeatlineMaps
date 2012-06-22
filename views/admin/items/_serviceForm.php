<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Exhibit selector for item add/edit form.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */
?>

<div class="field">
<label for="address"><?php echo __('WMS Address'); ?></label>
  <div class="inputs">
    <textarea cols="50" rows="2" id="address" class="textinput" name="address" ><?php if ($service) { echo $service->address; } ?></textarea>
  </div>
  <p class="explanation"><?php echo __('Enter the WMS address of the map.'); ?></p>
</div>

<div class="field">
<label for="layers"><?php echo __('Layers'); ?></label>
  <div class="inputs">
    <textarea cols="50" rows="2" id="layers" class="textinput" name="layers" ><?php if ($service) { echo $service->layers; } ?></textarea>
  </div>
  <p class="explanation"><?php echo __('Enter a comma-delimited list of layers.'); ?></p>
</div>
