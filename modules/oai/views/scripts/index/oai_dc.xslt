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
 * Transforms the xml representation of an Opus_Model_Document to dublin core
 * xml as required by the OAI-PMH protocol.
 *
 * @category    Application
 * @package     Module_Oai
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" />


    <xsl:template match="Opus_Document" mode="oai_dc">
        <xsl:element name="oai_dc:dc">
            xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/
            http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
            <!-- dc:title -->
            <xsl:apply-templates select="TitleMain" mode="oai_dc" />
            <!-- dc:creator -->
            <xsl:apply-templates select="PersonAuthor" mode="oai_dc" />
            <!-- dc:subject -->
            <xsl:apply-templates select="SubjectSwd" mode="oai_dc" />
            <!-- dc:description -->
            <xsl:apply-templates select="TitleAbstract" mode="oai_dc" />
            <!-- dc:publisher -->
            <!-- <xsl:apply-templates select="" /> -->
            <!-- dc:contributor -->
            <xsl:apply-templates select="@ContributingCorporation" mode="oai_dc" />
            <!-- dc:date -->
            <xsl:apply-templates select="@PublishedDate" mode="oai_dc" />
            <!-- dc:type -->
            <!-- <xsl:apply-templates select="" /> -->
            <!-- dc:format -->
            <xsl:apply-templates select="File/@MimeType" mode="oai_dc" />
            <!-- dc:identifier -->
            <xsl:apply-templates select="IdentifierIsbn|IdentifierUrn" mode="oai_dc" />
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
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleMain" mode="oai_dc">
        <xsl:element name="dc:title">
            <xsl:attribute name="xml:lang">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="PersonAuthor" mode="oai_dc">
        <xsl:element name="dc:creator">
            <xsl:value-of select="@AcademicTitle" />
            <xsl:text> </xsl:text>
            <xsl:value-of select="@FirstName" />
            <xsl:text> </xsl:text>
            <xsl:value-of select="@LastName" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectSwd" mode="oai_dc">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xml:lang">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleAbstract" mode="oai_dc">
        <xsl:element name="dc:description">
            <xsl:attribute name="xml:lang">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@ContributingCorporation" mode="oai_dc">
        <xsl:element name="dc:contributor">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@PublishedDate" mode="oai_dc">
        <xsl:element name="dc:date">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="File/@MimeType" mode="oai_dc">
        <xsl:element name="dc:format">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="IdentifierIsbn|IdentifierUrn" mode="oai_dc">
        <xsl:element name="dc:identifier">
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@Language" mode="oai_dc">
        <xsl:element name="dc:language">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Licence" mode="oai_dc">
        <xsl:element name="dc:rights">
            <xsl:value-of select="@NameLong" />
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
