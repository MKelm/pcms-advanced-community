<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

  <xsl:import href="./Ui/Content/Surfers.xsl"/>
  <xsl:import href="./Ui/Content/Dialog.xsl"/>
  <xsl:import href="./Ui/Content/Paging.xsl"/>

  <xsl:template name="page-styles">
    <xsl:call-template name="link-style">
      <xsl:with-param name="file">page_acommunity.css</xsl:with-param>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="content-area">
    <xsl:param name="pageContent" select="content/topic"/>
    <xsl:choose>
      <xsl:when test="$pageContent/@module = 'ACommunityNotificationSettingsPage'">
        <xsl:call-template name="module-content-acommunity-notification-settings-page">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/@module = 'ACommunityMessagesPage'">
        <xsl:call-template name="module-content-acommunity-messages-page">
          <xsl:with-param name="pageContent" select="$pageContent/acommunity-messages"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/@module = 'ACommunitySurferPage'">
        <xsl:call-template name="module-content-acommunity-surfer-page">
          <xsl:with-param name="pageContent" select="$pageContent/surfer-page"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/@module = 'ACommunitySurfersPage'">
        <xsl:call-template name="acommunity-surfers">
          <xsl:with-param name="content" select="$pageContent/acommunity-surfers"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="module-content-default">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="module-content-acommunity-notification-settings-page">
    <xsl:param name="pageContent" />
    <xsl:if test="$pageContent/title/text() != ''">
      <h1><xsl:value-of select="$pageContent/title/text()" /></h1>
    </xsl:if>
    <xsl:if test="$pageContent/message">
      <div>
        <xsl:attribute name="class">
          <xsl:choose>
            <xsl:when test="$pageContent/message[@type = 'error']">message error</xsl:when>
            <xsl:otherwise>message</xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:value-of select="$pageContent/message/text()" />
      </div>
    </xsl:if>
    <xsl:call-template name="acommunity-content-dialog">
      <xsl:with-param name="dialog" select="$pageContent/notification-settings/dialog-box" />
      <xsl:with-param name="dialogMessage" select="$pageContent/notification-settings/dialog-message" />
      <xsl:with-param name="className" select="'dialogNotficationSettings'" />
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="module-content-acommunity-messages-page">
    <xsl:param name="pageContent" />

    <xsl:if test="$pageContent/title/text() != ''">
      <h1><xsl:value-of select="$pageContent/title/text()" /></h1>
    </xsl:if>

    <xsl:call-template name="acommunity-content-dialog">
      <xsl:with-param name="dialog" select="$pageContent/dialog-box" />
      <xsl:with-param name="dialogMessage" select="$pageContent/dialog-message" />
      <xsl:with-param name="className" select="'messageDialog'" />
    </xsl:call-template>

    <xsl:if test="$pageContent/message">
      <div class="message"><xsl:value-of select="$pageContent/message" /></div>
    </xsl:if>

    <div class="messagesList">
      <xsl:for-each select="$pageContent/messages/message">
        <div class="messageEntry">
          <div class="messageSurferAvatar"><a href="{surfer/@page-link}"><img src="{surfer/@avatar}" alt="" /></a></div>
          <div class="messageContainer">
            <div class="messageHeader">
              <div class="messageSurferName"><a href="{surfer/@page-link}"><xsl:value-of select="surfer/@name" /></a>
              </div>
              <div class="messageTime">
                <xsl:call-template name="format-date">
                  <xsl:with-param name="date" select="@time" />
                </xsl:call-template><xsl:text>, </xsl:text>
                <xsl:call-template name="format-time">
                  <xsl:with-param name="time" select="substring(@time, 12, 8)" />
                </xsl:call-template>
              </div>
              <xsl:call-template name="float-fix" />
            </div>
            <div class="messageText">
              <xsl:value-of select="text" disable-output-escaping="yes" />
            </div>
          </div>
          <xsl:call-template name="float-fix" />
        </div>
      </xsl:for-each>
      <xsl:call-template name="acommunity-content-paging">
        <xsl:with-param name="paging" select="$pageContent/messages/paging" />
      </xsl:call-template>
    </div>
  </xsl:template>

  <xsl:template name="module-content-acommunity-surfer-page">
    <xsl:param name="pageContent" />
    <xsl:choose>
      <xsl:when test="count($pageContent/details/group) &gt; 0">
        <div class="surferPage">
          <xsl:call-template name="module-content-acommunity-surfer-page-base-details">
            <xsl:with-param name="baseDetails" select="$pageContent/details/group[@id = 0]" />
          </xsl:call-template>
          <xsl:call-template name="module-content-acommunity-surfer-page-contact">
            <xsl:with-param name="pageContent" select="$pageContent" />
          </xsl:call-template>
          <xsl:if test="$pageContent/send-message-link">
            <a class="surferSendMessageLink" href="{$pageContent/send-message-link/text()}"><xsl:value-of select="$pageContent/send-message-link/@caption" /></a>
          </xsl:if>
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

  <xsl:template name="module-content-acommunity-surfer-page-contact">
    <xsl:param name="pageContent" />

    <xsl:if test="$pageContent/contact">
      <xsl:variable name="contact" select="$pageContent/contact" />
      <div class="surferContact">
        <xsl:choose>
          <xsl:when test="$contact/@status = 'none' or $contact/@status = 'own_pending' or $contact/@status = 'direct'">
            <a href="{$contact/command/text()}" title="{$contact/command/@caption}" ><xsl:value-of select="$contact/@status-caption" /></a>
          </xsl:when>
          <xsl:when test="$contact/@status = 'pending'">
            <xsl:value-of select="$contact/@status-caption" />
            <xsl:for-each select="$contact/command">
              <xsl:text> </xsl:text><a href="{text()}"><xsl:value-of select="@caption" /></a>
            </xsl:for-each>
          </xsl:when>
        </xsl:choose>
      </div>

    </xsl:if>
  </xsl:template>

  <xsl:template name="module-content-acommunity-surfer-page-base-details">
    <xsl:param name="baseDetails" />

    <div class="surferBaseDetailsGroup">
      <img src="{$baseDetails/detail[@name = 'avatar']/text()}" alt="{$baseDetails/detail[@name = 'avatar']/@caption}" class="surferAvatar" />
      <h1 class="surferTitle"><xsl:value-of select="$baseDetails/detail[@name = 'name']/text()" />
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
