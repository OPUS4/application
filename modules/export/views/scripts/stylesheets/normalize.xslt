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
 * @package     Module_Export
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: normalize.xslt 9112 2011-10-13 10:07:40Z gmaiwald $
 */
-->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

	<xsl:output method="xml" indent="yes" encoding="utf-8" />

	<xsl:template match="/">
	    <xsl:apply-templates select="export"/>
	</xsl:template>

	<xsl:template match="export">
	    <xsl:element name="export">
	      <xsl:apply-templates select="Opus_Document"/>	
	    </xsl:element>
	</xsl:template>


	<xsl:template match="Opus_Document">
              <xsl:element name="Opus_Document">
                <xsl:attribute name="Year">
                    <xsl:choose>
                        <xsl:when test="CompletedDate/@Year != ''">
                            <xsl:value-of select="CompletedDate/@Year" />
                        </xsl:when>
                        <xsl:when test="@CompletedYear != '0000'">
                            <xsl:value-of select="@CompletedYear" />
                        </xsl:when>
                        <xsl:when test="PublishedDate/@Year != ''">
                            <xsl:value-of select="PublishedDate/@Year" />
                        </xsl:when>
                        <xsl:when test="@PublishedYear != '0000'">
                            <xsl:value-of select="@PublishedYear" />
                        </xsl:when>
                    </xsl:choose>
                </xsl:attribute>
                <xsl:copy-of select="@*|node()" />
	   </xsl:element>
	</xsl:template>

</xsl:stylesheet> 


