<?php

require_once(dirname(__FILE__).'/../../../../../../Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/external/ACommunity/Ui/Content/Ressource.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/external/ACommunity/Ui/Content.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/external/ACommunity/Connector.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/external/ACommunity/Group/Surfer/Relations.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/_base/community/connector_surfers.php');

class parent_dummy {
  public $moduleObj = NULL;
  public function __construct() {
    $this->moduleObj = new base_content_dummy();
    $this->moduleObj->paramName = 'acpd';
    $this->moduleObj->params = array('parent' => 'dummy');
  }
}
class base_content_dummy extends base_content {
  public function __construct() {
    $this->paramName = 'accd';
    $this->params = array('content' => 'dummy');
  }
}
class base_actionbox_dummy extends base_actionbox {
  public $parentObj = NULL;
  public function __construct() {
    $this->parentObj = new parent_dummy();
    $this->paramName = 'acbd';
    $this->params = array('box' => 'dummy');
  }
}
class ACommunityImageGalleryPage_dummy extends base_content_dummy {
  public $surferHasGroupAccess = FALSE;
  public function callbackGetCurrentImageId() {
    return NULL;
  }
}
class base_surfer_dummy {
  public $isValid = FALSE;
  public $surfer = array('surfer_id' => NULL, 'surfer_handle' => NULL);
}
class contentGroup_dummy {
  public $id = NULL;
  public $handle = NULL;
  public $public = NULL;
  public function load() {}
}

class ACommunityUiContentRessourceTests extends PapayaTestCase {

  private function _getRessourceWithSourceData(
      $moduleIsPage = TRUE,
      $params = array('module' => 'active', 'ressource' => 'selected', 'something' => 'else'),
      $paramName = 'module',
      $parentModuleObj = NULL,
      $instance = FALSE
    ) {
    if ($moduleIsPage == TRUE) {
      if ($parentModuleObj != NULL) {
        $module = $parentModuleObj;
      } else {
        $module = new base_content_dummy();
      }
      $module->paramName = $paramName;
      $module->params = $params;
    } else {
      $module = new base_actionbox_dummy();
      if ($parentModuleObj != NULL) {
        $module->parentObj->moduleObj = $parentModuleObj;
      }
      $module->parentObj->moduleObj->paramName = $paramName;
      $module->parentObj->moduleObj->params = $params;
    }
    if ($instance == TRUE) {
      return ACommunityUiContentRessource::getInstance($module, TRUE);
    } else {
      return new ACommunityUiContentRessource($module);
    }
  }

  /**
   * @covers ACommunityUiContentRessource::__construct
   */
  public function testConstructorAndModuleDataWithBaseContent() {
    $baseContent = new base_content_dummy();
    $ressource = new ACommunityUiContentRessource($baseContent);
    $this->assertSame(
      $baseContent, $this->readAttribute($ressource, '_module')
    );
    $this->assertEquals(
      TRUE, $this->readAttribute($ressource, '_moduleIsPage')
    );
  }

  /**
   * @covers ACommunityUiContentRessource::__construct
   */
  public function testConstructorAndModuleDataWithBaseActionbox() {
    $baseActionbox = new base_actionbox_dummy();
    $ressource = new ACommunityUiContentRessource($baseActionbox);
    $this->assertSame(
      $baseActionbox, $this->readAttribute($ressource, '_module')
    );
    $this->assertEquals(
      FALSe, $this->readAttribute($ressource, '_moduleIsPage')
    );
  }

  /**
   * @covers ACommunityUiContentRessource::__construct
   * @covers ACommunityUiContentRessource::_initializeSourceParameters
   */
  public function testConstructorAndSourceDataWithBaseContent() {
    $baseContent = new base_content_dummy();
    $ressource = new ACommunityUiContentRessource($baseContent);
    $this->assertEquals(
      $baseContent->paramName, $this->readAttribute($ressource, '_sourceParameterGroup')
    );
    $this->assertEquals(
      $baseContent->params, $this->readAttribute($ressource, '_sourceParameters')
    );
  }

  /**
   * @covers ACommunityUiContentRessource::__construct
   * @covers ACommunityUiContentRessource::_initializeSourceParameters
   */
  public function testConstructorAndSourceDataWithBaseActionbox() {
    $baseActionbox = new base_actionbox_dummy();
    $ressource = new ACommunityUiContentRessource($baseActionbox);
    $this->assertEquals(
      $baseActionbox->parentObj->moduleObj->paramName,
      $this->readAttribute($ressource, '_sourceParameterGroup')
    );
    $this->assertEquals(
      $baseActionbox->parentObj->moduleObj->params,
      $this->readAttribute($ressource, '_sourceParameters')
    );
  }

  /**
   * @covers ACommunityUiContentRessource::getSourceParameter
   */
  public function testGetSourceParameter() {
    $ressource = $this->_getRessourceWithSourceData();
    $this->assertEquals('active', $ressource->getSourceParameter('module'));
  }

  /**
   * @covers ACommunityUiContentRessource::getSourceParameter
   */
  public function testGetSourceParameterWithNullParameterName() {
    $ressource = $this->_getRessourceWithSourceData();
    $this->assertEquals(NULL, $ressource->getSourceParameter(NULL));
  }

  /**
   * @covers ACommunityUiContentRessource::sourceHasParameter
   */
  public function testSourceHasParameter() {
    $ressource = $this->_getRessourceWithSourceData();
    $this->assertEquals(TRUE, $ressource->sourceHasParameter('module'));
  }

  /**
   * @covers ACommunityUiContentRessource::sourceHasParameter
   */
  public function testSourceHasParameterEmptyValueOnNotEmpty() {
    $ressource = $this->_getRessourceWithSourceData(TRUE, array('module' => ''));
    $this->assertEquals(FALSE, $ressource->sourceHasParameter('module'));
  }

  /**
   * @covers ACommunityUiContentRessource::sourceHasParameter
   */
  public function testSourceHasParameterEmptyValueOnIsset() {
    $ressource = $this->_getRessourceWithSourceData(TRUE, array('module' => ''));
    $this->assertEquals(TRUE, $ressource->sourceHasParameter('module', FALSE));
  }

