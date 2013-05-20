<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

  <xsl:import href="./Ui/Content/Comments/List.xsl"/>
  <xsl:import href="./Ui/Content/Surfer/Gallery/Upload/Dialog.xsl"/>

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
    <xsl:call-template name="acommunity-surfer-gallery-upload-dialog">
      <xsl:with-param name="dialog" select="dialog-box" />
      <xsl:with-param name="dialogMessage" select="dialog-message" />
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
