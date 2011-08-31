<form action="<?php echo uri('/neatline-maps/maps/' . $id . '/files'); ?>" method="post" class="button-form neatline-inline-form-servers">
  <input type="submit" value="View and Edit Files" class="neatline-inline-button">
</form>

<form action="<?php echo uri('/neatline-maps/maps/delete/' . $id); ?>" method="post" class="button-form neatline-inline-form-servers">
  <input type="hidden" name="confirm" value="false" />
  <input type="submit" value="Delete" class="neatline-inline-button neatline-delete">
</form>