  /**
   * @covers ACommunityUiContentRessource::sourceHasClass
   */
  public function testSourceHasClassWithBaseContent() {
    $ressource = $this->_getRessourceWithSourceData();
    $this->assertEquals(TRUE, $ressource->sourceHasClass('base_content_dummy'));
  }

  /**
   * @covers ACommunityUiContentRessource::sourceHasClass
   */
  public function testSourceHasClassWithBaseContentGetClass() {
    $ressource = $this->_getRessourceWithSourceData();
    $this->assertEquals('base_content_dummy', $ressource->sourceHasClass(NULL));
  }

  /**
   * @covers ACommunityUiContentRessource::sourceHasClass
   */
  public function testSourceHasClassWithBaseActionbox() {
    $ressource = $this->_getRessourceWithSourceData(FALSE);
    $this->assertEquals(TRUE, $ressource->sourceHasClass('base_content_dummy'));
  }

  /**
   * @covers ACommunityUiContentRessource::loadSourceDisplayMode
   */
  public function testLoadSourceDisplayMode() {
    $ressource = $this->_getRessourceWithSourceData(TRUE, array('mode' => 'test'));
    $this->assertEquals(TRUE, $ressource->loadSourceDisplayMode('mode'));
    $this->assertEquals('test', $this->readAttribute($ressource, 'displayMode'));
  }

  /**
   * @covers ACommunityUiContentRessource::loadSourceDisplayMode
   */
  public function testLoadSourceDisplayModeNonExistingParameter() {
    $ressource = $this->_getRessourceWithSourceData(TRUE, array('mode' => 'test'));
    $this->assertEquals(FALSE, $ressource->loadSourceDisplayMode('mode_special'));
    $this->assertEquals(NULL, $this->readAttribute($ressource, 'displayMode'));
  }

  /**
   * @covers ACommunityUiContentRessource::detectStopParameter
   */
  public function testDetectStopParameterParameterEmptyParameterNames() {
    $ressource = $this->_getRessourceWithSourceData();
    $stopParameterNames = array();
    $this->assertEquals(FALSE, $ressource->detectStopParameter($stopParameterNames));
  }

  /**
   * @covers ACommunityUiContentRessource::detectStopParameter
   */
  public function testDetectStopParameterParameterWithOneParameterName() {
    $ressource = $this->_getRessourceWithSourceData();
    $stopParameterNames = 'module';
    $this->assertEquals(TRUE, $ressource->detectStopParameter($stopParameterNames));
  }

  /**
   * @covers ACommunityUiContentRessource::detectStopParameter
   */
  public function testDetectStopParameterParameterWithTwoParameterNames() {
    $ressource = $this->_getRessourceWithSourceData();
    $stopParameterNames = array('module', 'this');
    $this->assertEquals(TRUE, $ressource->detectStopParameter($stopParameterNames));
  }

  /**
   * @covers ACommunityUiContentRessource::detectStopParameter
   */
  public function testDetectStopParameterParameterWithTwoParameterNamesAndType() {
    $ressource = $this->_getRessourceWithSourceData();
    $stopParameterNames = array('module', 'this');
    $this->assertEquals(FALSE, $ressource->detectStopParameter($stopParameterNames, 'something'));
  }

  /**
   * @covers ACommunityUiContentRessource::detectStopParameter
   */
  public function testDetectStopParameterParameterWithTwoTypeParameterNamesAndType() {
    $ressource = $this->_getRessourceWithSourceData();
    $stopParameterNames = array('something' => array('module', 'this'));
    $this->assertEquals(TRUE, $ressource->detectStopParameter($stopParameterNames, 'something'));
  }

