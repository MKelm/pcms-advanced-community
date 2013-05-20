<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
  
  <xsl:template name="acommunity-surfer-gallery-upload-dialog">
    <xsl:param name="dialog" />
    <xsl:param name="dialogMessage" select="false()" />
    
    <xsl:if test="$dialog">
      <xsl:if test="$dialogMessage">
        <div>
          <xsl:attribute name="class">
            <xsl:text>surferGalleryUploadDialogMessage</xsl:text>
            <xsl:choose>
              <xsl:when test="$dialogMessage/@type = 'error'">
                <xsl:text> error</xsl:text>
              </xsl:when>
              <xsl:when test="$dialogMessage/@type = 'success'">
                <xsl:text> success</xsl:text>
              </xsl:when>
            </xsl:choose>
          </xsl:attribute>
          <xsl:value-of select="$dialogMessage/text()" />
        </div>
      </xsl:if>
      
      <form class="surferGalleryUploadDialog" enctype="multipart/form-data">
        <xsl:copy-of select="$dialog/@action" />
        <xsl:copy-of select="$dialog/@method" />
        <fieldset>
          <xsl:copy-of select="$dialog/input[@type='hidden']" />
          <xsl:for-each select="$dialog/field">
            <div class="surferGalleryUploadDialogField">
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
        <fieldset class="surferGalleryUploadDialogButton">
          <button style="float: {$dialog/button/@align}">
            <xsl:copy-of select="$dialog/button/@type" />
            <xsl:value-of select="$dialog/button/text()" />
          </button>
          <xsl:call-template name="float-fix" />
        </fieldset>
        <xsl:call-template name="float-fix" />
      </form>
    </xsl:if>
  </xsl:template>
  
</xsl:stylesheet>
