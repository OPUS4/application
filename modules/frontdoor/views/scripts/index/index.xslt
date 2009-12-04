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
 * @package     Module_Frontdoor
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
        <xsl:if test="normalize-space(File/@PathName)"> 
       <table class="fulltext">
         <tr class="fulltext">
           <xsl:apply-templates select="File" />
         </tr>
       </table>
      </xsl:if> 
       <xsl:call-template name="services"/>
       <table cellspacing="0">
         <colgroup class="angaben">
            <col class="name"/> 
         </colgroup>            
       <xsl:apply-templates select="IdentifierUrn" />
       <xsl:apply-templates select="IdentifierUrl" />
       <xsl:apply-templates select="IdentifierHandle" />
       <xsl:apply-templates select="IdentifierDoi" />
       <xsl:apply-templates select="IdentifierIsbn" />
       <xsl:apply-templates select="IdentifierIssn" />
       <xsl:apply-templates select="ReferenceUrn" />
       <xsl:apply-templates select="ReferenceUrl" />
       <xsl:apply-templates select="ReferenceDoi" />
       <xsl:apply-templates select="ReferenceHandle" />
       <xsl:apply-templates select="ReferenceIsbn" />
       <xsl:apply-templates select="ReferenceIssn" />
       <xsl:apply-templates select="TitleParent" />
       <xsl:apply-templates select="@PublisherName" />
       <xsl:apply-templates select="@PublisherPlace" />
       <xsl:apply-templates select="PersonEditor" />
       <xsl:apply-templates select="PersonTranslator" />
       <xsl:apply-templates select="PersonContributor" />
       <xsl:apply-templates select="PersonOther" />
       <xsl:apply-templates select="PersonReferee" />
       <xsl:apply-templates select="PersonAdvisor" />
       <xsl:apply-templates select="@Type" />
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
         <xsl:when test="normalize-space(PublishedDate/@Year)">
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
       <xsl:if test="Collection/@RoleName='Computing Classification System'">
          <xsl:apply-templates select="Collection" mode="ccs" />
       </xsl:if>   
       <xsl:if test="Collection/@RoleName='Dewey Decimal Classification'">
          <xsl:apply-templates select="Collection" mode="ddc" />
       </xsl:if>   
       <xsl:if test="Collection/@RoleName='Mathematics Subject Classification'">
          <xsl:apply-templates select="Collection" mode="msc" />
       </xsl:if>   
       <xsl:apply-templates select="Collection" mode="other"/> 
       <xsl:apply-templates select="Licence" />
         </table>
    </xsl:template>


    <!-- Templates for "internal fields". -->
    <xsl:template match="@CompletedYear">
      <tr> 
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@ContributingCorporation">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@CreatingCorporation">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@Edition">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@Issue">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@Language">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@PageFirst">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@PageLast">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@PageNumber">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@PublicationVersion">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@PublishedYear">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@PublisherName">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@PublisherPlace">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@Reviewed">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@Source">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@Type">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>

    <xsl:template match="@Volume">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname" /><xsl:text>:</xsl:text></th>
        <td>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="." />
            </xsl:call-template>
         </td>
       </tr>    
    </xsl:template>


    <!-- Templates for "external fields". -->
    <xsl:template match="Collection" mode="ccs">
      <tr>
          <th class="name">
          <xsl:call-template name="translateString">
              <xsl:with-param name="string">col_ccs</xsl:with-param>
          </xsl:call-template>
          <xsl:text>:</xsl:text></th>
        <td><xsl:value-of select="@Name" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="Collection" mode="ddc">
      <tr>
          <th class="name">
          <xsl:call-template name="translateString">
              <xsl:with-param name="string">col_ddc</xsl:with-param>
          </xsl:call-template>
          <xsl:text>:</xsl:text></th>
        <td><xsl:value-of select="@Name" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="Collection" mode="msc">
      <tr>
          <th class="name">
          <xsl:call-template name="translateString">
              <xsl:with-param name="string">col_msc</xsl:with-param>
          </xsl:call-template>
          <xsl:text>:</xsl:text></th>
        <td><xsl:value-of select="@Name" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="Collection" mode="other">
      <xsl:if test="@RoleName!='Computing Classification System' and @RoleName!='Dewey Decimal Classification' and @RoleName!='Mathematics Subject Classification'">
        <tr>
          <th class="name">
            <xsl:call-template name="translateString">
              <xsl:with-param name="string">col</xsl:with-param>
            </xsl:call-template>
            <xsl:text>:</xsl:text></th>
          <td><xsl:value-of select="@Name" /></td>
        </tr>
      </xsl:if>    
    </xsl:template>

    <xsl:template match="CompletedDate">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="concat(format-number(@Day,'00'),'.',format-number(@Month,'00'),'.',@Year)" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="DateAccepted">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="concat(format-number(@Day,'00'),'.',format-number(@Month,'00'),'.',@Year)" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="File">
      <xsl:choose>
        <xsl:when test="position()=1">
          <td class="fulltext_label">
          <xsl:attribute name="width"><xsl:text>60px</xsl:text></xsl:attribute>
          <h3><nobr> 
          <xsl:call-template name="translateString">
              <xsl:with-param name="string">File(s)</xsl:with-param>
          </xsl:call-template>
          </nobr></h3>
          </td>
        </xsl:when>
        <xsl:otherwise>
          <td class="fulltext_label"></td>
        </xsl:otherwise>
        </xsl:choose>
          <td class="fulltext">
          <nobr>
           <xsl:element name="a">
              <!-- TODO: Use Zend Url-Helper to build href attribute -->
              <xsl:attribute name="href">
                <xsl:text>/documents/</xsl:text>
                <xsl:value-of select="@DocumentId" />
                <xsl:text>/</xsl:text>
                <xsl:value-of select="@PathName" />
              </xsl:attribute>
              <xsl:element name="img">   
                <xsl:attribute name="src">
                  <xsl:value-of select="$layoutPath"/>
                   <xsl:text>/img/filetypelogo_</xsl:text>
                   <xsl:value-of select="@FileType"/>
                   <xsl:text>.png</xsl:text>  
                </xsl:attribute>
                <xsl:attribute name="width"><xsl:text>50</xsl:text></xsl:attribute>
                <xsl:attribute name="height"><xsl:text>53</xsl:text></xsl:attribute>
                <xsl:attribute name="border"><xsl:text>0</xsl:text></xsl:attribute>
                <xsl:attribute name="title"><xsl:text>Download</xsl:text></xsl:attribute>
              </xsl:element>
              <xsl:value-of select="@PathName" />
           </xsl:element>
      <xsl:text> (</xsl:text><xsl:value-of select="@Label" /><xsl:text>)</xsl:text>
      </nobr>
      </td>
    </xsl:template>
         
    <xsl:template match="IdentifierHandle|IdentifierUrl">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Value" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="IdentifierDoi|ReferenceDoi">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td>
          <xsl:element name="a">
            <xsl:attribute name="href"><xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="@Value" /></xsl:attribute>
            <xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="@Value" />
          </xsl:element>
        </td>
      </tr>
    </xsl:template>

    <xsl:template match="IdentifierUrn|ReferenceUrn">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td>
            <xsl:element name="a">
                <!-- TODO: Use Zend Url-Helper to build href attribute -->
                  <xsl:attribute name="href">
                     <xsl:text>http://nbn-resolving.de/urn/resolver.pl?</xsl:text>
                     <xsl:value-of select="@Value" />
                </xsl:attribute>
                <xsl:value-of select="@Value" />
             </xsl:element>
         </td>
      </tr>    
     </xsl:template>

    <xsl:template match="IdentifierIsbn|IdentifierIssn|ReferenceIsbn|ReferenceIssn">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Value" /></td>
      </tr>    
    </xsl:template>
 
    <xsl:template match="Licence">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td>
          <xsl:choose>
             <xsl:when test="starts-with(@NameLong,'Creative')">
               <xsl:element name="img">
                   <xsl:attribute name="src">
                      <xsl:value-of select="$layoutPath"/>
                      <xsl:text>/img/somerights20.gif</xsl:text>
                   </xsl:attribute>
                   <xsl:attribute name="title">
                      <xsl:text>Creative Commons</xsl:text>
                   </xsl:attribute>
                   <xsl:attribute name="border">
                     <xsl:text>0</xsl:text>
                   </xsl:attribute>
               </xsl:element>
               <xsl:element name="a">
                   <!-- TODO: Use Zend Url-Helper to build href attribute -->
                   <xsl:attribute name="href">
                       <xsl:value-of select="@LinkLicence" />
                  </xsl:attribute>
                  <xsl:value-of select="@NameLong" />
               </xsl:element>
             </xsl:when>
             <xsl:otherwise>
               <xsl:element name="img">
                   <xsl:attribute name="src">
                      <xsl:value-of select="$layoutPath"/>
                      <xsl:text>/img/unilogo.gif</xsl:text>
                   </xsl:attribute>
                   <xsl:attribute name="title">
                      <xsl:text>Unilogo</xsl:text>
                   </xsl:attribute>
                   <xsl:attribute name="border">
                     <xsl:text>0</xsl:text>
                   </xsl:attribute>
               </xsl:element>
               <xsl:element name="a">
                   <!-- TODO: Use Zend Url-Helper to build href attribute -->
                   <xsl:attribute name="href">
                       <xsl:value-of select="$baseUrl" />
                       <xsl:value-of select="@LinkLicence" />
                  </xsl:attribute>
                  <xsl:value-of select="@NameLong" />
               </xsl:element>
              </xsl:otherwise>
          </xsl:choose> 
        </td>
      </tr>    
    </xsl:template>
      
    <xsl:template match="Note">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Message" /></td>
      </tr>    
    </xsl:template>
 
    <xsl:template match="Institute"/>
    <xsl:template match="Patent"/>
 
    <xsl:template match="PersonAdvisor">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Name" /></td>
      </tr>    
    </xsl:template>
 
    <xsl:template match="PersonAuthor">
         <xsl:if test="position()=1">
      <xsl:element name="br" />
        </xsl:if>
      <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
           <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/search/search/metadatasearch/author/</xsl:text>
               <xsl:value-of select="@Name" />
           </xsl:attribute>
           <xsl:value-of select="@Name" />
      </xsl:element>
      <xsl:choose>
         <xsl:when test="position()=last()">
            <xsl:text>:</xsl:text>
            <xsl:element name="br"/>
            <xsl:element name="br"/>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text>; </xsl:text>
         </xsl:otherwise>
      </xsl:choose>
    </xsl:template>  
          
    <xsl:template match="PersonOther">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Name" /></td>
      </tr>    
    </xsl:template>
 
    <xsl:template match="PersonReferee">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Name" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="PersonContributor">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Name" /></td>
      </tr>    
    </xsl:template>
 
    <xsl:template match="PersonEditor">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Name" /></td>
      </tr>    
    </xsl:template>
 
    <xsl:template match="PersonTranslator">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Name" /></td>
      </tr>    
    </xsl:template>
 
    <xsl:template match="PublishedDate">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="concat(format-number(@Day,'00'),'.',format-number(@Month,'00'),'.',@Year)" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="PublisherUniversity"/>

    <xsl:template match="ReferenceHandle|ReferenceUrl">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Value" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="SubjectPsyndex">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Value" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="SubjectSwd">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Value" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="SubjectUncontrolled">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Value" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="TitleMain">
        <h3><xsl:value-of select="@Value" />
        <xsl:text> (</xsl:text>
        <xsl:call-template name="translateFieldname" />
        <xsl:text>)</xsl:text></h3>
    </xsl:template>

    <xsl:template match="TitleAbstract">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Value" /></td>
      </tr>    
    </xsl:template>
    
    <xsl:template match="TitleParent">
      <tr>
        <th class="name"><xsl:call-template name="translateFieldname"/>:</th>
        <td><xsl:value-of select="@Value" /></td>
      </tr>    
    </xsl:template>

    <xsl:template match="IdentifierStdDoi"/>
    <xsl:template match="IdentifierCrisLink"/>
    <xsl:template match="IdentifierSplashUrl"/>
    <xsl:template match="ReferenceStdDoi"/>
    <xsl:template match="ReferenceCrisLink"/>
    <xsl:template match="ReferenceSplashUrl"/>


    <!--  Named template for services-buttons -->
    <xsl:template name="services">
      <xsl:element name="br"/>
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
        <xsl:text> </xsl:text>
 
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
        <xsl:text> </xsl:text>

        <!-- statistic -->
        <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
             <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/statistic/index/index/docId/</xsl:text>
               <xsl:value-of select="@Id" />
             </xsl:attribute>
               <xsl:element name="img">   
             <xsl:attribute name="src">
               <xsl:value-of select="$baseUrl"/>
                <xsl:text>/statistic/graph/thumb/docId/</xsl:text>
                <xsl:value-of select="@Id" />
                <xsl:text>/id/</xsl:text>
                <xsl:value-of select="@Id" />
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
        </xsl:element>
        <xsl:text> </xsl:text>

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
        <xsl:text> </xsl:text>

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
        <xsl:text> </xsl:text>

        <!-- google-scholar -->
        <xsl:if test="normalize-space(TitleMain/@Value)">
          <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
            <xsl:attribute name="href">
               <xsl:text disable-output-escaping="yes">http://scholar.google.de/scholar?hl=de&amp;q="</xsl:text>
               <xsl:value-of select="TitleMain/@Value"/><xsl:text>"</xsl:text>
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
         <xsl:text> </xsl:text>
        </xsl:if>

        <!-- Bib-Export -->
        <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
             <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/citationExport/index/index/output/bibtex/docId/</xsl:text>
               <xsl:value-of select="@Id" />
             </xsl:attribute>
           <xsl:element name="img">   
             <xsl:attribute name="src">
               <xsl:value-of select="$layoutPath"/>
                <xsl:text>/img/bibtex.jpg</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="border">
               <xsl:text>0</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="title">
                <xsl:call-template name="translateString">
                   <xsl:with-param name="string">frontdoor_exportbibtex</xsl:with-param>
                </xsl:call-template>
             </xsl:attribute>
          </xsl:element>
        </xsl:element>
        <xsl:text> </xsl:text>

        <!-- Ris-Export -->
        <xsl:element name="a">
           <!-- TODO: Use Zend Url-Helper to build href attribute -->
             <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/citationExport/index/index/output/ris/docId/</xsl:text>
               <xsl:value-of select="@Id" />
             </xsl:attribute>
           <xsl:element name="img">   
             <xsl:attribute name="src">
               <xsl:value-of select="$layoutPath"/>
                <xsl:text>/img/ris.jpg</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="border">
               <xsl:text>0</xsl:text>
             </xsl:attribute>
             <xsl:attribute name="title">
                <xsl:call-template name="translateString">
                   <xsl:with-param name="string">frontdoor_exportris</xsl:with-param>
                </xsl:call-template>
             </xsl:attribute>
          </xsl:element>
        </xsl:element>


      <xsl:element name="br"/>
      <xsl:element name="br"/>
    </xsl:template>


    <!-- Named template to translate a field's name. Needs no parameter. -->
    <xsl:template name="translateFieldname">
        <xsl:value-of select="php:functionString('Frontdoor_IndexController::translate', name())" />
        <xsl:if test="normalize-space(@Language)">
            <!-- translation of language abbreviations  -->
            <xsl:text> (</xsl:text>
            <xsl:call-template name="translateString">
                <xsl:with-param name="string" select="@Language" />
            </xsl:call-template>
            <xsl:text>)</xsl:text>
            
        </xsl:if>
    </xsl:template>

    <!-- Named template to translate an arbitrary string. Needs the translation key as a parameter. -->
    <xsl:template name="translateString">
        <xsl:param name="string" />
        <xsl:value-of select="php:functionString('Frontdoor_IndexController::translate', $string)" />
    </xsl:template>

</xsl:stylesheet>
