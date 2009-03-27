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
 * @package     Module_Oai
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_Oai
 */
-->

<xsl:stylesheet version="1.0"
    xmlns="http://www.openarchives.org/OAI/2.0/"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">


    <xsl:include href="oai_dc.xslt"/>
    <xsl:include href="epicur.xslt"/>
    <xsl:output method="xml" indent="yes" />

    <xsl:param name="dateTime" />
    <xsl:param name="oai_verb" />
    <xsl:param name="oai_from" />
    <xsl:param name="oai_until" />
    <xsl:param name="oai_metadataPrefix" />
    <xsl:param name="oai_identifier" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />
    <xsl:template match="*" mode="oai_dc" />

    <xsl:template match="/">
        <xsl:element name="OAI-PMH">
            <xsl:attribute name="xsi:schemaLocation">
                <xsl:text>http://www.openarchives.org/OAI/2.0/
                http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd</xsl:text>
            </xsl:attribute>
            <xsl:element name="responseDate">
                <xsl:value-of select="$dateTime" />
            </xsl:element>
            <xsl:element name="request">
                <xsl:attribute name="verb"><xsl:value-of select="$oai_verb" /></xsl:attribute>
                <xsl:if test="$oai_from != ''">
                    <xsl:attribute name="from"><xsl:value-of select="$oai_from" /></xsl:attribute>
                </xsl:if>
                <xsl:if test="$oai_until != ''">
                    <xsl:attribute name="until"><xsl:value-of select="$oai_until" /></xsl:attribute>
                </xsl:if>
                <xsl:attribute name="metadataPrefix"><xsl:value-of select="$oai_metadataPrefix" /></xsl:attribute>
            </xsl:element>
            <xsl:choose>
                <xsl:when test="$oai_verb='ListRecords'">
                    <xsl:apply-templates select="Documents" mode="ListRecords" />
                </xsl:when>
                <xsl:when test="$oai_verb='GetRecord'">
                    <xsl:apply-templates select="Opus_Document" mode="GetRecord" />
                </xsl:when>
                <xsl:otherwise>
                    <error code="badVerb">The verb <xsl:value-of select="$oai_verb" /> provided in the request is illegal.</error>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Documents" mode="ListRecords">
        <xsl:element name="ListRecords">
            <xsl:apply-templates select="Opus_Document" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Opus_Document" mode="GetRecord">
        <xsl:element name="GetRecord">
            <xsl:apply-templates select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Opus_Document">
        <xsl:element name="record">
            <xsl:element name="header">
                <!--
                    This is the identifier for the metadata, not a digital object:
                    http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm
                -->
                <xsl:element name="identifier">
                    <xsl:value-of select="$oai_identifier" />
                </xsl:element>
                <xsl:element name="datestamp">
                    <xsl:value-of select="@PublishedDate" />
                </xsl:element>
            </xsl:element>
            <xsl:element name="metadata">
                <xsl:apply-templates select="." mode="oai_dc" />
            </xsl:element>
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
