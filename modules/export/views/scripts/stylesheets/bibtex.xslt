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
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    exclude-result-prefixes="php">

    <xsl:output method="text"  omit-xml-declaration="yes"/>

    <xsl:include href="utils/bibtex_replace_nonascii.xslt"/>
    <xsl:include href="utils/bibtex_authors.xslt"/>
    <xsl:include href="utils/bibtex_editors.xslt"/>
	<xsl:include href="utils/bibtex_institutions.xslt"/>
    <xsl:include href="utils/bibtex_pages.xslt"/>

    <xsl:template match="*" />



	<xsl:template match="/">
	  <xsl:apply-templates select="Documents" />
    </xsl:template>

    <xsl:template match="Documents">
          <xsl:apply-templates select="Opus_Document" />
    </xsl:template>

    <xsl:template match="Opus_Document">

        <!--  Preprocessing: some variables must be defined -->
        <xsl:variable name="doctype">
            <xsl:value-of select="@Type" />
        </xsl:variable>

        <xsl:variable name="year">
            <xsl:choose>
            	<!-- <xsl:when test="normalize-space(@PublishedYear) != '0000'">
                    <xsl:value-of select="@PublishedYear" />
                </xsl:when>
                <xsl:when test="string-length(normalize-space(CompletedDate/@Year)) > 0">
                    <xsl:value-of select="CompletedDate/@Year" />
                </xsl:when>



                <xsl:when test="string-length(normalize-space(PublishedDate/@Year)) > 0">
                    <xsl:value-of select="PublishedDate/@Year" />

                </xsl:when> -->
                <xsl:when test="normalize-space(@CompletedYear) != '0000'">
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
            <xsl:choose>
                <xsl:when test="string-length(normalize-space($author)) > 0">
                    <xsl:apply-templates select="PersonAuthor">
                         <xsl:with-param name="type">identifier</xsl:with-param>
                    </xsl:apply-templates>
                    <xsl:value-of select="$year" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>OPUS4-</xsl:text>
                    <xsl:value-of select="@Id" />
                </xsl:otherwise>
            </xsl:choose>
         </xsl:variable>

        <xsl:variable name="editor">
            <xsl:apply-templates select="PersonEditor" />
        </xsl:variable>

		<xsl:variable name="institution">
            <xsl:apply-templates select="Collection[@RoleName='institutes']" />
        </xsl:variable>

        <xsl:variable name="pages">
            <xsl:call-template name="Pages">
                <xsl:with-param name="first"><xsl:value-of select="@PageFirst" /></xsl:with-param>
                <xsl:with-param name="last"><xsl:value-of select="@PageLast" /></xsl:with-param>
                <xsl:with-param name="number"><xsl:value-of select="@PageNumber" /></xsl:with-param>
            </xsl:call-template>
        </xsl:variable>

        <xsl:variable name="pubtype">
        	<xsl:choose>
        		<xsl:when test="@Type='workingpaper'">techreport</xsl:when>
				<xsl:when test="@Type='report'">techreport</xsl:when>
				<xsl:when test="@Type='preprint'">unpublished</xsl:when>
				<xsl:when test="@Type='periodicalpart'">misc</xsl:when>
				<xsl:when test="@Type='periodical'">periodical</xsl:when>
				<xsl:when test="@Type='lecture'">misc</xsl:when>
				<xsl:when test="@Type='doctoralthesis'">phdthesis</xsl:when>
				<xsl:when test="@Type='habilitation'">phdthesis</xsl:when>
				<xsl:when test="@Type='contributiontoperiodical'">article</xsl:when>
				<xsl:when test="@Type='conferenceobject'">inproceedings</xsl:when>
				<xsl:when test="@Type='bookpart'">incollection</xsl:when>
				<xsl:when test="@Type='book'">book</xsl:when>
				<xsl:when test="@Type='bachelorthesis'">masterthesis</xsl:when>
				<xsl:when test="@Type='article'">article</xsl:when>
				<xsl:otherwise>misc</xsl:otherwise>
			</xsl:choose>
   	    </xsl:variable>
	    <xsl:variable name="lang">
        	<xsl:choose>
        		<xsl:when test="@Language='deu'">de</xsl:when>
				<xsl:when test="@Language='eng'">en</xsl:when>
				<xsl:when test="@Language='spa'">es</xsl:when>
				<xsl:when test="@Language='fra'">fr</xsl:when>
				<xsl:when test="@Language='zho'">zh</xsl:when>
				<xsl:when test="@Language='ara'">ar</xsl:when>
				<xsl:when test="@Language='ita'">it</xsl:when>
				<xsl:when test="@Language='nld'">nl</xsl:when>
				<xsl:when test="@Language='pol'">pl</xsl:when>
				<xsl:when test="@Language='rus'">ru</xsl:when>
				<xsl:when test="@Language='dan'">da</xsl:when>
				<xsl:when test="@Language='est'">et</xsl:when>
				<xsl:when test="@Language='fin'">fi</xsl:when>
				<xsl:when test="@Language='jpn'">ja</xsl:when>
				<xsl:when test="@Language='kor'">ko</xsl:when>
				<xsl:when test="@Language='por'">pt</xsl:when>
				<xsl:when test="@Language='tur'">tr</xsl:when>
				<xsl:otherwise>mul</xsl:otherwise>
			</xsl:choose>
   	    </xsl:variable>



		<xsl:variable name="subject">
		  <xsl:apply-templates select="Subject[@Type='swd']" />
		</xsl:variable>





        <!-- Output: print Opus-Document in bibtex -->


        <xsl:text>@</xsl:text><xsl:value-of select="$pubtype"/><xsl:text>{</xsl:text><xsl:value-of select="$identifier" />
<xsl:text>,
</xsl:text>

         <xsl:choose>
        		<xsl:when test="@Type='workingpaper'"><xsl:text>  type      = {Working Paper},&#10;</xsl:text></xsl:when>
				<xsl:when test="@Type='masterthesis'"><xsl:text>  type      = {Master Thesis},&#10;</xsl:text></xsl:when>
				<xsl:when test="@Type='bachelorthesis'"><xsl:text>  type      = {Bachelor Thesis},&#10;</xsl:text></xsl:when>
         </xsl:choose>
         <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">author   </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="$author" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">title    </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="TitleMain/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">series</xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="TitleParent/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">volume   </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="@Volume" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>


       <xsl:choose>
                <xsl:when test="@Type='conferenceobject'">
                    <xsl:call-template name="outputFieldValue">
                     <xsl:with-param name="field">booktitle</xsl:with-param>
                     <xsl:with-param name="value"><xsl:value-of select ="TitleParent/@Value" /></xsl:with-param>
                     <xsl:with-param name="delimiter">,</xsl:with-param>
		            </xsl:call-template>
                </xsl:when>
				<xsl:when test="@Type='bookpart'">
                    <xsl:call-template name="outputFieldValue">
                     <xsl:with-param name="field">booktitle</xsl:with-param>
                     <xsl:with-param name="value"><xsl:value-of select ="TitleParent/@Value" /></xsl:with-param>
                     <xsl:with-param name="delimiter">,</xsl:with-param>
		            </xsl:call-template>
                </xsl:when>

                <xsl:otherwise>
                    <xsl:call-template name="outputFieldValue">
                     <xsl:with-param name="field">journal  </xsl:with-param>
                    <xsl:with-param name="value"><xsl:value-of select ="TitleParent/@Value" /></xsl:with-param>
                     <xsl:with-param name="delimiter">,</xsl:with-param>
		            </xsl:call-template>
                </xsl:otherwise>
        </xsl:choose>



		<xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">volume   </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Enrichment[@KeyName='VolumeSource']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">number   </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="@Issue" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">editor   </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="$editor" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">edition  </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="@Edition" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">publisher</xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="@PublisherName" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">address  </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="@PublisherPlace" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">organization   </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="@CreatingCorporation" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">isbn     </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Identifier[@Type = 'isbn']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">issn     </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Identifier[@Type = 'issn']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">doi      </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Identifier[@Type = 'doi']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:if test="string-length(normalize-space(Identifier[@Type = 'urn']/@Value)) > 0">
          <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">url      </xsl:with-param>
            <xsl:with-param name="value">http://nbn-resolving.de/<xsl:value-of select ="Identifier[@Type = 'urn']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
          </xsl:call-template>
		</xsl:if>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">institute      </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="@Institutes" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <!--<xsl:call-template name="outputFieldValue">
                <xsl:with-param name="field">institution</xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select ="$institution" /></xsl:with-param>
                <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>-->
            <xsl:call-template name="outputFieldValue">
                <xsl:with-param name="field">series   </xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select ="Collection[@RoleName='series']/@Name" /></xsl:with-param>
                <xsl:with-param name="delimiter">,</xsl:with-param>
            </xsl:call-template>
            <xsl:call-template name="outputFieldValue">
                <xsl:with-param name="field">number   </xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select ="Identifier[@Type = 'serial']/@Value" /></xsl:with-param>
                <xsl:with-param name="delimiter">,</xsl:with-param>
            </xsl:call-template>

		<xsl:if test="@Type='bachelorthesis' or @Type='doctoralthesis' or @Type='habilitation' or @Type='masterthesis' ">
          <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">school     </xsl:with-param>
            <xsl:with-param name="value">
                <xsl:value-of select="ThesisPublisher/@Name" />
                <xsl:if test="ThesisPublisher/@Department">, <xsl:value-of select="ThesisPublisher/@Department" /></xsl:if>
            </xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
		</xsl:if>

			<xsl:if test="@Type='book' or @Type='bookpart' ">
          <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">publisher     </xsl:with-param>
            <xsl:with-param name="value">
                <xsl:value-of select="ThesisPublisher/@Name" />
            </xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
		</xsl:if>

        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">address  </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Enrichment[@KeyName='address']/@Value" /></xsl:with-param>
	    <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">month    </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Enrichment[@KeyName='month']/@Value" /></xsl:with-param>
	    <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">contributor</xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Enrichment[@KeyName='contributor']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
		 <!--
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">type     </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="@Type" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
		-->
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">howpublished</xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Enrichment[@KeyName='howpublished']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">source   </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Enrichment[@KeyName='source']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">pages    </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="$pages" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">year     </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="$year" /></xsl:with-param>
			<xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>
		<xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">abstract </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="TitleAbstract/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>

		<xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">subject     </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Subject[@Type='swd']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter">,</xsl:with-param>
        </xsl:call-template>


		<xsl:text>  language  = {</xsl:text><xsl:value-of select="$lang"/><xsl:text>}&#10;</xsl:text>
		<!--
		<xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">language </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="@Language" /></xsl:with-param>
        </xsl:call-template>-->

<xsl:text>}
</xsl:text>
     </xsl:template>


</xsl:stylesheet>