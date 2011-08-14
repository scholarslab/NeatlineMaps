<?php if ( count($maps) > 0 ): ?>
    <div id="file-list">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Server</th>
                <th>Namespace</th>
                <th>Item</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach( $maps as $map ): ?>
        <?php $numberOfFiles = $filesTable->numberOfFilesInMap($map->map_id); ?>
        <tr>
            <td>
                <a href="<?php echo uri('neatline-maps/maps/' . $map->map_id . '/files'); ?>"><strong><?php echo $map->name; ?></strong></a> (<?php echo $numberOfFiles; ?> file<?php if ($numberOfFiles > 1) { echo 's'; } ?>)
            </td>
            <td><a href="<?php echo uri('neatline-maps/servers/edit/' . $map->getServer()->id); ?>"><?php echo $map->server; ?></a></td>
            <td><a href="<?php echo $map->getNamespaceUrl(); ?>" target="_blank"><?php echo $map->namespace; ?></a></td>
            <td><a href="<?php echo uri('items/show/' . $map->item_id); ?>"><?php echo $map->parent_item; ?></a></td>
            <td></td>
        </tr>

    <?php endforeach; ?>

    </tbody>
    </table>
    </div>

<?php else: ?>

<p style="font-size: 1em; color: gray;">There are no maps for the item. Add some!</p>

<?php endif; ?>
