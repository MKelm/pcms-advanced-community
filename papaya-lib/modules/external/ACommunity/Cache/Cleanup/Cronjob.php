<?php
/**
 * Advanced community cache cleanup cronjob
 *
 * Delete cache files and last change timestamps older than n days
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
 * Basic cronjob class
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_cronjob.php');

/**
 * Advanced community cache cleanup cronjob
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCacheCleanupCronjob extends base_cronjob {

  /**
  * Modified?
  * @var boolean
  */
  public $modified = FALSE;

  /**
  * Edit Fields
  * @var array
  */
  public $editFields = array(
    'days_timeframe' => array(
      'Timeframe In Days', 'isNoHTML', TRUE, 'input', 200,
      'Delete cache files and last change timestamps older than n days.', 7
    )
  );

  /**
   * Check execution parameters
   *
   * @return boolean Execution possible?
   */
  public function checkExecParams() {
    return TRUE;
  }

  /**
   * Basic execution
   *
   * @return integer 0
   */
  public function execute() {
    include_once(dirname(__FILE__).'/../Cleanup.php');
    $cleanup = new ACommunityCacheCleanup();
    $cleanup->papaya($this->papaya());
    $cleanup->deleteCacheFiles(
      $this->data['days_timeframe']
    );
    $cleanup->deleteLastChangeTimestamps(
      $this->data['days_timeframe']
    );
    return 0;
  }

}
