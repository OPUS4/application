<?xml version="1.0" encoding="UTF-8"?>
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
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2009-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:php="http://php.net/xsl"
                exclude-result-prefixes="php">

   <xsl:template match="File[@VisibleInFrontdoor='1']">
      <li>
          <!-- TODO use single image file with flag sprites? -->
          <xsl:variable name="flagIcon">
              <xsl:choose>
                  <xsl:when test="php:functionString('Application_Xslt::languageImageExists', @Language)">
                  <img width="16" height="11">
                      <xsl:attribute name="src">
                          <xsl:value-of select="$baseUrl"/>
                          <xsl:text>/img/lang/</xsl:text>
                          <xsl:call-template name="replaceCharsInString">
                              <xsl:with-param name="stringIn" select="string(@Language)"/>
                              <xsl:with-param name="charsIn" select="'/'"/>
                              <xsl:with-param name="charsOut" select="'_'"/>
                          </xsl:call-template>
                          <xsl:text>.png</xsl:text>
                      </xsl:attribute>
                      <xsl:attribute name="class">
                          <xsl:text>file-language </xsl:text>
                          <xsl:value-of select="@Language" />
                      </xsl:attribute>
                      <xsl:attribute name="alt">
                          <xsl:value-of select="@Language" />
                      </xsl:attribute>
                  </img>
                  </xsl:when>
                  <xsl:otherwise>
                      <span class="file-language">
                          <xsl:text>(</xsl:text>
                          <xsl:value-of select="@Language"/>
                          <xsl:text>)</xsl:text>
                      </span>
                  </xsl:otherwise>
              </xsl:choose>
          </xsl:variable>

         <xsl:variable name="fileLink">
            <xsl:value-of select="$baseUrl"/>
            <xsl:text>/files/</xsl:text>
            <xsl:value-of select="../@Id" />
            <xsl:text>/</xsl:text>
            <xsl:value-of select="php:function('urlencode',string(@PathName))"/>
         </xsl:variable>

         <xsl:variable name="fileLinkText">
            <xsl:choose>
               <xsl:when test="normalize-space(@Label)">
                  <xsl:value-of select="@Label" />
               </xsl:when>
               <xsl:otherwise>
                  <xsl:value-of select="@PathName" />
               </xsl:otherwise>
            </xsl:choose>
         </xsl:variable>

         <xsl:choose>
            <xsl:when test="php:functionString('Application_Xslt::fileAccessAllowed', @Id)">
               <div class="accessible-file">
                  <xsl:attribute name="title">
                      <xsl:call-template name="translateString">
                          <xsl:with-param name="string">frontdoor_download_file</xsl:with-param>
                      </xsl:call-template>
                      <xsl:text> </xsl:text>
                      <xsl:choose>
                          <xsl:when test="normalize-space(@Label)">
                              <xsl:value-of select="@Label" />
                          </xsl:when>  
                          <xsl:otherwise>
                              <xsl:value-of select="@PathName" />
                          </xsl:otherwise>
                      </xsl:choose>  
                      <xsl:text> (</xsl:text>
                      <xsl:value-of select="@MimeType" />
                      <xsl:text>)</xsl:text>
                  </xsl:attribute>
                  <xsl:element name="a">
                      <xsl:attribute name="class">
                          <xsl:call-template name="replaceCharsInString">
                              <xsl:with-param name="stringIn" select="string(@MimeType)"/>
                              <xsl:with-param name="charsIn" select="'/'"/>
                              <xsl:with-param name="charsOut" select="'_'"/>
                          </xsl:call-template>
                      </xsl:attribute>
                     <xsl:attribute name="href">
                        <xsl:copy-of select="$fileLink" />
                     </xsl:attribute>
                     <xsl:copy-of select="$fileLinkText" />
                  </xsl:element>
                   <xsl:copy-of select="$flagIcon" />
                   <xsl:if test="@FileSize">
                       <div class="file-size">(<xsl:value-of select="round(@FileSize div 1024)" />KB)</div>
                   </xsl:if>
               </div>
            </xsl:when>
            <xsl:otherwise>
               <div class="protected-file">
                  <xsl:attribute name="title">
                     <xsl:call-template name="translateString">
                        <xsl:with-param name="string">frontdoor_protected_file</xsl:with-param>
                     </xsl:call-template>
                  </xsl:attribute>
                  <xsl:copy-of select="$fileLinkText" />
               </div>
               <xsl:copy-of select="$flagIcon" />
            </xsl:otherwise>
         </xsl:choose>
         <xsl:if test="@Comment">
             <p>
                 <xsl:value-of select="@Comment" />
             </p>
         </xsl:if>
      </li>
   </xsl:template>

   <!--Named Templates for the service block (MailToAuthor, AdditionalServices, ExportFunctions).--> 
     
   <xsl:template name="MailToAuthor">
      <xsl:if test ="$isMailPossible">
         <xsl:element name="br"/>
         <xsl:element name="a">
            <!--TODO: Use Zend Url-Helper to build href attribute--> 
            <xsl:attribute name="href">
               <xsl:value-of select="$baseUrl"/>
               <xsl:text>/frontdoor/mail/toauthor/docId/</xsl:text>
               <xsl:value-of select="@Id" />
            </xsl:attribute>
            <xsl:call-template name="translateString">
               <xsl:with-param name="string">frontdoor_mailtoauthor</xsl:with-param>
            </xsl:call-template>
         </xsl:element>
      </xsl:if>
   </xsl:template>

   <!--Named template for services-buttons--> 
   <xsl:template name="AdditionalServices">
      <!--Twitter--> 
      <xsl:variable name="frontdoor_share_twitter">
         <xsl:call-template name="translateString">
            <xsl:with-param name="string">frontdoor_share_twitter</xsl:with-param>
         </xsl:call-template>
      </xsl:variable>

      <a>
         <xsl:attribute name="href">
            <xsl:text disable-output-escaping="yes">http://twitter.com/share?url=</xsl:text><xsl:value-of select="$baseUrlServer"/><xsl:text>/frontdoor/index/index/docId/</xsl:text>
            <xsl:value-of select="@Id" />
         </xsl:attribute>
         <img>
            <xsl:attribute name="src">
               <xsl:value-of select="$layoutPath"/>
               <xsl:text>/img/twitter.png</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="name">
               <xsl:value-of select="$frontdoor_share_twitter"/>
            </xsl:attribute>
            <xsl:attribute name="title">
               <xsl:value-of select="$frontdoor_share_twitter"/>
            </xsl:attribute>
            <xsl:attribute name="alt">
               <xsl:value-of select="$frontdoor_share_twitter"/>
            </xsl:attribute>
         </img>
      </a>
      <xsl:text> </xsl:text>

      <!--google-scholar-->
      <xsl:if test="normalize-space(TitleMain/@Value)">
         <xsl:element name="a">
            <!--TODO: Use Zend Url-Helper to build href attribute--> 
            <xsl:attribute name="href">
                <xsl:text disable-output-escaping="yes">http://scholar.google.de/scholar?hl=</xsl:text>
                <xsl:value-of select="php:functionString('Application_Xslt::locale')" />
                <xsl:text disable-output-escaping="yes">&amp;q="</xsl:text>
                <xsl:value-of select="TitleMain/@Value"/>       <!-- q: Titelsuchfeld -->
                <xsl:text>"</xsl:text>
                <xsl:call-template name="AuthorUrl" />       <!-- as_sauthors: Suchfeld für Autor -->
                <xsl:text>&amp;as_ylo=</xsl:text>       <!-- as_ylo: gibt die untere Grenze des Suchzeitraums an -->
                <xsl:call-template name="DateUrl" />
                <xsl:text>&amp;as_yhi=</xsl:text>       <!-- as_yhi: gibt die obere Grenze des Suchzeitraums an-->
                <xsl:call-template name="DateUrl" />
            </xsl:attribute>
            <xsl:element name="img">
               <xsl:attribute name="src">
                  <xsl:value-of select="$layoutPath"/><xsl:text>/img/google_scholar.jpg</xsl:text>
               </xsl:attribute>
               <xsl:attribute name="title">
                  <xsl:call-template name="translateString">
                     <xsl:with-param name="string">frontdoor_searchgoogle</xsl:with-param>
                  </xsl:call-template>
               </xsl:attribute>
               <xsl:attribute name="alt">
                  <xsl:call-template name="translateString">
                     <xsl:with-param name="string">frontdoor_searchgoogle</xsl:with-param>
                  </xsl:call-template>
               </xsl:attribute>
            </xsl:element>
         </xsl:element>
         <xsl:text> </xsl:text>
      </xsl:if>
   </xsl:template>

    <xsl:template name="AuthorUrl">
        <xsl:for-each select="PersonAuthor">
            <xsl:text>&amp;as_sauthors=</xsl:text>
            <xsl:value-of select="@FirstName" />
            <xsl:text>+</xsl:text>
            <xsl:value-of select="@LastName" />
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="DateUrl" >
        <xsl:choose>
            <xsl:when test="PublishedDate">
                <xsl:value-of select="PublishedDate/@Year"/>
            </xsl:when>
            <xsl:when test="CompletedDate">
                <xsl:value-of select="CompletedDate/@Year"/>
            </xsl:when>
            <xsl:when test="@PublishedYear">
                <xsl:value-of select="@PublishedYear"/>
            </xsl:when>
            <xsl:when test="@CompletedYear">
                <xsl:value-of select="@CompletedYear"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="format-number(ServerDatePublished/@Year, '0000')"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

   <xsl:template name="ExportFunctions">
        <xsl:value-of disable-output-escaping="yes" select="php:function('Application_Xslt::exportLinks', 'docId', 'frontdoor')" />
   </xsl:template>
    
   <xsl:template name="PrintOnDemand">
      <a>
         <xsl:attribute name="href">
            <xsl:value-of select="$printOnDemandUrl"/>
            <xsl:value-of select="@Id" />
         </xsl:attribute>
         <xsl:choose>
            <xsl:when test="$printOnDemandButton != ''">
               <xsl:element name="img">
                  <xsl:attribute name="src">
                     <xsl:value-of select="$layoutPath"/>
                     <xsl:text>/img/</xsl:text>
                     <xsl:value-of select="$printOnDemandButton" />
                  </xsl:attribute>
                  <xsl:attribute name="name">
                     <xsl:text>epubli</xsl:text>
                  </xsl:attribute>
                  <xsl:attribute name="title">
                     <xsl:call-template name="translateString">
                        <xsl:with-param name="string">frontdoor_pod_description</xsl:with-param>
                     </xsl:call-template>
                  </xsl:attribute>
                  <xsl:attribute name="alt">
                     <xsl:call-template name="translateString">
                        <xsl:with-param name="string">frontdoor_pod_description</xsl:with-param>
                     </xsl:call-template>
                  </xsl:attribute>
               </xsl:element>
            </xsl:when>
            <xsl:otherwise>
               <xsl:call-template name="translateString">
                  <xsl:with-param name="string">frontdoor_pod_description</xsl:with-param>
               </xsl:call-template>
            </xsl:otherwise>
         </xsl:choose>
      </a>
   </xsl:template>

</xsl:stylesheet>
