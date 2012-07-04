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
 * @author      Sascha Szott <szott@zib.de>
 * @author      Doreen Thiede <thiede@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:marcxml="http://www.loc.gov/MARC21/slim">
    <xsl:output method="xml"/>

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
            <xsl:apply-templates select="marcxml:controlfield[@tag='001']"/>
            <xsl:apply-templates select="marcxml:datafield[@tag='773']/marcxml:subfield[@code='q']"/>
            <xsl:apply-templates select="marcxml:datafield[@tag='953']/marcxml:subfield[@code='j']"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="marcxml:controlfield[@tag='001']">
        <xsl:attribute name="oldId">
            <xsl:value-of select="text()"/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="marcxml:datafield[@tag='953']/marcxml:subfield[@code='j']">
        <xsl:element name="date">
            <xsl:attribute name="type">published</xsl:attribute>
            <xsl:attribute name="year">
            <xsl:value-of select="text()"/>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match="marcxml:datafield[@tag='773']/marcxml:subfield[@code='q']">
        <xsl:attribute name="pageNumber">
            <!-- am Anfang scheint immer das Entity &lt; zu stehen - daher ignorieren wir das erste Zeichen -->
            <xsl:value-of select="substring(text(), 2)"/>
        </xsl:attribute>
    </xsl:template>

</xsl:stylesheet>