  /**
   * @covers ACommunityUiContentRessource::detectStopParameter
   */
  public function testDetectStopParameterParameterWithOneParameterNameAndOverwritePropertiesFlag() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->id = 123;
    $ressource->handle = 'ahuman';
    $ressource->type = 'special';
    $stopParameterNames = 'module';
    $ressource->detectStopParameter($stopParameterNames, NULL, TRUE);
    $this->assertEquals(NULL, $ressource->id);
    $this->assertEquals(NULL, $ressource->type);
    $this->assertEquals(NULL, $ressource->handle);
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithEmptyParameterNames() {
    $ressource = $this->_getRessourceWithSourceData();
    $parameterNames = array();
    $this->assertEquals(FALSE, $ressource->detectSourceParameterValue($parameterNames));
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithOneParameterNameAndNoResultValue() {
    $ressource = $this->_getRessourceWithSourceData();
    $parameterNames = 'invalid';
    $this->assertEquals(FALSE, $ressource->detectSourceParameterValue($parameterNames));
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithOneParameterNameAndResultValue() {
    $ressource = $this->_getRessourceWithSourceData();
    $parameterNames = 'module';
    $this->assertEquals('active', $ressource->detectSourceParameterValue($parameterNames));
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithTwoParameterNamesAndResultValue() {
    $ressource = $this->_getRessourceWithSourceData();
    $parameterNames = array('module', 'somethinelse');
    $this->assertEquals('active', $ressource->detectSourceParameterValue($parameterNames));
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithOneTypeParameterNameAndNoResultValue() {
    $ressource = $this->_getRessourceWithSourceData();
    $parameterNames = array('ressource_type' => 'invalid');
    $this->assertEquals(FALSE, $ressource->detectSourceParameterValue($parameterNames));
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithOneTypeParameterNameAndResultValues() {
    $ressource = $this->_getRessourceWithSourceData();
    $parameterNames = array('ressource_type' => 'module');
    $this->assertEquals(
      array('ressource_type', 'active'), $ressource->detectSourceParameterValue($parameterNames)
    );
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithTwoTypeParameterNameAndResultValues() {
    $ressource = $this->_getRessourceWithSourceData();
    $parameterNames = array('ressource_type' => array('module', 'something_else'));
    $this->assertEquals(
      array('ressource_type', 'active'), $ressource->detectSourceParameterValue($parameterNames)
    );
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithOneTypeParameterNameAndEmptyValueResult() {
    $ressource = $this->_getRessourceWithSourceData(TRUE, array('nothing' => ''));
    $parameterNames = array('ressource_type' => 'nothing');
    $this->assertEquals(
      FALSE, $ressource->detectSourceParameterValue($parameterNames)
    );
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithOneParameterNameAndEmptyValueResult() {
    $ressource = $this->_getRessourceWithSourceData(TRUE, array('nothing' => ''));
    $parameterNames = array('ressource_type' => 'nothing');
    $this->assertEquals(
      FALSE, $ressource->detectSourceParameterValue($parameterNames)
    );
  }

  /**
   * @covers ACommunityUiContentRessource::detectSourceParameterValue
   */
  public function testDetectSourceParameterValueWithOneParameterNameAndType() {
    $ressource = $this->_getRessourceWithSourceData();
    $parameterNames = array('ressource_type' => 'module');
    $this->assertEquals(
      'active', $ressource->detectSourceParameterValue($parameterNames, 'ressource_type')
    );
  }

  /**
   * @covers ACommunityUiContentRessource::filterSourceParameters
   */
  public function testFilterSourceParametersWithUnsetParameterNames() {
    $ressource = $this->_getRessourceWithSourceData();
    $filteredParameters = $ressource->filterSourceParameters(NULL);
    $this->assertEquals($filteredParameters, $this->readAttribute($ressource, '_sourceParameters'));
  }

  /**
   * @covers ACommunityUiContentRessource::filterSourceParameters
   */
  public function testFilterSourceParametersWithEmptyParameterNames() {
    $ressource = $this->_getRessourceWithSourceData();
    $filteredParameters = $ressource->filterSourceParameters(array());
    $this->assertEquals(array(), $filteredParameters);
  }

  /**
   * @covers ACommunityUiContentRessource::filterSourceParameters
   */
  public function testFilterSourceParametersWithInvalidParameterNames() {
    $ressource = $this->_getRessourceWithSourceData();
    $filteredParameters = $ressource->filterSourceParameters(array('invalid1', 'invalid2'));
    $this->assertEquals(array(), $filteredParameters);
  }

  /**
   * @covers ACommunityUiContentRessource::filterSourceParameters
   */
  public function testFilterSourceParametersWithValidParameterNames() {
    $ressource = $this->_getRessourceWithSourceData();
    $filteredParameters = $ressource->filterSourceParameters(array('module', 'ressource'));
    $this->assertEquals(array('module' => 'active', 'ressource' => 'selected'), $filteredParameters);
  }

  /**
   * @covers ACommunityUiContentRessource::filterSourceParameters
   */
  public function testFilterSourceParametersWithValidParameterName() {
    $ressource = $this->_getRessourceWithSourceData();
    $filteredParameters = $ressource->filterSourceParameters('module');
    $this->assertEquals(array('module' => 'active'), $filteredParameters);
  }

  /**
   * @covers ACommunityUiContentRessource::filterSourceParameters
   */
  public function testFilterSourceParametersWithValidParameterNameByType() {
    $ressource = $this->_getRessourceWithSourceData();
    $filteredParameters = $ressource->filterSourceParameters(
      array('ressource_type' => 'module'), 'ressource_type'
    );
    $this->assertEquals(array('module' => 'active'), $filteredParameters);
  }

  /**
   * @covers ACommunityUiContentRessource::filterSourceParameters
   */
  public function testFilterSourceParametersWithValidParameterNameNoParametersOverwriting() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->filterSourceParameters('module');
    $this->assertEquals(array(), $ressource->parameters());
  }

  /**
   * @covers ACommunityUiContentRessource::filterSourceParameters
   */
  public function testFilterSourceParametersWithValidParameterNameWithParametersOverwriting() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->filterSourceParameters('module', NULL, TRUE);
    $this->assertEquals(
      array('module' => array('module' => 'active')), $ressource->parameters()
    );
  }

  /**
   * @covers ACommunityUiContentRessource::parameters
   */
  public function testParametersWithNullParameters() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->parameters(NULL, NULL);
    $this->assertEquals(array(), $ressource->parameters());
  }

  /**
   * @covers ACommunityUiContentRessource::parameters
   */
  public function testParametersWithFirstParameters() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->parameters('test1', NULL);
    $this->assertEquals(array(), $ressource->parameters());
  }

  /**
   * @covers ACommunityUiContentRessource::parameters
   */
  public function testParametersWithSecondParameters() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->parameters(NULL, 'test2');
    $this->assertEquals(array(), $ressource->parameters());
  }

  /**
   * @covers ACommunityUiContentRessource::parameters
   */
  public function testParametersWithBothParameters() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->parameters('group', array('parameter' => 'value'));
    $this->assertEquals(
      array('group' => array('parameter' => 'value')), $ressource->parameters()
    );
  }

  /**
   * @covers ACommunityUiContentRessource::parameters
   */
  public function testParametersWithBothParametersAndTwoDifferentGroups() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->parameters('group1', array('parameter' => 'value'));
    $ressource->parameters('group2', array('parameter' => 'value'));
    $this->assertEquals(
      array('group1' => array('parameter' => 'value'), 'group2' => array('parameter' => 'value')),
      $ressource->parameters()
    );
  }

  /**
   * @covers ACommunityUiContentRessource::parameters
   */
  public function testParametersWithBothParametersAndTwoCallsOneGroup() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->parameters('group1', array('parameter' => 'value'));
    $ressource->parameters();
    $this->assertEquals(
      array('group1' => array('parameter' => 'value')), $ressource->parameters()
    );
  }

