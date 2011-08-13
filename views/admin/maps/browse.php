<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Maps')); ?>

<div id="primary">

    <p id="add-item" class="add-button"><a class="add" href="<?php echo html_escape(uri('neatline-maps/maps/create')); ?>">Add Map</a></p>

    <?php echo flash(); ?>

    <?php if (count($maps) == 0): ?>

        <p>There are no maps yet.</p>

    <?php else: ?>

            <table class="fedora">
                <thead>
                    <tr>
                        <?php browse_headings(array(
                            'Map' => 'name',
                            'Server' => 'server',
                            'Namespace' => 'namespace',
                            'Item' => 'parent_item',
                            'Actions' => null
                        )); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maps as $map): ?>
                    <?php $numberOfFiles = $filesTable->numberOfFilesInMap($map->map_id); ?>
                        <tr>
                            <td width="150">
                                <a href="<?php echo uri('neatline-maps/maps/' . $map->map_id . '/files'); ?>"><strong><?php echo $map->name; ?></strong></a> (<?php echo $numberOfFiles; ?> file<?php if ($numberOfFiles > 1) { echo 's'; } ?>)
                            </td>
                            <td width="130"><a href="<?php echo uri('neatline-maps/servers/edit/' . $map->getServer()->id); ?>"><?php echo $map->server; ?></a></td>
                            <td><a href="<?php echo $map->getNamespaceUrl(); ?>" target="_blank"><?php echo $map->namespace; ?></a></td>
                            <td width="120"><a href="<?php echo uri('items/show/' . $map->item_id); ?>"><?php echo $map->parent_item; ?></a></td>
                            <td><?php echo $this->partial('maps/maps-actions.php', array('id' => $map->map_id)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

    <?php endif; ?>

          <div class="pagination">

              <?php echo pagination_links(array('scrolling_style' => 'All', 
              'page_range' => '5',
              'partial_file' => 'common/pagination_control.php',
              'page' => $current_page,
              'per_page' => $results_per_page,
              'total_results' => $total_results)); ?>

          </div>

</div>

<?php foot(); ?>
