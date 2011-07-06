<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Configuration form.
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
 * @subpackage  theplugin
 * @author      Organization <>
 * @author      Author McAuthor <author.mcauthor@virginia.edu>
 * @copyright   2010 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 * @version     $Id$
 * @link        http://omeka.org/add-ons/plugins/FedoraConnector/
 * @tutorial    tutorials/omeka/FedoraConnector.pkg
 */
?>

<div class="field">
    <label for="neatlinemaps_geoserver_url">Geoserver URL:</label>
    <input name="neatlinemaps_geoserver_url" id="neatlinemaps_geoserver_url" value="<?php echo get_option('neatlinemaps_geoserver_url'); ?>" size="40" />
</div>

<div class="field">
    <label for="neatlinemaps_geoserver_namespace_prefix">Geoserver Namespace Prefix:</label>
    <input name="neatlinemaps_geoserver_namespace_prefix" id="neatlinemaps_geoserver_namespace_prefix" value="<?php echo get_option('neatlinemaps_geoserver_namespace_prefix'); ?>" size="40" />
</div>

<div class="field">
    <label for="neatlinemaps_geoserver_namespace_url">Geoserver Namespace URL:</label>
    <input name="neatlinemaps_geoserver_namespace_url" id="neatlinemaps_geoserver_namespace_url" value="<?php echo get_option('neatlinemaps_geoserver_namespace_url'); ?>" size="40" />
</div>

<div class="field">
    <label for="neatlinemaps_geoserver_namespace_user">Geoserver User:</label>
    <input name="neatlinemaps_geoserver_namespace_user" id="neatlinemaps_geoserver_namespace_user" value="<?php echo get_option('neatlinemaps_geoserver_namespace_user'); ?>" size="40" />
</div>

<div class="field">
    <label for="neatlinemaps_geoserver_namespace_password">Geoserver Password:</label>
    <input name="neatlinemaps_geoserver_namespace_password" id="neatlinemaps_geoserver_namespace_password" value="<?php echo get_option('neatlinemaps_geoserver_namespace_password'); ?>" size="40" />
</div>

<div class="field">
    <label for="neatlinemaps_geoserver_spatial_reference_service">Spatial Reference Service:</label>
    <input name="neatlinemaps_geoserver_spatial_reference_service" id="neatlinemaps_geoserver_spatial_reference_service" value="<?php echo get_option('neatlinemaps_geoserver_spatial_reference_service'); ?>" size="40" />
</div>

<div class="field">
    <label for="neatlinemaps_geoserver_tag_prefix">Tag Prefix:</label>
    <input name="neatlinemaps_geoserver_tag_prefix" id="neatlinemaps_geoserver_tag_prefix" value="<?php echo get_option('neatlinemaps_geoserver_tag_prefix'); ?>" size="40" />
</div>

<?php
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
