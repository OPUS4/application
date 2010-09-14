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
 * @version     $Id$
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

    <xsl:template match="/">
      <xsl:apply-templates select="Opus/Opus_Model_Filter" />
    </xsl:template>

    <!-- Suppress spilling values with no corresponding templates -->
    <xsl:template match="@*|node()" />

    <xsl:template match="Opus_Model_Filter">

        <!-- I) Preprocessing: some variables are defines -->
        <xsl:variable name="type">
            <xsl:call-template name="mapPublicationType">
                    <xsl:with-param name="type"><xsl:value-of select ="@Type" /></xsl:with-param>
            </xsl:call-template>
        </xsl:variable>

        <xsl:variable name="year">
            <xsl:choose>
                <xsl:when test="string-length(ComletedDate/@Year)>0">
                    <xsl:value-of select="ComletedDate/@Year" />
                </xsl:when>
                <xsl:when test="normalize-space(PublishedDate/@Year)">
                    <xsl:value-of select="PublishedDate/@Year" />
                </xsl:when>
                <xsl:when test="string-length(@CompletedYear)>0">
                    <xsl:value-of select="@CompletedYear" />
                </xsl:when>
                <xsl:when test="string-length(@PublishedYear)>0">
                    <xsl:value-of select="@PublishedYear" />
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

        <xsl:variable name="institution">
            <xsl:apply-templates select="Collection[@RoleName='Organisatorische Einheiten']" />
        </xsl:variable>

        <xsl:variable name="editor">
            <xsl:apply-templates select="PersonEditor" />
        </xsl:variable>


        <!-- II) Output: print Opus-Document in bibtex -->
        <xsl:value-of select="$type" /><xsl:text>{</xsl:text><xsl:value-of select="$identifier" />
<xsl:text>
</xsl:text>

        <!-- Author, Title, Year for all publication types -->
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">author</xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="$author" /></xsl:with-param>
        </xsl:call-template>

        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">title</xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="TitleMain/@Value" /></xsl:with-param>
        </xsl:call-template>

        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">year</xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="$year" /></xsl:with-param>
        </xsl:call-template>

        <xsl:choose>
            <!-- Inbook -->
            <xsl:when test="$type='@inbook'">
                <xsl:call-template name="outputFieldValue">
                    <xsl:with-param name="field">booktitle</xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select ="TitleParent/@Value" /></xsl:with-param>
                </xsl:call-template>
                <xsl:call-template name="outputFieldValue">
                    <xsl:with-param name="field">editor</xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select ="$editor" /></xsl:with-param>
                </xsl:call-template>
                <xsl:call-template name="outputFieldValue">
                    <xsl:with-param name="field">publisher</xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select ="PublisherName" /></xsl:with-param>
                </xsl:call-template>
                <xsl:call-template name="outputFieldValue">
                    <xsl:with-param name="field">address</xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select ="PublisherPlace" /></xsl:with-param>
                </xsl:call-template>
            </xsl:when>

            <!-- Inproceedings -->
            <xsl:when test="$type='@inproceedings'">
                <xsl:call-template name="outputFieldValue">
                    <xsl:with-param name="field">booktitle</xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select ="TitleParent/@Value" /></xsl:with-param>
                </xsl:call-template>
            </xsl:when>

            <!-- PhdThesis -->
            <xsl:when test="$type='@phdthesis'">
                <xsl:call-template name="outputFieldValue">
                    <xsl:with-param name="field">school</xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select ="PublisherUniversity" /> </xsl:with-param>
                </xsl:call-template>
            </xsl:when>

            <!-- Techreport -->
            <xsl:when test="$type='@techreport'">
                <xsl:call-template name="outputFieldValue">
                    <xsl:with-param name="field">institution</xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select="$institution" /></xsl:with-param>
                </xsl:call-template>
            </xsl:when>

            <!-- Misc -->
            <xsl:when test="$type='@misc'">
                <xsl:call-template name="outputFieldValue">
                    <xsl:with-param name="field">publisher</xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select ="PublisherName" /></xsl:with-param>
                </xsl:call-template>
                <xsl:call-template name="outputFieldValue">
                    <xsl:with-param name="field">address</xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select ="PublisherPlace" /></xsl:with-param>
                </xsl:call-template>
            </xsl:when>
        </xsl:choose>
<xsl:text>}
</xsl:text>
     </xsl:template>

     <!-- mapping publicationtypes from opus to bibtex -->
     <xsl:template name="mapPublicationType">
        <xsl:param name="type" required="yes" />
        <xsl:choose>
            <xsl:when test="$type='article'">
                <xsl:text>@article</xsl:text>
            </xsl:when>
            <xsl:when test="$type='conference'">
                <xsl:text>@proceedings</xsl:text>
            </xsl:when>
            <xsl:when test="$type='conference_item'">
                <xsl:text>@inproceedings</xsl:text>
            </xsl:when>
            <xsl:when test="$type='doctoral_thesis'">
                <xsl:text>@phdthesis</xsl:text>
            </xsl:when>
            <xsl:when test="$type='festschrift'">
                <xsl:text>@misc</xsl:text>
            </xsl:when>
            <xsl:when test="$type='monograph_section'">
                <xsl:text>@inbook</xsl:text>
            </xsl:when>
            <xsl:when test="$type='paper'">
                <xsl:text>@techreport</xsl:text>
            </xsl:when>
            <xsl:when test="$type='preprint'">
                <xsl:text>@techreport</xsl:text>
            </xsl:when>
            <xsl:when test="$type='report'">
                <xsl:text>@techreport</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>@misc</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
     </xsl:template>

    <!-- bibtex-style for authors  -->
    <xsl:template match="PersonAuthor">
      <xsl:param name="type" required="yes" />
      <xsl:choose>
          <xsl:when test="$type='author'">
            <xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
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
            <xsl:value-of select="@LastName" />
           </xsl:when>
      </xsl:choose>
    </xsl:template>

    <!-- bibtex-style for editors  -->
    <xsl:template match="PersonEditor">
      <xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
      <xsl:choose>
         <xsl:when test="position()=last()">
            <xsl:text></xsl:text>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text> and </xsl:text>
         </xsl:otherwise>
      </xsl:choose>
    </xsl:template>

    <!-- institutions -->
    <xsl:template match="Collection[@RoleName='Organisatorische Einheiten']">
        <xsl:value-of select="@Name" />
        <xsl:choose>
            <xsl:when test="position()=last()">
               <xsl:text></xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>, </xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- output field and value -->
    <xsl:template name="outputFieldValue">
        <xsl:param name="field" required="yes" />
        <xsl:param name="value" required="yes" />
        <xsl:if test="string-length($field)>0">
            <xsl:if test="string-length($value)>0">
<xsl:text>  </xsl:text><xsl:value-of select="$field" /><xsl:text> = "</xsl:text><xsl:value-of select="$value" /><xsl:text>",
</xsl:text>
            </xsl:if>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>
