<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

  <xsl:import href="../../../../../free/thumbs/content_thumbs.xsl"/>

  <xsl:param name="PAGE_LANGUAGE"></xsl:param>
  <xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
  <xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

  <xsl:template name="module-content-gallery-images">
    <xsl:param name="images" />
    <xsl:param name="options" />
    <xsl:if test="$images">
      <xsl:for-each select="$images">
        <div class="galleryThumbnail">
          <a class="galleryThumbnailFrame" href="{destination/@href}" title="{title}">
            <img src="{img/@src}" alt="{img/@alt}"/>
          </a>
          <xsl:if test="following::delete-image">
          <a class="galleryThumbnailDelete" href="{following::delete-image/@href}"><xsl:call-template name="language-text">
            <xsl:with-param name="text" select="'DELETE_IMAGE'"/>
          </xsl:call-template></a>
          </xsl:if>
        </div>
      </xsl:for-each>
      <xsl:if test="$options/lightbox = '1'">
        <script type="text/javascript"><xsl:comment>
          jQuery('#gallery').children().hide();
          var galleryMapping = {
            images : {
              <xsl:for-each select="$images">
                <xsl:if test="position() &gt; 1">, </xsl:if>
                <xsl:call-template name="javascript-escape-string">
                  <xsl:with-param name="string" select="destination/@href" />
                </xsl:call-template>
                <xsl:text> : </xsl:text>
                <xsl:call-template name="javascript-escape-string">
                  <xsl:with-param name="string" select="destination/img/@src" />
                </xsl:call-template>
              </xsl:for-each>
            },
            getImageUrl : function(href) {
              return (this.images[href]) ? this.images[href] : href;
            }
          };
        </xsl:comment></script>
      </xsl:if>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>