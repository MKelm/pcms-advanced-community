-----------------------------------------------------------------------
|  Adavanced community modules for papaya CMS 5.5.2 or higher         |
|  Version: 0.10 (18.05.2013)                                         |
|  Author: Martin Kelm                                                |
-----------------------------------------------------------------------

This package contains modules to extend your papaya CMS community.

-----------
|  Todos  |
-----------

- Some improvements
- Calender integration, t.b.d.
- Administration

-----------
| Patches |
-----------

This package needs some patches in your papaya CMS installation.
You have to use one file from the patches folder to patch your system.
Go to your papaya CMS folder by command line and enter the following command:
patch -p 0 < /path/to/package/patches/patchfile.patches
patchfile.patches can replaced by:
- trunk_r38500.patches if you use a nightly build
- release_r38112.patches if you use the 5.5.2 release build
The patches file assumes a directory with all folders. If you have papaya-lib and papaya-data
in a seperate folder, you have to perform the patch command in each folder.

-----------
| Changes |
-----------

Revision 0.10 (18.05.2013)
- Added notify-method in connector to support notifications from other module packages
- Added patches files for trunk revision 38500 and release revision 38112
- Removed replacement files in ACommunity package

Revision 0.9 (17.05.2013)
- Added links to notifications and notification settings in surfer status box
- Added notifications view in messages page
- Added notification settings page
- Fixed some notice errors in surfer page and commenters ranking box
- Change, filter all content ressource parameters on empty array in filter parameter
- Moved and renamed some files to get "Surfers List" to "Surfers"

Revision 0.8 (16.05.2013)
- Added notification handler to notify surfers by system message or email
- Added three notifications, new-surfer-comment, new-surfer-image-comment and new-contact-request
-- added csv files for notifcations data import
- Refactored modules to get surfer data with support for display mode surfer name
- Added new module option, display mode for surfer names
- Improved comments and comments ranking output, reworked css styling and added surfer page links
- Added messages deletion in connector module -> onDeleteSurfer for action dispatcher
- Refactored page links generation in connector module

Revision 0.7 (15.05.2013)
- Added messages page link to surfer status box
- Added send message link to surfer page
- Added message conversation box to navigate between message conversations
- Added messages page to show messages between surfers
- Added paging to surfer lists

Revision 0.6 (14.05.2013)
- Added surfer contacts display mode in surfers list box
- Added surfer contacts statistic and contact page links in surfer status box
- Added surfers list page with surfer contacts and contact requests display mode
-- additionally this module supports the last action time and registration time display mode
- Added surfer contact handling in surfer page

Revision 0.5 (12.05.2013)
- Added surfers list box
- Added surfer status box
- Added no surfer message to surfer page
- Added no images messages to gallery page (via template language text)
- Refactored data handling and specifically ressource handling
- Added more database table keys to optimize query execution times
- Community improvement, show titles of user data classes in form output with a correct order by
-- activated in content_registration and content_userdata

Revision 0.4 (11.05.2013)
- Added surfer gallery teaser box
- Added links to surfer page in comments, comments ranking and commenters ranking box
- Added connector module options for surfer page id and surfer gallery page id

Revision 0.3 (11.05.2013)
- Added surfer page module
- Correct text paragraph css sizes in comments
- Added support for action dispatcher call onDeletePages
-- needs an base_topic_edit->destroy() code replacement to delete page dependend data
--- see base_topic_edit_destroy_replacement.txt
- Added an extended version of content_thumbs with template modifications
-- includes lightbox switch and orinal image link in image detail page and more

Revision 0.2 (10.05.2013)
- Added anchors to comments list ouput
- Added surfer gallery folders box

Revision 0.1 (10.05.2013):
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
