<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
  
  <xsl:template name="acommunity-comment-dialog">
    <xsl:param name="dialog" />
    <xsl:param name="dialogMessage" select="false()" />
    <xsl:param name="indent" select="false()" />
    
    <xsl:if test="$dialog">
      <xsl:if test="$dialogMessage">
        <div>
          <xsl:attribute name="class">
            <xsl:text>commentDialogMessage</xsl:text>
            <xsl:choose>
              <xsl:when test="$dialogMessage/@type = 'error'">
                <xsl:text> error</xsl:text>
              </xsl:when>
              <xsl:when test="$dialogMessage/@type = 'success'">
                <xsl:text> success</xsl:text>
              </xsl:when>
            </xsl:choose>
            <xsl:if test="$indent">
              <xsl:text> indent</xsl:text>
            </xsl:if>
          </xsl:attribute>
          <xsl:value-of select="$dialogMessage/text()" />
        </div>
      </xsl:if>
      
      <form>
        <xsl:attribute name="class">
          <xsl:text>commentDialog</xsl:text>
          <xsl:if test="$indent">
            <xsl:text> indent</xsl:text>
          </xsl:if>
        </xsl:attribute>
        <xsl:copy-of select="$dialog/@action" />
        <xsl:copy-of select="$dialog/@method" />
        <fieldset>
          <xsl:copy-of select="$dialog/input[@type='hidden']" />
          <xsl:for-each select="$dialog/field">
            <div class="commentDialogField">
              <label for="{@id}"><xsl:value-of select="@caption" /></label>
              <xsl:choose>
                <xsl:when test="textarea">
                  <textarea class="text">
                    <xsl:copy-of select="textarea/@*[local-name() != 'lines']" />
                    <xsl:attribute name="rows">
                      <xsl:value-of select="textarea/@lines" />
                    </xsl:attribute>
                    <xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
                    <xsl:text> </xsl:text>
                  </textarea>
                </xsl:when>
                <xsl:when test="input">
                  <input class="text">
                    <xsl:copy-of select="input/@*" />
                    <xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
                  </input>
                </xsl:when>
              </xsl:choose>
            </div>
          </xsl:for-each>
        </fieldset>
        <fieldset class="commentDialogButton">
          <button style="float: {$dialog/button/@align}">
            <xsl:copy-of select="$dialog/button/@type" />
            <xsl:value-of select="$dialog/button/text()" />
          </button>
          <xsl:call-template name="float-fix" />
        </fieldset>
      </form>
    </xsl:if>
  </xsl:template>
  
</xsl:stylesheet>
