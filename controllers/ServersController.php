<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Admin controller.
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package     omeka
 * @subpackage  neatlinemaps
 * @author      Scholars' Lab <>
 * @author      Bethany Nowviskie <bethany@virginia.edu>
 * @author      Adam Soroka <ajs6f@virginia.edu>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2010 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 * @version     $Id$
 */
?>

<?php

class NeatlineMaps_ServersController extends Omeka_Controller_Action
{

    /**
     * Show servers.
     *
     * @return void
     */
    public function browseAction()
    {

        $sort_field = $this->_request->getParam('sort_field');
        $sort_dir = $this->_request->getParam('sort_dir');

        // Get the servers.
        $page = $this->_request->page;
        $order = _doColumnSortProcessing($sort_field, $sort_dir);
        $maps = $this->getTable('NeatlineMapsMap')->getMaps($page, $order);

        $this->view->maps = $maps;

        $this->view->current_page = $page;
        $this->view->total_results = $this->getTable('NeatlineMapsMap')->count();
        $this->view->results_per_page = get_option('per_page_admin');

    }

}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
