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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:marcxml="http://www.loc.gov/MARC21/slim">
    <xsl:output	method="xml"
				encoding="UTF-8"/>

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*"/>
    
    <xsl:template match="/">
        <xsl:element name="import">
            <xsl:apply-templates select="records/marcxml:record"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="marcxml:record">
        <xsl:element name="opusDocument">
		<!--Reihenfolge entspricht import.xsd-->
            <xsl:apply-templates select="marcxml:controlfield[@tag='008']"/>
            <xsl:apply-templates select="marcxml:datafield[@tag='953']/marcxml:subfield[@code='h']"/>
            <xsl:apply-templates select="marcxml:datafield[@tag='245']/marcxml:subfield[@code='a']"/>
			<xsl:apply-templates select="marcxml:datafield[@tag='773']/marcxml:subfield[@code='t']"/>
			<xsl:apply-templates select="marcxml:datafield[@tag='100']/marcxml:subfield[@code='a']"/>
			<xsl:apply-templates select="marcxml:datafield[@tag='953']/marcxml:subfield[@code='j']"/>
			<xsl:apply-templates select="marcxml:datafield[@tag='900']/marcxml:subfield[@code='d']"/>
			<xsl:apply-templates select="marcxml:controlfield[@tag='001']"/>
            
        </xsl:element>
    </xsl:template>
	
	<!--OPUSDOCUMENT Attribute-->
    <xsl:template match="marcxml:controlfield[@tag='008']">
        <xsl:attribute name="oldId">
            <xsl:value-of select="text()"/>
        </xsl:attribute>
		<!--Da Serverstate und Dokumenttyp nicht im MarcXML enthalten sind, werden "type" und "serverState
		aktuell statisch befüllt-->
		<xsl:attribute name="type">article</xsl:attribute>
		<xsl:attribute name="serverState">published</xsl:attribute>
		<!--TODO: Hier Feld 040 auswerten bzw. Fallunterscheidung einführen,
			die eine Standardsprache setzt, wenn 040 nicht existiert.-->
		<xsl:attribute name="language">deu</xsl:attribute>
    </xsl:template>

    <xsl:template match="marcxml:datafield[@tag='953']/marcxml:subfield[@code='h']">
        <xsl:attribute name="pageNumber">
            <xsl:value-of select="text()"/>
        </xsl:attribute>
    </xsl:template>
	
	<!--TITLESMAIN-->
   <xsl:template match="marcxml:datafield[@tag='245']/marcxml:subfield[@code='a']">
    <xsl:element name="titlesMain">
        <xsl:element name="titleMain">
            <!--TODO: Hier auf Sprache aus Feld 040 zugreifen bzw. Fallunterscheidung einführen,
			die eine Standardsprache setzt, wenn 040 nicht existiert.-->
			<xsl:attribute name="language">deu</xsl:attribute>
            <xsl:value-of select="text()"/>
        </xsl:element>
     </xsl:element>
    </xsl:template>
	
	<!--PERSONS-->
    <xsl:template match="marcxml:datafield[@tag='100']/marcxml:subfield[@code='a']">
     <xsl:element name="persons">
        <xsl:element name="person">
            <xsl:attribute name="role">author</xsl:attribute>
            <xsl:attribute name="firstName">
			<xsl:value-of select="substring-after(text(), ',')"/>
			</xsl:attribute>
			<xsl:attribute name="lastName">
			<xsl:value-of select="substring-before(text(), ',')"/>
		</xsl:attribute>
        </xsl:element>
     </xsl:element>
    </xsl:template>
	
	<!--TITLES-->
    <xsl:template match="marcxml:datafield[@tag='773']/marcxml:subfield[@code='t']">
        <xsl:element name="titles">
         <xsl:element name="title">
            <!--TODO: Hier auf Sprache aus Feld 040 zugreifen bzw. Fallunterscheidung einführen,
			die eine Standardsprache setzt, wenn 040 nicht existiert.-->
			<xsl:attribute name="language">deu</xsl:attribute>
            <xsl:attribute name="type">parent</xsl:attribute>
            <xsl:value-of select="text()"/>
        </xsl:element>
     </xsl:element>
    </xsl:template>
	
	<!--DATES-->
	    <xsl:template match="marcxml:datafield[@tag='953']/marcxml:subfield[@code='j']">
     <xsl:element name="dates">
        <xsl:element name="date">
            <xsl:attribute name="type">published</xsl:attribute>
            <xsl:attribute name="year">
			<!--Bei Angaben mit mehr als 4 Zeichen werden nur die ersten 4 genommen, sonst validiert "year" nicht.-->
            <xsl:value-of select="substring(text(), 0, 5)"/>
            </xsl:attribute>
        </xsl:element>
     </xsl:element>
    </xsl:template>
	
	<!--IDENTIFIERs-->
	<xsl:template match="marcxml:datafield[@tag='900']/marcxml:subfield[@code='d']">
        <xsl:element name="identifiers">
         <xsl:element name="identifier">
            <xsl:attribute name="type">opac-id</xsl:attribute>
            <xsl:value-of select="text()"/>
        </xsl:element>
     </xsl:element>
    </xsl:template>
	
	<!--ENRICHMENTS-->
	<xsl:template match="marcxml:controlfield[@tag='001']">
        <xsl:element name="enrichments">
         <xsl:element name="enrichment">
            <xsl:attribute name="key">ppn</xsl:attribute>
            <xsl:value-of select="text()"/>
        </xsl:element>
     </xsl:element>
    </xsl:template>
	
</xsl:stylesheet>
