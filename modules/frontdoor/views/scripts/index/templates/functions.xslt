<?xml version="1.0" encoding="UTF-8"?>
<!--
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:php="http://php.net/xsl"
                exclude-result-prefixes="php">

    <!-- Additional Templates with auxilliary functions. -->


    <!-- Named template to translate a field's name. Needs no parameter. -->
    <xsl:template name="translateFieldname">
        <xsl:value-of select="php:functionString('Application_Xslt::translate', name())" />
        <xsl:if test="normalize-space(@Language)">
            <!-- translation of language abbreviations  -->
            <xsl:text> (</xsl:text>
            <xsl:call-template name="translateLanguage">
                <xsl:with-param name="string" select="@Language" />
            </xsl:call-template>
            <xsl:text>)</xsl:text>
        </xsl:if>
        <xsl:text>:</xsl:text>
    </xsl:template>

    <xsl:template name="translateIdentifier">
        <xsl:value-of select="php:functionString('Application_Xslt::translateIdentifier', @Type)" />
        <xsl:text>:</xsl:text>
    </xsl:template>

    <!-- Named template to translate an arbitrary string. Needs the translation key as a parameter. -->
    <xsl:template name="translateString">
        <xsl:param name="string" />
        <xsl:value-of select="php:functionString('Application_Xslt::translate', $string)" />
    </xsl:template>

    <xsl:template name="translateLanguage">
        <xsl:param name="string" />
        <xsl:value-of select="php:functionString('Application_Xslt::translateLanguage', $string)" />
    </xsl:template>

    <xsl:template name="translateStringWithDefault">
        <xsl:param name="string" />
        <xsl:param name="default" />
        <xsl:value-of select="php:functionString('Application_Xslt::translateWithDefault', $string, $default)" />
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

    <xsl:template name="formatDate">
        <xsl:param name="day"/>
        <xsl:param name="month"/>
        <xsl:param name="year"/>
        <xsl:value-of select="php:functionString('Application_Xslt::formatDate', $day, $month, $year)" />
    </xsl:template>
</xsl:stylesheet>
