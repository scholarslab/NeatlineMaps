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
                        <tr>
                            <td width="150">
                              <a href="<?php echo uri('neatline-maps/maps/' . $map->id); ?>"><strong><?php echo $map->name; ?></strong></a>
                              <span style="color: gray; font-size: 0.9em;">(<?php echo $map->getNumberOfFiles(); ?> <?php echo ($map->getNumberOfFiles() > 1) ? 'files' : 'file'; ?> )</span>
                            </td>
                            <td width="130"><?php echo $map->getServer()->name; ?></td>
                            <td><?php echo $map->namespace; ?></td>
                            <td width="120"><a href="<?php echo uri('items/show/' . $map->item_id); ?>"><?php echo $map->parent_item; ?></a></td>
                            <td><?php echo $this->partial('maps/maps-actions.php', array('id' => $map->id)); ?></td>
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
