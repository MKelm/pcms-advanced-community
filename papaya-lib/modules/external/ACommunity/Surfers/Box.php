<?php
/**
 * Advanced community surfers box
 *
 * Offers status information of logged in user and links to certain surfer pages
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
 * Basic box class
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
 * Advanced community surfers box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurfersBox extends base_actionbox {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acssb';

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 60
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'display_mode' => array(
      'Display Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'contacts' => 'Surfer contacts of current or selected surfer',
         'lastaction' => 'Surfers by last action Time',
         'registration' => 'Surfers by registration Time'
       ), '', 'lastaction'
    ),
    'timeframe' => array(
      'Timeframe in days', 'isNum', TRUE, 'input', 30,
      'Get surfers by last action or registration time in a specified timeframe.', 365
    ),
    'limit' => array(
      'Limit', 'isNum', TRUE, 'input', 30, '', 5
    ),
    'show_paging' => array(
      'Show paging', 'isNum', TRUE, 'yesno', NULL, NULL, 0
    ),
    'Captions',
    'caption_last_action' => array(
      'Last Action', 'isNoHTML', TRUE, 'input', 200, '', 'Last action'
    ),
    'caption_registration' => array(
      'Registered Since', 'isNoHTML', TRUE, 'input', 200, '', 'Registered sine'
    ),
    'Messages',
    'message_empty_list' => array(
      'No Entries', 'isNoHTML', TRUE, 'input', 200, '', 'No entries.'
    )
  );

  /**
   * Surfers object
   * @var ACommunitySurfers
   */
  protected $_surfers = NULL;

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    $this->surfers()->data()->ressource(
      'surfer', $this, array('surfer' => array('surfer_handle'))
    );
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurfers object
  *
  * @return ACommunitySurfers $surfers
  */
  public function surfers(ACommunitySurfers $surfers = NULL) {
    if (isset($surfers)) {
      $this->_surfers = $surfers;
    } elseif (is_null($this->_surfers)) {
      include_once(dirname(__FILE__).'/../Surfers.php');
      $this->_surfers = new ACommunitySurfers();
      $this->_surfers->parameterGroup($this->paramName);
      $this->_surfers->data()->setPluginData(
        $this->data,
        array(
          'caption_last_action', 'caption_registration'
        ),
        array('message_empty_list')
      );
      $this->_surfers->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_surfers;
  }

  /**
   * Get parsed data
   *
   * @return string $result XML
   */
  public function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $this->setRessourceData();
    return $this->surfers()->getXml();
  }

}
