<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

  <xsl:import href="Paging.xsl"/>
  <xsl:import href="Dialog.xsl"/>

  <xsl:template name="acommunity-surfers">
    <xsl:param name="content" />
    <xsl:param name="surferSingleLine" select="false()" />
    <xsl:choose>
      <xsl:when test="count($content/group) &gt; 0">
        <xsl:for-each select="$content/group">
          <a name="{@name}"><xsl:text> </xsl:text></a>
          <div class="surfersGroup">
            <h2><xsl:value-of select="@caption" /></h2>
            <xsl:call-template name="acommunity-surfers-surfer">
              <xsl:with-param name="content" select="." />
              <xsl:with-param name="surferSingleLine" select="$surferSingleLine" />
            </xsl:call-template>
            <xsl:call-template name="acommunity-content-paging">
              <xsl:with-param name="paging" select="paging" />
              <xsl:with-param name="additionalClass" select="'surfersPaging'" />
            </xsl:call-template>
          </div>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="acommunity-surfers-filter-navigation">
          <xsl:with-param name="content" select="$content/filter-navigation" />
        </xsl:call-template>
        <xsl:call-template name="acommunity-content-dialog">
          <xsl:with-param name="dialog" select="$content/search/dialog-box" />
          <xsl:with-param name="className" select="'dialogSurfersSearch'" />
        </xsl:call-template>
        <xsl:call-template name="acommunity-surfers-surfer">
          <xsl:with-param name="content" select="$content" />
          <xsl:with-param name="surferSingleLine" select="$surferSingleLine" />
        </xsl:call-template>
        <xsl:call-template name="acommunity-content-paging">
          <xsl:with-param name="paging" select="$content/paging" />
          <xsl:with-param name="additionalClass" select="'surfersPaging'" />
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="acommunity-surfers-filter-navigation">
    <xsl:param name="content" />
    <xsl:if test="$content">
      <ul class="surfersFilterNavigation">
        <xsl:for-each select="$content/character">
          <li><a href="{@href}"><xsl:value-of select="." /></a></li>
        </xsl:for-each>
      </ul>
    </xsl:if>
  </xsl:template>

  <xsl:template name="acommunity-surfers-surfer">
    <xsl:param name="content" />
    <xsl:param name="surferSingleLine" select="false()" />
    <xsl:choose>
      <xsl:when test="count($content/surfer) &gt; 0">
        <ul class="surfers">
          <xsl:for-each select="$content/surfer">
            <li>
              <xsl:attribute name="class">
                <xsl:choose>
                  <xsl:when test="$surferSingleLine">
                    <xsl:text>singleLine</xsl:text>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:text>doubleLine</xsl:text>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:attribute>
              <span class="surferAvatar"><a href="{@page-link}"><img src="{@avatar}" alt="" /></a></span>
              <xsl:text> </xsl:text>
              <span class="surferDetails">
                <span class="surferName"><a href="{@page-link}"><xsl:value-of select="@name" /></a></span>
                <xsl:text> </xsl:text>
                <xsl:if test="last-time/text()">
                  <span class="surferLastTime"><xsl:value-of select="last-time/@caption" />:
                  <xsl:text> </xsl:text><xsl:call-template name="format-date-time">
                    <xsl:with-param name="dateTime" select="last-time/text()" />
                  </xsl:call-template></span>
                </xsl:if>
                <xsl:if test="count(command) &gt; 0">
                  <xsl:for-each select="command">
                    <span class="surferCommand"><a href="{text()}"><xsl:value-of select="@caption" /></a></span>
                  </xsl:for-each>
                </xsl:if>
              </span>
              <xsl:call-template name="float-fix" />
            </li>
          </xsl:for-each>
        </ul>
      </xsl:when>
      <xsl:otherwise>
        <div class="surfersMessage message"><xsl:value-of select="$content/message/text()" /></div>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>