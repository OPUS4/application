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
 * @category    Application
 * @package     Import
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
                xmlns:mods="http://www.loc.gov/mods/v3">

    <xsl:output	method="xml" encoding="UTF-8" indent="yes" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*"/>
    
    <xsl:template match="/">
        <xsl:element name="import">
            <xsl:apply-templates select="mods:modsCollection/mods:mods"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="mods:mods">
        <xsl:element name="opusDocument">

            <!--OPUSXML: ATTRIBUTE (required) -->
            <xsl:attribute name="oldId">
                <xsl:value-of select="@ID"/>
            </xsl:attribute>

            <xsl:attribute name="language">
                <xsl:text>deu</xsl:text>
            </xsl:attribute>

            <xsl:attribute name="type">
                <xsl:choose>
                    <xsl:when test="mods:relatedItem[@type='host']/mods:genre[@authority='marcgt'] = 'periodical'">article</xsl:when>
                    <xsl:when test="mods:genre = 'book'">book</xsl:when>
                    <xsl:when test="mods:relatedItem[@type='host']/mods:genre[@authority='marcgt'] = 'book'">bookpart</xsl:when>
                    <xsl:when test="mods:relatedItem[@type='host']/mods:genre = 'collection'">bookpart</xsl:when>
                    <xsl:when test="mods:relatedItem[@type='host']/mods:genre[@authority='marcgt'] = 'conference publication'">conferenceobject</xsl:when>
                    <xsl:when test="mods:genre[@authority='marcgt'] = 'conference publication'">conferenceobject</xsl:when>
                    <xsl:when test="mods:genre = 'instruction'">other</xsl:when>
                    <xsl:when test="mods:genre = 'Masters thesis'">masterthesis</xsl:when>
                    <xsl:when test="mods:genre = 'Ph.D. thesis'">doctoralthesis</xsl:when>
                    <xsl:when test="mods:genre = 'report'">report</xsl:when>
                    <xsl:when test="mods:genre = 'unpublished'">other</xsl:when>
                    <xsl:otherwise>other</xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>

            <xsl:attribute name="serverState">unpublished</xsl:attribute>

            <!--OPUSXML: ATTRIBUTE (optional) -->
            <xsl:apply-templates select="mods:name[@type='corporate']"/>
            <xsl:apply-templates select="mods:name/mods:role"/>
            
            <xsl:apply-templates select="mods:originInfo/mods:edition"/>
            <xsl:apply-templates select="mods:originInfo/mods:publisher"/>
            <xsl:apply-templates select="mods:originInfo/mods:place/mods:placeTerm"/>
            <xsl:apply-templates select="mods:part/mods:extent[@unit='page']/mods:start"/>
            <xsl:apply-templates select="mods:part/mods:extent[@unit='page']/mods:end"/>
            <xsl:apply-templates select="mods:part/mods:detail[@type='volume']/mods:number"/>
            <xsl:apply-templates select="mods:part/mods:detail[@type='number']/mods:number"/>
            <xsl:apply-templates select="mods:part/mods:detail[@type='issue']/mods:number"/>
            <xsl:apply-templates select="mods:part/mods:detail[@type='page']/mods:number"/>

            <xsl:apply-templates select="mods:relatedItem[@type='host']/mods:name[@type='corporate']"/>
            <xsl:apply-templates select="mods:relatedItem[@type='host']/mods:part/mods:extent[@unit='page']/mods:start"/>
            <xsl:apply-templates select="mods:relatedItem[@type='host']/mods:part/mods:extent[@unit='page']/mods:end"/>
            <xsl:apply-templates select="mods:relatedItem[@type='host']/mods:relatedItem[@type='host']/mods:part/mods:detail[@type='volume']/mods:number"/>
            <xsl:apply-templates select="mods:relatedItem[@type='host']/mods:originInfo/mods:edition"/>
            <xsl:apply-templates select="mods:relatedItem[@type='host']/mods:originInfo/mods:publisher"/>
            <xsl:apply-templates select="mods:relatedItem[@type='host']/mods:originInfo/mods:place/mods:placeTerm"/>

            
            <!--OPUSXML: ELEMENTE (required) -->
            <xsl:choose>
                <xsl:when test="mods:titleInfo/mods:title != ''">
                    <xsl:apply-templates select="mods:titleInfo/mods:title"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates select="mods:part/mods:detail[@type='chapter']/mods:number"/>
                </xsl:otherwise>
            </xsl:choose>
            
            <xsl:apply-templates select="mods:relatedItem[@type='host']/mods:titleInfo/mods:title"/>
            <xsl:if test="mods:name[@type='personal'] or mods:relatedItem[@type='host']/mods:name[@type='personal']">
                <xsl:element name="persons">
                    <xsl:apply-templates select="mods:name[@type='personal']"/>
                    <xsl:apply-templates select="mods:relatedItem[@type='host']/mods:name[@type='personal']"/>
                </xsl:element>
            </xsl:if>
            <xsl:apply-templates select="mods:originInfo/mods:dateIssued"/>
            <xsl:if test="mods:identifier[@type='isbn']">
                <xsl:element name="identifiers">
                    <xsl:apply-templates select="mods:identifier[@type='isbn']"/>
                </xsl:element>
            </xsl:if>
            <xsl:apply-templates select="mods:note"/>

        </xsl:element>
    </xsl:template>

    <!-- mods:part -->
    <xsl:template match="mods:identifier[@type='isbn']">
        <xsl:element name="identifier">
            <xsl:attribute name="type">isbn</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="mods:name[@type='personal']">
        <xsl:element name="person">
            <xsl:attribute name="role">
                <xsl:value-of select="mods:role/mods:roleTerm"/>
            </xsl:attribute>
            <xsl:attribute name="firstName">
                <xsl:value-of select="mods:namePart[@type='given']"/>
            </xsl:attribute>
            <xsl:attribute name="lastName">
                <xsl:value-of select="mods:namePart[@type='family']"/>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match="mods:name[@type='corporate']">
        <xsl:attribute name="contributingCorporation">
            <xsl:value-of select="mods:namePart"/>
        </xsl:attribute>
    </xsl:template>
    
    <xsl:template match="mods:name/mods:role">
        <xsl:if test="mods:roleTerm = 'degree grantor'">
            <xsl:attribute name="contributingCorporation">
                <xsl:value-of select="../mods:namePart"/>
            </xsl:attribute>
        </xsl:if>
    </xsl:template>


    <xsl:template match="mods:note">
        <xsl:element name="notes">
            <xsl:element name="note">
                <xsl:attribute name="visibility">public</xsl:attribute>
                <xsl:value-of select="."/>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="mods:originInfo/mods:edition">
        <xsl:attribute name="edition">
            <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="mods:originInfo/mods:publisher">
        <xsl:choose>
            <xsl:when test="../../mods:genre = 'instruction'">
                 <xsl:attribute name="contributingCorporation">
                    <xsl:value-of select="."/>
                </xsl:attribute>              
            </xsl:when>
            <xsl:otherwise>
                <xsl:attribute name="publisherName">
                    <xsl:value-of select="."/>
                </xsl:attribute>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="mods:originInfo/mods:place/mods:placeTerm">
        <xsl:attribute name="publisherPlace">
            <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="mods:originInfo/mods:dateIssued">
        <xsl:element name="dates">
            <xsl:element name="date">
                <xsl:attribute name="type">published</xsl:attribute>
                <xsl:attribute name="year">
                    <!--Bei Angaben mit mehr als 4 Zeichen werden nur die ersten 4 genommen, sonst validiert "year" nicht.-->
                    <xsl:value-of select="substring(., 0, 5)"/>
                </xsl:attribute>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="mods:part/mods:extent[@unit='page']/mods:start">
        <xsl:attribute name="pageFirst">
            <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="mods:part/mods:extent[@unit='page']/mods:end">
        <xsl:attribute name="pageLast">
            <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="mods:part/mods:detail[@type='volume']/mods:number">
        <xsl:attribute name="volume">
            <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="mods:part/mods:detail[@type='number' or @type='issue']/mods:number">
        <xsl:attribute name="issue">
            <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="mods:part/mods:detail[@type='page']/mods:number">
        <xsl:attribute name="pageFirst">
            <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="mods:part/mods:detail[@type='chapter']/mods:number">
        <xsl:element name="titlesMain">
            <xsl:element name="titleMain">
                <xsl:attribute name="language">deu</xsl:attribute>
                <xsl:value-of select="."/>
            </xsl:element>
         </xsl:element>
    </xsl:template>

    <xsl:template match="mods:titleInfo/mods:title">
        <xsl:element name="titlesMain">
            <xsl:element name="titleMain">
                <xsl:attribute name="language">deu</xsl:attribute>
                <xsl:value-of select="."/>
                <xsl:if test="../mods:subTitle != ''">
                    <xsl:text>: </xsl:text>
                    <xsl:value-of select="../mods:subTitle" />
                </xsl:if>
            </xsl:element>
         </xsl:element>
     </xsl:template>

    <xsl:template match="mods:relatedItem[@type='host']/mods:titleInfo/mods:title">
        <xsl:element name="titles">
            <xsl:element name="title">
                <xsl:attribute name="language">deu</xsl:attribute>
                <xsl:attribute name="type">parent</xsl:attribute>
                <xsl:value-of select="."/>
            </xsl:element>
        </xsl:element>
    </xsl:template>

 
</xsl:stylesheet>
