<?xml version="1.0" encoding="ISO-8859-1"?>

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

    <xsl:output method="text" encoding="ISO-8859-1" omit-xml-declaration="yes"/>

    <xsl:include href="utils/csv_replace_nonascii.xslt"/>
    <xsl:include href="utils/csv_authors.xslt"/>
    <xsl:include href="utils/csv_editors.xslt"/>
	<xsl:include href="utils/csv_institutions.xslt"/>
    <xsl:include href="utils/bibtex_pages.xslt"/>

    <xsl:template match="*" />



	<xsl:template match="/">
	  <xsl:text>Dokument-ID	Dokumenttyp	Verfasser/Autoren	Herausgeber	Haupttitel	Abstract	Auflage	Verlagsort	Verlag	Erscheinungsjahr	Seitenzahl	Schriftenreihe Titel	Schriftenreihe Bandzahl	ISBN	Quelle der Hochschulschrift	Konferenzname	Quelle:Titel	Quelle:Jahrgang	Quelle:Heftnummer	Quelle:Erste Seite	Quelle:Letzte Seite	URN	DOI	Abteilungen
</xsl:text>
	  <xsl:apply-templates select="Documents" />
    </xsl:template>

    <xsl:template match="Documents">
          <xsl:apply-templates select="Opus_Document" />
    </xsl:template>

    <xsl:template match="Opus_Document">

        <!--  Preprocessing: some variables must be defined -->

        <xsl:variable name="identifier">
            <xsl:text>OPUS4-</xsl:text>
            <xsl:value-of select="@Id" />
         </xsl:variable>

        <xsl:variable name="doctype">
            <xsl:value-of select="@Type" />
        </xsl:variable>

        <xsl:variable name="pubtype">
        	<xsl:choose>
        		<xsl:when test="@Type='workingpaper'">Arbeitspapier</xsl:when>
				<xsl:when test="@Type='report'">Bericht</xsl:when>
				<xsl:when test="@Type='preprint'">unpublished</xsl:when>
				<xsl:when test="@Type='periodicalpart'">Vollständige Ausgabe (Heft) einer Zeitschriftenreihe</xsl:when>
				<xsl:when test="@Type='periodical'">Periodikum</xsl:when>
				<xsl:when test="@Type='review'">Review</xsl:when>
				<xsl:when test="@Type='masterthesis'">Masterarbeit / Diplomarbeit</xsl:when>
				<xsl:when test="@Type='image'">Bild</xsl:when>
				<xsl:when test="@Type='movingimage'">Bewegte Bilder</xsl:when>
				<xsl:when test="@Type='lecture'">Vorlesung</xsl:when>
				<xsl:when test="@Type='doctoralthesis'">Dissertation</xsl:when>
				<xsl:when test="@Type='habilitation'">Habilitation</xsl:when>
				<xsl:when test="@Type='contributiontoperiodical'">Beitrag zu einem Periodikum</xsl:when>
				<xsl:when test="@Type='conferenceobject'">Konferenzver&#246;ffentlichung</xsl:when>
				<xsl:when test="@Type='bookpart'">Teil eines Buches</xsl:when>
				<xsl:when test="@Type='book'">Buch (Monographie)</xsl:when>
				<xsl:when test="@Type='bachelorthesis'">Bachelorarbeit</xsl:when>
				<xsl:when test="@Type='studythesis'">Studienarbeit</xsl:when>
				<xsl:when test="@Type='article'">Wissenschaftlicher Artikel</xsl:when>
				<xsl:otherwise>misc</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

        <xsl:variable name="author">
            <xsl:apply-templates select="PersonAuthor">
                 <xsl:with-param name="type">author</xsl:with-param>
            </xsl:apply-templates>
        </xsl:variable>

        <xsl:variable name="editor">
            <xsl:apply-templates select="PersonEditor" />
        </xsl:variable>

        <xsl:variable name="title">
	        <xsl:value-of select ="TitleMain/@Value" />
        </xsl:variable>

        <xsl:variable name="abstract">
            <xsl:value-of select ="TitleAbstract/@Value" />
        </xsl:variable>

        <xsl:variable name="edition">
	        <xsl:value-of select ="@Edition" />
        </xsl:variable>

        <xsl:variable name="publisher_place">
	        <xsl:value-of select ="@PublisherPlace" />
        </xsl:variable>

        <xsl:variable name="publisher_name">
	        <xsl:value-of select ="@PublisherName" />
        </xsl:variable>

        <xsl:variable name="completed_year">
            <xsl:choose>
             <xsl:when test="normalize-space(@CompletedYear) != '0000'">
                    <xsl:value-of select="@CompletedYear" />
                </xsl:when>
                <xsl:when test="string-length(normalize-space(CompletedDate/@Year)) > 0">
                    <xsl:value-of select="CompletedDate/@Year" />
                </xsl:when>
				<xsl:when test="normalize-space(@PublishedYear) != '0000'">
                    <xsl:value-of select="@PublishedYear" />
                </xsl:when>
                <xsl:when test="string-length(normalize-space(PublishedDate/@Year)) > 0">
                    <xsl:value-of select="PublishedDate/@Year" />
                </xsl:when>
           </xsl:choose>
       </xsl:variable>




       <xsl:variable name="pages">
           <xsl:choose>
               <xsl:when test="string-length(@PageNumber) > 0">
                   <xsl:value-of select="@PageNumber" />
               </xsl:when>
               <xsl:when test="(string-length(@PageFirst) > 0) and (string-length(@PageLast) > 0)" >
                   <xsl:value-of select="(@PageLast)-(@PageFirst)"/>
               </xsl:when>
               <xsl:otherwise>
                   <xsl:text></xsl:text>
               </xsl:otherwise>
           </xsl:choose>
       </xsl:variable>



        <xsl:variable name="titleParent">
            <xsl:value-of select ="TitleParent/@Value" />
        </xsl:variable>

        <xsl:variable name="volume">
            <xsl:value-of select ="@Volume" />
        </xsl:variable>

        <xsl:variable name="isbn">
            <xsl:value-of select ="Identifier[@Type = 'isbn']/@Value" />
        </xsl:variable>

        <xsl:variable name="doi">
            <xsl:value-of select ="Identifier[@Type = 'doi']/@Value" />
        </xsl:variable>

        <xsl:variable name="thesisSource">
            <xsl:value-of select ="Enrichment[@KeyName='ThesisSource']/@Value" />
        </xsl:variable>

        <xsl:variable name="conferenceName">
            <xsl:value-of select ="Enrichment[@KeyName='ConferenceName']/@Value" />
        </xsl:variable>

        <xsl:variable name="titleAdditional">
            <xsl:value-of select ="TitleAdditional/@Value" />
        </xsl:variable>

        <xsl:variable name="volumeSource">
            <xsl:value-of select ="Enrichment[@KeyName='VolumeSource']/@Value" />
        </xsl:variable>

        <xsl:variable name="issue">
            <xsl:value-of select ="concat(' ',@Issue)" />
        </xsl:variable>

        <xsl:variable name="pageFirst">
            <xsl:value-of select ="@PageFirst" />
        </xsl:variable>

        <xsl:variable name="pageLast">
            <xsl:value-of select ="@PageLast" />
        </xsl:variable>

        <xsl:variable name="urn">
            <xsl:value-of select ="Identifier[@Type = 'urn']/@Value" />
        </xsl:variable>

		<!--
		<xsl:variable name="institution">
            <xsl:apply-templates select="Collection[@RoleName='institutes']" />
        </xsl:variable>
		 -->
         <xsl:variable name="institution">
            <xsl:value-of select ="Collection[@RoleName='institutes']/@Name" />
        </xsl:variable>

        <!-- Ausgabe: Jede Zeile ist ein Record. -->

        <xsl:value-of select="$identifier" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$pubtype" /><xsl:text>	</xsl:text>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">author   </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="$author" /></xsl:with-param>
            <xsl:with-param name="delimiter"></xsl:with-param>
        </xsl:call-template><xsl:text>	</xsl:text>
        <xsl:value-of select="$editor" /><xsl:text>	</xsl:text>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">title    </xsl:with-param>

            <xsl:with-param name="value"><xsl:value-of select ="TitleMain/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter"></xsl:with-param>
        </xsl:call-template><xsl:text>	</xsl:text>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">abstract    </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="TitleAbstract/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter"></xsl:with-param>
        </xsl:call-template><xsl:text>	</xsl:text>
        <xsl:value-of select="$edition" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$publisher_place" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$publisher_name" /><xsl:text>	</xsl:text>
	     <xsl:value-of select="$completed_year" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$pages" /><xsl:text>	</xsl:text>
		<xsl:value-of select="$titleParent" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$volume" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$isbn" /><xsl:text>	</xsl:text>
		<xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">thesisSource    </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Enrichment[@KeyName='ThesisSource']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter"></xsl:with-param>
        </xsl:call-template><xsl:text>	</xsl:text>
        <xsl:value-of select="$conferenceName" /><xsl:text>	</xsl:text>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">titleAdditional    </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="TitleAdditional/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter"></xsl:with-param>
        </xsl:call-template><xsl:text>	</xsl:text>
        <xsl:call-template name="outputFieldValue">
            <xsl:with-param name="field">volumeSource    </xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select ="Enrichment[@KeyName='VolumeSource']/@Value" /></xsl:with-param>
            <xsl:with-param name="delimiter"></xsl:with-param>
        </xsl:call-template><xsl:text>	</xsl:text>
		<xsl:value-of select="$issue" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$pageFirst" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$pageLast" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$urn" /><xsl:text>	</xsl:text>
        <xsl:value-of select="$doi" /><xsl:text>	</xsl:text>
        <xsl:call-template name="outputFieldValue">
                <xsl:with-param name="field">institution</xsl:with-param>
                <xsl:with-param name="value"><xsl:value-of select ="$institution" /></xsl:with-param>
                <xsl:with-param name="delimiter"></xsl:with-param>
        </xsl:call-template><xsl:text>
</xsl:text>
     </xsl:template>
</xsl:stylesheet>
