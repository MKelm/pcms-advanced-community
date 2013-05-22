-----------------------------------------------------------------------
|  Adavanced community modules for papaya CMS 5.5.2 or higher         |
|  Author: Martin Kelm                                                |
|  Repository: https://github.com/MKelm/advanced-community            |
-----------------------------------------------------------------------

This package contains modules to extend your papaya CMS community.

-----------
|  Todos  |
-----------

- Some improvements
- Surfers page, searchable and with filters
- Groups for surfers

----------------
| Installation |
----------------

Please read the installation manual here
https://github.com/MKelm/pcms-advanced-community/blob/master/installation-en.txt (english version)
or here
https://github.com/MKelm/pcms-advanced-community/blob/master/installation-de.txt (german version)

-----------
| Changes |
-----------

==> 21.05.2013
- Added delete images feature in surfer gallery page
- Added xsl support for surfer editor and surfer gallery in page_acommunity.xsl
- Changed surfer gallery page to use a refactored image gallery module
- Improved getCurrentSurferId, use papaya()->surfer
- Removed deprecated last vote time

==> 20.05.2013
- Fixed a caching bug in surfer gallery teaser box
- Fixed a ressource detection bug in surfer gallery page
- Added extended text filter with URL to link replacement for comments and messages
-- Added optional thumbnail links feature for comment and message text
- Fixed an error with =-chars in PapayaFilterText, in patches
- Fixed some errors in onDeleteSurfer methods
- Added deletion methods for last changes timestamps in onDeleteSurfer and onDeletePages methods
- Added pages connector in connector->getPageLink-method to get login / registration page title
- Improved ressource handling in surfer gallery page
- Added checkURLFilename-Methods to avoid URL Fixation

==> 19.05.2013
- Added last changes table to detect changes for caching
-- Added change detection for surfer_gallery_folders and surfer_gallery_images in gallery modules
--- set last change of folders and images in folders and upload module
-- Added change detection for all types of contacts in contact modules
--- set last change of contacts for all types in Surfer/Contact/Changes.php
-- Added change detection for surfer in surfer page
- Changed surfers page to surfer contacts page only, use boxes for the deprecated display modes
- Added constants in surfers boxes to use dynamic cache identifier values or not
- Splitted surfers box into surfers last action, surfers registration and contacts box to
  get valid cache ids for each display mode
- Splitted comments box into page comments box, surfer comments box and image comments box to
  get valid cache ids for each output type
- Added cache identifier definitions to modules
- Added show paging option to surfers page module
- Added dynamic data categories option to surfer page module
- Added patches for community/content_profile module

==> 18.05.2013
- Added notify-method in connector to support notifications from other module packages
- Added patches files for trunk revision 38500 and release revision 38112
- Removed replacement files in ACommunity package

==> 17.05.2013
- Added links to notifications and notification settings in surfer status box
- Added notifications view in messages page
- Added notification settings page
- Fixed some notice errors in surfer page and commenters ranking box
- Change, filter all content ressource parameters on empty array in filter parameter
- Moved and renamed some files to get "Surfers List" to "Surfers"

==> 16.05.2013
- Added notification handler to notify surfers by system message or email
- Added three notifications, new-surfer-comment, new-surfer-image-comment and new-contact-request
-- added csv files for notifcations data import
- Refactored modules to get surfer data with support for display mode surfer name
- Added new module option, display mode for surfer names
- Improved comments and comments ranking output, reworked css styling and added surfer page links
- Added messages deletion in connector module -> onDeleteSurfer for action dispatcher
- Refactored page links generation in connector module

==> 15.05.2013
- Added messages page link to surfer status box
- Added send message link to surfer page
- Added message conversation box to navigate between message conversations
- Added messages page to show messages between surfers
- Added paging to surfer lists

==> 14.05.2013
- Added surfer contacts display mode in surfers list box
- Added surfer contacts statistic and contact page links in surfer status box
- Added surfers list page with surfer contacts and contact requests display mode
-- additionally this module supports the last action time and registration time display mode
- Added surfer contact handling in surfer page

==> 12.05.2013
- Added surfers list box
- Added surfer status box
- Added no surfer message to surfer page
- Added no images messages to gallery page (via template language text)
- Refactored data handling and specifically ressource handling
- Added more database table keys to optimize query execution times
- Community improvement, show titles of user data classes in form output with a correct order by
-- activated in content_registration and content_userdata

==> 11.05.2013
- Added surfer gallery teaser box
- Added links to surfer page in comments, comments ranking and commenters ranking box
- Added connector module options for surfer page id and surfer gallery page id
- Added surfer page module
- Correct text paragraph css sizes in comments
- Added support for action dispatcher call onDeletePages
-- needs an base_topic_edit->destroy() code replacement to delete page dependend data
--- see base_topic_edit_destroy_replacement.txt
- Added an extended version of content_thumbs with template modifications
-- includes lightbox switch and orinal image link in image detail page and more

==> 10.05.2013
- Added anchors to comments list ouput
- Added surfer gallery folders box
- Added surfer gallery upload module
- Added surfer gallery module
- Added commenters ranking module
- Added comments ranking module
- Added comments module
- Added connector module

-----------
| License |
-----------

This module is offered under GNU General Public Licence
(GPL). The detailed license text can be found in gpl.txt
