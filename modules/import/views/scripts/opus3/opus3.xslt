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
 * @package     Module_Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_Import
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="no" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />

    <xsl:template match="/">
        <xsl:element name="Documents">
            <xsl:apply-templates select="/mysqldump/database/table_data[@name='opus']/row" />
        </xsl:element>
    </xsl:template>

    <!--
    Suppress fields with nil value
    -->
    <xsl:template match="table_data[@name='opus']/row/field[@xsi:nil='true']" />

    <xsl:template match="table_data[@name='opus']/row">
        <xsl:element name="Opus_Document">
            <xsl:attribute name="Language">
                <xsl:value-of select="field[@name='language']" />
            </xsl:attribute>
            <xsl:attribute name="CreatingCorporation">
                <xsl:value-of select="field[@name='creator_corporate']" />
            </xsl:attribute>
            <xsl:attribute name="Type">
                <xsl:choose>
                    <xsl:when test="field[@name='type']='1'">manual</xsl:when>
                    <xsl:when test="field[@name='type']='2'">article</xsl:when>
                    <xsl:when test="field[@name='type']='4'">monograph</xsl:when>
                    <xsl:when test="field[@name='type']='5'">book section</xsl:when>
                    <xsl:when test="field[@name='type']='7'">master thesis</xsl:when>
                    <xsl:when test="field[@name='type']='8'">doctoral thesis</xsl:when>
                    <xsl:when test="field[@name='type']='9'">honour thesis</xsl:when>
                    <xsl:when test="field[@name='type']='11'">journal</xsl:when>
                    <xsl:when test="field[@name='type']='15'">conference</xsl:when>
                    <xsl:when test="field[@name='type']='16'">conference item</xsl:when>
                    <xsl:when test="field[@name='type']='17'">paper</xsl:when>
                    <xsl:when test="field[@name='type']='19'">study paper</xsl:when>
                    <xsl:when test="field[@name='type']='20'">report</xsl:when>
                    <xsl:when test="field[@name='type']='22'">preprint</xsl:when>
                    <xsl:when test="field[@name='type']='23'">other</xsl:when>
                    <xsl:when test="field[@name='type']='24'">habil thesis</xsl:when>
                    <xsl:when test="field[@name='type']='25'">bachelor thesis</xsl:when>
                    <xsl:when test="field[@name='type']='26'">lecture</xsl:when>
                    <xsl:otherwise>other</xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
            <xsl:apply-templates select="field" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="table_data[@name='opus']/row/field[@name='title']">
        <xsl:element name="TitleMain">
            <xsl:attribute name="Language">
                <xsl:value-of select="../field[@name='language']" />
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
