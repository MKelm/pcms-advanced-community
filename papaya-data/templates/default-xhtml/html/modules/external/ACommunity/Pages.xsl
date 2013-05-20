<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

  <xsl:template name="page-styles">
    <xsl:call-template name="link-style">
      <xsl:with-param name="file">page_acommunity.css</xsl:with-param>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="content-area">
    <xsl:param name="pageContent" select="content/topic"/>
    <xsl:choose>
      <xsl:when test="$pageContent/@module = 'ACommunitySurferPage'">
        <xsl:call-template name="module-content-acommunity-surfer-page">
          <xsl:with-param name="pageContent" select="$pageContent/surfer-page"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="module-content-default">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="module-content-acommunity-surfer-page">
    <xsl:param name="pageContent" />
    <xsl:choose>
      <xsl:when test="count($pageContent/details/group) &gt; 0">
        <div class="surferPage">
          <xsl:call-template name="module-content-acommunity-surfer-page-base-details">
            <xsl:with-param name="baseDetails" select="$pageContent/details/group[@id = 0]" />
          </xsl:call-template>
          <xsl:call-template name="float-fix" />
          <xsl:for-each select="$pageContent/details/group[@id != 0]">
            <xsl:call-template name="module-content-acommunity-surfer-page-details">
              <xsl:with-param name="details" select="." />
            </xsl:call-template>
          </xsl:for-each>
          <xsl:call-template name="float-fix" />
        </div>
      </xsl:when>
      <xsl:otherwise>
        <div class="message"><xsl:value-of select="$pageContent/message[@type = 'no-surfer']" /></div>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="module-content-acommunity-surfer-page-base-details">
    <xsl:param name="baseDetails" />

    <div class="surferBaseDetailsGroup">
      <img src="{$baseDetails/detail[@name = 'avatar']/text()}" alt="{$baseDetails/detail[@name = 'avatar']/@caption}" class="surferAvatar" />
      <h1 class="surferTitle"><xsl:value-of select="$baseDetails/detail[@name = 'givenname']/text()" />
      <xsl:text> '</xsl:text>
      <xsl:value-of select="$baseDetails/detail[@name = 'handle']/text()" />
      <xsl:text>' </xsl:text>
      <xsl:value-of select="$baseDetails/detail[@name = 'surname']/text()" />
      <xsl:text> </xsl:text>
      <span class="surferEmail"><a href="mailto:{$baseDetails/detail[@name = 'email']/text()}"><xsl:value-of select="$baseDetails/detail[@name = 'email']/@caption" /></a></span></h1>
      <div class="surferGender"><xsl:value-of select="$baseDetails/detail[@name = 'gender']/@caption" />: <xsl:value-of select="$baseDetails/detail[@name = 'gender']/text()" /></div>
      <div class="surferGroup"><xsl:value-of select="$baseDetails/detail[@name = 'group']/@caption" />: <xsl:value-of select="$baseDetails/detail[@name = 'group']/text()" /></div>
      <div class="surferStatus">
        <div class="surferLastLogin"><xsl:value-of select="$baseDetails/detail[@name = 'lastlogin']/@caption" />: <xsl:call-template name="format-date-time">
        <xsl:with-param name="dateTime" select="$baseDetails/detail[@name = 'lastlogin']/text()" />
      </xsl:call-template></div>
        <div class="surferLastAction"><xsl:value-of select="$baseDetails/detail[@name = 'lastaction']/@caption" />: <xsl:call-template name="format-date-time">
        <xsl:with-param name="dateTime" select="$baseDetails/detail[@name = 'lastaction']/text()" />
      </xsl:call-template></div>
        <div class="surferRegistration"><xsl:value-of select="$baseDetails/detail[@name = 'registration']/@caption" />: <xsl:call-template name="format-date-time">
        <xsl:with-param name="dateTime" select="$baseDetails/detail[@name = 'registration']/text()" />
      </xsl:call-template></div>
      </div>
    </div>
  </xsl:template>

  <xsl:template name="module-content-acommunity-surfer-page-get-detail-name-class">
    <xsl:param name="detailName" />
    <xsl:variable name="lowerCase" select="'abcdefghijklmnopqrstuvwxyz'" />
    <xsl:variable name="upperCase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
    <xsl:text>surfer</xsl:text>
    <xsl:value-of select="translate(substring($detailName, 1, 1), $lowerCase, $upperCase)" />
    <xsl:value-of select="substring($detailName, 2)" />
  </xsl:template>

  <xsl:template name="module-content-acommunity-surfer-page-details">
    <xsl:param name="details" />

    <div class="surferDetailsGroup">
      <h2><xsl:value-of select="$details/@caption" /></h2>
      <xsl:for-each select="$details/detail">
        <div>
          <xsl:attribute name="class">
            <xsl:call-template name="module-content-acommunity-surfer-page-get-detail-name-class">
              <xsl:with-param name="detailName" select="@name" />
            </xsl:call-template>
          </xsl:attribute>
          <xsl:value-of select="@caption" />: <xsl:value-of select="text()" />
        </div>
      </xsl:for-each>
    </div>

  </xsl:template>

</xsl:stylesheet>