  /**
   * @covers ACommunityUiContentRessource::parameters
   */
  public function testParametersWithBothParametersAndTwoCallsOneGroupAndReset() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->parameters('group1', array('parameter' => 'value'));
    $ressource->parameters(NULL, NULL, TRUE);
    $this->assertEquals(array(), $ressource->parameters());
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithNoParameters() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->set();
    $this->assertEquals(NULL, $ressource->id);
    $this->assertEquals(TRUE, $ressource->isInvalid);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypePageParameterOnly() {
    $ressource = $this->_getRessourceWithSourceData();
    $request = new PapayaRequest();
    $request->papaya($this->getMockApplicationObject());
    $request->setParameters(
      PapayaRequest::SOURCE_PATH, new PapayaRequestParameters(array('page_id' => 42))
    );
    $application = $this->getMockApplicationObject(array('request' => $request));
    $ressource->papaya($application);
    $ressource->set('page');
    $this->assertEquals(42, $ressource->id);
    $this->assertEquals('page', $ressource->type);
    $this->assertEquals(FALSE, $ressource->isInvalid);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypePageParameterAndSourceParameterValue() {
    $ressource = $this->_getRessourceWithSourceData();
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $ressource->papaya($application);
    $ressource->set('page', NULL, NULL, NULL, 42);
    $this->assertEquals(42, $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypePageParameterAndSourceParameterValueWithStopParameter() {
    $ressource = $this->_getRessourceWithSourceData();
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $ressource->papaya($application);
    $this->assertEquals(
      FALSE, $ressource->set('page', NULL, NULL, array('page' => 'module'), 42)
    );
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypePageParameterAndSourceParameterName() {
    $ressource = $this->_getRessourceWithSourceData(TRUE, array('page_id' => 42));
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $ressource->papaya($application);
    $this->assertEquals(
      TRUE, $ressource->set('page', array('page' => 'page_id'), NULL, NULL, NULL)
    );
    $this->assertEquals(42, $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypePageParameterAndSourceParameterNameAndFilterParameterName() {
    $ressource = $this->_getRessourceWithSourceData(TRUE, array('page_id' => 42));
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $ressource->papaya($application);
    $ressource->set('page', array('page' => 'page_id'), array('page' => 'page_id'), NULL, NULL);
    $this->assertEquals(array('module' => array('page_id' => 42)), $ressource->parameters());
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeGroupAndNoFurtherParameters() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->set('group');
    $this->assertEquals(NULL, $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeGroupAndSourceParameterValueAndNoLoginOnPublicGroup() {
    $groupHandle = 'my_handle';
    $groupId = 23445;

    $contentGroup = $this->getMock('contentGroup_dummy');
    $contentGroup->id = $groupId;
    $contentGroup->public = 1;
    $contentGroup
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo(array('handle' => $groupHandle)))
      ->will($this->returnValue(TRUE));

    $groupSurferRelations = $this->getMock('ACommunityGroupSurferRelations');
    $groupSurferRelations
      ->expects($this->any())
      ->method('group')
      ->will($this->returnValue($contentGroup));
    $connector = $this->getMock('ACommunityConnector');
    $connector
      ->expects($this->any())
      ->method('groupSurferRelations')
      ->will($this->returnValue($groupSurferRelations));

    $uiContent = $this->getMock('ACommunityUiContent');
    $uiContent
      ->expects($this->any())
      ->method('acommunityConnector')
      ->will($this->returnValue($connector));

    $surfer = new base_surfer_dummy();
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));

    $ressource = $this->_getRessourceWithSourceData();
    $ressource->papaya($application);
    $ressource->uiContent = $uiContent;
    $ressource->set('group', NULL, NULL, NULL, $groupHandle);
    $this->assertEquals($groupId, $ressource->id);
    $this->assertEquals($groupHandle, $ressource->handle);
  }

  public static function providerGroupSurferStatusOnPublicGroup() {
    return array(
      'group surfer is member does not need valid surfer' =>
        array(FALSE, array('is_member' => 1, 'is_owner' => 0), TRUE),
      'group surfer is owner does not need valid surfer' =>
        array(FALSE, array('is_member' => 0, 'is_owner' => 1), TRUE),
      'group surfer is none does not need valid surfer' =>
        array(FALSE, array('is_member' => 0, 'is_owner' => 0), TRUE),
      'group surfer is member needs valid surfer' =>
        array(TRUE, array('is_member' => 1, 'is_owner' => 0), TRUE),
      'group surfer is owner needs valid surfer' =>
        array(TRUE, array('is_member' => 0, 'is_owner' => 1), TRUE),
      'group surfer is none needs valid surfer' =>
        array(TRUE, array('is_member' => 0, 'is_owner' => 0), FALSE),
      'group surfer is member owner needed' =>
        array('is_owner', array('is_member' => 1, 'is_owner' => 0), FALSE),
      'group surfer is owner owner needed' =>
        array('is_owner', array('is_member' => 0, 'is_owner' => 1), TRUE),
      'group surfer is none owner needed' =>
        array('is_owner', array('is_member' => 0, 'is_owner' => 0), FALSE),
      'group surfer is member member needed' =>
        array('is_member', array('is_member' => 1, 'is_owner' => 0), TRUE),
      'group surfer is owner member needed' =>
        array('is_member', array('is_member' => 0, 'is_owner' => 1), FALSE),
      'group surfer is none member needed' =>
        array('is_member', array('is_member' => 0, 'is_owner' => 0), FALSE),
    );
  }

  /**
   * @covers ACommunityUiContentRessource::set
   * @dataProvider providerGroupSurferStatusOnPublicGroup
   */
  public function testSetWithTypeGroupAndSourceParameterValueAndLoginWithSurferValidationOnPublicGroup(
                    $needsValidSurfer, $groupSurferStatus, $resultStatus) {
    $currentSurferHandle = 'jamesbond';
    $currentSurferId = '28ba9f2d3f1047019be54dac44a30c7d';
    $groupHandle = 'my_handle';
    $groupId = 23445;

    $contentGroup = $this->getMock('contentGroup_dummy');
    $contentGroup->id = $groupId;
    $contentGroup->public = 1;
    $contentGroup
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo(array('handle' => $groupHandle)))
      ->will($this->returnValue(TRUE));

    $groupSurferRelations = $this->getMock('ACommunityGroupSurferRelations');
    $groupSurferRelations
      ->expects($this->any())
      ->method('group')
      ->will($this->returnValue($contentGroup));
    $groupSurferRelations
      ->expects($this->once())
      ->method('status')
      ->with($this->equalTo($groupId), $this->equalTo($currentSurferId))
      ->will($this->returnValue($groupSurferStatus));

    $connector = $this->getMock('ACommunityConnector');
    $connector
      ->expects($this->any())
      ->method('groupSurferRelations')
      ->will($this->returnValue($groupSurferRelations));
    $uiContent = $this->getMock('ACommunityUiContent');
    $uiContent
      ->expects($this->any())
      ->method('acommunityConnector')
      ->will($this->returnValue($connector));

    $surfer = new base_surfer_dummy();
    $surfer->isValid = TRUE;
    $surfer->surfer['surfer_id'] = $currentSurferId;
    $surfer->surfer['surfer_handle'] = $currentSurferHandle;
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));

    $ressource = $this->_getRessourceWithSourceData();
    $ressource->papaya($application);
    $ressource->uiContent = $uiContent;
    $ressource->set('group', NULL, NULL, NULL, $groupHandle, $needsValidSurfer);
    $this->assertEquals($resultStatus, $groupId == $ressource->id);
  }

  public static function providerGroupSurferStatusOnPrivateGroup() {
    return array(
      'group surfer is member does not need valid surfer' =>
        array(FALSE, array('is_member' => 1, 'is_owner' => 0), TRUE),
      'group surfer is owner does not need valid surfer' =>
        array(FALSE, array('is_member' => 0, 'is_owner' => 1), TRUE),
      'group surfer is none does not need valid surfer' =>
        array(FALSE, array('is_member' => 0, 'is_owner' => 0), FALSE), // needs valid surfer always
      'group surfer is member needs valid surfer' =>
        array(TRUE, array('is_member' => 1, 'is_owner' => 0), TRUE),
      'group surfer is owner needs valid surfer' =>
        array(TRUE, array('is_member' => 0, 'is_owner' => 1), TRUE),
      'group surfer is none needs valid surfer' =>
        array(TRUE, array('is_member' => 0, 'is_owner' => 0), FALSE),
      'group surfer is member owner needed' =>
        array('is_owner', array('is_member' => 1, 'is_owner' => 0), FALSE),
      'group surfer is owner owner needed' =>
        array('is_owner', array('is_member' => 0, 'is_owner' => 1), TRUE),
      'group surfer is none owner needed' =>
        array('is_owner', array('is_member' => 0, 'is_owner' => 0), FALSE),
      'group surfer is member member needed' =>
        array('is_member', array('is_member' => 1, 'is_owner' => 0), TRUE),
      'group surfer is owner member needed' =>
        array('is_member', array('is_member' => 0, 'is_owner' => 1), FALSE),
      'group surfer is none member needed' =>
        array('is_member', array('is_member' => 0, 'is_owner' => 0), FALSE),
    );
  }

  /**
   * @covers ACommunityUiContentRessource::set
   * @dataProvider providerGroupSurferStatusOnPrivateGroup
   */
  public function testSetWithTypeGroupAndSourceParameterValueAndLoginWithSurferValidationOnPrivateGroup(
                    $needsValidSurfer, $groupSurferStatus, $resultStatus) {
    $currentSurferHandle = 'jamesbond';
    $currentSurferId = '28ba9f2d3f1047019be54dac44a30c7d';
    $groupHandle = 'my_handle';
    $groupId = 23445;

    $contentGroup = $this->getMock('contentGroup_dummy');
    $contentGroup->id = $groupId;
    $contentGroup->public = 0;
    $contentGroup
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo(array('handle' => $groupHandle)))
      ->will($this->returnValue(TRUE));

    $groupSurferRelations = $this->getMock('ACommunityGroupSurferRelations');
    $groupSurferRelations
      ->expects($this->any())
      ->method('group')
      ->will($this->returnValue($contentGroup));
    $groupSurferRelations
      ->expects($this->once())
      ->method('status')
      ->with($this->equalTo($groupId), $this->equalTo($currentSurferId))
      ->will($this->returnValue($groupSurferStatus));

    $connector = $this->getMock('ACommunityConnector');
    $connector
      ->expects($this->any())
      ->method('groupSurferRelations')
      ->will($this->returnValue($groupSurferRelations));
    $uiContent = $this->getMock('ACommunityUiContent');
    $uiContent
      ->expects($this->any())
      ->method('acommunityConnector')
      ->will($this->returnValue($connector));

    $surfer = new base_surfer_dummy();
    $surfer->isValid = TRUE;
    $surfer->surfer['surfer_id'] = $currentSurferId;
    $surfer->surfer['surfer_handle'] = $currentSurferHandle;
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));

