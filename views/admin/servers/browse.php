<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Servers')); ?>

<div id="primary">

    <p id="add-item" class="add-button"><a class="add" href="<?php echo html_escape(uri('fedora-connector/datastreams/create')); ?>">Add Server</a></p>

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
                            'Actions' => null
                        )); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servers as $server): ?>
                        <tr>
                            <td width="100" class="fedora-td-small"><?php // echo $server->name; ?></td>
                            <td width="100" class="fedora-td-small"><?php // echo $server->url; ?></td>
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
