<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Servers')); ?>

<div id="primary">

    <p id="add-item" class="add-button"><a class="add" href="<?php echo html_escape(uri('neatline-maps/servers/create')); ?>">Add Server</a></p>

    <?php echo flash(); ?>

    <?php if (count($servers) == 0): ?>

        <p>There are no servers yet.</p>

    <?php else: ?>

            <table class="fedora">
                <thead>
                    <tr>
                        <?php browse_headings(array(
                            'Name' => 'name',
                            'URL' => 'server',
                            'Status' => null,
                            'Actions' => null
                        )); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servers as $server): ?>
                        <tr>
                            <td width="100"><a href="<?php echo uri('neatline-maps/servers/edit/' . $server->id); ?>"><strong><?php echo $server->name; ?></strong></a></td>
                            <td width="100"><a href="<?php echo $server->url; ?>"><?php echo $server->url; ?></a></td>
                            <td width="100">
                            <?php
                                if ($server->isOnline()) {
                                    echo '<span style="font-size: 0.8em; color: green;">Online</span>';
                                } else {
                                    echo '<span style="font-size: 0.8em; color: red;">Offline</span>';
                                }
                            ?>
                            </td>
                            <td width="100"><?php echo $this->partial('servers/servers-actions.php', array('id' => $server->id)); ?></td>
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
