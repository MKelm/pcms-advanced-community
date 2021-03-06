<?php
/**
 * Advanced community comment dialog
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
 * Advanced community comment dialog
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentCommentDialog
  extends PapayaUiControlCommandDialogDatabaseRecord {

  /**
  * Comments data
  * @var ACommunityCommentsData
  */
  protected $_data = NULL;

  /**
  * Current error message.
  * @var string
  */
  protected $_errorMessage = NULL;

  /**
   * Get/set comments data
   *
   * @param ACommunityCommentsData $data
   * @return ACommunityCommentsData
   */
  public function data(ACommunityCommentsData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    }
    return $this->_data;
  }

  /**
  * Get/set error message
  * @var string $errorMessage
  */
  public function errorMessage($errorMessage = NULL) {
    if (isset($errorMessage)) {
      PapayaUtilConstraints::assertString($errorMessage);
      $this->_errorMessage = $errorMessage;
    }
    return $this->_errorMessage;
  }

  /**
  * Create dialog
  *
  * @see PapayaUiControlCommandDialog::createDialog()
  * @return PapayaUiDialog
  */
  public function createDialog() {
    $buttonCaption = $this->data()->captions['dialog_button'];

    $dialog = new PapayaUiDialogDatabaseSave($this->record());
    $dialog->caption = NULL;
    $dialog->callbacks()->onBeforeSave = array($this, 'callbackBeforeSaveRecord');
    $dialog->papaya($this->papaya());
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->action(
      $this->data()->absoluteReferenceUrl ? $this->data()->reference()->get() :
        $this->data()->reference()->getRelative()
    );
    $ressource = $this->data()->owner->ressource();
    $commentId = $this->parameters()->get('comment_id', 0);
    $dialog->hiddenFields()->merge(array('command' => 'reply', 'comment_id' => $commentId));

    $textOptions = $this->data()->owner->acommunityConnector()->getTextOptions();
    if ($textOptions['thumbnails'] == 1 && $commentId == 0) {
      $imageHandlerRequestIdent = $this->parameters()->get('image_handler_request_ident', NULL);
      if (empty($imageHandlerRequestIdent)) {
        $imageHandlerRequestIdent = md5(
          'request_a_thumbnail_link_image:comments:surfer_'.$this->data()->currentSurferId().
          ':'.$ressource->type.'_'.$ressource->id
        );
      }
      $dialog->hiddenFields()->merge(array(
        'image_handler_url' => $this->data()->owner->acommunityConnector()->getCommentsPageLink(
          $this->data()->languageId, $ressource->type, $ressource->id,
          array(
            'request' => 'thumbnail_link',
            'from' => 'comments',
            'ident' => $imageHandlerRequestIdent,
            'url' => '{URL}'
          )
        ),
        'image_handler_request_ident' => $imageHandlerRequestIdent
      ));
    }

    // check comment id to allow video links for comments without a parent comment only
    if ($textOptions['videos'] == 1 && $commentId == 0) {
      $videoHandlerRequestIdent = $this->parameters()->get('video_handler_request_ident', NULL);
      if (empty($videoHandlerRequestIdent)) {
        $videoHandlerRequestIdent = md5(
          'request_a_video_link:comments:surfer_'.$this->data()->currentSurferId().
          ':'.$ressource->type.'_'.$ressource->id
        );
      }
      $dialog->hiddenFields()->merge(array(
        'video_handler_url' => $this->data()->owner->acommunityConnector()->getCommentsPageLink(
          $this->data()->languageId, $ressource->type, $ressource->id,
          array(
            'request' => 'video_link',
            'from' => 'comments',
            'ident' => $videoHandlerRequestIdent,
            'url' => '{URL}'
          )
        ),
        'video_handler_request_ident' => $videoHandlerRequestIdent
      ));
    }

    include_once(dirname(__FILE__).'/../../../Filter/Text/Extended.php');
    $dialog->fields[] = $field = new PapayaUiDialogFieldTextarea(
      $this->data()->captions['dialog_text'],
      'text',
      3,
      '',
      new ACommunityFilterTextExtended(
        PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS|PapayaFilterText::ALLOW_LINES,
        'comments:'.$ressource->type.'_'.$ressource->id,
        $this->papaya()->session,
        isset($imageHandlerRequestIdent) ? $imageHandlerRequestIdent : NULL,
        isset($videoHandlerRequestIdent) ? $videoHandlerRequestIdent : NULL
      )
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogCommentText');
    $dialog->buttons[] = new PapayaUiDialogButtonSubmit($buttonCaption);

    $this->callbacks()->onExecuteSuccessful = array($this, 'callbackExecuteSuccessful');
    $this->callbacks()->onExecuteFailed = array($this, 'callbackShowError');
    return $dialog;
  }

  /**
  * Callback before save record in PapayaUiDialogDatabaseSave
  *
  * @param object $context
  * @param object $record
  */
  public function callbackBeforeSaveRecord($context, $record) {
    $commentId = (int)$this->parameters()->get('comment_id', 0);
    $ressource = $this->data()->owner->ressource();
    $record->assign(
      array(
        'language_id' => $this->data()->languageId,
        'parent_id' => $commentId,
        'surfer_id' => $this->data()->currentSurferId(),
        'ressource_id' => $ressource->id,
        'ressource_type' => $ressource->type,
        'time' => time(),
        'votes_score' => 0,
        'deleted_surfer' => 0
      )
    );
    return TRUE;
  }

  /**
  * Perform actions on success
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackExecuteSuccessful($context, $dialog) {
    // activate dialog reset on sub-command reply
    $commentId = (int)$this->parameters()->get('comment_id', 0);
    $command = $this->parameters()->get('command', '');
    if ($command == 'reply' && $commentId > 0) {
      $this->parameters()->set('comment_id', 0);
      $this->parameters()->set('reset_dialog', 1);
    }
    // send notification on surfer or image comment
    $ressource = $this->data()->owner->ressource();
    if ($ressource->type == 'surfer') {
      if ($ressource->validSurfer !== 'is_selected') {
        $this->data()->owner->acommunityConnector()->notify(
          'new-surfer-comment',
          $this->data()->languageId,
          $ressource->id,
          array(
            'recipient_surfer' => $ressource->id,
            'context_surfer' => $this->data()->currentSurferId(),
            'page_url' => $this->data()->reference()->url()->getUrl()
          )
        );
      }
    } elseif ($ressource->type == 'group') {
      if ($ressource->validSurfer !== 'is_owner') {
        // use owner id from group data previously loaded by ressource object
        $groupOwnerId = $this->data()->owner->acommunityConnector()->groupSurferRelations()
          ->group()->owner;
        $this->data()->owner->acommunityConnector()->notify(
          'new-group-comment',
          $this->data()->languageId,
          $groupOwnerId,
          array(
            'recipient_surfer' => $groupOwnerId,
            'context_surfer' => $this->data()->currentSurferId(),
            'group_title' => $this->data()->owner->acommunityConnector()->groupSurferRelations()
              ->group()->title,
            'page_url' => $this->data()->reference()->url()->getUrl()
          )
        );
      }
    } elseif ($ressource->type == 'image') {
      $pointer = $ressource->pointer;
      $ressource->pointer = 0;
      $pageRessourceType = $ressource->type;
      $pageRessourceId = $ressource->id;
      $pageRessourceValidSurfer = $ressource->validSurfer;
      $ressource->pointer = $pointer;
      if ($pageRessourceType == 'surfer' && $pageRessourceValidSurfer !== 'is_selected') {
        $this->data()->owner->acommunityConnector()->notify(
          'new-surfer-image-comment',
          $this->data()->languageId,
          $pageRessourceId,
          array(
            'recipient_surfer' => $pageRessourceId,
            'context_surfer' => $this->data()->currentSurferId(),
            'page_url' => $this->data()->reference()->url()->getUrl()
          )
        );
      } elseif ($pageRessourceType == 'group' && $pageRessourceValidSurfer !== 'is_owner') {
        // use owner id from group data previously loaded by ressource object
        $groupOwnerId = $this->data()->owner->acommunityConnector()->groupSurferRelations()
          ->group()->owner;
        $this->data()->owner->acommunityConnector()->notify(
          'new-group-image-comment',
          $this->data()->languageId,
          $groupOwnerId,
          array(
            'recipient_surfer' => $groupOwnerId,
            'context_surfer' => $this->data()->currentSurferId(),
            'group_title' => $this->data()->owner->acommunityConnector()->groupSurferRelations()
              ->group()->title,
            'page_url' => $this->data()->reference()->url()->getUrl()
          )
        );
      }
    }
    // set last change of comment ressource
    $this->data()->setLastChangeTime('comments:'.$ressource->type.'_'.$ressource->id);
    $this->data()->setLastChangeTime('comments');
  }

  /**
  * Show error message
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackShowError($context, $dialog) {
    $this->errorMessage(
      sprintf(
        $this->data()->messages['dialog_input_error'],
        implode(', ', $dialog->errors()->getSourceCaptions())
      )
    );
  }
}