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
 * @author      Simone Finkbeiner <simone.finkbeiner@ub.uni-stuttgart.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * Transforms the xml representation of an Opus_Model_Document to epicur
 * xml as required by the OAI-PMH protocol.
 *
 * @category    Application
 * @package     Module_Oai
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:epicur="urn:nbn:de:1111-2004033116"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" />


    <xsl:template match="Opus_Document" mode="epicur">
        <xsl:element name="epicur">
            <xsl:attribute name="xsi:schemaLocation">
              <xsl:text>urn:nbn:de:1111-2004033116 http://www.persistent-identifier.de/xepicur/version1.0/xepicur.xsd</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="xmlns:xsi">
              <xsl:text>http://www.w3.org/2001/XMLSchema-instance</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="xmlns:epicur">
              <xsl:text>urn:nbn:de:1111-2004033116</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="xmlns">
              <xsl:text>urn:nbn:de:1111-2004033116</xsl:text>
            </xsl:attribute>

            <xsl:element name="administrative_data">
                <xsl:element name="delivery">
                    <update_status type="urn_new"/>
                </xsl:element>
            </xsl:element>
            <xsl:element name="record">
               <!-- IdentifierUrn -->
               <xsl:apply-templates select="IdentifierUrn" mode="epicur" />
               <xsl:element name="resource">
                    <!-- IdentifierUrl -->
                    <xsl:apply-templates select="IdentifierUrl" mode="epicur" />
                    <xsl:element name="format">
                        <xsl:attribute name="scheme"><xsl:text>imt</xsl:text>
                        </xsl:attribute>
                        <xsl:text>text/html</xsl:text>
                    </xsl:element>
               </xsl:element>
            </xsl:element>
        </xsl:element>
    </xsl:template>


    <!--xsl:template match="IdentifierIsbn|IdentifierUrn" mode="epicur"-->
    <xsl:template match="IdentifierUrn" mode="epicur">
        <xsl:element name="identifier">
            <xsl:attribute name="scheme"><xsl:text>urn:nbn:de</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>


    <xsl:template match="IdentifierUrl" mode="epicur">
        <xsl:element name="identifier">
            <xsl:attribute name="scheme"><xsl:text>url</xsl:text></xsl:attribute>
            <xsl:attribute name="type"><xsl:text>frontpage</xsl:text></xsl:attribute>
            <xsl:attribute name="role"><xsl:text>primary</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>



</xsl:stylesheet>
