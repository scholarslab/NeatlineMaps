<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/** {{{ license
 * Index controller. Just for the reroute.
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
 */ // }}} 

class NeatlineMaps_IndexController extends Omeka_Controller_Action
{

    /**
     * Redirect to the maps controller. This way, the 'Neatline Maps'
     * tab is always highlighted, no matter what sub-controller is active.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_redirect('neatline-maps/maps');
    }

}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

