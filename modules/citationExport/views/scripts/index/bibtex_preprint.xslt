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
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: bibtex.xslt 5890 2010-09-26 17:13:48Z tklein $
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_Frontdoor
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    exclude-result-prefixes="php">

    <xsl:output method="text" omit-xml-declaration="yes" />

    <xsl:include href="bibtex_output.xslt" />

    <xsl:template match="/">
      <xsl:apply-templates select="Opus/Opus_Model_Filter" />
    </xsl:template>

    <!-- Suppress spilling values with no corresponding templates -->
    <xsl:template match="@*|node()" />

    <xsl:template match="Opus_Model_Filter">

        <!-- Preprocessing: some variables will be defined -->
        <xsl:variable name="year">
            <xsl:choose>
                <xsl:when test="string-length(@PublishedYear)>0">
                    <xsl:value-of select="@PublishedYear" />
                </xsl:when>
                <xsl:when test="string-length(@CompletedYear)>0">
                    <xsl:value-of select="@CompletedYear" />
                </xsl:when>
           </xsl:choose>
       </xsl:variable>

        <xsl:variable name="author">
            <xsl:apply-templates select="PersonAuthor">
                 <xsl:with-param name="type">author</xsl:with-param>
            </xsl:apply-templates>
        </xsl:variable>

        <xsl:variable name="identifier">
            <xsl:apply-templates select="PersonAuthor">
                 <xsl:with-param name="type">identifier</xsl:with-param>
            </xsl:apply-templates>
            <xsl:value-of select="$year" />
         </xsl:variable>

        <xsl:variable name="editor">
            <xsl:apply-templates select="PersonEditor" />
        </xsl:variable>

        <xsl:variable name="pages">
            <xsl:call-template name="Pages">
                <xsl:with-param name="number"><xsl:value-of select="@PageNumber" /></xsl:with-param>
            </xsl:call-template>
        </xsl:variable>

        <!-- II) Output: print Opus-Document in bibtex -->
        <xsl:text>@unpublished{</xsl:text><xsl:value-of select="$identifier" />,
<xsl:text></xsl:text>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">author </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="$author" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">title  </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="TitleMain/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:if test="Collection/@RoleName='series' = 'true'">
            <xsl:call-template name="outputFieldValue">
                <xsl:with-param name="field">series   </xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select ="Collection[@RoleName='series']/@Name" /></xsl:with-param>
                <xsl:with-param name="delimiter">,</xsl:with-param>
            </xsl:call-template>
            <xsl:call-template name="outputFieldValue">
                <xsl:with-param name="field">number  </xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select ="@Issue" /></xsl:with-param>
                <xsl:with-param name="delimiter">,</xsl:with-param>
            </xsl:call-template>
            <xsl:call-template name="outputFieldValue">
                <xsl:with-param name="field">institution</xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select ="Enrichment[@KeyName='institution']/@Value" /></xsl:with-param>
                <xsl:with-param name="delimiter">,</xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">pages  </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="$pages" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">pages      </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="$pages" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">year   </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="$year" /></xsl:with-param>
        </xsl:call-template>
<xsl:text>}</xsl:text>
     </xsl:template>

    <!-- bibtex-style for authors  -->
    <xsl:template match="PersonAuthor">
      <xsl:param name="type" required="yes" />
      <xsl:choose>
          <xsl:when test="$type='author'">
            <xsl:value-of select="concat(@FirstName, ' ', @LastName)" />
            <xsl:choose>
                <xsl:when test="position()=last()">
                    <xsl:text></xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text> and </xsl:text>
                </xsl:otherwise>
            </xsl:choose>
           </xsl:when>
          <xsl:when test="$type='identifier'">
	<xsl:choose>	
	<xsl:when test="position()=1 or position()=2 or position()=3">
		<xsl:value-of select="@LastName" />
	</xsl:when>	
	<xsl:when test="position()=4">
		<xsl:text>etal</xsl:text>
	</xsl:when>
	<xsl:otherwise>
               <xsl:text></xsl:text>
          </xsl:otherwise>	
	</xsl:choose>
           </xsl:when>
      </xsl:choose>
    </xsl:template>

    <!-- bibtex-style for editors  -->
    <xsl:template match="PersonEditor">
      <xsl:value-of select="concat(@FirstName, ' ', @LastName)" />
      <xsl:choose>
         <xsl:when test="position()=last()">
            <xsl:text></xsl:text>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text> and </xsl:text>
         </xsl:otherwise>
      </xsl:choose>
    </xsl:template>

    <!-- bibtex-style for pages  -->
    <xsl:template name="Pages">
      <xsl:param name="number" required="yes" />
      <xsl:choose>
         <xsl:when test="string-length($number) > 0">
            <xsl:value-of select="$number" />
         </xsl:when>
      </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
