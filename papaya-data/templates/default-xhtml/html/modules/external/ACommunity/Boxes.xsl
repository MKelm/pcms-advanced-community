<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

  <xsl:import href="./Ui/Content/Dialog.xsl"/>
  <xsl:import href="./Ui/Content/Comments/List.xsl"/>

  <xsl:template match="acommunity-comments">
    <xsl:call-template name="acommunity-comments-list">
      <xsl:with-param name="commandName" select="command/@name" />
      <xsl:with-param name="commandCommentId" select="command/@comment_id" />
      <xsl:with-param name="comments" select="comments" />
      <xsl:with-param name="dialog" select="dialog-box" />
      <xsl:with-param name="dialogMessage" select="dialog-message" />
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
            <div class="commenterAvatar"><img alt="" src="{@surfer_avatar}" /></div>
            <div class="commenterUserName"><xsl:value-of select="@surfer_handle" /></div>
            <div class="commenterCommentsAmount">
              <xsl:value-of select="@comments_amount" /><xsl:text> </xsl:text><xsl:value-of select="@comments_amount_caption" />              
            </div>
            <xsl:call-template name="float-fix" />
          </div>
        </xsl:for-each>
      </xsl:if>
      <xsl:text> </xsl:text>
    </div>
  </xsl:template>

</xsl:stylesheet>
