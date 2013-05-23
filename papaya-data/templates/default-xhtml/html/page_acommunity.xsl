<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
<!--
  @papaya:modules ACommunitySurferPage, ACommunitySurferGalleryPage, ACommunitySurferEditorPage,
    ACommunitySurferContactsPage, ACommunitySurfersLastActionPage, ACommunitySurfersRegistrationPage,
    ACommunityMessagesPage, ACommunityNotificationSettingsPage
-->

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./page_main.xsl"/>

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/external/ACommunity/Pages.xsl"/>

<!-- to change the output, redefine the imported rules here -->

</xsl:stylesheet>