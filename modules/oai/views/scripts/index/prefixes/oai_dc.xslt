<?xml version="1.0" encoding="utf-8"?>
<!--
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
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
 * @package     Module_Oai
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * Transforms the xml representation of an Opus_Model_Document to dublin core
 * xml as required by the OAI-PMH protocol.
 */
-->
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" />


    <xsl:template match="Opus_Document" mode="oai_dc">
        <oai_dc:dc xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
            <!-- dc:title -->
            <xsl:apply-templates select="TitleMain" mode="oai_dc" />
            <!-- dc:creator -->
            <!-- Creator: Autor (falls vorhanden), sonst Herausgeber (falls vorhanden), sonst Urhebende Koerperschaft  -->
            <xsl:choose>
                <xsl:when test="PersonAuthor">
                    <xsl:apply-templates select="PersonAuthor" mode="oai_dc" />
                </xsl:when>
                <xsl:when test="PersonEditor">
                    <xsl:apply-templates select="PersonEditor" mode="oai_dc" />
                </xsl:when>
                <xsl:when test="@CreatingCorporation">
                    <dc:creator>
                        <xsl:value-of select="@CreatingCorporation" />
                    </dc:creator>
                </xsl:when>
            </xsl:choose>
            <!--<xsl:apply-templates select="PersonAuthor" mode="oai_dc" />-->
            <!-- dc:contributor -->
            <xsl:apply-templates select="PersonContributor" mode="oai_dc" />
            <!-- dc:subject -->
            <xsl:apply-templates select="Subject[@Type='swd']" mode="oai_dc" />
            <xsl:apply-templates select="Collection[@RoleName='ddc' and @Visible=1]" mode="oai_dc" />
            <!-- dc:description -->
            <xsl:apply-templates select="TitleAbstract" mode="oai_dc" />
            <!-- dc:publisher -->
            <!-- <xsl:apply-templates select="" /> -->
            <!-- dc:contributor -->
            <xsl:apply-templates select="@ContributingCorporation" mode="oai_dc" />
            <!-- dc:date -->
            <!-- TODO: date-code has been copy-pasted from XMetaDissPlus.xslt!-->
            <dc:date>
                <xsl:choose>
                    <xsl:when test="PublishedDate">
                        <xsl:value-of select="PublishedDate/@Year"/>-<xsl:value-of select="format-number(PublishedDate/@Month,'00')"/>-<xsl:value-of select="format-number(PublishedDate/@Day,'00')"/>
                    </xsl:when>
                    <xsl:when test="CompletedDate">
                        <xsl:value-of select="CompletedDate/@Year"/>-<xsl:value-of select="format-number(CompletedDate/@Month,'00')"/>-<xsl:value-of select="format-number(CompletedDate/@Day,'00')"/>
                    </xsl:when>
                    <xsl:when test="@PublishedYear">
                        <xsl:value-of select="@PublishedYear"/>
                    </xsl:when>
                    <xsl:when test="@CompletedYear">
                        <xsl:value-of select="@CompletedYear"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="ServerDatePublished/@Year"/>-<xsl:value-of select="format-number(ServerDatePublished/@Month,'00')"/>-<xsl:value-of select="format-number(ServerDatePublished/@Day,'00')"/>
                    </xsl:otherwise>
                </xsl:choose>
            </dc:date>
            <!-- dc:date: embargo date -->
            <xsl:apply-templates select="EmbargoDate" mode="oai_dc" />
            <!-- dc:type -->
            <xsl:apply-templates select="@Type" mode="oai_dc" />
            <!-- dc:format -->
            <xsl:apply-templates select="File/@MimeType" mode="oai_dc" />
            <!-- dc:identifier -->
            <dc:identifier>
                <xsl:value-of select="@frontdoorurl"/>
            </dc:identifier>
            <xsl:apply-templates select="IdentifierUrn" mode="oai_dc" />
            <xsl:apply-templates select="IdentifierIsbn" mode="oai_dc" />
            <xsl:apply-templates select="File" mode="oai_dc" />
            <!-- dc:source -->
            <!-- <xsl:apply-templates select="" /> -->
            <!-- dc:language -->
            <xsl:apply-templates select="@Language" mode="oai_dc" />
            <!-- dc:relation -->
            <!-- <xsl:apply-templates select="" /> -->
            <!-- dc:coverage -->
            <!-- <xsl:apply-templates select="" /> -->
            <!-- dc:rights -->
            <xsl:apply-templates select="Licence" mode="oai_dc" />
            <!-- open aire -->
            <xsl:apply-templates select="Enrichment" mode="oai_dc" />
            <xsl:apply-templates select="Rights" mode="oai_dc" />
            <!-- dc:type -->
            <!-- <dc:type>info:eu-repo/semantics/publishedVersion</dc:type> -->
            <!-- dc:source -->
            <xsl:apply-templates select="TitleParent" mode="oai_dc" />
        </oai_dc:dc>
    </xsl:template>

    <xsl:template match="TitleMain" mode="oai_dc">
        <dc:title>
            <xsl:attribute name="xml:lang">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </dc:title>
    </xsl:template>

    <xsl:template match="PersonAuthor|PersonEditor" mode="oai_dc">
        <dc:creator>
            <xsl:value-of select="@LastName" />
            <xsl:if test="@FirstName != ''" >
                <xsl:text>, </xsl:text>
            </xsl:if>
            <xsl:value-of select="@FirstName" />
            <xsl:if test="@AcademicTitle != ''" >
                <xsl:text> (</xsl:text>
                <xsl:value-of select="@AcademicTitle" />
                <xsl:text>)</xsl:text>
            </xsl:if>
        </dc:creator>
    </xsl:template>

    <xsl:template match="PersonContributor" mode="oai_dc">
        <dc:contributor>
            <xsl:value-of select="@LastName" />
            <xsl:if test="@FirstName != ''" >
                <xsl:text>, </xsl:text>
            </xsl:if>
            <xsl:value-of select="@FirstName" />
            <xsl:if test="@AcademicTitle != ''" >
                <xsl:text> (</xsl:text>
                <xsl:value-of select="@AcademicTitle" />
                <xsl:text>)</xsl:text>
            </xsl:if>
        </dc:contributor>
    </xsl:template>

    <xsl:template match="Subject[@Type='swd']" mode="oai_dc">
        <dc:subject>
            <xsl:if test="@language != ''">
                <xsl:attribute name="xml:lang">
                    <xsl:value-of select="@Language" />
                </xsl:attribute>
            </xsl:if>
            <xsl:value-of select="@Value" />
        </dc:subject>
    </xsl:template>

    <xsl:template match="Collection[@RoleName='ddc' and @Visible=1]" mode="oai_dc">
        <dc:subject>
            <xsl:text>ddc:</xsl:text><xsl:value-of select="@Number" />
        </dc:subject>
    </xsl:template>

    <xsl:template match="TitleAbstract" mode="oai_dc">
        <dc:description>
            <xsl:attribute name="xml:lang">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </dc:description>
    </xsl:template>

    <xsl:template match="@Type" mode="oai_dc">
        <xsl:choose>
            <xsl:when test=".='habilitation'" >
                <dc:type>
                    <xsl:value-of select="$OpenAirePrefix"/>
                    <xsl:text>doctoralthesis</xsl:text>
                </dc:type>
                <dc:type>
                    <xsl:value-of select="$OpenAirePrefix"/>
                    <xsl:text>doc-type:doctoralthesis</xsl:text>
                </dc:type>
            </xsl:when>
            <xsl:otherwise>
                <dc:type>
                    <xsl:value-of select="$OpenAirePrefix"/>
                    <xsl:value-of select="." />
                </dc:type>
                <dc:type>
                    <xsl:value-of select="$OpenAirePrefix"/>
                    <xsl:text>doc-type:</xsl:text><xsl:value-of select="." />
                </dc:type>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="@ContributingCorporation" mode="oai_dc">
        <dc:contributor>
            <xsl:value-of select="." />
        </dc:contributor>
    </xsl:template>

    <xsl:template match="File/@MimeType" mode="oai_dc">
        <dc:format>
            <xsl:value-of select="." />
        </dc:format>
    </xsl:template>

    <xsl:template match="File" mode="oai_dc">
        <dc:identifier>
            <xsl:value-of select="@url" />
        </dc:identifier>
    </xsl:template>

    <xsl:template match="IdentifierIsbn" mode="oai_dc">
        <dc:identifier>
            <xsl:value-of select="@Value" />
        </dc:identifier>
    </xsl:template>

    <xsl:template match="IdentifierUrn" mode="oai_dc">
        <dc:identifier>
            <xsl:value-of select="@Value" />
        </dc:identifier>
        <dc:identifier>
            <xsl:text>http://nbn-resolving.de/urn/resolver.pl?</xsl:text>
            <xsl:value-of select="@Value" />
        </dc:identifier>
    </xsl:template>

    <xsl:template match="@Language" mode="oai_dc">
        <dc:language>
            <xsl:value-of select="." />
        </dc:language>
    </xsl:template>

    <xsl:template match="Licence" mode="oai_dc">
        <dc:rights>
            <xsl:value-of select="@NameLong" />
        </dc:rights>
    </xsl:template>

    <xsl:template match="Enrichment[@KeyName='Relation']" mode="oai_dc">
                <xsl:choose>
            <xsl:when test="$oai_set='openaire'">
        <dc:relation>
            <xsl:value-of select="@Value" />
        </dc:relation>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="Rights" mode="oai_dc">
        <dc:rights>
            <xsl:value-of select="@Value" />
        </dc:rights>
    </xsl:template>

    <xsl:template match="EmbargoDate" mode="oai_dc">
        <xsl:choose>
            <xsl:when test="following-sibling::Rights/@Value='info:eu-repo/semantics/embargoedAccess'">
                <dc:date>
                    <xsl:text>info:eu-repo/date/embargoEnd/</xsl:text>
                    <xsl:value-of select="./@Year"/>-<xsl:value-of select="format-number(./@Month,'00')"/>-<xsl:value-of select="format-number(./@Day,'00')"/>
                </dc:date>
            </xsl:when>
        </xsl:choose>
    </xsl:template>


    <!--    <xsl:template match="TitleParent" mode="oai_dc">
            <dc:source>
                <xsl:attribute name="xml:lang">
                    <xsl:value-of select="@Language" />
                </xsl:attribute>
                <xsl:value-of select="@Value" />
            </dc:source>
        </xsl:template>     -->

    <xsl:param name="OpenAirePrefix">
        <xsl:choose>
            <xsl:when test="$oai_set='openaire'">
                <xsl:text>info:eu-repo/semantics/</xsl:text>
            </xsl:when>
        </xsl:choose>
    </xsl:param>

</xsl:stylesheet>