    $ressource = $this->_getRessourceWithSourceData();
    $ressource->papaya($application);
    $ressource->uiContent = $uiContent;
    $ressource->set('group', NULL, NULL, NULL, $groupHandle, $needsValidSurfer);
    $this->assertEquals($resultStatus, $groupId == $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeImageAndNoFurtherParameters() {
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->set('image');
    $this->assertEquals(NULL, $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeImageAndSourceParameterValue() {
    $imageId = '9c8fe9984b6448f1bb71f2a25d89e8bc';
    $ressource = $this->_getRessourceWithSourceData();
    $ressource->set('image', NULL, NULL, NULL, $imageId);
    $this->assertEquals($imageId, $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeImageWithImageIdByImageGalleryDetailPage() {
    $imageId = '9c8fe9984b6448f1bb71f2a25d89e8bc';
    $imageGalleryPage = $this->getMock('ACommunityImageGalleryPage_dummy');
    $imageGalleryPage
      ->expects($this->once())
      ->method('callbackGetCurrentImageId')
      ->will($this->returnValue($imageId));
    $ressource = $this->_getRessourceWithSourceData(
      FALSE, array('enlarge' => 1), 'acig', $imageGalleryPage
    );
    $ressource->set('image');
    $this->assertEquals($imageId, $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeSurferAndNoFurtherParameters() {
    $ressource = $this->_getRessourceWithSourceData();
    $surfer = new base_surfer_dummy();
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));
    $ressource->papaya($application);
    $ressource->set('surfer');
    $this->assertEquals(NULL, $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeSurferAndNoFurtherParametersAndNoLogin() {
    $ressource = $this->_getRessourceWithSourceData();
    $surfer = new base_surfer_dummy();
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));
    $ressource->papaya($application);
    $ressource->set('surfer');
    $this->assertEquals(NULL, $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeSurferAndNoFurtherParametersAndLogin() {
    $surferHandle = 'surferhandle';
    $surferId = 'ea90c0a2371b44efb617c82853ad036e';
    $ressource = $this->_getRessourceWithSourceData();
    $surfer = new base_surfer_dummy();
    $surfer->isValid = TRUE;
    $surfer->surfer['surfer_id'] = $surferId;
    $surfer->surfer['surfer_handle'] = $surferHandle;
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));
    $ressource->papaya($application);
    $ressource->set('surfer');
    $this->assertEquals($surferId, $ressource->id);
    $this->assertEquals($surferHandle, $ressource->handle);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeSurferAndSourceParameterValueNoLogin() {
    $selectedSurferHandle = 'thesurfer';
    $selectedSurferId = '610ffec2030548049355a8aad1d23157';

    $connector = $this->getMock('connector_surfers');
    $connector
      ->expects($this->once())
      ->method('getIdByHandle')
      ->with($this->equalTo($selectedSurferHandle))
      ->will($this->returnValue($selectedSurferId));
    $uiContent = $this->getMock('ACommunityUiContent');
    $uiContent
      ->expects($this->once())
      ->method('communityConnector')
      ->will($this->returnValue($connector));

    $ressource = $this->_getRessourceWithSourceData();
    $ressource->uiContent = $uiContent;
    $surfer = new base_surfer_dummy();
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));
    $ressource->papaya($application);
    $ressource->set('surfer', NULL, NULL, NULL, $selectedSurferHandle);
    $this->assertEquals($selectedSurferId, $ressource->id);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeSurferAndSourceParameterValueWithAnotherSurferLogin() {
    $currentSurferHandle = 'anothersurfer';
    $currentSurferId = 'ea90c0a2371b44efb617c82853ad036e';
    $selectedSurferHandle = 'thesurfer';
    $selectedSurferId = '610ffec2030548049355a8aad1d23157';

    $connector = $this->getMock('connector_surfers');
    $connector
      ->expects($this->once())
      ->method('getIdByHandle')
      ->with($this->equalTo($selectedSurferHandle))
      ->will($this->returnValue($selectedSurferId));
    $uiContent = $this->getMock('ACommunityUiContent');
    $uiContent
      ->expects($this->once())
      ->method('communityConnector')
      ->will($this->returnValue($connector));

    $ressource = $this->_getRessourceWithSourceData();
    $ressource->uiContent = $uiContent;
    $surfer = new base_surfer_dummy();
    $surfer->isValid = TRUE;
    $surfer->surfer['surfer_id'] = $currentSurferId;
    $surfer->surfer['surfer_handle'] = $currentSurferHandle;
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));
    $ressource->papaya($application);
    $ressource->set('surfer', NULL, NULL, NULL, $selectedSurferHandle);
    $this->assertEquals($selectedSurferId, $ressource->id);
    $this->assertEquals(FALSE, $ressource->validSurfer);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeSurferAndSourceParameterValueWithValidSurferLogin() {
    $currentSurferHandle = 'thesurfer';
    $currentSurferId = 'ea90c0a2371b44efb617c82853ad036e';
    $selectedSurferHandle = 'thesurfer';
    $selectedSurferId = 'ea90c0a2371b44efb617c82853ad036e';

    $connector = $this->getMock('connector_surfers');
    $connector
      ->expects($this->once())
      ->method('getIdByHandle')
      ->with($this->equalTo($selectedSurferHandle))
      ->will($this->returnValue($selectedSurferId));
    $uiContent = $this->getMock('ACommunityUiContent');
    $uiContent
      ->expects($this->once())
      ->method('communityConnector')
      ->will($this->returnValue($connector));

    $ressource = $this->_getRessourceWithSourceData();
    $ressource->uiContent = $uiContent;
    $surfer = new base_surfer_dummy();
    $surfer->isValid = TRUE;
    $surfer->surfer['surfer_id'] = $currentSurferId;
    $surfer->surfer['surfer_handle'] = $currentSurferHandle;
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));
    $ressource->papaya($application);
    $ressource->set('surfer', NULL, NULL, NULL, $selectedSurferHandle);
    $this->assertEquals($currentSurferId, $ressource->id);
    $this->assertEquals($currentSurferHandle, $ressource->handle);
    $this->assertEquals(TRUE, $ressource->validSurfer);
  }

  /**
   * @covers ACommunityUiContentRessource::set
   */
  public function testSetWithTypeSurferAndSourceParameterValueNotValidSurferLoginButNeedsLogin() {
    $currentSurferHandle = 'anothersurfer';
    $currentSurferId = 'ea90c0a2371b44efb617c82853ad036e';
    $selectedSurferHandle = 'thesurfer';
    $selectedSurferId = '610ffec2030548049355a8aad1d23157';

    $connector = $this->getMock('connector_surfers');
    $connector
      ->expects($this->once())
      ->method('getIdByHandle')
      ->with($this->equalTo($selectedSurferHandle))
      ->will($this->returnValue($selectedSurferId));
    $uiContent = $this->getMock('ACommunityUiContent');
    $uiContent
      ->expects($this->once())
      ->method('communityConnector')
      ->will($this->returnValue($connector));

    $ressource = $this->_getRessourceWithSourceData();
    $ressource->uiContent = $uiContent;
    $surfer = new base_surfer_dummy();
    $surfer->isValid = TRUE;
    $surfer->surfer['surfer_id'] = $currentSurferId;
    $surfer->surfer['surfer_handle'] = $currentSurferHandle;
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));
    $ressource->papaya($application);
    $ressource->set('surfer', NULL, NULL, NULL, $selectedSurferHandle, TRUE);
    $this->assertEquals(NULL, $ressource->id);
  }

