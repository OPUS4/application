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
 * @package     Module_FrontdoorXSLT
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_FrontdoorXSLT
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    exclude-result-prefixes="php">

    <xsl:output method="html" omit-xml-declaration="yes" />

    <xsl:template match="/">
        <div about="{/Opus/Opus_Model_Filter/TitleMain/@Value}">
            <xsl:apply-templates select="/Opus/Opus_Model_Filter/@*|/Opus/Opus_Model_Filter/node()" />
        </div>
    </xsl:template>

    <!-- Suppress spilling values with no corresponding templates -->
    <xsl:template match="@*|node()" />

    <!-- Templates for "internal fields". -->
    <xsl:template match="@DateAccepted">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value"><xsl:value-of select="." /></span>
    </xsl:template>

    <xsl:template match="@Type">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <!-- Templates for "external fields". -->
    <xsl:template match="TitleMain">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:title" xml:lang="{@Language}"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="TitleAbstract">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:description" xml:lang="{@Language}"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="PersonAuthor">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:creator"><xsl:value-of select="@Name" /></span>
    </xsl:template>

    <xsl:template match="Licence">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:rights"><xsl:value-of select="@NameLong" /></span>
    </xsl:template>

    <xsl:template match="SubjectUncontrolled">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:subject" xml:lang="{@Language}"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="IdentifierUrn|IdentifierDoi|IdentifierIsbn|IdentifierHandle|IdentifierUrl|IdentifierIssn">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="File">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:element name="a">
                <!-- TODO: Use Zend Url-Helper to build href attribute -->
                <xsl:attribute name="href">
                    <xsl:text>/documents/</xsl:text>
                    <xsl:value-of select="@DocumentId" />
                    <xsl:text>/</xsl:text>
                    <xsl:value-of select="@PathName" />
                </xsl:attribute>
                <xsl:value-of select="@PathName" />
            </xsl:element>
            <xsl:text> (</xsl:text><xsl:value-of select="@Label" /><xsl:text>)</xsl:text>
        </span>
    </xsl:template>

    <xsl:template match="Collection"/>
    <xsl:template match="CompletedDate"/>
    <xsl:template match="ContributingCorporation"/>
    <xsl:template match="CreatingCorporation"/>
    <xsl:template match="Edition"/>
    <xsl:template match="Enrichment"/>
    <xsl:template match="Institute"/>
    <xsl:template match="Issue"/>
    <xsl:template match="Language"/>
    <xsl:template match="NonInstituteAffiliation"/>
    <xsl:template match="Note"/>
    <xsl:template match="PageFirst"/>
    <xsl:template match="PageLast"/>
    <xsl:template match="PageNumber"/>
    <xsl:template match="Patent"/>
    <xsl:template match="PersonAdvisor"/>
    <xsl:template match="PersonOther"/>
    <xsl:template match="PersonReferee"/>
    <xsl:template match="PersonContributor"/>
    <xsl:template match="PersonEditor"/>
    <xsl:template match="PersonTranslator"/>
    <xsl:template match="PublicationVersion"/>
    <xsl:template match="PublishedDate"/>
    <xsl:template match="PublishedYear"/>
    <xsl:template match="PublisherName"/>
    <xsl:template match="PublisherPlace"/>
    <xsl:template match="PublisherUniversity"/>
    <xsl:template match="Reviewed"/>
    <xsl:template match="ServerDateUnlocking"/>
    <xsl:template match="ServerDateValid"/>
    <xsl:template match="Source"/>
    <xsl:template match="SubjectDdc"/>
    <xsl:template match="SubjectPsyndex"/>
    <xsl:template match="SubjectSwd"/>
    <xsl:template match="TitleParent"/>
    <xsl:template match="Volume"/>
    <xsl:template match="IdentifierIsbn"/>
    <xsl:template match="IdentifierDoi"/>
    <xsl:template match="IdentifierHandle"/>
    <xsl:template match="IdentifierUrl"/>
    <xsl:template match="IdentifierIssn"/>
    <xsl:template match="IdentifierStdDoi"/>
    <xsl:template match="IdentifierCrisLink"/>
    <xsl:template match="IdentifierSplashUrl"/>
    <xsl:template match="IdentifierOpus3"/>
    <xsl:template match="IdentifierOpac"/>
    <xsl:template match="ReferenceIsbn"/>
    <xsl:template match="ReferenceUrn"/>
    <xsl:template match="ReferenceDoi"/>
    <xsl:template match="ReferenceHandle"/>
    <xsl:template match="ReferenceUrl"/>
    <xsl:template match="ReferenceIssn"/>
    <xsl:template match="ReferenceStdDoi"/>
    <xsl:template match="ReferenceCrisLink"/>
    <xsl:template match="ReferenceSplashUrl"/>

    <!-- Named template to translate a field's name. Needs no parameter. -->
    <xsl:template name="translateFieldname">
        <xsl:value-of select="php:functionString('FrontdoorXSLT_IndexController::translate', name())" />
        <xsl:if test="normalize-space(@Language)">
            <!-- TODO: Enable translation of language abbreviations when they are available.
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="@Language" />
            </xsl:call-template>
            -->
            <xsl:text> (</xsl:text><xsl:value-of select="@Language" /><xsl:text>)</xsl:text>
        </xsl:if>
    </xsl:template>

    <!-- Named template to translate an arbitrary string. Needs the translation key as a parameter. -->
    <xsl:template name="translateString">
        <xsl:param name="string" />
        <xsl:value-of select="php:functionString('FrontdoorXSLT_IndexController::translate', $string)" />
    </xsl:template>

</xsl:stylesheet>
