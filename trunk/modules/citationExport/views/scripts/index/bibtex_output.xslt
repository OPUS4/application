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
 * @package     Module_Frontdoor
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: bibtex_output.xslt 6855 2010-11-05 11:49:06Z gmaiwald $
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_CitationExport
 */
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html"/>

    <!-- output field and value -->
    <xsl:template name="outputFieldValue">
        <xsl:param name="field" required="yes" />
        <xsl:param name="value" required="yes" />
        <xsl:param name="delimiter" required="no" />
        <xsl:if test="string-length($field)>0">
            <xsl:if test="string-length($value)>0">
<xsl:text>  </xsl:text><xsl:value-of select="$field" /><xsl:text> = "</xsl:text>
                <xsl:call-template name="replaceSpecialCharacters">
                    <xsl:with-param name="value"><xsl:value-of select="$value" /></xsl:with-param>
                </xsl:call-template><xsl:text>"</xsl:text>
	  <xsl:if test="string-length($delimiter)>0">
		<xsl:value-of select="$delimiter" />
	  </xsl:if>
<xsl:text>
</xsl:text>
            </xsl:if>
        </xsl:if>
    </xsl:template>

    <!-- Replace Special Characters -->
    <xsl:template name ="replaceSpecialCharacters">
        <xsl:param name="value" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($value, 'ä')">
                <xsl:call-template name="replaceSpecialCharacters">
                    <xsl:with-param name="value">
                        <xsl:value-of select="substring-before($value, 'ä')" />
                        <xsl:text>{\"a}</xsl:text>
                        <xsl:value-of select="substring-after($value, 'ä')" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="contains($value, 'ö')">
                <xsl:call-template name="replaceSpecialCharacters">
                    <xsl:with-param name="value">
                        <xsl:value-of select="substring-before($value, 'ö')" />
                        <xsl:text>{\"o}</xsl:text>
                        <xsl:value-of select="substring-after($value, 'ö')" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="contains($value, 'ü')">
                <xsl:call-template name="replaceSpecialCharacters">
                    <xsl:with-param name="value">
                        <xsl:value-of select="substring-before($value, 'ü')" />
                        <xsl:text>{\"u}</xsl:text>
                        <xsl:value-of select="substring-after($value, 'ü')" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="contains($value, 'Ä')">
                <xsl:call-template name="replaceSpecialCharacters">
                    <xsl:with-param name="value">
                        <xsl:value-of select="substring-before($value, 'Ä')" />
                        <xsl:text>{\"A}</xsl:text>
                        <xsl:value-of select="substring-after($value, 'Ä')" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="contains($value, 'Ö')">
                <xsl:call-template name="replaceSpecialCharacters">
                    <xsl:with-param name="value">
                        <xsl:value-of select="substring-before($value, 'Ö')" />
                        <xsl:text>{\"O}</xsl:text>
                        <xsl:value-of select="substring-after($value, 'Ö')" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="contains($value, 'Ü')">
                <xsl:call-template name="replaceSpecialCharacters">
                    <xsl:with-param name="value">
                        <xsl:value-of select="substring-before($value, 'Ü')" />
                        <xsl:text>{\"U}</xsl:text>
                        <xsl:value-of select="substring-after($value, 'Ü')" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$value" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