  /**
   * Scenario TESTS
   */
  public static function providerScenarioImageGalleryPage() {
    $anotherSurferHandle = 'anothersurfer';
    $anotherSurferId = 'ea90c0a2371b44efb617c82853ad036e';
    $anotherSurfer = array($anotherSurferHandle, $anotherSurferId, TRUE);
    $surferHandle = 'mynameisbond';
    $surferId = '77f623da00d54f8eb904019cdb4fdfb2';
    $activeSurfer = array($surferHandle, $surferId, TRUE);
    $groupHandle = 'monsterag';
    $groupId = 356;
    $pageRessourceParametersSurfer = array('surfer_handle' => $surferHandle);
    $pageRessourceParametersSurferWithFolder = array('surfer_handle' => $surferHandle, 'folder_id' => 2);
    $pageRessourceParametersGroup = array('group_handle' => $groupHandle);
    $pageRessourceParametersGroupWithFolder = array('group_handle' => $groupHandle, 'folder_id' => 4);
    $foldersRessourceParametersSurfer = $pageRessourceParametersSurfer;
    $foldersRessourceParametersGroup = $pageRessourceParametersGroup;
    $uploadRessourceParametersSurfer = array('acig' => $pageRessourceParametersSurfer);
    $uploadRessourceParametersGroup = array('acig' => $pageRessourceParametersGroup);
    $uploadRessourceParametersSurferWithFolder = array('acig' => $pageRessourceParametersSurferWithFolder);
    $uploadRessourceParametersGroupWithFolder = array('acig' => $pageRessourceParametersGroupWithFolder);
    $noUploadRessourceParameters = array();
    $uploadRessourceParametersSurferPageSelection = array(
      'acig' => array('surfer_handle' => $surferHandle, 'folder_id' => 2, 'offset' => 4)
    );
    $uploadRessourceParametersGroupPageSelection = array(
      'acig' => array('group_handle' => $groupHandle, 'folder_id' => 4, 'offset' => 8)
    );
    $invalidUploadBoxRessourceId = NULL;
    $validUploadBoxSurferId = $surferId;
    $validUploadBoxGroupId = $groupId;

    // parameter order sourceParameters, ressourceId, activeSurfer,
    return array(
      'surfer handly only with another surfer login' => array(
        array('surfer_handle' => $surferHandle), $surferId,
        $pageRessourceParametersSurfer, $foldersRessourceParametersSurfer, $noUploadRessourceParameters,
        $invalidUploadBoxRessourceId, $anotherSurfer
      ),
      'group handle only with login' => array(
        array('group_handle' => $groupHandle), $groupId,
        $pageRessourceParametersGroup, $foldersRessourceParametersGroup, $uploadRessourceParametersGroup,
        $validUploadBoxGroupId, $activeSurfer
      ),
      'surfer handle and folder with another surfe rlogin' => array(
        array('surfer_handle' => $surferHandle, 'folder_id' => 2),
        $surferId, $pageRessourceParametersSurferWithFolder, $foldersRessourceParametersSurfer,
        $noUploadRessourceParameters, $invalidUploadBoxRessourceId, $anotherSurfer
      ),
      'surfer handle, delete command and folder with login' => array(
        array('surfer_handle' => $surferHandle, 'folder_id' => 2, 'command' => 'delete_folder'),
        $surferId, $pageRessourceParametersSurfer, $foldersRessourceParametersSurfer,
        $uploadRessourceParametersSurfer, $validUploadBoxSurferId, $activeSurfer
      ),
      'group handle and folder with another surfer login' => array(
        array('group_handle' => $groupHandle, 'folder_id' => 4),
        $groupId, $pageRessourceParametersGroupWithFolder, $foldersRessourceParametersGroup,
        $noUploadRessourceParameters, $invalidUploadBoxRessourceId, $anotherSurfer
      ),
      'group handle, delete folder and folder with login' => array(
        array('group_handle' => $groupHandle, 'folder_id' => 4, 'command' => 'delete_folder'),
        $groupId, $pageRessourceParametersGroup, $foldersRessourceParametersGroup,
        $uploadRessourceParametersGroup, $validUploadBoxGroupId, $activeSurfer
      ),
      'image selection for surfer gallery with folder and login' => array(
        array('surfer_handle' => $surferHandle, 'folder_id' => 2, 'enlarge' => 1, 'offset' => 4, 'index' => 0),
        $surferId, $pageRessourceParametersSurferWithFolder, $foldersRessourceParametersSurfer,
        $noUploadRessourceParameters, $invalidUploadBoxRessourceId, $anotherSurfer
      ),
      'image selection for group gallery with folder and login' => array(
        array('group_handle' => $groupHandle, 'folder_id' => 4, 'enlarge' => 1, 'offset' => 8, 'index' => 4),
        $groupId, $pageRessourceParametersGroupWithFolder, $foldersRessourceParametersGroup,
         $noUploadRessourceParameters, $invalidUploadBoxRessourceId, $activeSurfer
      ),
      'page selection for surfer gallery with folder and another surfer login' => array(
        array('surfer_handle' => $surferHandle, 'folder_id' => 2, 'offset' => 4),
        $surferId, $pageRessourceParametersSurferWithFolder, $foldersRessourceParametersSurfer,
        $noUploadRessourceParameters, $invalidUploadBoxRessourceId, $anotherSurfer
      ),
      'page selection for group gallery with folder and login' => array(
        array('group_handle' => $groupHandle, 'folder_id' => 4, 'offset' => 8),
        $groupId, $pageRessourceParametersGroupWithFolder, $foldersRessourceParametersGroup,
        $uploadRessourceParametersGroupPageSelection, $validUploadBoxGroupId, $activeSurfer
      )
    );
  }

