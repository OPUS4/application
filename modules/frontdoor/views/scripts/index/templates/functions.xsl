<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : functions.xsl
    Created on : 5. November 2012, 13:39
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
   
    <!-- Additional Templates with auxilliary functions. -->
    <!-- -->
    <!-- Named template to proof, what to show for collections, depending on display_frontdoor -->
    <xsl:template name="checkdisplay">
        <xsl:if test="contains(@RoleDisplayFrontdoor,'Number') and @Number != ''">
            <xsl:value-of select="@Number" />
            <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:if test="contains(@RoleDisplayFrontdoor,'Name') and @Name != ''">
            <xsl:value-of select="@Name" />
        </xsl:if>
    </xsl:template>

    <!-- Named template to translate a field's name. Needs no parameter. -->
    <xsl:template name="translateFieldname">
        <xsl:value-of select="php:functionString('Frontdoor_IndexController::translate', name())" />
        <xsl:if test="normalize-space(@Language)">
            <!-- translation of language abbreviations  -->
            <xsl:text> (</xsl:text>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="@Language" />
            </xsl:call-template>
            <xsl:text>)</xsl:text>
        </xsl:if>
        <xsl:text>:</xsl:text>
    </xsl:template>

    <!-- Named template to translate an arbitrary string. Needs the translation key as a parameter. -->
    <xsl:template name="translateString">
        <xsl:param name="string" />
        <xsl:value-of select="php:functionString('Frontdoor_IndexController::translate', $string)" />
    </xsl:template>

    <xsl:template name="translateStringWithDefault">
        <xsl:param name="string" />
        <xsl:param name="default" />
        <xsl:value-of select="php:functionString('Frontdoor_IndexController::translateWithDefault', $string, $default)" />
    </xsl:template>

    <xsl:template name="replaceCharsInString">
        <xsl:param name="stringIn"/>
        <xsl:param name="charsIn"/>
        <xsl:param name="charsOut"/>
        <xsl:choose>
            <xsl:when test="contains($stringIn,$charsIn)">
                <xsl:value-of select="concat(substring-before($stringIn,$charsIn),$charsOut)"/>
                <xsl:call-template name="replaceCharsInString">
                    <xsl:with-param name="stringIn" select="substring-after($stringIn,$charsIn)"/>
                    <xsl:with-param name="charsIn" select="$charsIn"/>
                    <xsl:with-param name="charsOut" select="$charsOut"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$stringIn"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
