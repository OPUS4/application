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
 * @category    Application
 * @package     Module_CitationExport
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2010-2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
	xmlns:ext="http://exslt.org/common"
    exclude-result-prefixes="php ext">

    <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>
    
    <xsl:include href="utils/csv_utils.xslt"/>        
    
	<xsl:template match="*" />

	<xsl:template match="/">
    
    <!-- Translations for column header -->
    <xsl:variable name="label_personAuthor" select="php:functionString('Application_Xslt::translate', 'PersonAuthor')"></xsl:variable>
    <xsl:variable name="label_titleParent" select="php:functionString('Application_Xslt::translate', 'TitleParent')"></xsl:variable>
    <xsl:variable name="label_seriesTitle" select="php:functionString('Application_Xslt::translate', 'Series')"></xsl:variable>
    <xsl:variable name="label_titleAdditional" select="php:functionString('Application_Xslt::translate', 'TitleAdditional')"></xsl:variable>
    <xsl:variable name="label_publishedDate" select="php:functionString('Application_Xslt::translate', 'PublishedDate')"></xsl:variable>
    <xsl:variable name="label_completedYear" select="php:functionString('Application_Xslt::translate', 'CompletedYear')"></xsl:variable>
    <xsl:variable name="label_volume" select="php:functionString('Application_Xslt::translate', 'Volume')"></xsl:variable>
    
    <!-- Count necessary columns for Enrichments and Collections -->
    <xsl:variable name="collections_in_config" select="php:functionString('Application_Xslt::optionValue', 'export.csv.collections')"></xsl:variable>
	<xsl:variable name="enrichments_in_config" select="php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments')"></xsl:variable>
    
	<xsl:variable name="anzahl_collections">
	<xsl:choose>
		<xsl:when test="string-length($collections_in_config) > 0">
			<xsl:value-of select="string-length(php:functionString('Application_Xslt::optionValue', 'export.csv.collections'))-string-length(translate(php:functionString('Application_Xslt::optionValue', 'export.csv.collections'),',','')) + 1"/>
		</xsl:when>
		<xsl:otherwise>
			0
		</xsl:otherwise>
	</xsl:choose>
	</xsl:variable>

	<xsl:variable name="anzahl_enrichments">
	<xsl:choose>
		<xsl:when test="string-length($enrichments_in_config)>0">
			<xsl:value-of select="string-length(php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments'))-string-length(translate(php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments'),',','')) + 1"/>
		</xsl:when>
		<xsl:otherwise>
			0
		</xsl:otherwise>
	</xsl:choose>
	</xsl:variable>

    <xsl:text>OPUS4-ID	Dokumenttyp	Status	Sprache	</xsl:text>
    <xsl:value-of select="$label_personAuthor"/>
    <xsl:text>	Herausgeber	Haupttitel	Untertitel	</xsl:text>
    <xsl:value-of select="$label_titleAdditional"/>
    <xsl:text>	Abstract	Auflage	Verlagsort	Verlag	</xsl:text>
    <xsl:value-of select="$label_completedYear"/>
    <xsl:text>	</xsl:text>
    <xsl:value-of select="$label_publishedDate"/>
    <xsl:text>	Seitenzahl	</xsl:text>
    <xsl:value-of select="$label_titleParent"/>
    <xsl:text>	</xsl:text>
    <xsl:value-of select="$label_volume"/>
    <xsl:text>	Erste Seite	Letzte Seite	Aufsatznummer	</xsl:text>
    <xsl:value-of select="$label_seriesTitle"/>
    <xsl:text>	Nummer	ISBN	ISSN	Urheb. K&#246;rperschaft	Beteiligte K&#246;rperschaft	Konferenzname	Konferenzort	Ausgabe / Heft	URN	DOI	Schlagw&#246;rter	Fakult&#228;t/Institut/Abteilung	Lizenz	Bemerkung	</xsl:text>
	<xsl:if test="php:function('Application_Xslt::accessAllowed', 'documents') != 0">
			<xsl:text>Interne Bemerkung	</xsl:text>
	</xsl:if>
    <xsl:if test="$anzahl_collections > 0">
		<xsl:call-template name="column_collection">
			<xsl:with-param name="i" select="1"/>
			<xsl:with-param name="anzahl" select="$anzahl_collections"/>
		</xsl:call-template>
	</xsl:if>    
	<xsl:if test="$anzahl_enrichments > 0">
		<xsl:call-template name="column_enrichment">
			<xsl:with-param name="i" select="1"/>
			<xsl:with-param name="anzahl" select="$anzahl_enrichments"/>
		</xsl:call-template>
	</xsl:if>
	<xsl:text>
