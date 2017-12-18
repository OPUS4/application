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
 * @category    Application
 * @package     Module_Frontdoor
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2009-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:php="http://php.net/xsl"
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
        <xsl:for-each select="TitleMain">
            <xsl:if test="(@Language = $docLang) or (position() = 1 and not($docLang))">
                <h2 class="titlemain">
                    <xsl:attribute name="lang"><xsl:value-of
                            select="php:functionString('Application_Xslt::languageWebForm', @Language)"/></xsl:attribute>
                    <xsl:value-of select="@Value" />
                </h2>
            </xsl:if>
        </xsl:for-each>
        <xsl:for-each select="TitleMain">
            <xsl:if test="(@Language != $docLang) or not($docLang) and position() > 1">
                <h3 class="titlemain">
                    <xsl:attribute name="lang"><xsl:value-of
                            select="php:functionString('Application_Xslt::languageWebForm', @Language)"/></xsl:attribute>
                    <xsl:value-of select="@Value" />
                </h3>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="SortedAbstracts">
        <ul>
        <xsl:for-each select="TitleAbstract">
            <xsl:if test="(@Language = $docLang) or (position() = 1 and not($docLang))">
                <xsl:call-template name="Abstract" />
            </xsl:if>
        </xsl:for-each>
        <xsl:for-each select="TitleAbstract">
            <xsl:if test="(@Language != $docLang) or not($docLang) and position() > 1">
                <xsl:call-template name="Abstract" />
            </xsl:if>
        </xsl:for-each>
        </ul>
    </xsl:template>

    <xsl:template name="Abstract">
            <li class="abstract preserve-spaces">
                <xsl:attribute name="lang"><xsl:value-of
                        select="php:functionString('Application_Xslt::languageWebForm', @Language)"/></xsl:attribute>
                <xsl:choose>
                    <xsl:when test="$numOfShortAbstractChars = '0' or string-length(@Value) &lt; $numOfShortAbstractChars">
                       <xsl:value-of select="@Value" />
                    </xsl:when>
                    <xsl:otherwise>
                        <span>
                            <xsl:attribute name="id">abstractShort_<xsl:value-of select="@Id"/>
                            </xsl:attribute>
                            <xsl:attribute name="class">abstractShort</xsl:attribute>
                            <xsl:value-of select="php:functionString('Application_Xslt::shortenText', @Value)"/>
                        </span>
                        <span>
                            <xsl:attribute name="id">abstractFull_<xsl:value-of select="@Id"/>
                            </xsl:attribute>
                            <xsl:attribute name="class">abstractFull</xsl:attribute>
                            <xsl:value-of select="@Value"/>
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
            </li>
    </xsl:template>

   
</xsl:stylesheet>
