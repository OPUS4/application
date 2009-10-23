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
 * @package     Module_FrontdoorXSLT
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de> 
 * @author      Simone Finkbeiner <simone.finkbeiner@ub.uni-stuttgart.de> 
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_FrontdoorXSLT
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    exclude-result-prefixes="php">

    <xsl:output method="html" omit-xml-declaration="yes" />  
 
    <xsl:param name="baseUrl" />
    <xsl:param name="layoutPath" />

    <xsl:template match="/"> 
       <div about="{/Opus/Opus_Model_Filter/TitleMain/@Value}"> 
          <xsl:apply-templates select="Opus/Opus_Model_Filter" />   
       </div>
    </xsl:template>

    <!-- Suppress spilling values with no corresponding templates -->
      <xsl:template match="@*|node()" /> 

<!-- here you can change the order of the fields, just change the order of the apply-templates-rows
     if there is a choose-block for the field, you have to move the whole choose-block
     if you wish new fields, you have to add a new line xsl:apply-templates...
     and a special template for each new field below, too -->
    <xsl:template match="Opus_Model_Filter">
       <xsl:apply-templates select="PersonAuthor" />
       <xsl:apply-templates select="TitleMain" />
       <xsl:apply-templates select="TitleParent" />
       <xsl:apply-templates select="PersonEditor" />
       <xsl:apply-templates select="PersonTranslator" />
       <xsl:apply-templates select="PersonContributor" />
       <xsl:apply-templates select="PersonOther" />
       <xsl:apply-templates select="File" />
       <xsl:call-template name="services"/>
       <xsl:apply-templates select="IdentifierUrn|IdentifierDoi|IdentifierHandle|IdentifierUrl" />
       <xsl:apply-templates select="@PublisherName" />
       <xsl:apply-templates select="@PublisherPlace" />
       <xsl:apply-templates select="PersonReferee" />
       <xsl:apply-templates select="PersonAdvisor" />
       <xsl:apply-templates select="@Type" />
       <xsl:apply-templates select="IdentifierIsbn" />
       <xsl:apply-templates select="IdentifierIssn" />
       <xsl:apply-templates select="@Language" />
       <xsl:choose>
         <xsl:when test="normalize-space(@CompletedYear)">
           <xsl:apply-templates select="@CompletedYear" />
         </xsl:when>
         <xsl:otherwise>
           <xsl:apply-templates select="ComletedDate" />
         </xsl:otherwise>
       </xsl:choose>
       <xsl:choose>
         <xsl:when test="normalize-space(PublishedDate)">
           <xsl:apply-templates select="PublishedDate" />
         </xsl:when>
         <xsl:otherwise>
           <xsl:apply-templates select="@PublishedYear" />
         </xsl:otherwise>
       </xsl:choose>
       <xsl:apply-templates select="DateAccepted" />
       <xsl:apply-templates select="@CreatingCorporation" />
       <xsl:apply-templates select="@ContributingCorporation" />
       <xsl:apply-templates select="TitleAbstract" />
       <xsl:apply-templates select="SubjectSwd" />
       <xsl:apply-templates select="SubjectUncontrolled" />
       <xsl:apply-templates select="SubjectPsyndex" />
       <xsl:apply-templates select="@Source" />
       <xsl:apply-templates select="@Volume" />
       <xsl:apply-templates select="@Issue" />
       <xsl:apply-templates select="@Edition" />
       <xsl:apply-templates select="@PageNumber" />
       <xsl:apply-templates select="@PageFirst" />
       <xsl:apply-templates select="@PageLast" />
       <xsl:apply-templates select="@Reviewed" />
       <xsl:apply-templates select="Note" />
       <xsl:apply-templates select="@PublicationVersion" />
       <xsl:apply-templates select="Licence" />
    </xsl:template>


    <!-- Templates for "internal fields". -->
    <xsl:template match="@CompletedYear">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@ContributingCorporation">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@CreatingCorporation">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@Edition">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@Issue">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@Language">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@PageFirst">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@PageLast">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@PageNumber">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@PublicationVersion">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@PublishedYear">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@PublisherName">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@PublisherPlace">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@Reviewed">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@Source">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@Type">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>

    <xsl:template match="@Volume">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
        </span>
    </xsl:template>


    <!-- Templates for "external fields". -->
    <xsl:template match="CompletedDate">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value"><xsl:value-of select="concat(format-number(@Day,'00'),'.',format-number(@Month,'00'),'.',@Year)" /></span>
    </xsl:template>

    <xsl:template match="DateAccepted">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value"><xsl:value-of select="concat(format-number(@Day,'00'),'.',format-number(@Month,'00'),'.',@Year)" /></span>
    </xsl:template>

    <xsl:template match="File">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value">
             <xsl:element name="a">
                <!-- TODO: Use Zend Url-Helper to build href attribute -->
                  <xsl:attribute name="href">
                    <xsl:text>/documents/</xsl:text>
                    <xsl:value-of select="@DocumentId" />
                    <xsl:text>/</xsl:text>
                    <xsl:value-of select="@PathName" />
                </xsl:attribute>
                <xsl:value-of select="@PathName" />
             </xsl:element>
            <xsl:text> (</xsl:text><xsl:value-of select="@Label" /><xsl:text>)</xsl:text>
        </span>
    </xsl:template>
         
    <xsl:template match="IdentifierUrn|IdentifierDoi|IdentifierHandle|IdentifierUrl">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="IdentifierIsbn">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value"><xsl:value-of select="@Value" /></span>
    </xsl:template>
 
    <xsl:template match="IdentifierIssn">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="Licence">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:rights"><xsl:value-of select="@NameLong" /></span>
    </xsl:template>
      

    <xsl:template match="Collection"/>
    <xsl:template match="Enrichment"/>

    <xsl:template match="Note">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value"><xsl:value-of select="@Message" /></span>
    </xsl:template>

 
    <xsl:template match="Institute"/>
    <xsl:template match="Patent"/>


 
    <xsl:template match="PersonAdvisor">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:creator"><xsl:value-of select="@Name" /></span>
    </xsl:template>
 
    <xsl:template match="PersonAuthor">
      <xsl:element name="br" />
      <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
           <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/search/search/metadatasearch/author/</xsl:text>
               <xsl:value-of select="@Name" />
           </xsl:attribute>
           <xsl:value-of select="@Name" />
      </xsl:element>
      <xsl:if test="position()=last()">
         <xsl:element name="br" />
         <xsl:element name="br" />
      </xsl:if>
    </xsl:template>  
          
    <xsl:template match="PersonOther">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:creator"><xsl:value-of select="@Name" /></span>
    </xsl:template>
 
    <xsl:template match="PersonReferee">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:creator"><xsl:value-of select="@Name" /></span>
    </xsl:template>

    <xsl:template match="PersonContributor">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:creator"><xsl:value-of select="@Name" /></span>
    </xsl:template>
 
    <xsl:template match="PersonEditor">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:creator"><xsl:value-of select="@Name" /></span>
    </xsl:template>
 
    <xsl:template match="PersonTranslator">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:creator"><xsl:value-of select="@Name" /></span>
    </xsl:template>
 

    <xsl:template match="PublishedDate">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value"><xsl:value-of select="concat(format-number(@Day,'00'),'.',format-number(@Month,'00'),'.',@Year)" /></span>
    </xsl:template>

    <xsl:template match="PublisherUniversity"/>

    <xsl:template match="SubjectPsyndex">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:subject" xml:lang="{@Language}"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="SubjectSwd">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:subject" xml:lang="{@Language}"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="SubjectUncontrolled">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:subject" xml:lang="{@Language}"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="TitleMain">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:title" xml:lang="{@Language}"><xsl:value-of select="@Value" /></span>
    </xsl:template>

    <xsl:template match="TitleAbstract">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:description" xml:lang="{@Language}"><xsl:value-of select="@Value" /></span>
    </xsl:template>
    
    <xsl:template match="TitleParent">
        <span class="md name"><xsl:call-template name="translateFieldname" /></span>
        <span class="md value" property="dc:title" xml:lang="{@Language}"><xsl:value-of select="@Value" /></span>
    </xsl:template>


    <xsl:template match="IdentifierStdDoi"/>
    <xsl:template match="IdentifierCrisLink"/>
    <xsl:template match="IdentifierSplashUrl"/>
    <xsl:template match="ReferenceIsbn"/>
    <xsl:template match="ReferenceUrn"/>
    <xsl:template match="ReferenceDoi"/>
    <xsl:template match="ReferenceHandle"/>
    <xsl:template match="ReferenceUrl"/>
    <xsl:template match="ReferenceIssn"/>
    <xsl:template match="ReferenceStdDoi"/>
    <xsl:template match="ReferenceCrisLink"/>
    <xsl:template match="ReferenceSplashUrl"/>


    <!--  Named template for services-buttons -->
    <xsl:template name="services">
      <xsl:element name="br"/>
        <!-- integrity -->
        <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
             <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/frontdoor/hash/index/docId/</xsl:text>
               <xsl:value-of select="@Id" />
             </xsl:attribute>
           <xsl:element name="img">   
             <xsl:attribute name="src">
               <xsl:value-of select="$layoutPath"/>
                <xsl:text>/img/unversehrt.jpg</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="border">
               <xsl:text>0</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="title">
                <xsl:call-template name="translateString">
                   <xsl:with-param name="string">frontdoor_integrity</xsl:with-param>
                </xsl:call-template>
             </xsl:attribute>
          </xsl:element>
        </xsl:element>

        <!-- recommendation -->
        <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
             <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/frontdoor/mail/index/docId/</xsl:text>
               <xsl:value-of select="@Id" />
             </xsl:attribute>
           <xsl:element name="img">   
             <xsl:attribute name="src">
               <xsl:value-of select="$layoutPath"/>
                <xsl:text>/img/hand.jpg</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="border">
               <xsl:text>0</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="title">
                <xsl:call-template name="translateString">
                   <xsl:with-param name="string">frontdoor_recommendationtitle</xsl:with-param>
                </xsl:call-template>
             </xsl:attribute>
          </xsl:element>
        </xsl:element>

        <!-- statistic -->
        <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
             <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/statistic/index/index/docId/</xsl:text>
               <xsl:value-of select="@Id" />
             </xsl:attribute>
