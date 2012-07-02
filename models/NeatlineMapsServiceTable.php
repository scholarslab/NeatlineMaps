<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Table class for Neatline WMS.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class NeatlineMapsServiceTable extends Omeka_Db_Table
{

    /**
     * Try to find a record for an item. If no record exists, return false.
     *
     * @param Omeka_record $item The item.
     *
     * @return Omeka_record $service The service, if one exists; else false.
     */
    public function findByItem($item)
    {

        $service = $this->fetchObject(
            $this->getSelect()->where('item_id=?', $item->id)
        );

        return $service ? $service : false;

    }

    /**
     * For a given item, try to find an existing service record for the item.
     * If one exists, update the address and layers with the passed data. If the
     * data is empty, delete the record. If a record does not already exist for
     * the item, create a new record.
     *
     * @param Omeka_record $item The item.
     * @param Omeka_record $exhibit The exhibit.
     *
     * @return Omeka_record $edition The new or updated service.
     */
    public function createOrUpdate($item, $address, $layers)
    {

        // Try to get existing record.
        $record = $this->findByItem($item);

        // If no record exists, create a new one.
        if (!$record) { $record = new NeatlineMapsService($item); }

        // If the data is empty, delete.
        if ($address === '' && $layers === '') {
            $record->delete();
        }

        // Otherwise, update.
        else {

          // Update and save.
          $record->address = $address;
          $record->layers = $layers;
          $record->save();

        }

        return $record;

    }

    /**
     * Create a new service for a file and a server.
     *
     * @param Omeka_record $file The file.
     * @param Omeka_record $server The server.
     *
     * @return Omeka_record $edition The new service.
     */
    public function createFromFileAndServer($file, $server)
    {

        // Get parent item.
        $item = $file->getItem();

        // If the parent item already has a service, break;
        if ($this->findByItem($item)) {
            return false;
        }

        // Create service.
        $wms = new NeatlineMapsService($item);
        $wms->address = $server->getWmsAddress();
        $wms->layers = nlwms_layerName($server, $file);
        $wms->save();

        return $wms;

    }

    /**
     * Get services for dropdown select, using the title of the parent item
     * as the text value for the listing.
     *
     * @return array $services The constructed array.
     */
    public function getServicesForSelect()
    {

        // Get service records.
        $services = $this->fetchObjects($this->getSelect());

        // Walk the records, build array.
        $options = array('none' => '-');
        foreach ($services as $service) {
            $options[$service->id] = nlwms_getItemMetadata(
                $service->getItem(), 'Dublin Core', 'Title');
        }

        return $options;

    }

}
