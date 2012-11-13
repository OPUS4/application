<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : main.xsl
    Created on : 5. November 2012, 15:13
    Author     : edouard
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:php="http://php.net/xsl"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:xml="http://www.w3.org/XML/1998/namespace"
                exclude-result-prefixes="php">

   <!-- Named Templates for the introducing block (Author, Title, Abstract). -->
   <!-- -->
    <xsl:template name="Author">
        <p>
            <xsl:for-each select="PersonAuthor">
                <xsl:element name="a">
                    <xsl:attribute name="href">
                        <xsl:value-of select="$baseUrl"/>
                        <xsl:text>/solrsearch/index/search/searchtype/authorsearch/author/</xsl:text>
                        <xsl:value-of select="php:function('urlencode', concat(@FirstName, ' ', @LastName))" />
                    </xsl:attribute>
                    <xsl:attribute name="title">
                        <xsl:call-template name="translateString">
                            <xsl:with-param name="string">frontdoor_author_search</xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>
                    <xsl:value-of select="concat(@FirstName, ' ', @LastName)" />
                </xsl:element>
                <xsl:if test="not(position()=last())">
                    <xsl:text>, </xsl:text>
                </xsl:if>
            </xsl:for-each>
        </p>
    </xsl:template>

    <xsl:template name="Title">
        <p>
            <xsl:for-each select="TitleMain">
                <xsl:if test="position() = 1">
                    <h2 class="titlemain">
                        <xsl:value-of select="@Value" />
                    </h2>
                </xsl:if>
                <xsl:if test="position() > 1">
                    <h3 class="titlemain">
                        <xsl:value-of select="@Value" />
                    </h3>
                </xsl:if>
            </xsl:for-each>
        </p>
    </xsl:template>

    <xsl:template name="Abstract">
        <p>
            <xsl:for-each select="TitleAbstract">
                <div class="abstract">                    
                    <xsl:choose>           
                        <xsl:when test="$numOfShortAbstractChars = '0' or string-length(@Value) &lt; $numOfShortAbstractChars">
                           <pre class="preserve-spaces"><xsl:value-of select="@Value" /></pre>
                        </xsl:when>
                        <xsl:otherwise>
                            <span>
                                <xsl:attribute name="id">abstractShort_<xsl:value-of select="@Id"/>
                                </xsl:attribute>
                                <xsl:attribute name="class">abstractShort</xsl:attribute>
                                <pre class="preserve-spaces"><xsl:value-of select="substring(@Value, 1, $numOfShortAbstractChars)"/></pre>
                            </span>
                            <span>
                                <xsl:attribute name="id">abstractFull_<xsl:value-of select="@Id"/>
                                </xsl:attribute>
                                <xsl:attribute name="class">abstractFull</xsl:attribute>
                                <pre class="preserve-spaces"><xsl:value-of select="@Value"/></pre>
                            </span>
                            <span>
                                <xsl:attribute name="id">abstractThreeDots_<xsl:value-of select="@Id" />
                                </xsl:attribute>
                                <xsl:attribute name="class">abstractThreeDots</xsl:attribute>
                                <xsl:text disable-output-escaping="yes">&#x2026;</xsl:text>
                            </span>
                            <img>
                                <xsl:attribute name="src">
                                    <xsl:value-of select="$layoutPath"/>
                                    <xsl:text>/img/arrow_down.png</xsl:text>
                                </xsl:attribute>
                                <xsl:attribute name="id">abstractButtonShow_<xsl:value-of select="@Id" />
                                </xsl:attribute>
                                <xsl:attribute name="class">abstractButtonShow abstractButton</xsl:attribute>
                                <xsl:attribute name="title">
                                    <xsl:call-template name="translateString">
                                        <xsl:with-param name="string">frontdoor_abstract_show_more</xsl:with-param>
                                    </xsl:call-template>
                                </xsl:attribute>
                                <xsl:attribute name="alt">
                                    <xsl:call-template name="translateString">
                                        <xsl:with-param name="string">frontdoor_abstract_show_more</xsl:with-param>
                                    </xsl:call-template>
                                </xsl:attribute>
                            </img>
                            <img>
                                <xsl:attribute name="src">
                                    <xsl:value-of select="$layoutPath"/>
                                    <xsl:text>/img/arrow_up.png</xsl:text>
                                </xsl:attribute>
                                <xsl:attribute name="id">abstractButtonHide_<xsl:value-of select="@Id" />
                                </xsl:attribute>
                                <xsl:attribute name="class">abstractButtonHide abstractButton</xsl:attribute>
                                <xsl:attribute name="title">
                                    <xsl:call-template name="translateString">
                                        <xsl:with-param name="string">frontdoor_abstract_show_less</xsl:with-param>
                                    </xsl:call-template>
                                </xsl:attribute>
                                <xsl:attribute name="alt">
                                    <xsl:call-template name="translateString">
                                        <xsl:with-param name="string">frontdoor_abstract_show_less</xsl:with-param>
                                    </xsl:call-template>
                                </xsl:attribute>
                            </img>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
            </xsl:for-each>
        </p>
    </xsl:template>

   
</xsl:stylesheet>
