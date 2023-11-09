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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<!--
/**
 * Transforms the xml representation of an Opus_Model_Document to oai_pp
 * xml as required by the OAI-PMH protocol.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:PP="http://www.proprint-service.de/xml/schemes/v1/CHECKED"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" />

    <xsl:template match="Opus_Document" >
        <PP:ProPrint
            xsi:schemaLocation="http://www.proprint-service.de/xml/schemes/v1/ http://www.proprint-service.de/xml/schemes/v1/PROPRINT_METADATA_SET.xsd">

            <!--  Identifier -->
            <xsl:apply-templates select="Identifier[@Type = 'urn']"  />
            <!-- dc:title -->
            <xsl:apply-templates select="TitleMain"  />
            <!-- dc:subject -->
            <xsl:apply-templates select="Subject[@Type='swd']"  />
            <xsl:apply-templates select="Subject[@Type='uncontrolled']"  />
            <!-- dc:abstract -->
            <xsl:apply-templates select="TitleAbstract"  />
            <!-- contributor, noch anpassen (Personen und Institutionen) -->
            <xsl:apply-templates select="PersonAdvisor"  />
            <xsl:apply-templates select="@ContributingCorporation"  />
            <!--  eigentlich soll hier DateCreated stehen, aber welchem Feld entspricht das??  -->
            <xsl:apply-templates select="@DateAccepted"  />
            <xsl:apply-templates select="@Language"  />
            <xsl:apply-templates select="Identifier[@Type = 'url']"  />

            <!-- adding download urls -->
            <xsl:apply-templates select="File"  />

            <!-- dc:creator -->
            <xsl:apply-templates select="PersonAuthor"  />
            <!-- dc:publisher -->
            <!--  was soll hier genau stehen ??? -->
            <xsl:element name="PP:DC.publisher">
               <xsl:apply-templates select="@PublisherName"  />
               <xsl:apply-templates select="@PublisherPlace"  />
            </xsl:element>
        </PP:ProPrint>
    </xsl:template>

    <xsl:template match="TitleMain" >
        <xsl:element name="PP:DC.title">
            <xsl:choose>
              <!--  noch aendern auf "=", wenn das mit den Sprachen geklaert ist -->
              <xsl:when test="../@Language!=@Language">
                 <xsl:attribute name="language">
                   <xsl:value-of select="@Language" />
                 </xsl:attribute>
                <xsl:value-of select="@Value" />
              </xsl:when>
              <xsl:otherwise>
              </xsl:otherwise>
            </xsl:choose>
        </xsl:element>
    </xsl:template>

    <xsl:template match="PersonAuthor" >
        <xsl:element name="PP:DC.creator">
           <xsl:value-of select="@LastName" />,
           <xsl:value-of select="@FirstName" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Subject[@Type='swd']" >
        <xsl:element name="PP:DC.subject">
            <xsl:attribute name="scheme">
                <xsl:text>swd</xsl:text>
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Subject[@Type='uncontrolled']" >
        <xsl:element name="PP:DC.subject">
            <xsl:attribute name="language">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleAbstract" >
        <xsl:element name="PP:DCTERMS.Description.Abstract">
            <xsl:attribute name="language">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@PublisherName" >
        <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@PublisherPlace" >
        <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="PersonAdvisor" >
       <xsl:element name="PP:DC.contributor">
           <xsl:value-of select="@Name" />
       </xsl:element>
    </xsl:template>

    <xsl:template match="@ContributingCorporation" >
       <xsl:element name="PP:PPQ.Contributor.CorporateName">
          <xsl:value-of select="." />
       </xsl:element>
    </xsl:template>

    <xsl:template match="@DateAccepted" >
        <xsl:element name="PP:DCTERMS.Date.Created">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'urn']" >
        <xsl:element name="PP:Metadata">
            <xsl:attribute name="id">
              <xsl:value-of select="@Value" />
            </xsl:attribute>
        </xsl:element>
        <xsl:element name="PP:DCTERMS.Identifier">
           <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@Language" >
        <xsl:element name="PP:DCTERMS.Language">
           <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'url']" >
        <xsl:element name="PP:PP.Origin">
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="File" >
        <PP:PP.Origindoc>
            <xsl:value-of select="@url" />
        </PP:PP.Origindoc>
    </xsl:template>

</xsl:stylesheet>
