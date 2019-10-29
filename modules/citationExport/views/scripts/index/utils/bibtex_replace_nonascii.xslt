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
 * @package     Module_CitationExport
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2010-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    exclude-result-prefixes="php">

    <xsl:output method="text" omit-xml-declaration="yes" />

    <!-- output field and value -->
    <xsl:template name="outputFieldValue">
        <xsl:param name="field" required="yes" />
        <xsl:param name="value" required="yes" />
        <xsl:param name="delimiter" required="no" />
        <xsl:if test="string-length($field) > 0">
            <xsl:if test="string-length($value) > 0">
<xsl:text>  </xsl:text><xsl:value-of select="$field" /><xsl:text> = {</xsl:text>
                <xsl:call-template name="replace_strings">
                    <xsl:with-param name="input_text"><xsl:value-of select="normalize-space($value)" /></xsl:with-param>
                </xsl:call-template><xsl:text>}</xsl:text>
	  <xsl:if test="string-length($delimiter) > 0">
		<xsl:value-of select="$delimiter" />
	  </xsl:if>
<xsl:text>
</xsl:text>
            </xsl:if>
        </xsl:if>
    </xsl:template>

    <!-- Replace Special Characters -->
    <xsl:template name="replace_strings">
      <xsl:param name="input_text" />
      <xsl:param name="search" select="document('bibtex_special_characters.xml')/string_replacement/search" />
      <xsl:variable name="replaced_text">
        <xsl:call-template name="replace_substring">
          <xsl:with-param name="text" select="$input_text" />
          <xsl:with-param name="from" select="$search[1]/find" />
          <xsl:with-param name="to" select="$search[1]/replace" />
        </xsl:call-template>
      </xsl:variable>

      <xsl:choose>
        <xsl:when test="$search[2]">
          <xsl:call-template name="replace_strings">
            <xsl:with-param name="input_text" select="$replaced_text" />
            <xsl:with-param name="search" select="$search[position() > 1]" />
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$replaced_text" />
        </xsl:otherwise>
      </xsl:choose>
    </xsl:template>

    <xsl:template name="replace_substring">
        <xsl:param name="text" />
        <xsl:param name="from" />
        <xsl:param name="to" />
        <xsl:choose>
            <xsl:when test="contains($text, $from)">
            	<xsl:value-of select="substring-before($text, $from)" />
                <xsl:value-of select="$to" />
                <xsl:call-template name="replace_substring">
                    <xsl:with-param name="text">
                        <xsl:value-of select="substring-after($text, $from)" />
                    </xsl:with-param>
                    <xsl:with-param name="from">
                        <xsl:value-of select="$from" />
                    </xsl:with-param>
                    <xsl:with-param name="to">
                        <xsl:value-of select="$to" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$text" />
            </xsl:otherwise>
       </xsl:choose>
    </xsl:template>

</xsl:stylesheet>


