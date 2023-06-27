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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: bibtex_authors.xslt 8422 2011-05-27 16:53:31Z sszott $
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    xmlns:ext="http://exslt.org/common"
    exclude-result-prefixes="php ext">

    <xsl:output method="text" omit-xml-declaration="yes" /> 

    <xsl:variable name="admin" select="php:function('Application_Xslt::accessAllowed', 'documents')"/>   

    <!-- Map collections as $maincategory for csv_fn -->
    <xsl:template name="maincategory">
        <xsl:variable name="collectionnumber" select="Collection[@RoleName='relevance']/@Number"/>
		<xsl:variable name="maincategory">
			<xsl:choose>
                <xsl:when test="starts-with($collectionnumber,'1')">wiss. Artikel, peer reviewed</xsl:when>
                <xsl:when test="starts-with($collectionnumber,'2')">wissenschaftl. Veröffentlichung</xsl:when>
                <xsl:when test="starts-with($collectionnumber,'3')">Abgeschlossene Promotion</xsl:when>
                <xsl:when test="starts-with($collectionnumber,'4')">offengelegte Patentanmeldung</xsl:when>
                <!-- <xsl:when test="starts-with($collectionnumber,'5')">Sonstige Publikation</xsl:when> -->
				<xsl:otherwise>Nicht zugeordnet</xsl:otherwise>
           </xsl:choose>
        </xsl:variable>
        <xsl:value-of select ="$maincategory" /><xsl:text>	</xsl:text>
    </xsl:template>

    <!-- csv-style for persons  -->

    <xsl:template match="PersonAuthor" mode="csv">          
            <!--- Asterisk as marker for members of institution / xxx/bf 04.08.2022 -->
            <xsl:if test="@IdentifierMisc">
              <xsl:text>*</xsl:text>
            </xsl:if>
			      <xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
            <xsl:choose>
                <xsl:when test="position() = last()">
                    <xsl:text></xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>; </xsl:text>
                </xsl:otherwise>
            </xsl:choose>
    </xsl:template>

    <xsl:template match="PersonAuthor" mode="csv_fn">          
            <!--- Asterisk as marker für members of institution / xxx/bf 04.08.2022 -->
            <xsl:if test="@IdentifierMisc">
              <xsl:text>*</xsl:text>
            </xsl:if>
			      <xsl:value-of select="concat(@LastName, ', ', substring(@FirstName,1,1))" />
            <xsl:text>.</xsl:text>
            <xsl:choose>
                <xsl:when test="position() = last()">
                    <xsl:text></xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>; </xsl:text>
                </xsl:otherwise>
            </xsl:choose>
    </xsl:template>

    <xsl:template match="PersonEditor" mode="csv">      
			<!--- Asterisk as marker für members of institution / xxx/bf 04.08.2022 -->
            <xsl:if test="@IdentifierMisc">
              <xsl:text>*</xsl:text>
            </xsl:if>
			      <xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
            <xsl:choose>
                <xsl:when test="position() = last()">
                    <xsl:text></xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>; </xsl:text>
                </xsl:otherwise>
            </xsl:choose>
    </xsl:template>

    <xsl:template match="PersonEditor" mode="csv_fn">
      <xsl:variable name="initiale">
				<xsl:value-of select="substring(@FirstName,1,1)" />
				<xsl:text>.</xsl:text>
			</xsl:variable>
			<xsl:variable name="und">
				<xsl:choose>
					<xsl:when test="position() = last() and position() != 1">
						<xsl:text> und </xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text></xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:value-of select="$und"/>
            <!--- Asterisk as marker für members of institution / xxx/bf 04.08.2022 -->
			<xsl:if test="@IdentifierMisc">
              <xsl:text>*</xsl:text>
            </xsl:if>
            <xsl:value-of select="concat(@LastName, ', ', $initiale)" />
            <xsl:choose>
                <xsl:when test="position() = last() or position() = last()-1">
                    <xsl:text></xsl:text>
					<xsl:choose>
						<xsl:when test="position() = last()">
							<xsl:text> (Hrsg.)</xsl:text>
						</xsl:when>
					</xsl:choose>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>, </xsl:text>
                </xsl:otherwise>
            </xsl:choose>
    </xsl:template>
	
	<xsl:template match="PersonContributor">
      <xsl:variable name="initiale">
				<xsl:value-of select="substring(@FirstName,1,1)" />
				<xsl:text>. </xsl:text>
			</xsl:variable>
			<xsl:variable name="und">
				<xsl:choose>
					<xsl:when test="position() = last() and position() != 1">
						<xsl:text> und </xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text></xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
            <xsl:value-of select="concat($und, @LastName, ', ', $initiale, ' (Beteiligte Person)')" />
            <xsl:choose>
                <xsl:when test="position() = last() or position() = last()-1">
                    <xsl:text></xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>, </xsl:text>
                </xsl:otherwise>
            </xsl:choose>
    </xsl:template>
	
	<xsl:template match="PersonAdvisor">
      <xsl:variable name="initiale">
				<xsl:value-of select="substring(@FirstName,1,1)" />
				<xsl:text>.</xsl:text>
			</xsl:variable>
			<xsl:variable name="und">
				<xsl:choose>
					<xsl:when test="position() = last() and position() != 1">
						<xsl:text> und </xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text></xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
            <xsl:value-of select="concat($und, @LastName, ', ', $initiale)" />
            <xsl:choose>
                <xsl:when test="position() = last() or position() = last()-1">
                    <xsl:text></xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>, </xsl:text>
                </xsl:otherwise>
            </xsl:choose>
    </xsl:template>
	
	<xsl:template match="PersonReferee">
      <xsl:variable name="initiale">
				<xsl:value-of select="substring(@FirstName,1,1)" />
				<xsl:text>.</xsl:text>
			</xsl:variable>
			<xsl:variable name="und">
				<xsl:choose>
					<xsl:when test="position() = last() and position() != 1">
						<xsl:text> und </xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text></xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
            <xsl:value-of select="concat($und, @LastName, ', ', $initiale)" />
            <xsl:choose>
                <xsl:when test="position() = last() or position() = last()-1">
                    <xsl:text></xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>, </xsl:text>
                </xsl:otherwise>
            </xsl:choose>
    </xsl:template>
    
    <!-- institutes -->
    <xsl:template name="institutes">
        <xsl:for-each select="Collection[@RoleName='institutes']/@Name" >
            <xsl:choose>
                <xsl:when test="position()=1">
                    <xsl:value-of select="." />                        
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>; </xsl:text>
                    <xsl:value-of select="." />
                </xsl:otherwise>
            </xsl:choose>	
        </xsl:for-each>
    </xsl:template>
    
    <!-- Tokenize a comma separated string / ###BSZ/BF 22.03.2022 -->
    <xsl:template name="tokenize">
        <xsl:param name="text"/>
        <xsl:param name="separator" select="','"/>
        
        <xsl:choose>
            <xsl:when test="not(contains($text, $separator))">
                <item>
                    <xsl:value-of select="normalize-space($text)"/>
                </item>
            </xsl:when>
            <xsl:otherwise>
                <item>
                    <xsl:value-of select="normalize-space(substring-before($text, $separator))"/>
                </item>
                <xsl:call-template name="tokenize">
                    <xsl:with-param name="text" select="substring-after($text, $separator)"/>
                    <xsl:with-param name="separator" select="$separator"/>
                </xsl:call-template>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!-- Header for Enrichments -->
    <xsl:template name="column_enrichment">
        <xsl:param name="i" />
        <xsl:param name="anzahl" />
        <xsl:variable name="name-set">
            <xsl:call-template name="tokenize">
                <xsl:with-param name="text">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments_labels')"/>
                </xsl:with-param>
            </xsl:call-template>
	    </xsl:variable>
        <xsl:variable name="visible">
            <xsl:call-template name="tokenize">
                <xsl:with-param name="text">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments_visible')"/>
                </xsl:with-param>
            </xsl:call-template>
	    </xsl:variable>
        <xsl:if test="$i &lt; $anzahl">
            <xsl:if test="ext:node-set($visible)/item[$i]/text() = '1' or $admin='1'">
                <xsl:copy-of select="ext:node-set($name-set)/item[$i]/text()"/><xsl:text>	</xsl:text>
            </xsl:if>
            <xsl:call-template name="column_enrichment">
                <xsl:with-param name="i" select="$i + 1"/>
                <xsl:with-param name="anzahl" select="$anzahl" />
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="$i = $anzahl">
            <xsl:if test="ext:node-set($visible)/item[$i]/text() = '1' or $admin='1'">
                <xsl:copy-of select="ext:node-set($name-set)/item[$i]/text()" /><xsl:text>	</xsl:text>
            </xsl:if>
        </xsl:if>
	</xsl:template>

    <!-- Field content "Enrichments" / ###BSZ/BF 22.03.2022 / Loop: 11.11.2022-->
    <xsl:template name="enrichments">    
        <xsl:if test="Enrichment and string-length(php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments')) > 0">            
            <xsl:variable name="anzahl" select="string-length(php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments'))-string-length(translate(php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments'),',','')) + 1"></xsl:variable>                
                <xsl:call-template name="aggregate_enrichments">
                    <xsl:with-param name="i" select="1"/>
                    <xsl:with-param name="anzahl" select="$anzahl"/>
			    </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <xsl:template name="aggregate_enrichments">  
        <xsl:param name="i" />
        <xsl:param name="anzahl" />

        <xsl:variable name="name-set">
                    <xsl:call-template name="tokenize">
                        <xsl:with-param name="text">
                            <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments')"/>
                        </xsl:with-param>
                    </xsl:call-template>
        </xsl:variable>
        <xsl:variable name="visible">
                    <xsl:call-template name="tokenize">
                        <xsl:with-param name="text">
                            <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'export.csv.enrichments_visible')"/>
                        </xsl:with-param>
                    </xsl:call-template>
        </xsl:variable>
        <xsl:choose>
            <xsl:when test="count(Enrichment[@KeyName=ext:node-set($name-set)/item[$i]/text()]) > 1">
                <xsl:for-each select="Enrichment[@KeyName=ext:node-set($name-set)/item[$i]/text()]/@Value">
                    <xsl:if test="ext:node-set($visible)/item[$i]/text() = '1' or php:function('Application_Xslt::accessAllowed', 'documents') != 0">
                        <xsl:if test="position()=1">
                            <xsl:value-of select="." />                        
                        </xsl:if>
                        <xsl:if test="position()>1">
                            <xsl:text>; </xsl:text>
                            <xsl:value-of select="." />
                        </xsl:if>
                    </xsl:if>
                </xsl:for-each>
                <xsl:if test="ext:node-set($visible)/item[$i]/text() = '1' or php:function('Application_Xslt::accessAllowed', 'documents') != 0">
                    <xsl:text>	</xsl:text>
                </xsl:if>                
            </xsl:when>
            <xsl:otherwise>
                <xsl:if test="ext:node-set($visible)/item[$i]/text() = '1' or php:function('Application_Xslt::accessAllowed', 'documents') != 0">           
                    <xsl:value-of select="Enrichment[@KeyName=ext:node-set($name-set)/item[$i]/text()]/@Value" />
                    <xsl:text>	</xsl:text>
                </xsl:if>
            </xsl:otherwise>
        </xsl:choose> 

        <xsl:if test="$i &lt; $anzahl">
            <xsl:call-template name="aggregate_enrichments">
                <xsl:with-param name="i" select="$i + 1"/>
                <xsl:with-param name="anzahl" select="$anzahl" />
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <!-- Header Collections -->
    <xsl:template name="column_collection">
        <xsl:param name="i" />
        <xsl:param name="anzahl" />
        <xsl:variable name="name-set">
            <xsl:call-template name="tokenize">
                <xsl:with-param name="text">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'export.csv.collections_labels')"/>
                </xsl:with-param>
            </xsl:call-template>
	    </xsl:variable>
        <xsl:variable name="visible">
            <xsl:call-template name="tokenize">
                <xsl:with-param name="text">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'export.csv.collections_visible')"/>
                </xsl:with-param>
            </xsl:call-template>
	    </xsl:variable>
        <xsl:if test="$i &lt; $anzahl">
            <xsl:if test="ext:node-set($visible)/item[$i]/text() = '1' or $admin='1'">
                <xsl:copy-of select="ext:node-set($name-set)/item[$i]/text()"/><xsl:text>	</xsl:text>
            </xsl:if>
            <xsl:call-template name="column_collection">
                <xsl:with-param name="i" select="$i + 1"/>
                <xsl:with-param name="anzahl" select="$anzahl" />
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="$i = $anzahl">
            <xsl:if test="ext:node-set($visible)/item[$i]/text() = '1' or $admin='1'">
                <xsl:copy-of select="ext:node-set($name-set)/item[$i]/text()"/><xsl:text>	</xsl:text>
            </xsl:if>
        </xsl:if>
	</xsl:template>

    <!-- Field content "Collections" / ###BSZ/BF 27.03.2023 -->
    <xsl:template name="collections">
            <xsl:if test="string-length(php:functionString('Application_Xslt::optionValue', 'export.csv.collections')) > 0">             
                <xsl:variable name="anzahl" select="string-length(php:functionString('Application_Xslt::optionValue', 'export.csv.collections'))-string-length(translate(php:functionString('Application_Xslt::optionValue', 'export.csv.collections'),',','')) + 1"></xsl:variable>                
                    <xsl:call-template name="aggregate_collections">
                        <xsl:with-param name="i" select="1"/>
                        <xsl:with-param name="anzahl" select="$anzahl"/>
                    </xsl:call-template>
            </xsl:if>    
    </xsl:template>
    
     <xsl:template name="aggregate_collections">  
        <xsl:param name="i" />
        <xsl:param name="anzahl" />

        <xsl:variable name="name-set">
                    <xsl:call-template name="tokenize">
                        <xsl:with-param name="text">
                            <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'export.csv.collections')"/>
                        </xsl:with-param>
                    </xsl:call-template>
        </xsl:variable>

        <xsl:variable name="visible">
                    <xsl:call-template name="tokenize">
                        <xsl:with-param name="text">
                            <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'export.csv.collections_visible')"/>
                        </xsl:with-param>
                    </xsl:call-template>
        </xsl:variable>

        <xsl:if test="ext:node-set($visible)/item[$i]/text() = '1' or php:function('Application_Xslt::accessAllowed', 'documents') != 0">           
            <xsl:value-of select="Collection[@RoleName=ext:node-set($name-set)/item[$i]/text()]/@Name" />
        </xsl:if>
        <xsl:text>	</xsl:text>

        <xsl:if test="$i &lt; $anzahl">
            <xsl:call-template name="aggregate_collections">
                <xsl:with-param name="i" select="$i + 1"/>
                <xsl:with-param name="anzahl" select="$anzahl" />
            </xsl:call-template>
        </xsl:if>

    </xsl:template>

</xsl:stylesheet>
                
                