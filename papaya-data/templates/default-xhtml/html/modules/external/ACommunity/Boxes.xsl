<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

  <xsl:import href="./Ui/Content/Dialog.xsl"/>
  <xsl:import href="./Ui/Content/Paging.xsl"/>
  <xsl:import href="./Ui/Content/Surfers.xsl"/>
  <xsl:import href="./Ui/Content/Comments.xsl"/>

  <xsl:template match="acommunity-comments">
    <xsl:call-template name="acommunity-comments">
      <xsl:with-param name="commandName" select="command/@name" />
      <xsl:with-param name="commandCommentId" select="command/@comment_id" />
      <xsl:with-param name="comments" select="comments" />
      <xsl:with-param name="dialog" select="dialog-box" />
      <xsl:with-param name="dialogMessage" select="dialog-message" />
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="acommunity-message-conversations">
    <div class="messageConversations">
      <xsl:if test="message">
        <div class="message"><xsl:value-of select="message" /></div>
      </xsl:if>
      <xsl:if test="count(conversations/conversation) &gt; 0">
        <ul class="conversationsList">
          <xsl:for-each select="conversations/conversation">
            <li>
              <span class="surferAvatar"><a href="{messages-page-link/text()}"><img src="{surfer-contact/@avatar}" alt="" /></a></span>
              <xsl:text> </xsl:text>
              <span class="right">
                <span class="surferName"><a href="{messages-page-link/text()}"><xsl:value-of select="surfer-contact/@name" /></a></span>
                <xsl:text> </xsl:text>
                <span class="lastMessageText"><xsl:call-template name="acommunity-message-conversations-get-last-message-text">
                  <xsl:with-param name="text" select="last-message/text-raw" />
                  <xsl:with-param name="maxLength" select="last-message/@max-length" />
                </xsl:call-template></span>
                <xsl:text> </xsl:text>
                <span class="lastMessageTime"><xsl:call-template name="format-date-time">
                  <xsl:with-param name="dateTime" select="last-message/@time" />
                </xsl:call-template></span>
                </span>
              <xsl:call-template name="float-fix" />
            </li>
          </xsl:for-each>
        </ul>
        <xsl:call-template name="acommunity-content-paging">
          <xsl:with-param name="paging" select="conversations/paging" />
        </xsl:call-template>
      </xsl:if>
    </div>
  </xsl:template>

  <xsl:template name="acommunity-message-conversations-get-last-message-text">
    <xsl:param name="text" />
    <xsl:param name="maxLength" />
    <xsl:choose>
      <xsl:when test="string-length($text) &gt; $maxLength">
        <xsl:value-of select="substring($text, 1, $maxLength - 1)" /><xsl:text>...</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$text" />
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="acommunity-surfer-gallery-teaser">
    <xsl:if test="add-new-images-link/@href or count(images/image) &gt; 0">
      <div class="surferGalleryTeaser">
        <xsl:choose>
          <xsl:when test="add-new-images-link/@href">
            <a class="surferGalleryTeaserAddNewImages" href="{add-new-images-link/@href}"><xsl:value-of select="add-new-images-link/text()" /></a>
          </xsl:when>
          <xsl:otherwise>
            <xsl:variable name="moreImagesLink" select="more-images-link/@href" />
            <xsl:for-each select="images/image">
              <a class="surferGalleryTeaserImageLink" href="{$moreImagesLink}"><img class="surferGalleryTeaserImage" src="{@src}" alt="" /></a>
            </xsl:for-each>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="more-images-link/@href">
          <a class="surferGalleryTeaserMoreImages" href="{more-images-link/@href}"><xsl:value-of select="more-images-link/text()" /></a>
        </xsl:if>
      </div>
      <xsl:call-template name="float-fix" />
    </xsl:if>
  </xsl:template>

  <xsl:template match="acommunity-surfer-status">
    <xsl:if test="message[@type = 'no-login'] or active-surfer">
      <div class="surferStatus">
        <xsl:choose>
          <xsl:when test="active-surfer">
            <div class="surferAvatar"><a href="{active-surfer/page-link}"><img src="{active-surfer/@avatar}" alt="" /></a></div>
            <div class="surferName"><a href="{active-surfer/page-link}"><xsl:value-of select="active-surfer/@name" /></a></div>
            <div class="surferMainLinks">
              <a class="surferEdit" href="{active-surfer/edit-link}"><xsl:value-of select="active-surfer/edit-link/@caption" /></a>
              <a class="surferLogout" href="{active-surfer/logout-link}"><xsl:value-of select="active-surfer/logout-link/@caption" /></a>
            </div>
            <xsl:call-template name="float-fix" />
            <xsl:if test="active-surfer/contacts-link or active-surfer/contact-requests-link or active-surfer/contact-own-requests-link">
              <div class="surferContactLinks">
                <xsl:if test="active-surfer/contacts-link">
                 <a class="surferContactsLink" href="{active-surfer/contacts-link/text()}"><xsl:value-of select="active-surfer/contacts-link/@caption" /></a>
                </xsl:if>
                <xsl:if test="active-surfer/contact-own-requests-link">
                 <a class="surferContactOwnRequestsLink" href="{active-surfer/contact-own-requests-link/text()}"><xsl:value-of select="active-surfer/contact-own-requests-link/@caption" /></a>
                </xsl:if>
                <xsl:if test="active-surfer/contact-requests-link">
                 <a class="surferContactRequestsLink" href="{active-surfer/contact-requests-link/text()}"><xsl:value-of select="active-surfer/contact-requests-link/@caption" /></a>
                </xsl:if>
              </div>
            </xsl:if>
            <a class="surferMessagesLink" href="{active-surfer/messages-link}"><xsl:value-of select="active-surfer/messages-link/@caption" /></a>
            <a class="surferNotificationsLink" href="{active-surfer/notifications-link}"><xsl:value-of select="active-surfer/notifications-link/@caption" /></a>
            <a class="surferNotificationSettingsLink" href="{active-surfer/notification-settings-link}"><xsl:value-of select="active-surfer/notification-settings-link/@caption" /></a>
          </xsl:when>
          <xsl:otherwise>
            <xsl:copy-of select="message[@type = 'no-login']/node()" />
          </xsl:otherwise>
        </xsl:choose>
      </div>
    </xsl:if>
  </xsl:template>

  <xsl:template match="acommunity-surfers">
    <xsl:call-template name="acommunity-surfers">
      <xsl:with-param name="content" select="." />
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="acommunity-surfer-gallery-upload">
    <xsl:call-template name="acommunity-content-dialog">
      <xsl:with-param name="dialog" select="dialog-box" />
      <xsl:with-param name="dialogMessage" select="dialog-message" />
      <xsl:with-param name="className" select="'surferGalleryUploadDialog'" />
      <xsl:with-param name="multipartFormData" select="true()" />
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="acommunity-surfer-gallery-folders">
    <xsl:if test="count(folders/folder) &gt; 0">
      <xsl:variable name="commandLinks" select="command-links" />
      <ul class="surferGalleryFolders">
        <xsl:for-each select="folders/folder">
          <xsl:variable name="folderId" select="@id" />
          <li class="surferGalleryFolder">
            <xsl:choose>
              <xsl:when test="@selected = '1'">
                <xsl:value-of select="@name" />
              </xsl:when>
              <xsl:otherwise>
                <a class="surferGallerySelectFolderLink" href="{@href}"><xsl:value-of select="@name" /></a>
              </xsl:otherwise>
            </xsl:choose>
            <xsl:if test="$commandLinks and $commandLinks/command-link[@folder_id = $folderId and @name = 'delete_folder']">
              <xsl:text> </xsl:text>
              <a class="surferGalleryDeleteFolderLink" href="{$commandLinks/command-link[@folder_id = $folderId and @name = 'delete_folder']/text()}" title="{$commandLinks/command-link[@folder_id = $folderId and @name = 'delete_folder']/@caption}"><img src="{$PAGE_THEME_PATH}pics/folder-delete.png" alt="" /></a>
            </xsl:if>
          </li>
        </xsl:for-each>
      </ul>
      <xsl:if test="command-links/command-link[@name = 'add_folder']">
        <a class="surferGalleryAddFolderLink" href="{command-links/command-link[@name = 'add_folder']/text()}" title="{command-links/command-link[@name = 'add_folder']/@caption}"><img src="{$PAGE_THEME_PATH}pics/folder-add.png" alt="" /></a>
      </xsl:if>
    </xsl:if>
    <xsl:call-template name="float-fix" />
    <xsl:call-template name="acommunity-content-dialog">
      <xsl:with-param name="dialog" select="dialog-box" />
      <xsl:with-param name="dialogMessage" select="dialog-message" />
      <xsl:with-param name="className" select="'surferGalleryFolderDialog'" />
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="acommunity-commenters-ranking">
    <div class="commentersRanking">
      <xsl:if test="count(commenter) &gt; 0">
        <xsl:for-each select="commenter">
          <div class="commenter">
            <div class="commenterAvatar"><a href="{surfer/@page-link}"><img alt="" src="{surfer/@avatar}" /></a></div>
            <div class="commenterSurferName"><a href="{surfer/@page-link}"><xsl:value-of select="surfer/@name" /></a></div>
            <div class="commenterComments">
              <xsl:value-of select="comments/@amount" /><xsl:text> </xsl:text><xsl:value-of select="comments/@caption" />
            </div>
            <xsl:call-template name="float-fix" />
          </div>
        </xsl:for-each>
      </xsl:if>
      <xsl:text> </xsl:text>
    </div>
  </xsl:template>

</xsl:stylesheet>