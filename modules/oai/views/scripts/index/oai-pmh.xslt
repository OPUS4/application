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
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:include href="oai_dc.xslt"/>
    <xsl:output method="xml" indent="yes" />

    <xsl:param name="dateTime" />
    <xsl:param name="oai_verb" />
    <xsl:param name="oai_from" />
    <xsl:param name="oai_until" />
    <xsl:param name="oai_metadataPrefix" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />
    <xsl:template match="*" mode="oai_dc" />

    <xsl:template match="/">
        <OAI-PMH>
            <responseDate><xsl:value-of select="$dateTime" /></responseDate>
            <request>
                <xsl:attribute name="verb"><xsl:value-of select="$oai_verb" /></xsl:attribute>
                <xsl:attribute name="from"><xsl:value-of select="$oai_from" /></xsl:attribute>
                <xsl:attribute name="until"><xsl:value-of select="$oai_until" /></xsl:attribute>
                <xsl:attribute name="metadataPrefix"><xsl:value-of select="$oai_metadataPrefix" /></xsl:attribute>
            </request>
            <xsl:choose>
                <xsl:when test="$oai_verb='ListRecords'">
                    <xsl:apply-templates select="Documents" mode="ListRecords" />
                </xsl:when>
                <xsl:when test="$oai_verb='GetRecord'">
                    <xsl:apply-templates select="Document" mode="GetRecord" />
                </xsl:when>
                <xsl:otherwise>
                    <error code="badVerb">The verb <xsl:value-of select="$oai_verb" /> provided in the request is illegal.</error>
                </xsl:otherwise>
            </xsl:choose>
        </OAI-PMH>
    </xsl:template>

    <xsl:template match="Documents" mode="ListRecords">
        <ListRecords>
            <xsl:apply-templates select="Document" />
        </ListRecords>
    </xsl:template>

    <xsl:template match="Document" mode="GetRecord">
        <GetRecord>
            <xsl:apply-templates select="." />
        </GetRecord>
    </xsl:template>

    <xsl:template match="Document">
        <record>
            <header>
                <identifier><xsl:value-of select="Urn" /></identifier>
                <datestamp><xsl:value-of select="PublishedDate" /></datestamp>
            </header>
            <metadata>
                <xsl:apply-templates select="." mode="oai_dc" />
            </metadata>
        </record>
    </xsl:template>

</xsl:stylesheet>
