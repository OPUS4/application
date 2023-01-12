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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:php="http://php.net/xsl"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    exclude-result-prefixes="php xsl xsi">

    <xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

    <xsl:param name="fullUrl" />
    <xsl:param name="collName" />
    <xsl:param name="groupBy" />
    <xsl:param name="pluginName" />
    <xsl:param name="urnResolverUrl" />

    <xsl:template match="/">
        <xsl:apply-templates select="Documents"/>
    </xsl:template>

    <xsl:key name="published_year" match="Opus_Document" use="php:functionString('max', PublishedDate/@Year, @PublishedYear)"/>
    <xsl:key name="completed_year" match="Opus_Document" use="php:functionString('max', CompletedDate/@Year, @CompletedYear)"/>

    <xsl:template match="Documents">
        <xsl:call-template name="set_div"/>
    </xsl:template>

    <xsl:template name="set_div">
        <xsl:element name="div">
            <xsl:attribute name="id">opus-publist</xsl:attribute>
            <!-- Navibar Year -->
            <xsl:call-template name="render_header"/>
            <xsl:call-template name="render_navibar"/>
            <xsl:call-template name="render_table"/>
        </xsl:element>
    </xsl:template>

  <!-- Template for Header -->
  <xsl:template name="render_header">
    <div id="opus-header">
      <xsl:element name="h1">
          <xsl:value-of select="$collName" />
      </xsl:element>
    </div>
  </xsl:template>

    <xsl:template name="render_navibar">
        <xsl:choose>
            <xsl:when test="$groupBy = 'publishedYear'">
                <xsl:for-each select="Opus_Document[count(. | key('published_year', php:functionString('max', PublishedDate/@Year, @PublishedYear))[1]) = 1]">
                    <xsl:sort select="php:functionString('max', PublishedDate/@Year, @PublishedYear)" order="descending"/>
                    <xsl:call-template name="render_navibar_link">
                        <xsl:with-param name="year" select="php:functionString('max', PublishedDate/@Year, @PublishedYear)"/>
                    </xsl:call-template>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
               <xsl:for-each select="Opus_Document[count(. | key('completed_year', php:functionString('max', CompletedDate/@Year, @CompletedYear))[1]) = 1]">
                    <xsl:sort select="php:functionString('max', CompletedDate/@Year, @CompletedYear)" order="descending"/>
                    <xsl:call-template name="render_navibar_link">
                        <xsl:with-param name="year" select="php:functionString('max', CompletedDate/@Year, @CompletedYear)"/>
                    </xsl:call-template>
                </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>


    <xsl:template name="render_navibar_link">
        <xsl:param name="year" required="yes" />
            <xsl:element name="span">
                <xsl:text> </xsl:text>
            </xsl:element>
            <xsl:element name="a">
                <xsl:attribute name="href">
                    <xsl:text>#opus-year-</xsl:text>
                    <xsl:value-of select="$year" />
                </xsl:attribute>
                <xsl:value-of select="$year" />
            </xsl:element>
            <xsl:if test="position() != last()">
                <xsl:element name="span">
                    <xsl:text> | </xsl:text>
                </xsl:element>
            </xsl:if>
    </xsl:template>


    <xsl:template name="render_table">
        <xsl:element name="table">
            <xsl:attribute name="cellspacing">0</xsl:attribute>
            <xsl:choose>
                <xsl:when test="$groupBy = 'publishedYear'">
                    <xsl:for-each select="Opus_Document[generate-id()=generate-id(key('published_year',php:functionString('max', PublishedDate/@Year, @PublishedYear))[1])]">
                        <xsl:sort select="php:functionString('max', PublishedDate/@Year, @PublishedYear)" order="descending"/>
                        <xsl:call-template name="render_table_row">
                            <xsl:with-param name="year" select="php:functionString('max', PublishedDate/@Year, @PublishedYear)"/>
                        </xsl:call-template>
                    </xsl:for-each>
               </xsl:when>
               <xsl:otherwise>
                     <xsl:for-each select="Opus_Document[generate-id()=generate-id(key('completed_year',php:functionString('max', CompletedDate/@Year, @CompletedYear))[1])]">
                        <xsl:sort select="php:functionString('max', CompletedDate/@Year, @CompletedYear)" order="descending"/>
                        <xsl:call-template name="render_table_row">
                            <xsl:with-param name="year" select="php:functionString('max', CompletedDate/@Year, @CompletedYear)"/>
                        </xsl:call-template>
                    </xsl:for-each>
               </xsl:otherwise>
            </xsl:choose>
        </xsl:element>
    </xsl:template>

    <xsl:template name="render_table_row">
        <xsl:param name="year" required="yes" />
                <xsl:element name="tr">
                    <xsl:attribute name="class">opus-year</xsl:attribute>
                    <xsl:element name="td">
                    <xsl:attribute name="colspan">4</xsl:attribute>
                        <xsl:element name="h4">
                            <xsl:attribute name="id">
                                <xsl:text>opus-year-</xsl:text>
                                <xsl:value-of select="$year" />
                            </xsl:attribute>
                            <xsl:value-of select="$year" />
                        </xsl:element>
                    </xsl:element>
                </xsl:element>
        <xsl:choose>
            <xsl:when test="$groupBy = 'publishedYear'">
                <xsl:for-each select="key('published_year', $year)">
                    <xsl:sort select="TitleMain/@Value" order="ascending"/>
                    <xsl:apply-templates select="."/>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:for-each select="key('completed_year', $year)">
                    <xsl:sort select="TitleMain/@Value" order="ascending"/>
                    <xsl:apply-templates select="."/>
            </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>


    <xsl:template match="Opus_Document">
        <xsl:element name="tr">
            <xsl:element name="td">
                <xsl:attribute name="class">opus-persons</xsl:attribute>
                <xsl:choose>
                    <xsl:when test="@Type = 'book'">
                        <xsl:apply-templates select="Person" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:apply-templates select="PersonAuthor" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:element>
            <xsl:element name="td">
                <xsl:attribute name="class">opus-title</xsl:attribute>
                <xsl:apply-templates select="TitleMain" />
            </xsl:element>
            <xsl:element name="td">
                <xsl:attribute name="class">opus-metadata</xsl:attribute>
                <xsl:choose>
                    <xsl:when test="@Type = 'article'">
                        <xsl:call-template name="render_article"/>
                    </xsl:when>
                    <xsl:when test="@Type = 'bachelorthesis'">
                        <xsl:call-template name="render_thesis"/>
                    </xsl:when>
                    <xsl:when test="@Type = 'book'">
                        <xsl:call-template name="render_book"/>
                    </xsl:when>
                    <xsl:when test="@Type = 'bookpart'">
                        <xsl:call-template name="render_bookpart"/>
                    </xsl:when>
                    <xsl:when test="@Type = 'conferenceobject'">
                        <xsl:call-template name="render_conferenceobject"/>
                    </xsl:when>
                    <xsl:when test="@Type = 'doctoralthesis'">
                        <xsl:call-template name="render_thesis"/>
                    </xsl:when>
                    <xsl:when test="@Type = 'habilitation'">
                        <xsl:call-template name="render_thesis"/>
                    </xsl:when>
                    <xsl:when test="@Type = 'masterthesis'">
                        <xsl:call-template name="render_thesis"/>
                    </xsl:when>
                    <xsl:when test="@Type = 'preprint'">
                        <xsl:call-template name="render_preprint"/>
                    </xsl:when>
                    <xsl:when test="@Type = 'report'">
                        <xsl:call-template name="render_report"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:call-template name="render_misc"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:element>
            <xsl:element name="td">
                <xsl:attribute name="class">opus-links</xsl:attribute>
                <xsl:call-template name="render_links"/>
            </xsl:element>
        </xsl:element>
    </xsl:template>


    <!-- Templates for Document Type -->


    <xsl:template name="render_article">
        <!-- Journal -->
        <xsl:apply-templates select="TitleParent" />
        <!-- Volume, Issue -->
        <xsl:call-template name="VolumeIssue" />
        <!-- PageFirst, PageLast -->
        <xsl:call-template name="Pages" />
        <!-- CompleteadDate, CompletedYear -->
        <xsl:call-template name="Year" />
    </xsl:template>


    <xsl:template name="render_book">
        <!-- PublisherName, PublisherPlace -->
        <xsl:call-template name="PublisherNamePlace" />
        <!-- Edition -->
        <xsl:apply-templates select="@Edition" />
        <!-- Isbn -->
        <xsl:apply-templates select="Identifier[@Type = 'isbn']" />
        <!-- CompleteadDate, CompletedYear -->
        <xsl:call-template name="Year" />
    </xsl:template>


    <xsl:template name="render_bookpart">
        <!-- BookTitle -->
        <xsl:apply-templates select="TitleParent" />
        <!-- Editor -->
        <xsl:apply-templates select="PersonEditor" />
        <!-- PublisherName, PublisherPlace -->
        <xsl:call-template name="PublisherNamePlace" />
        <!-- Edition -->
        <xsl:apply-templates select="@Edition" />
        <!-- PageFirst, PageLast -->
        <xsl:call-template name="Pages" />
        <!-- CompleteadDate, CompletedYear -->
        <xsl:call-template name="Year" />
    </xsl:template>


    <xsl:template name="render_conferenceobject">
        <!-- BookTitle -->
        <xsl:apply-templates select="TitleParent" />
        <!-- Editor -->
        <xsl:apply-templates select="PersonEditor" />
        <!-- PageFirst, PageLast -->
        <xsl:call-template name="Pages" />
        <!-- CompleteadDate, CompletedYear -->
        <xsl:call-template name="Year" />
    </xsl:template>


    <xsl:template name="render_thesis">
        <!-- Type: Bachelorthesis, Masterhesis, Doctoral thesis, Habilitation thesis -->
        <xsl:call-template name="ThesisType" />
        <!-- School -->
        <xsl:apply-templates select="ThesisGrantor" />
        <!-- CompleteadDate, CompletedYear -->
        <xsl:call-template name="Year" />
    </xsl:template>


    <xsl:template name="render_report">
        <!-- CreatingCorporation -->
        <xsl:apply-templates select="@CreatingCorporation" />
        <!-- CompleteadDate, CompletedYear -->
        <xsl:call-template name="Year" />
    </xsl:template>


    <xsl:template name="render_preprint">
        <!-- Preprint -->
        <xsl:call-template name="Preprint" />
        <!-- CompleteadDate, CompletedYear -->
        <xsl:call-template name="Year" />
    </xsl:template>

    <xsl:template name="render_misc">
        <xsl:apply-templates select="TitleParent" />
        <xsl:call-template name="VolumeIssue" />
        <xsl:call-template name="Pages" />
        <xsl:apply-templates select="PersonEditor" />
        <xsl:call-template name="PublisherNamePlace" />
        <xsl:apply-templates select="@Edition" />
        <xsl:call-template name="Year" />
    </xsl:template>


    <!-- Matched Templates for Metadata -->

    <xsl:template match="@Edition|@CreatingCorporation">
        <xsl:value-of select="." />
        <xsl:text>, </xsl:text>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'isbn']">
        <xsl:value-of select="@Value" />
        <xsl:text>, </xsl:text>
    </xsl:template>

    <xsl:template match="Person[@Role='author' or @Role='editor']">
        <xsl:value-of select="concat(normalize-space(@FirstName), ' ',normalize-space(@LastName))" />
        <xsl:if test="@Role='editor'">
            <xsl:text>(Ed.)</xsl:text>
        </xsl:if>
        <xsl:if test="position() != last()">,
            <xsl:text>, </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template match="PersonAuthor">
        <xsl:value-of select="concat(normalize-space(@FirstName), ' ',normalize-space(@LastName))" />
        <xsl:if test="position() != last()">
            <xsl:text>, </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template match="PersonEditor">
        <xsl:value-of select="concat(normalize-space(@FirstName), ' ',normalize-space(@LastName))" />
        <xsl:choose>
            <xsl:when test="position() != last()">, </xsl:when>
            <xsl:otherwise>
                <xsl:if test="count(../PersonEditor) > 1"> (Eds.)</xsl:if>
                <xsl:if test="count(../PersonEditor) = 1"> (Ed.)</xsl:if>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="ThesisGrantor">
        <xsl:value-of select="@Name" />
        <xsl:text>, </xsl:text>
    </xsl:template>


    <xsl:template match="TitleMain[position() =1]">
        <xsl:element name="a">
            <xsl:attribute name="href">
                <xsl:value-of select="$fullUrl"/>
                <xsl:text>/frontdoor/index/index/docId/</xsl:text>
                <xsl:value-of select="../@Id" />
            </xsl:attribute>
            <xsl:element name="b">
                <xsl:value-of select="@Value" />
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleParent[position() =1]">
        <xsl:element name="i">
            <xsl:value-of select="@Value" />
        </xsl:element>
        <xsl:text>, </xsl:text>
    </xsl:template>


    <!-- Named Templates for Metadata -->

    <xsl:template name="Pages">
        <xsl:if test="@PageFirst != ''">
            <xsl:choose>
                <xsl:when test="@PageLast !=''">
                    <xsl:text>pp. </xsl:text>
                    <xsl:value-of select="@PageFirst" />
                    <xsl:text>-</xsl:text>
                    <xsl:value-of select="@PageLast" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>p. </xsl:text>
                    <xsl:value-of select="@PageFirst" />
                </xsl:otherwise>
            </xsl:choose>
            <xsl:text>, </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template name="Preprint">
        <xsl:text>preprint, </xsl:text>
    </xsl:template>

    <xsl:template name="PublisherNamePlace">
        <xsl:if test="@PublisherName != '' or @PublisherPlace !=''">
            <xsl:choose>
                <xsl:when test="@PublisherName != '' and @PublisherPlace !=''">
                    <xsl:value-of select="@PublisherName" />
                    <xsl:text>: </xsl:text>
                    <xsl:value-of select="@PublisherPlace" />
                </xsl:when>
                <xsl:when test="@PublisherName != ''">
                    <xsl:value-of select="@PublisherName" />
                </xsl:when>
                <xsl:when test="@PublisherPlace != ''">
                    <xsl:value-of select="@PublisherPlace" />
                </xsl:when>
            </xsl:choose>
            <xsl:text>, </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template name="ThesisType">
        <xsl:choose>
            <xsl:when test="@Type = 'bachelorthesis'">
                <xsl:text>Bachelor thesis</xsl:text>
            </xsl:when>
            <xsl:when test="@Type = 'doctoralthesis'">
                <xsl:text>Doctoral thesis</xsl:text>
            </xsl:when>
            <xsl:when test="@Type = 'habilitation'">
                <xsl:text>Habilitation thesis</xsl:text>
            </xsl:when>
            <xsl:when test="@Type = 'masterthesis'">
                <xsl:text>Masters thesis</xsl:text>
            </xsl:when>
        </xsl:choose>
        <xsl:text>, </xsl:text>
    </xsl:template>

    <xsl:template name="VolumeIssue">
        <xsl:if test="@Volume != ''">
            <xsl:choose>
                <xsl:when test="@Issue !=''">
                    <xsl:value-of select="@Volume" />
                    <xsl:text>(</xsl:text>
                    <xsl:value-of select="@Issue" />
                    <xsl:text>)</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>Vol.</xsl:text>
                    <xsl:value-of select="@Volume" />
                </xsl:otherwise>
            </xsl:choose>
            <xsl:text>, </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template name="Year">
        <xsl:choose>
            <xsl:when test="$groupBy = 'publishedYear'">
        <xsl:value-of select="php:functionString('max', PublishedDate/@Year, @PublishedYear)" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="php:functionString('max', CompletedDate/@Year, @CompletedYear)" />
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>


    <!-- Templates for Links -->

    <xsl:template name="render_links">
        <!-- Files -->
        <xsl:apply-templates select="File[@VisibleInFrontdoor = '1']" >
            <xsl:sort select="@Label"/>
        </xsl:apply-templates>
        <br/>
        <!-- RIS, BibTeX -->
        <xsl:call-template name="CitationExport"/>
        <!-- Identifier: Doi, Arxiv, Urn, Pubmed ... -->
        <xsl:apply-templates select="Identifier"/>
    </xsl:template>

    <xsl:template match="File">
        <xsl:variable name="MimeTypeDisplayName" select="php:function('Export_Model_PublistExport::getMimeTypeDisplayName', $pluginName, string(@MimeType))" />
        <xsl:choose>
            <xsl:when test="$MimeTypeDisplayName != ''">
                <xsl:element name="a">
                    <xsl:attribute name="href">
                        <xsl:value-of select="$fullUrl"/>
                        <xsl:text>/files/</xsl:text>
                        <xsl:value-of select="../@Id" />
                        <xsl:text>/</xsl:text>
                        <xsl:value-of select="php:function('urlencode',string(@PathName))"/>
                    </xsl:attribute>
                    <xsl:element name="b">
                        <xsl:value-of select="$MimeTypeDisplayName"/>
                    </xsl:element>
                    <xsl:text> </xsl:text>
                </xsl:element>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="CitationExport">
        <xsl:element name="a">
            <xsl:attribute name="href">
                <xsl:value-of select="$fullUrl"/>
                <xsl:text>/citationExport/index/download/output/bibtex/docId/</xsl:text>
                <xsl:value-of select="@Id" />
            </xsl:attribute>
            <xsl:text>BibTeX</xsl:text>
        </xsl:element>
        <xsl:text> | </xsl:text>
        <xsl:element name="a">
            <xsl:attribute name="href">
                <xsl:value-of select="$fullUrl"/>
                <xsl:text>/citationExport/index/download/output/ris/docId/</xsl:text>
                <xsl:value-of select="@Id" />
            </xsl:attribute>
            <xsl:text>RIS</xsl:text>
        </xsl:element>
        <br/>
    </xsl:template>


    <xsl:template match="Identifier[@Type='arxiv' or @Type='doi' or @Type='pubmed' or @Type='urn']">
        <xsl:if test="@Type='arxiv'">
            <xsl:element name="a">
                <xsl:attribute name="href">
                    <xsl:text>http://arxiv.org/abs/</xsl:text>
                    <xsl:value-of select="@Value" />
                </xsl:attribute>
                <xsl:text>ARXIV</xsl:text>
            </xsl:element>
            <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:if test="@Type='doi'">
            <xsl:element name="a">
                <xsl:attribute name="href">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'doi.resolverUrl')"/>
                    <xsl:value-of select="@Value" />
                </xsl:attribute>
                <xsl:text>DOI</xsl:text>
            </xsl:element>
            <xsl:text> </xsl:text>
        </xsl:if>
       <xsl:if test="@Type='pubmed'">
            <xsl:element name="a">
                <xsl:attribute name="href">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'baseUrl', 'pubmed')"/>
                    <xsl:value-of select="@Value" />
                </xsl:attribute>
                <xsl:text>PMID</xsl:text>
            </xsl:element>
            <xsl:text> </xsl:text>
        </xsl:if>
       <xsl:if test="@Type='urn'">
            <xsl:element name="a">
                <xsl:attribute name="href">
                    <xsl:value-of select="$urnResolverUrl" />
                    <xsl:value-of select="@Value" />
                </xsl:attribute>
                <xsl:text>URN</xsl:text>
            </xsl:element>
            <xsl:text> </xsl:text>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>
