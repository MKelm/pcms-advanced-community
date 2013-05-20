<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
  
  <xsl:import href="../Dialog.xsl"/>
  
  <xsl:template name="acommunity-comments-list">
    <xsl:param name="commandName" select="'reply'" />
    <xsl:param name="commandCommentId" select="0" />
    <xsl:param name="commentId" select="0" />
    <xsl:param name="comments" />
    <xsl:param name="dialog" />
    <xsl:param name="dialogMessage" />
    <xsl:param name="indent" select="false()" />
    <xsl:param name="parentAnchor" select="''" />
    
    <xsl:if test="$commandName = 'reply' and $commandCommentId = $commentId">
      <xsl:call-template name="acommunity-content-dialog">
        <xsl:with-param name="dialog" select="$dialog" />
        <xsl:with-param name="dialogMessage" select="$dialogMessage" />
        <xsl:with-param name="indent" select="$commentId &gt; 0" />
        <xsl:with-param name="parentAnchor" select="$parentAnchor" />
        <xsl:with-param name="className" select="'commentDialog'" />
      </xsl:call-template>
    </xsl:if>
    
    <xsl:if test="count($comments/comment) &gt; 0">
      <div>
        <xsl:attribute name="class">
          <xsl:text>comments</xsl:text>
          <xsl:if test="$indent"><xsl:text> indent</xsl:text></xsl:if>
        </xsl:attribute>

        <xsl:for-each select="$comments/comment">
          <xsl:variable name="anchor">
            <xsl:text>comment_</xsl:text><xsl:value-of select="@id" />
          </xsl:variable>
          <a name="{$anchor}"><xsl:text> </xsl:text></a>
          <div class="comment">
            <div class="commentUser">
              <div class="commentUserAvatar"><img src="{@surfer_avatar}" alt="" /></div>
              <div class="commentUserHandle"><xsl:value-of select="@surfer_handle" /></div>
              <div class="commentTime">
                <xsl:call-template name="format-date">
                  <xsl:with-param name="date" select="@time" />
                </xsl:call-template><xsl:text>, </xsl:text>
                <xsl:call-template name="format-time">
                  <xsl:with-param name="time" select="substring(@time, 12, 8)" />
                </xsl:call-template>
              </div>
            </div>
            <div class="commentText">
              <div class="commentTextParagraph"><xsl:value-of select="text" disable-output-escaping="yes" /></div>
              <div class="commentExtras">
                <div class="commentExtrasVotesScore"><xsl:value-of select="@votes_score" /></div>
                <xsl:if test="count(links/link[@name = 'vote_up']) &gt; 0 and count(links/link[@name = 'vote_down']) &gt; 0">
                  <div class="commentExtrasVoting">
                    <a class="commentExtrasVotingLinkVoteUp" href="{links/link[@name = 'vote_up']/text()}#{$anchor}">[ + ]</a>
                    <a class="commentExtrasVotingLinkVoteDown" href="{links/link[@name = 'vote_down']/text()}#{$anchor}">[ - ]</a>
                    <xsl:call-template name="float-fix" />
                  </div>
                </xsl:if>
                <div class="commentExtrasReply">
                  <xsl:if test="count(links/link[@name = 'reply']) &gt; 0">
                    <a class="commentExtrasReplyLink" href="{links/link[@name = 'reply']/text()}#{$anchor}">Antworten</a>
                  </xsl:if>
                  <xsl:text> </xsl:text>
                </div>
              </div>
              <xsl:call-template name="float-fix" />
            </div>
            <xsl:call-template name="float-fix" />
            
            <xsl:call-template name="acommunity-comments-list">
              <xsl:with-param name="commandName" select="$commandName" />
              <xsl:with-param name="commandCommentId" select="$commandCommentId" />
              <xsl:with-param name="commentId" select="@id" />
              <xsl:with-param name="comments" select="comments" />
              <xsl:with-param name="dialog" select="$dialog" />
              <xsl:with-param name="dialogMessage" select="$dialogMessage" />
              <xsl:with-param name="indent" select="true()" />
              <xsl:with-param name="parentAnchor" select="$anchor" />
            </xsl:call-template>

          </div>
          
        </xsl:for-each>
        
        <xsl:call-template name="acommunity-comments-list-paging">
          <xsl:with-param name="paging" select="$comments/paging" />
          <xsl:with-param name="parentAnchor" select="$parentAnchor" />
        </xsl:call-template>
        <xsl:call-template name="float-fix" />

      </div>
    </xsl:if>

  </xsl:template>
  
  <xsl:template name="acommunity-comments-list-paging">
    <xsl:param name="paging" />
    <xsl:param name="parentAnchor" />
    
    <xsl:if test="$paging/@count &gt; 0">
      <div class="paging">
        <xsl:for-each select="$paging/page">
          <xsl:if test="not(@type) and @number = 1">
            <xsl:text> [ </xsl:text>
          </xsl:if>
          <a>
            <xsl:attribute name="href">
              <xsl:value-of select="@href" />
              <xsl:if test="not($parentAnchor = '')">
                <xsl:text>#</xsl:text><xsl:value-of select="$parentAnchor" />
              </xsl:if>
            </xsl:attribute>
            <xsl:choose>
              <xsl:when test="@type and @type = 'first'">
                <xsl:text>&#60;&#60;</xsl:text>
              </xsl:when>
              <xsl:when test="@type and @type = 'previous'">
                <xsl:text>&#60;</xsl:text>
              </xsl:when>
              <xsl:when test="@type and @type = 'next'">
                <xsl:text>&#62;</xsl:text>
              </xsl:when>
              <xsl:when test="@type and @type = 'last'">
                <xsl:text>&#62;&#62;</xsl:text>
              </xsl:when>
              <xsl:otherwise>
                <xsl:choose>
                  <xsl:when test="@selected">
                    <strong><xsl:value-of select="@number" /></strong>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="@number" />
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:otherwise>
            </xsl:choose>
          </a>
          <xsl:choose>
            <xsl:when test="not(@type) and @number &lt; $paging/@count">
              <xsl:text> </xsl:text>&#183;<xsl:text> </xsl:text>
            </xsl:when>
            <xsl:when test="@type and position() != last()">
              <xsl:text> </xsl:text>
            </xsl:when>
          </xsl:choose>
          <xsl:if test="not(@type) and @number = $paging/@count">
            <xsl:text> ] </xsl:text>
          </xsl:if>
        </xsl:for-each>
      </div>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>
