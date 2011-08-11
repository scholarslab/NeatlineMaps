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
                            'Preview' => null,
                            'Actions' => null
                        )); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maps as $map): ?>
                        <tr>
                            <td width="220">
                                <strong><?php echo $map->name; ?></strong>
                            </td>
                            <td width="100" class="fedora-td-small"><?php // echo $map->namespace; ?></td>
                            <td width="100" class="fedora-td-small"><?php echo $map->namespace; ?></td>
                            <td class="fedora-td-small"><a href="<?php echo uri('items/show/' . $map->item_id); ?>"><?php echo $map->parent_item; ?></a></td>
                            <td class="fedora-td-small"><a href="<?php echo uri('fedora-connector/servers/edit/' . $datastream->server_id); ?>"><?php echo $datastream->server_name; ?></a></td>
                            <!-- <td><?php echo $datastream->metadata_stream; ?></td> -->
                            <td style="text-align: center;"><?php echo $datastream->renderPreview(); ?></td>
                            <td width="60"><?php echo $this->partial('datastreams/datastreams-actions.php', array('id' => $datastream->datastream_id)); ?></td>
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