</xsl:text>					
	  
    <xsl:apply-templates select="Documents" />
	</xsl:template>
	
	<xsl:template match="Documents">
		<xsl:apply-templates select="Opus_Document" />
	</xsl:template>

	<xsl:template match="Opus_Document">
        <xsl:if test="@ServerState = 'published'"> <!-- Only published documents are processed -->

            <!--  Preprocessing: some variables must be defined -->            
            <xsl:variable name="identifier">
                <xsl:value-of select="@Id" />
            </xsl:variable> 

            <xsl:variable name="status">
                <xsl:value-of select="@ServerState" />
            </xsl:variable> 

            <xsl:variable name="doctype">         
                <xsl:value-of select="php:functionString('Application_Xslt::translate', @Type)" />        
            </xsl:variable>

            <xsl:variable name="author">
                <xsl:apply-templates select="PersonAuthor" mode="csv"/>
            </xsl:variable>

            <xsl:variable name="editor">
                <xsl:apply-templates select="PersonEditor" mode="csv"/>
            </xsl:variable>
            
            <xsl:variable name="titleMain">
                <xsl:value-of select ="translate(TitleMain/@Value,'&#13;&#10;',' ')" />
            </xsl:variable>
            
            <xsl:variable name="abstract">
                <xsl:value-of select ="translate(TitleAbstract/@Value, '&#13;&#10;', ' ')" />
            </xsl:variable>
            
            <xsl:variable name="edition">
                <xsl:value-of select ="@Edition" />
            </xsl:variable>

            <xsl:variable name="contributingCorporation">
                <xsl:value-of select ="@ContributingCorporation" />
            </xsl:variable>

            <xsl:variable name="creatingCorporation">
                <xsl:value-of select ="@CreatingCorporation" />
            </xsl:variable>
            
            <xsl:variable name="publisher_place">
                <xsl:value-of select ="@PublisherPlace" />
            </xsl:variable>
            
            <xsl:variable name="publisher_name">
                <xsl:value-of select ="@PublisherName" />
            </xsl:variable>
            
            <xsl:variable name="completed_year">
                <xsl:choose>
                <xsl:when test="@CompletedYear">
                        <xsl:value-of select="@CompletedYear" />
                    </xsl:when>
                    <xsl:when test="CompletedDate/@Year">
                        <xsl:value-of select="CompletedDate/@Year" />
                    </xsl:when>  
                    <xsl:when test="@PublishedYear">
                        <xsl:value-of select="@PublishedYear" />
                    </xsl:when>
                    <xsl:when test="PublishedDate/@Year">
                        <xsl:value-of select="PublishedDate/@Year" />
                    </xsl:when>                
                </xsl:choose>
            </xsl:variable>

            <xsl:variable name="published_date">
                    <xsl:choose>             
                        <xsl:when test="string-length(normalize-space(PublishedDate/@Year)) > 0">
                            <xsl:value-of select="PublishedDate/@Day" /><xsl:text>.</xsl:text><xsl:value-of select="PublishedDate/@Month" /><xsl:text>.</xsl:text><xsl:value-of select="PublishedDate/@Year" />
                        </xsl:when>
                        <xsl:when test="normalize-space(@PublishedYear) != '0000'">
                            <xsl:value-of select="@PublishedYear" />
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
        
            <xsl:variable name="pageFirst">
                <xsl:value-of select ="@PageFirst" />
            </xsl:variable>

            <xsl:variable name="pageLast">
                <xsl:value-of select ="@PageLast" />
            </xsl:variable>

            <xsl:variable name="articleNumber">
                <xsl:value-of select ="@ArticleNumber" />
            </xsl:variable>
        
            <xsl:variable name="titleSub">
                <xsl:value-of select ="translate(TitleSub/@Value,'&#13;&#10;',' ')" />
            </xsl:variable>

            <xsl:variable name="titleAdditional">
                <xsl:value-of select ="translate(TitleAdditional/@Value,'&#13;&#10;',' ')" />
            </xsl:variable>
            
            <xsl:variable name="titleParent">
                <xsl:value-of select ="translate(TitleParent/@Value,'&#13;&#10;',' ')" />
            </xsl:variable>
            
            <xsl:variable name="volume">
                <xsl:value-of select ="@Volume" />
            </xsl:variable>

            <xsl:variable name="seriesTitle">
                <xsl:value-of select ="Series/@Title" />
            </xsl:variable>

            <xsl:variable name="seriesNumber">
                <xsl:value-of select ="Series/@Number" />
            </xsl:variable>

            <xsl:variable name="isbn">
                <xsl:value-of select ="Identifier[@Type = 'isbn']/@Value" />
            </xsl:variable>

            <xsl:variable name="issn">
                <xsl:value-of select ="Identifier[@Type = 'issn']/@Value" />
            </xsl:variable>

            <xsl:variable name="doi">
                <xsl:value-of select ="Identifier[@Type = 'doi']/@Value" />
            </xsl:variable>

            <xsl:variable name="conferenceName">
                <xsl:value-of select ="translate(Enrichment[@KeyName='ConferenceName']/@Value,'&#13;&#10;',' ')" />
            </xsl:variable>

            <xsl:variable name="conferencePlace">
                <xsl:value-of select ="Enrichment[@KeyName='ConferencePlace']/@Value" />
            </xsl:variable>
                                    
            <xsl:variable name="issue">
                <xsl:value-of select ="concat(' ',@Issue)" />
            </xsl:variable>

            <xsl:variable name="language">
                <xsl:value-of select ="@Language" />
            </xsl:variable>

            <xsl:variable name="urn">
                <xsl:value-of select ="Identifier[@Type = 'urn']/@Value" />
            </xsl:variable>
            
            <xsl:variable name="institution">
                <xsl:call-template name="institutes" />
            </xsl:variable>

            <xsl:variable name="licence">
                <xsl:value-of select ="Licence/@Name" />
            </xsl:variable>
            
            <xsl:variable name="keywords">
                <xsl:for-each select="Subject[@Type='uncontrolled']">                   
                    <xsl:value-of select="@Value" />                
                        <xsl:if test="not(position()=last())">
                            <xsl:text>; </xsl:text>
                        </xsl:if>
                </xsl:for-each>
            </xsl:variable> 

            <!-- Column "Bemerkung (public)" -->
            <xsl:variable name="note_public">
		        <xsl:for-each select="Note[@Visibility='public']" >			        
                        <xsl:if test="position()=1">
                            <xsl:value-of select ="translate(@Message, '&#13;&#10;', ' ')" />                        
                        </xsl:if>
                        <xsl:if test="position()>1">
                            <xsl:text> / </xsl:text>
                            <xsl:value-of select ="translate(@Message, '&#13;&#10;', ' ')" />
                        </xsl:if>			        
                </xsl:for-each>
            </xsl:variable>

            <!-- Column "Bemerkung (private)" -->
            <xsl:variable name="note_private">
            <xsl:if test="php:function('Application_Xslt::accessAllowed', 'documents') != 0">
		        <xsl:for-each select="Note[@Visibility='private']" >			        
				        <xsl:if test="position()=1">
                            <xsl:value-of select ="translate(@Message, '&#13;&#10;', ' ')" />                        
                        </xsl:if>
                        <xsl:if test="position()>1">
                            <xsl:text> / </xsl:text>
                            <xsl:value-of select ="translate(@Message, '&#13;&#10;', ' ')" />
                        </xsl:if>			        
                </xsl:for-each>
            </xsl:if>
            </xsl:variable>		
            
            <!-- Output -->

            <xsl:value-of select="$identifier" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$doctype" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$status" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$language" /><xsl:text>	</xsl:text>  
            <xsl:value-of select="$author" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$editor" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$titleMain" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$titleSub" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$titleAdditional" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$abstract" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$edition" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$publisher_place" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$publisher_name" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$completed_year" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$published_date" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$pages" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$titleParent" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$volume" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$pageFirst" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$pageLast" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$articleNumber" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$seriesTitle" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$seriesNumber" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$isbn" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$issn" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$creatingCorporation" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$contributingCorporation" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$conferenceName" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$conferencePlace" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$issue" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$urn" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$doi" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$keywords" /><xsl:text>	</xsl:text>        
            <xsl:value-of select="$institution" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$licence" /><xsl:text>	</xsl:text>
            <xsl:value-of select="$note_public" /><xsl:text>	</xsl:text>
            <xsl:if test="php:function('Application_Xslt::accessAllowed', 'documents') != 0">
                <xsl:value-of select="$note_private" /><xsl:text>	</xsl:text>
            </xsl:if>

            <xsl:call-template name="collections" />
            <xsl:call-template name="enrichments" />
            <!--in /utils/csv_utils.xslt -->
                
                <xsl:text>         
</xsl:text>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>
