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


   <xsl:template name="MetaData">
    </xsl:template>


    <xsl:template match="EmbargoDate" mode="fileDownloadEmbargo">
        <div id="embargo" class="services">
            <h3>
                <xsl:call-template name="translateString">
                    <xsl:with-param name="string">frontdoor_embargo_date</xsl:with-param>
                </xsl:call-template>
            </h3>
            <div>
                <xsl:call-template name="formatDate">
                    <xsl:with-param name="day"><xsl:value-of select="@Day"/></xsl:with-param>
                    <xsl:with-param name="month"><xsl:value-of select="@Month"/></xsl:with-param>
                    <xsl:with-param name="year"><xsl:value-of select="@Year"/></xsl:with-param>
                </xsl:call-template>
            </div>
        </div>
    </xsl:template>


    <!--  -->
    <!-- Templates for "internal fields". -->
    <!--  -->
    <xsl:template match="@CompletedYear|@ContributingCorporation|@CreatingCorporation|@Volume|@Issue|@Edition">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname" />
            </th>
            <td>
                <xsl:value-of select="." />
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="@PageFirst|@PageLast|@PageNumber|@PublishedYear|@PublisherName|@PublisherPlace">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname" />
            </th>
            <td>
                <xsl:value-of select="." />
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="@Language|@Type">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname" />
            </th>
            <td>
                <xsl:call-template name="translateString">
                    <xsl:with-param name="string">
                        <xsl:value-of select="." />
                    </xsl:with-param>
                </xsl:call-template>

            </td>
        </tr>
    </xsl:template>

    <xsl:template match="@BelongsToBibliography">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname" />
            </th>
            <td class="value BelongsToBibliography">
                <xsl:choose>
                    <xsl:when test=". = '1'">
                        <xsl:call-template name="translateString">
                           <xsl:with-param name="string">answer_yes</xsl:with-param>
                        </xsl:call-template>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:call-template name="translateString">
                           <xsl:with-param name="string">answer_no</xsl:with-param>
                        </xsl:call-template>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
        </tr>
    </xsl:template>

    <!-- -->
    <!-- Templates for "external fields". -->
    <!-- -->
    <xsl:template match="Collection[@Visible='1' and @RoleVisibleFrontdoor='true']">
        <tr>
            <xsl:choose>
                <xsl:when test="position()=1">
                    <th class="name">
                        <xsl:call-template name="translateStringWithDefault">
                            <xsl:with-param name="string">default_collection_role_<xsl:value-of select="@RoleName" />
                            </xsl:with-param>
                            <xsl:with-param name="default">
                                <xsl:value-of select="@RoleName" />
                            </xsl:with-param>
                        </xsl:call-template>
                        <xsl:text>:</xsl:text>
                    </th>
                </xsl:when>
                <xsl:otherwise>
                    <th class="name"></th>
                </xsl:otherwise>
            </xsl:choose>
            <td>
                <a>
                    <xsl:attribute name="href">
                        <xsl:value-of select="$baseUrl"/>
                        <xsl:text>/solrsearch/index/search/searchtype/collection/id/</xsl:text>
                        <xsl:value-of select="@Id" />
                    </xsl:attribute>
                    <xsl:choose>
                        <xsl:when test="@DisplayFrontdoor != ''">
                            <xsl:attribute name="title">
                                <xsl:call-template name="translateString">
                                    <xsl:with-param name="string">frontdoor_collection_link</xsl:with-param>
                                </xsl:call-template>
                            </xsl:attribute>
                            <xsl:value-of select="@DisplayFrontdoor" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:call-template name="translateStringWithDefault">
                                <xsl:with-param name="string">default_collection_role_<xsl:value-of select="@RoleName" />
                                </xsl:with-param>
                                <xsl:with-param name="default">
                                    <xsl:value-of select="@RoleName" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:otherwise>
                    </xsl:choose>
                </a>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="CompletedDate|PublishedDate|ThesisDateAccepted|ServerDatePublished">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:call-template name="formatDate">
                    <xsl:with-param name="day"><xsl:value-of select="@Day"/></xsl:with-param>
                    <xsl:with-param name="month"><xsl:value-of select="@Month"/></xsl:with-param>
                    <xsl:with-param name="year"><xsl:value-of select="@Year"/></xsl:with-param>
                </xsl:call-template>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="EmbargoDate" mode="metadataEmbargo">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:call-template name="formatDate">
                    <xsl:with-param name="day"><xsl:value-of select="@Day"/></xsl:with-param>
                    <xsl:with-param name="month"><xsl:value-of select="@Month"/></xsl:with-param>
                    <xsl:with-param name="year"><xsl:value-of select="@Year"/></xsl:with-param>
                </xsl:call-template>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="Enrichment" mode="unescaped">
        <tr>
            <th class="name">
                <xsl:call-template name="translateString">
                    <xsl:with-param name="string">Enrichment<xsl:value-of select="@KeyName" />
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text>:</xsl:text>
            </th>
            <td>
                <xsl:value-of select="@Value" disable-output-escaping="yes"/>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="Enrichment">
        <tr>
            <th class="name">
                <xsl:call-template name="translateString">
                    <xsl:with-param name="string">Enrichment<xsl:value-of select="@KeyName" />
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text>:</xsl:text>
            </th>
            <td>
                <xsl:value-of select="@Value" />
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="PersonAuthor|PersonReferee">
        <xsl:if test="position() = 1">
            <xsl:text disable-output-escaping="yes">&lt;tr&gt;</xsl:text>
            <th class="name">
                <xsl:if test="position() = 1">
                    <xsl:call-template name="translateFieldname"/>
                </xsl:if>
            </th>
            <xsl:text disable-output-escaping="yes">&lt;td&gt;</xsl:text>
        </xsl:if>
        <xsl:element name="a">
            <xsl:attribute name="href">
                <xsl:value-of select="$baseUrl"/>
                <xsl:if test="name()='PersonAuthor'">
                    <xsl:text>/solrsearch/index/search/searchtype/authorsearch/author/</xsl:text>
                </xsl:if>
                <xsl:if test="name()='PersonReferee'">
                    <xsl:text>/solrsearch/index/search/searchtype/authorsearch/referee/</xsl:text>
                </xsl:if>
                <xsl:value-of select="php:function('urlencode', concat(@FirstName, ' ', @LastName))" />
            </xsl:attribute>
            <xsl:attribute name="title">
                <xsl:if test="name()='PersonAuthor'">
                    <xsl:call-template name="translateString">
                        <xsl:with-param name="string">frontdoor_author_search</xsl:with-param>
                    </xsl:call-template>
                </xsl:if>
                <xsl:if test="name()='PersonReferee'">
                    <xsl:call-template name="translateString">
                        <xsl:with-param name="string">frontdoor_referee_search</xsl:with-param>
                    </xsl:call-template>
                </xsl:if>
            </xsl:attribute>
            <xsl:value-of select="concat(@FirstName, ' ', @LastName)" />
        </xsl:element>

        <xsl:if test="@IdentifierOrcid and php:functionString('Application_Xslt::optionEnabled', 'linkAuthor.frontdoor', 'orcid')">
            <xsl:element name="a">
                <xsl:attribute name="href">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'baseUrl', 'orcid')"/>
                    <xsl:value-of select="@IdentifierOrcid"/>
                </xsl:attribute>
                <xsl:attribute name="class">
                    <xsl:text>orcid-link</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="title">
                    <xsl:call-template name="translateString">
                        <xsl:with-param name="string">frontdoor_orcid</xsl:with-param>
                    </xsl:call-template>
                </xsl:attribute>
                <xsl:text>ORCiD</xsl:text>
            </xsl:element>
        </xsl:if>

        <xsl:if test="@IdentifierGnd and php:functionString('Application_Xslt::optionEnabled', 'linkAuthor.frontdoor', 'gnd')">
            <xsl:element name="a">
                <xsl:attribute name="href">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'baseUrl', 'gnd')"/>
                    <xsl:value-of select="@IdentifierGnd"/>
                </xsl:attribute>
                <xsl:attribute name="class">
                    <xsl:text>gnd-link</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="title">
                    <xsl:call-template name="translateString">
                        <xsl:with-param name="string">frontdoor_gnd</xsl:with-param>
                    </xsl:call-template>
                </xsl:attribute>
                <xsl:text>GND</xsl:text>
            </xsl:element>
        </xsl:if>

        <xsl:if test="position() != last()">, </xsl:if>

        <xsl:if test="position() = last()">
            <xsl:text disable-output-escaping="yes">&lt;/td&gt;</xsl:text>
            <xsl:text disable-output-escaping="yes">&lt;/tr&gt;</xsl:text>
        </xsl:if>
    </xsl:template>


    <xsl:template match="PersonAdvisor|PersonOther|PersonContributor|PersonEditor|PersonTranslator">
        <xsl:if test="position() = 1">
            <xsl:text disable-output-escaping="yes">&lt;tr&gt;</xsl:text>
            <th class="name">
                <xsl:if test="position() = 1">
                    <xsl:call-template name="translateFieldname"/>
                </xsl:if>
            </th>
            <xsl:text disable-output-escaping="yes">&lt;td&gt;</xsl:text>
        </xsl:if>
        <xsl:value-of select="concat(@FirstName, ' ', @LastName)" />
        <xsl:if test="position() != last()">, </xsl:if>
        <xsl:if test="position() = last()">
            <xsl:text disable-output-escaping="yes">&lt;/td&gt;</xsl:text>
            <xsl:text disable-output-escaping="yes">&lt;/tr&gt;</xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template match="IdentifierArxiv">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:element name="a">
                    <xsl:attribute name="href">
                        <xsl:text>http://arxiv.org/abs/</xsl:text>
                        <xsl:value-of select="@Value" />
                    </xsl:attribute>
                    <xsl:text>http://arxiv.org/abs/</xsl:text>
                    <xsl:value-of select="@Value" />
                </xsl:element>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="IdentifierPubmed">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:element name="a">
                    <xsl:attribute name="href">
                        <xsl:text>http://www.ncbi.nlm.nih.gov/pubmed?term=</xsl:text>
                        <xsl:value-of select="@Value" />
                    </xsl:attribute>
                    <xsl:text>http://www.ncbi.nlm.nih.gov/pubmed?term=</xsl:text>
                    <xsl:value-of select="@Value" />
                </xsl:element>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="IdentifierHandle|IdentifierUrl">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:element name="a">
                    <xsl:choose>
                        <xsl:when test="contains(@Value, '://')">
                            <xsl:attribute name="href">
                                <xsl:value-of select="@Value" />
                            </xsl:attribute>
                            <xsl:value-of select="@Value" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="href">
                                <xsl:text>http://</xsl:text>
                                <xsl:value-of select="@Value" />
                            </xsl:attribute>
                            <xsl:text>http://</xsl:text>
                            <xsl:value-of select="@Value" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:element>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="IdentifierDoi|ReferenceDoi">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:element name="a">
                    <xsl:attribute name="href">
                        <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'doi.resolverUrl')"/>
                        <xsl:value-of select="@Value" />
                    </xsl:attribute>
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'doi.resolverUrl')"/>
                    <xsl:value-of select="@Value" />
                </xsl:element>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="IdentifierUrn|ReferenceUrn">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:element name="a">
                    <xsl:attribute name="href">
                        <xsl:value-of select="$urnResolverUrl" />
                        <xsl:value-of select="@Value" />
                    </xsl:attribute>
                    <xsl:value-of select="@Value" />
                </xsl:element>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="IdentifierIsbn|IdentifierIssn|IdentifierSerial|ReferenceIsbn|ReferenceIssn|ReferenceHandle">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:value-of select="@Value" />
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="TitleParent|TitleSub|TitleAdditional" mode="mainLanguage">
        <xsl:if test="@Language = $docLang">
            <tr>
                <th class="name">
                    <xsl:call-template name="translateFieldname"/>
                </th>
                <td>
                    <xsl:attribute name="class">
                        <xsl:text>title</xsl:text>
                        <xsl:value-of select="@Type"/>
                    </xsl:attribute>
                    <xsl:attribute name="lang">
                        <xsl:value-of select="php:functionString('Application_Xslt::languageWebForm', @Language)"/>
                    </xsl:attribute>
                    <xsl:value-of select="@Value" />
                </td>
            </tr>
        </xsl:if>
    </xsl:template>

    <xsl:template match="TitleParent|TitleSub|TitleAdditional" mode="otherLanguage">
        <xsl:if test="@Language != $docLang">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:attribute name="class">
                    <xsl:text>title</xsl:text>
                    <xsl:value-of select="@Type"/>
                </xsl:attribute>
                <xsl:attribute name="lang">
                    <xsl:value-of select="php:functionString('Application_Xslt::languageWebForm', @Language)"/>
                </xsl:attribute>
                <xsl:value-of select="@Value" />
            </td>
        </tr>
        </xsl:if>
    </xsl:template>

    <xsl:template match="Series[@Visible=1]">
        <tr>
            <xsl:choose>
                <xsl:when test="position()=1">
                    <th class="name">
                        <xsl:call-template name="translateString">
                            <xsl:with-param name="string">Series</xsl:with-param>
                        </xsl:call-template>
                        <xsl:text> (</xsl:text>
                        <xsl:call-template name="translateString">
                            <xsl:with-param name="string">SeriesNumber</xsl:with-param>
                        </xsl:call-template>
                        <xsl:text>)</xsl:text>
                        <xsl:text>:</xsl:text>
                    </th>
                </xsl:when>
                <xsl:otherwise>
                    <th class="name"></th>
                </xsl:otherwise>
            </xsl:choose>
            <td>
                <a>
                    <xsl:attribute name="href">
                        <xsl:value-of select="$baseUrl"/>
                        <xsl:text>/solrsearch/index/search/searchtype/series/id/</xsl:text>
                        <xsl:value-of select="@Id" />
                    </xsl:attribute>

                    <xsl:attribute name="title">
                        <xsl:call-template name="translateString">
                            <xsl:with-param name="string">frontdoor_series_link</xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>

                    <xsl:value-of select="@Title" />
                </a>
                <xsl:text> (</xsl:text>
                <xsl:value-of select="@Number" />
                <xsl:text>)</xsl:text>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="Licence">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <img alt="License Logo">
                    <xsl:attribute name="src">
                        <xsl:value-of select="@LinkLogo"/>
                    </xsl:attribute>
                    <xsl:attribute name="title">
                        <xsl:value-of select="@LinkLicence"/>
                    </xsl:attribute>
                </img>

                <xsl:element name="a">
                    <xsl:attribute name="href">
                        <xsl:value-of select="$baseUrl"/><xsl:text>/default/license/index/licId/</xsl:text><xsl:value-of select="@Id"/>
                    </xsl:attribute>
                    <xsl:value-of select="@NameLong"/>
                </xsl:element>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="Note">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
               <pre class="preserve-spaces"><xsl:value-of select="@Message" /></pre>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="Patent">
        <xsl:if test="@Number">
            <xsl:call-template name="PatentData">
                <xsl:with-param name="name">Opus_Patent_Number</xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select="@Number"/></xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="@Countries">
            <xsl:call-template name="PatentData">
                <xsl:with-param name="name">Countries</xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select="@Countries"/></xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="@YearApplied">
            <xsl:call-template name="PatentData">
                <xsl:with-param name="name">YearApplied</xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select="@YearApplied"/></xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="@Application">
            <xsl:call-template name="PatentData">
                <xsl:with-param name="name">Application</xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select="@Application"/></xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="DateGranted">
            <xsl:call-template name="PatentData">
                <xsl:with-param name="name">DateGranted</xsl:with-param>
                <xsl:with-param name="value">
                    <xsl:call-template name="formatDate">
                        <xsl:with-param name="year"><xsl:value-of select="DateGranted/@Year"/></xsl:with-param>
                        <xsl:with-param name="month"><xsl:value-of select="DateGranted/@Month"/></xsl:with-param>
                        <xsl:with-param name="day"><xsl:value-of select="DateGranted/@Day"/></xsl:with-param>
                    </xsl:call-template>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <xsl:template name="PatentData">
        <xsl:param name="name"/>
        <xsl:param name="value"/>
        <tr>
            <th class="name">
                <xsl:call-template name="translateString">
                    <xsl:with-param name="string">
                        <xsl:value-of select="$name"/>
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text>:</xsl:text>
            </th>
            <td>
                <xsl:value-of select="$value"/>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="ReferenceUrl">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:element name="a">
                    <xsl:attribute name="href">
                        <xsl:value-of select="@Value" />
                    </xsl:attribute>
                    <xsl:attribute name="rel">
                        <xsl:text>nofollow</xsl:text>
                    </xsl:attribute>
                    <xsl:value-of select="@Label" />
                </xsl:element>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="Subject">
        <xsl:if test="position() = 1">
            <xsl:text disable-output-escaping="yes">&lt;tr&gt;</xsl:text>
            <xsl:text disable-output-escaping="yes">&lt;th class="name"&gt;</xsl:text>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string">subject_frontdoor_<xsl:value-of select="@Type" />
                </xsl:with-param>
            </xsl:call-template>
            <xsl:text>:</xsl:text>
            <xsl:text disable-output-escaping="yes">&lt;/th&gt;</xsl:text>
            <xsl:text disable-output-escaping="yes">&lt;td&gt;&lt;em class="data-marker"&gt;</xsl:text>
        </xsl:if>
        <xsl:value-of select="@Value" />
        <xsl:if test="position() != last()">; </xsl:if>
        <xsl:if test="position() = last()">
            <xsl:text disable-output-escaping="yes">&lt;/em&gt;&lt;/td&gt;</xsl:text>
            <xsl:text disable-output-escaping="yes">&lt;/tr&gt;</xsl:text>
        </xsl:if>
    </xsl:template>


    <xsl:template match="ThesisGrantor|ThesisPublisher">
        <tr>
            <th class="name">
                <xsl:call-template name="translateFieldname"/>
            </th>
            <td>
                <xsl:value-of select="@Name" />
                <xsl:if test="@Department">, <xsl:value-of select="@Department" /></xsl:if>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="IdentifierStdDoi"/>
    <xsl:template match="IdentifierCrisLink"/>
    <xsl:template match="IdentifierSplashUrl"/>
    <xsl:template match="ReferenceStdDoi"/>
    <xsl:template match="ReferenceCrisLink"/>
    <xsl:template match="ReferenceSplashUrl"/>

</xsl:stylesheet>
