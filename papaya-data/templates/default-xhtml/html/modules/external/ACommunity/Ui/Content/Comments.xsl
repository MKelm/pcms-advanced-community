<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

  <xsl:import href="Dialog.xsl"/>
  <xsl:import href="Paging.xsl"/>

  <xsl:template name="acommunity-comments">
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
            <div class="commentSurferAvatar"><a href="{surfer/@page-link}"><img src="{surfer/@avatar}" alt="" /></a></div>
            <div class="commentContainer">
              <div class="commentHeader">
                <div class="commentSurferName"><a href="{surfer/@page-link}"><xsl:value-of select="surfer/@name" /></a></div>
                <div class="commentTime">
                  <xsl:call-template name="format-date">
                    <xsl:with-param name="date" select="@time" />
                  </xsl:call-template><xsl:text>, </xsl:text>
                  <xsl:call-template name="format-time">
                    <xsl:with-param name="time" select="substring(@time, 12, 8)" />
                  </xsl:call-template>
                </div>
                <xsl:call-template name="float-fix" />
              </div>
              <div class="commentText">
                <div class="commentTextParagraph"><xsl:copy-of select="text/node()" /><xsl:text> </xsl:text></div>
                <xsl:if test="text-thumbnail-links">
                  <div class="textThumbnailLinks">
                    <xsl:for-each select="text-thumbnail-links/a">
                      <xsl:copy-of select="." />
                    </xsl:for-each>
                  </div>
                  <xsl:call-template name="float-fix" />
                </xsl:if>
                <xsl:call-template name="acommunity-comments-comment-extras">
                  <xsl:with-param name="commandLinks" select="command-links/link" />
                  <xsl:with-param name="anchor" select="$anchor" />
                </xsl:call-template>
                <xsl:call-template name="float-fix" />
              </div>
              <xsl:call-template name="float-fix" />
            </div>
            <xsl:call-template name="float-fix" />

            <xsl:call-template name="acommunity-comments">
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

        <xsl:call-template name="acommunity-content-paging">
          <xsl:with-param name="paging" select="$comments/paging" />
          <xsl:with-param name="parentAnchor" select="$parentAnchor" />
        </xsl:call-template>
        <xsl:call-template name="float-fix" />

      </div>
    </xsl:if>
  </xsl:template>

  <xsl:template name="acommunity-comments-comment-extras">
    <xsl:param name="commandLinks" />
    <xsl:param name="anchor" />

    <div class="commentExtras">
      <div class="commentExtrasVotesScore"><xsl:value-of select="@votes_score" /></div>
      <xsl:if test="$commandLinks[@name = 'vote_up'] and $commandLinks[@name = 'vote_down']">
        <div class="commentExtrasVoting">
          <a class="commentExtrasVotingLinkVoteUp" href="{$commandLinks[@name = 'vote_up']/text()}#{$anchor}"><xsl:value-of select="$commandLinks[@name = 'vote_up']/@caption" /></a>
          <a class="commentExtrasVotingLinkVoteDown" href="{$commandLinks[@name = 'vote_down']/text()}#{$anchor}"><xsl:value-of select="$commandLinks[@name = 'vote_down']/@caption" /></a>
          <xsl:call-template name="float-fix" />
        </div>
      </xsl:if>
      <div class="commentExtrasReply">
        <xsl:if test="$commandLinks[@name = 'reply']">
          <a class="commentExtrasReplyLink" href="{$commandLinks[@name = 'reply']/text()}#{$anchor}"><xsl:value-of select="$commandLinks[@name = 'reply']/@caption" /></a>
        </xsl:if>
        <xsl:text> </xsl:text>
      </div>
    </div>
  </xsl:template>

</xsl:stylesheet>
