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
 */
-->
<xsl:stylesheet version="1.0"
    xmlns="urn:nbn:de:1111-2004033116"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" />


    <xsl:template match="Opus_Document" mode="epicur">
        <epicur
            xsi:schemaLocation="urn:nbn:de:1111-2004033116 http://www.persistent-identifier.de/xepicur/version1.0/xepicur.xsd"
            xmlns="urn:nbn:de:1111-2004033116"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

            <administrative_data>
                <delivery>
                    <update_status type="urn_new"/>
                </delivery>
            </administrative_data>

            <record>
               <!-- Identifier URN -->
               <xsl:apply-templates select="Identifier[@Type = 'urn']" mode="epicur" />

               <resource>
                    <identifier scheme="url" type="frontpage" role="primary" origin="original">
                        <xsl:value-of select="@frontdoorurl"/>
                    </identifier>

                    <format scheme="imt">
                        <xsl:text>text/html</xsl:text>
                    </format>
               </resource>

               <xsl:apply-templates select="File" mode="epicur"/>

            </record>

        </epicur>
    </xsl:template>


    <xsl:template match="Identifier[@Type = 'urn']" mode="epicur">
        <identifier scheme="urn:nbn:de">
            <xsl:value-of select="@Value" />
        </identifier>
    </xsl:template>

    <!-- skip container file -->
    <xsl:template match="File[@DnbContainer='1']" mode="epicur" />

    <xsl:template match="File" mode="epicur">
        <resource>
            <identifier scheme="url" target="transfer" origin="original">
                <xsl:value-of select="@url"/>
            </identifier>
            <format scheme="imt">
                <xsl:value-of select="@MimeType"/>
            </format>
        </resource>
    </xsl:template>

</xsl:stylesheet>
