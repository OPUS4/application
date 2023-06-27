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
 * @copyright   Copyright (c) 2010-2017, OPUS 4 development team
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
		
	<!-- Calculate amount of columns for Enrichments and Collections -->
	<xsl:variable name="collections_in_config" select="php:functionString('Application_Xslt::optionValue', 'export.csv.collections')"></xsl:variable>
	<xsl:variable name="enrichments_in_config" select="php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments')"></xsl:variable>
    
	<xsl:variable name="anzahl_collections">
	<xsl:choose>
		<xsl:when test="string-length($collections_in_config)>0">
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
	
	<xsl:text>OPUS-ID	Kategorie	Interne weitere Kategorie	Publikation	Bemerkung	</xsl:text>
	<xsl:if test="php:function('Application_Xslt::accessAllowed', 'documents') != 0">
		<xsl:text>Interne Bemerkung	</xsl:text>	
	</xsl:if>
	<xsl:text>Dokumenttyp	Fakult&#228;t/Institut/Abteilung	</xsl:text>
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

        <!--  Preprocessing: some variables must be defined -->
        
        <xsl:variable name="identifier">
            <xsl:value-of select="@Id" />
         </xsl:variable>        

        <xsl:variable name="pubtype">
			<xsl:value-of select="php:functionString('Application_Xslt::translate', @Type)" />        	
		</xsl:variable>
		
		<!-- ### bsz/aw, 16.2.23: Variable for Hrsg. -->
		<xsl:variable name="editor">
			<xsl:if test="PersonEditor">
				<xsl:apply-templates select="PersonEditor" mode="csv_fn"/>
			</xsl:if>
		</xsl:variable>
        		
		<xsl:variable name="author">
			<xsl:choose>
				<xsl:when test="PersonAuthor">
					<xsl:apply-templates select="PersonAuthor" mode="csv_fn"/>
				</xsl:when>
				<xsl:when test="string-length($editor) > 0">
					<xsl:value-of select="$editor"/>
				</xsl:when>
				<xsl:when test="PersonContributor">
					<xsl:apply-templates select="PersonContributor" />
				</xsl:when>
			</xsl:choose>	
        </xsl:variable>
		
		<xsl:variable name="betreuer">
			<xsl:choose>
				<xsl:when test="Person/@Role='advisor'">
					<xsl:apply-templates select="PersonAdvisor" />
				</xsl:when>
			</xsl:choose>	
        </xsl:variable>
		
		<xsl:variable name="gutachter">
			<xsl:choose>
				<xsl:when test="Person/@Role='referee'">
					<xsl:apply-templates select="PersonReferee" />
				</xsl:when>
			</xsl:choose>	
        </xsl:variable>
                
		<xsl:variable name="titleMain">			
			<xsl:value-of select ="translate(TitleMain/@Value, '&#13;&#10;', ' ')" />
		</xsl:variable>

		<xsl:variable name="titleSub">
			<xsl:value-of select ="translate(TitleSub/@Value, '&#13;&#10;', ' ')" />
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
   
        <xsl:variable name="titleParent">
            <xsl:value-of select ="normalize-space(TitleParent/@Value)" />
        </xsl:variable>
        
        <xsl:variable name="volume">
            <xsl:value-of select ="@Volume" />
        </xsl:variable>

        <xsl:variable name="isbn">
            <xsl:value-of select ="Identifier[@Type='isbn']/@Value" />
        </xsl:variable>

        <xsl:variable name="doi">
            <xsl:value-of select ="Identifier[@Type='doi']/@Value" />
        </xsl:variable>
				        
        <xsl:variable name="issue">
            <xsl:value-of select ="@Issue" />
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
		
		<xsl:variable name="url">
            <xsl:value-of select ="Identifier[@Type='url']/@Value" />
        </xsl:variable>
		
		<xsl:variable name="issn">
			<xsl:value-of select ="Identifier[@Type='issn']/@Value" />
        </xsl:variable>
		
		<xsl:variable name="patentnummer">
            <xsl:value-of select ="Enrichment[@KeyName='Number']/@Value" />
        </xsl:variable>
		
		<xsl:variable name="application">
            <xsl:value-of select ="Enrichment[@KeyName='Application']/@Value" />
        </xsl:variable>
		
		<xsl:variable name="patenterteilung">
            <xsl:value-of select ="Enrichment[@KeyName='DateGranted']/@Value" />
        </xsl:variable>
		
		<xsl:variable name="patentamt">
            <xsl:value-of select ="Enrichment[@KeyName='Agency']/@Value" />
        </xsl:variable>
		
		<xsl:variable name="patentinhaber">
            <xsl:value-of select ="Enrichment[@KeyName='PersonInventor']/@Value" />
        </xsl:variable>
		
		<xsl:variable name="datumAbschluss">
			<xsl:value-of select ="ThesisDateAccepted/@Day" />
			<xsl:text>.</xsl:text>
            <xsl:value-of select ="ThesisDateAccepted/@Month" />
			<xsl:text>.</xsl:text>
			<xsl:value-of select ="ThesisDateAccepted/@Year" />
        </xsl:variable>
		
		<xsl:variable name="thesisGrantor">
            <xsl:value-of select ="ThesisGrantor/@Name" />
        </xsl:variable>
		
		<xsl:variable name="category">
            <xsl:value-of select ="Collection[@RoleName='relevance']/@Name" />
        </xsl:variable> 
        
        <!-- Output: Each line is one record -->

		<!-- Column "ID" -->
		<xsl:value-of select ="$identifier" /><xsl:text>	</xsl:text>

		<!-- Column "Kategorie" - Template in /utils/csv_utils.xslt -->
		<xsl:call-template name="maincategory" />

		<!-- Column "Interne weitere Kategorie" -->        
		<xsl:value-of select ="$category" /><xsl:text>	</xsl:text>

		<!-- Column "Publikation" -->		
		<xsl:value-of select="$author" />
		
		<xsl:text> (</xsl:text><xsl:value-of select="$completed_year" /><xsl:text>): </xsl:text>
		<xsl:value-of select ="$titleMain" /><xsl:text>.</xsl:text>
		<xsl:if test="string-length($titleSub) > 0">
			<xsl:text> </xsl:text><xsl:value-of select ="$titleSub" /><xsl:text>.</xsl:text>
		</xsl:if>
		
		<!-- IN: (only for article & bookpart) -->
		<xsl:choose>
		  <xsl:when test="string-length(TitleParent/@Value) > 0">
			<xsl:text> In: </xsl:text>
			<!-- ### bsz/aw, 16.2.23: Angabe der Hrsg. von uebergeordneten Werken -->
			<xsl:if test="PersonAuthor">
				<xsl:if test="string-length($editor) > 0">
					<xsl:value-of select="$editor"/>
					<xsl:text>: </xsl:text>
				</xsl:if>
			</xsl:if>
            <xsl:value-of select="$titleParent" />
			<!-- <xsl:text>,</xsl:text>-->
		 </xsl:when>
		 <xsl:otherwise></xsl:otherwise>
		</xsl:choose>
		
		<!-- @volume/@issue (Jahrgang, Band) -->
		<xsl:if test="string-length($volume) > 0">
			<xsl:text>, </xsl:text>
			<xsl:value-of select="$volume" />
		</xsl:if>
		<xsl:if test="string-length($issue) > 0">
			<xsl:text> (</xsl:text>
			<xsl:value-of select="$issue" />
			<xsl:text>)</xsl:text>
		</xsl:if>
		<xsl:if test="string-length($articleNumber) > 0">
			<xsl:text>, </xsl:text>
			<xsl:value-of select="$articleNumber" />
			<!-- <xsl:text>)</xsl:text> -->
		</xsl:if>
				
		<!-- PublisherPlace: Publisher -->
		<xsl:if test="string-length($publisher_place) > 0">
			<xsl:text>, </xsl:text>
			<xsl:value-of select="$publisher_place" />
			<xsl:text>:</xsl:text>
		</xsl:if>
		<xsl:if test="string-length($publisher_name) > 0">
			<xsl:text> </xsl:text>
			<xsl:value-of select="$publisher_name" />
			<!-- <xsl:text>,</xsl:text>-->
		</xsl:if>
		
		<!-- ISBN, DOI, ISSN -->
		<xsl:if test="string-length($isbn) > 0">
			<xsl:text>, ISBN </xsl:text>
			<xsl:value-of select="$isbn" />
		</xsl:if>
		<xsl:if test="string-length($doi) > 0">
			<xsl:text>, DOI https://doi.org/</xsl:text>
			<xsl:value-of select="$doi" />
		</xsl:if>
		<xsl:if test="string-length($issn) > 0">
			<xsl:text>, ISSN </xsl:text>
			<xsl:value-of select="$issn" />
		</xsl:if>
		
		<!-- Seitenzahl oder pp. von-bis -->
		<xsl:choose>
               <xsl:when test="(string-length(@PageFirst) > 0) and (string-length(@PageLast) > 0)" >
                   <xsl:text>, pp. </xsl:text>
					<xsl:value-of select="$pageFirst" />
					<xsl:text>-</xsl:text>
					<xsl:value-of select="$pageLast" />
               </xsl:when>
			   <xsl:when test="string-length(@PageNumber) > 0">
					<xsl:text>, </xsl:text>
					<xsl:choose>
						<xsl:when test="contains(@PageNumber, 'S.')" >
							<xsl:value-of select="substring(@PageNumber, 0, string-length(@PageNumber) - 2)" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="@PageNumber" />
						</xsl:otherwise>
					</xsl:choose>
					<xsl:text> Seiten</xsl:text>
               </xsl:when>
               <xsl:otherwise>
                   <xsl:text></xsl:text>
               </xsl:otherwise>
           </xsl:choose>
		
		<xsl:choose>
               <xsl:when test="Collection[@RoleName='relevance']/@Name = 'Patent'">
					<xsl:if test="string-length($patentnummer) > 0">
						<xsl:text>, </xsl:text>
						<xsl:value-of select="$patentnummer" />
					</xsl:if>
					<xsl:if test="string-length($application) > 0">
						<xsl:text>, </xsl:text>
						<xsl:value-of select="$application" />
					</xsl:if>
					<xsl:if test="string-length($patenterteilung) > 0">
						<xsl:text>, </xsl:text>
						<xsl:value-of select="$patenterteilung" />
					</xsl:if>
					<xsl:if test="string-length($patentamt) > 0">
						<xsl:text>, </xsl:text>
						<xsl:value-of select="$patentamt" />
					</xsl:if>
					<xsl:if test="string-length($patentinhaber) > 0">
						<xsl:text>, Patentinhaber: </xsl:text>
						<xsl:value-of select="$patentinhaber" />
					</xsl:if>
               </xsl:when>	   
        </xsl:choose>
		
		<xsl:choose>
               <xsl:when test="Collection[@RoleName='relevance']/@Name = 'Dissertation'">
					<xsl:if test="string-length($datumAbschluss) > 0">
						<xsl:text>, </xsl:text>
						<xsl:value-of select="$datumAbschluss" />
					</xsl:if>
					<xsl:if test="string-length($betreuer) > 0">
						<xsl:text>, Betreuer: </xsl:text>
						<xsl:value-of select="$betreuer" />
					</xsl:if>
					<xsl:if test="string-length($gutachter) > 0">
						<xsl:text>, Gutachter: </xsl:text>
						<xsl:value-of select="$gutachter" />
					</xsl:if>
               </xsl:when>	   
        </xsl:choose>
		
		<!-- Titel verleihende Institution -->
		
		<xsl:if test="string-length($thesisGrantor) > 0">
			<xsl:text>, </xsl:text>
			<xsl:value-of select="$thesisGrantor" />
		</xsl:if>
				
		<!-- URL -->
		<xsl:if test="string-length($url) > 0">
			<xsl:text>, abrufbar unter: </xsl:text>
			<xsl:value-of select="$url" />
		</xsl:if>		
		<xsl:text>	</xsl:text>

		<!-- Column "Bemerkung" -->
		<xsl:for-each select="Note[@Visibility='public']" >			        
            <xsl:if test="position()=1">
                <xsl:value-of select ="translate(@Message, '&#13;&#10;', ' ')" />                        
            </xsl:if>
            <xsl:if test="position()>1">
                <xsl:text> / </xsl:text>
                <xsl:value-of select ="translate(@Message, '&#13;&#10;', ' ')" />
            </xsl:if>			        
        </xsl:for-each>
		<xsl:text>	</xsl:text>

		<!-- Column "Interne Bemerkung" -->
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
			<xsl:text>	</xsl:text>
        </xsl:if>

		<!-- Column "Dokumenttyp" -->
		<xsl:value-of select="$pubtype" /><xsl:text>	</xsl:text>

		<!-- Column "Fakultät" -->
		<xsl:call-template name="institutes" /><xsl:text>	</xsl:text>		

		<!-- Columns "Collections" and "Enrichments" -->
		<xsl:call-template name="collections" />
		<xsl:call-template name="enrichments" />
		<!--in /utils/csv_utils.xslt -->
		
		<xsl:text>         
</xsl:text>
</xsl:template>
	 


</xsl:stylesheet>
