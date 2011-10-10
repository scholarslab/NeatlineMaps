# Neatline Maps

Neatline Maps enables users to upload geoencoded TIFF files to an [Omeka]
archive, and display those files on their Omeka site and exhibits. The plugin
provides connection to one or more GeoServer instances as well as any 
specification-compliant WMS service, and uses [OpenLayers] to display the maps
in a “slippy” interface. 

# Installation and Configuration
[![](http://github.com/scholarslab/NeatlineMaps/tarball/master)][1]
1. Download the most current version of NeatlineMaps
2. Install the plugin
3. Tell the plugin where to look for [GeoServer] by adding a new server. 

## Feedback
We rely on the [Github issues tracker][issues] for feedback on issues
and improvements. 

## Tests
NeatlineMaps uses PHPUnit to ensure the quality of the software. The
easiest way to contribute to the project is to let us know about any
bugs and include a test case. 

## Note on Patches/Pull Requests
* Fork the project.
* Make your feature addition/bug fix.
* Add test for your code. This is important so we don't unintentionally
  break your code in a futre version.
* Commit
* Send a pull request...bonus points for topic branches.

[GeoServer]: http://geoserver.org/display/GEOS/Welcome
[Omeka]: http://omeka.org/
[issues]: http://github.com/scholarslab/NeatlineMaps/issues/
[omekatests]: http://omeka.org/codex/Unit_Testing/
[1]: http://github.com/images/modules/download/tar.png
[OpenLayers]: http://openlayers.org/
