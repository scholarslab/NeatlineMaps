<?php echo $this->partial('maps/admin-header.php', array('subtitle' => $map->name)); ?>

<div id="primary">

    <p id="add-item" class="add-button"><a class="add" href="<?php echo html_escape(uri('neatline-maps/maps/' . $map->id . '/addfiles')); ?>">Add Files to Map</a></p>

    <?php echo flash(); ?>

    <table class="neatline">
        <thead>
            <tr>
                <?php browse_headings(array(
                    'File Name' => NULL,
                    'Preview' => NULL,
                    'Actions' => NULL
                )); ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($files as $file): ?>
                <tr>
                    <td width="150">
                      <a href="<?php echo uri('files/show/' . $file->getFile()->id); ?>">
                        <strong><?php echo $file->getFile()->original_filename; ?></strong>
                      </a>
                    </td>
                    <td width="500">

                        <?php
                            $fileMap = new GeoserverMap_File($file);
                            $fileMap->display();
                        ?>

                    </td>
                    <td><?php echo $this->partial('maps/files-actions.php', array('id' => $file->id)); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">

        <?php echo pagination_links(array('scrolling_style' => 'All',
        'page_range' => '5',
        'partial_file' => 'common/pagination_control.php',
        'page' => $current_page,
        'per_page' => $results_per_page,
        'total_results' => $total_results)); ?>

    </div>

</div>

<?php foot();