<!--               <xsl:element name="img">   
             <xsl:attribute name="src">
               <xsl:value-of select="$baseUrl"/>
                <xsl:text>/statistic/graph/thumb/docId</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="border">
               <xsl:text>0</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="title">
                <xsl:call-template name="translateString">
                   <xsl:with-param name="string">frontdoor_statistics</xsl:with-param>
                </xsl:call-template>
             </xsl:attribute>
          </xsl:element>
-->         
            <xsl:call-template name="translateString">
               <xsl:with-param name="string">frontdoor_statistics</xsl:with-param>
            </xsl:call-template>
          
        </xsl:element>

        <!-- connotea -->
        <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
             <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/socialBookmarking/connotea/index/docId/</xsl:text>
               <xsl:value-of select="@Id" />
             </xsl:attribute>
           <xsl:element name="img">   
             <xsl:attribute name="src">
               <xsl:value-of select="$layoutPath"/>
                <xsl:text>/img/connotea_icon.jpg</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="border">
               <xsl:text>0</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="title">
                <xsl:call-template name="translateString">
                   <xsl:with-param name="string">frontdoor_bookmarkconnotea</xsl:with-param>
                </xsl:call-template>
             </xsl:attribute>
          </xsl:element>
        </xsl:element>

        <!-- delicious -->
        <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
             <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/socialBookmarking/delicious/index/docId/</xsl:text>
               <xsl:value-of select="@Id" />
             </xsl:attribute>
           <xsl:element name="img">   
             <xsl:attribute name="src">
               <xsl:value-of select="$layoutPath"/>
                <xsl:text>/img/delicious.jpg</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="border">
               <xsl:text>0</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="title">
                <xsl:call-template name="translateString">
                   <xsl:with-param name="string">frontdoor_bookmarkdelicious</xsl:with-param>
                </xsl:call-template>
             </xsl:attribute>
          </xsl:element>
        </xsl:element>

        <!-- google-scholar -->
        <xsl:if test="normalize-space(TitleMain/@Value)">
          <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
            <xsl:attribute name="href">
               <xsl:text disable-output-escaping="yes">http://scholar.google.de/scholar?hl=de&amp;q=</xsl:text>
               <xsl:value-of select="TitleMain/@Value"/>
            </xsl:attribute>
            <xsl:element name="img">   
               <xsl:attribute name="src">
                 <xsl:value-of select="$layoutPath"/>
                  <xsl:text>/img/google_scholar.jpg</xsl:text>
               </xsl:attribute>
               <xsl:attribute name="border">
                 <xsl:text>0</xsl:text>
               </xsl:attribute>
               <xsl:attribute name="title">
                  <xsl:call-template name="translateString">
                     <xsl:with-param name="string">frontdoor_searchgoogle</xsl:with-param>
                  </xsl:call-template>
               </xsl:attribute>
            </xsl:element>
          </xsl:element>
        </xsl:if>

    </xsl:template>


    <!-- Named template to translate a field's name. Needs no parameter. -->
    <xsl:template name="translateFieldname">
        <xsl:value-of select="php:functionString('FrontdoorXSLT_IndexController::translate', name())" />
        <xsl:if test="normalize-space(@Language)">
            <!-- TODO: Enable translation of language abbreviations when they are available.
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="@Language" />
            </xsl:call-template>
            -->
            <xsl:text> (</xsl:text><xsl:value-of select="@Language" /><xsl:text>)</xsl:text>
        </xsl:if>
    </xsl:template>

    <!-- Named template to translate an arbitrary string. Needs the translation key as a parameter. -->
    <xsl:template name="translateString">
        <xsl:param name="string" />
        <xsl:value-of select="php:functionString('FrontdoorXSLT_IndexController::translate', $string)" />
    </xsl:template>

</xsl:stylesheet>
