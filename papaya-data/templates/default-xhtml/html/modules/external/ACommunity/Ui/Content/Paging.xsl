<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

  <xsl:template name="acommunity-content-paging">
    <xsl:param name="paging" />
    <xsl:param name="parentAnchor" />
    <xsl:param name="additionalClass" select="false()" />

    <xsl:if test="$paging/@count &gt; 0">
      <div class="paging">
        <xsl:attribute name="class">
          <xsl:choose>
            <xsl:when test="$additionalClass != false()">
              <xsl:text>paging </xsl:text><xsl:value-of select="$additionalClass" />
            </xsl:when>
            <xsl:otherwise>paging</xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
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