<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Namespaces')); ?>

<div id="primary">

    <p id="add-item" class="add-button"><a class="add" href="<?php echo html_escape(uri('neatline-maps/servers/create')); ?>">Add Namespace</a></p>

    <?php echo flash(); ?>

    <?php if (count($namespaces) == 0): ?>

        <p>There are no namespaces yet.</p>

    <?php else: ?>

            <table class="fedora">
                <thead>
                    <tr>
                        <?php browse_headings(array(
                            'Name' => 'name',
                            'Server' => 'server',
                            'URL' => 'url',
                            'Actions' => null
                        )); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($namespaces as $namespace): ?>
                        <tr>
                            <td width="100"><a href="<?php echo uri('neatline-maps/namespaces/edit/' . $namespace->id); ?>"><strong><?php echo $namespace->name; ?></strong></a></td>
                            <td width="100"><a href="<?php echo $namespace->getServer()->url; ?>"><?php echo $namespace->getServer()->name; ?></a></td>
                            <td width="100"><a href="<?php echo $namespace->url; ?>"><?php echo $namespace->url; ?></a></td>
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