  /**
   * @dataProvider providerScenarioImageGalleryPage
   */
  public function testScenarioImageGalleryPageWithBoxDependencies(
           $sourceParameters, $ressourceId, $pageRessourceParameters, $foldersRessourceParameters,
           $uploadRessourceParameters, $uploadBoxRessourceId, $activeSurfer
         ) {

    $uiContent = $this->getMock('ACommunityUiContent');
    if (isset($sourceParameters['surfer_handle'])) {
      $connector = $this->getMock('connector_surfers');
      $connector
        ->expects($this->any())
        ->method('getIdByHandle')
        ->with($this->equalTo($sourceParameters['surfer_handle']))
        ->will($this->returnValue($ressourceId));
      $uiContent
        ->expects($this->any())
        ->method('communityConnector')
        ->will($this->returnValue($connector));
      $ressourceType = 'surfer';
    }

    if (isset($sourceParameters['group_handle'])) {
      $contentGroup = $this->getMock('contentGroup_dummy');
      $contentGroup->id = $ressourceId;
      $contentGroup->public = 1;
      $contentGroup
        ->expects($this->any())
        ->method('load')
        ->with($this->equalTo(array('handle' => $sourceParameters['group_handle'])))
        ->will($this->returnValue(TRUE));

      $groupSurferRelations = $this->getMock('ACommunityGroupSurferRelations');
      $groupSurferRelations
        ->expects($this->any())
        ->method('group')
        ->will($this->returnValue($contentGroup));
      // status call in page module
      $groupSurferRelations
        ->expects($this->at(3))
        ->method('status')
        ->with($this->equalTo($ressourceId), $this->equalTo($activeSurfer[1]))
        ->will($this->returnValue(array('is_owner' => 1, 'is_member' => 0)));
      if ($uploadRessourceParameters != array()) {
        // status call in upload box if reached
        if ($uploadBoxRessourceId == $ressourceId) {
          // valid surfer
          $groupSurferStatus = array('is_owner' => 1, 'is_member' => 0);
        } else {
          $groupSurferStatus = FALSE;
        }
        $groupSurferRelations
          ->expects($this->at(7))
          ->method('status')
          ->with($this->equalTo($ressourceId), $this->equalTo($activeSurfer[1]))
          ->will($this->returnValue($groupSurferStatus));
      }
      $connector = $this->getMock('ACommunityConnector');
      $connector
        ->expects($this->any())
        ->method('groupSurferRelations')
        ->will($this->returnValue($groupSurferRelations));
      $connector
        ->expects($this->any())
        ->method('getGroupIdByHandle')
        ->with($this->equalTo($sourceParameters['group_handle']))
        ->will($this->returnValue($ressourceId));
      $uiContent
        ->expects($this->any())
        ->method('acommunityConnector')
        ->will($this->returnValue($connector));
      $ressourceType = 'group';
    }

    $surfer = new base_surfer_dummy();
    if (isset($activeSurfer)) {
      $surfer->isValid = $activeSurfer[2];
      $surfer->surfer['surfer_id'] = $activeSurfer[1];
      $surfer->surfer['surfer_handle'] = $activeSurfer[0];
    }
    $application = $this->getMockApplicationObject(array('surfer' => $surfer));
    // initialize ressource instance in gallery page
    $imageGalleryPage = new ACommunityImageGalleryPage_dummy();
    if ($ressourceId == $uploadBoxRessourceId) {
      $imageGalleryPage->surferHasGroupAccess = TRUE;
    }
    $ressource = $this->_getRessourceWithSourceData(TRUE, $sourceParameters, 'acig', $imageGalleryPage, TRUE);
    $ressource->papaya($application);
    $ressource->uiContent = $uiContent;
    $ressource->displayMode = 'gallery';
    $command = $ressource->getSourceParameter('command');
    if ($command != 'delete_folder') {
      $filterParameterNames = array(
        'surfer' => array('surfer_handle', 'folder_id'),
        'group' => array('group_handle', 'folder_id')
      );
    } else {
      $filterParameterNames = array('surfer' => 'surfer_handle', 'group' => 'group_handle');
    }
    $groupHandle = $ressource->getSourceParameter('group_handle');
    $ressource->set(
      isset($groupHandle) ? 'group' : 'surfer',
      array('surfer' => 'surfer_handle', 'group' => 'group_handle'),
      $filterParameterNames
    );

    $this->assertEquals($ressourceId, $ressource->id);
    $this->assertEquals($ressourceType, $ressource->type);
    $this->assertEquals(array('acig' => $pageRessourceParameters), $ressource->parameters());

    // the surfer status box uses an existing ressource instance with another pointer
    $ressource->set('surfer', NULL, array('surfer' => array()));
    $this->assertEquals($surfer->surfer['surfer_id'], $ressource->id);
    $this->assertEquals(array('acig' => array()), $ressource->parameters());

    // page ressource for folders box with the initial pointer and custom ressource parameters
    $ressource->pointer = 0;
    $ressource->filterSourceParameters(
      array('surfer' => 'surfer_handle', 'group' => 'group_handle'), $ressource->type, TRUE
    );
    $this->assertEquals(array('acig' => $foldersRessourceParameters), $ressource->parameters());

    // the upload box uses type/handle by the initial pointer and set additional data with a new pointer
    $ressource->pointer = 0;
    $command = $ressource->getSourceParameter('command');
    if ($command != 'delete_folder') {
      $filterParameterNames = array(
        'surfer' => array('surfer_handle', 'folder_id', 'offset'),
        'group' => array('group_handle', 'folder_id', 'offset')
      );
    } else {
      $filterParameterNames = array(
        'surfer' => array('surfer_handle'), 'group' => array('group_handle')
      );
    }
    $ressource->set(
      $ressource->type,
      NULL,
      $filterParameterNames,
      array('surfer' => 'enlarge', 'group' => 'enlarge'),
      $ressource->handle,
      TRUE
    );
    $this->assertEquals($uploadBoxRessourceId, $ressource->id);
    $this->assertEquals($uploadRessourceParameters, $ressource->parameters());

    // check for overwrite protection of surfer status box data
    $ressource->pointer = 1;
    $this->assertEquals($surfer->surfer['surfer_id'], $ressource->id);
  }

}