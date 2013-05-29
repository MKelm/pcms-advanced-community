<?php
/**
 * Advanced community surfer groups page
 *
 * @copyright 2013 by Martin Kelm
 * @link http://idx.shrt.ws
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 * You can redistribute and/or modify this script under the terms of the GNU General Public
 * License (GPL) version 2, provided that the copyright and license notes, including these
 * lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */

/**
* Basic class page module
*/
require_once(dirname(__FILE__).'/../../Groups/Page.php');

/**
 * Advanced community surfer groups page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGroupsPage extends ACommunityGroupsPage {

  /**
   * Contains groups of current active surfer only
   * @var boolean
   */
  protected $_showOwnGroups = TRUE;
}