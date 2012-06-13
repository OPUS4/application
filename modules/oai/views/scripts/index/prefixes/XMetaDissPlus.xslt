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
 * @package     Module_Oai
 * @author      Simone Finkbeiner <simone.finkbeiner@ub.uni-stuttgart.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2009-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * Transforms the xml representation of an Opus_Model_Document to XMetaDissPlus
 * xml as required by the OAI-PMH protocol.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xMetaDiss="http://www.d-nb.de/standards/xmetadissplus/"
    xmlns:cc="http://www.d-nb.de/standards/cc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:dcmitype="http://purl.org/dc/dcmitype/"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:pc="http://www.d-nb.de/standards/pc/"
    xmlns:urn="http://www.d-nb.de/standards/urn/"
    xmlns:hdl="http://www.d-nb.de/standards/hdl/"
    xmlns:doi="http://www.d-nb.de/standards/doi/"
    xmlns:thesis="http://www.ndltd.org/standards/metadata/etdms/1.0/"
    xmlns:ddb="http://www.d-nb.de/standards/ddb/"
    xmlns:dini="http://www.d-nb.de/standards/xmetadissplus/type/"
    xmlns="http://www.d-nb.de/standards/subject/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.d-nb.de/standards/xmetadissplus/ http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd">

    <xsl:output method="xml" indent="yes" />

    <xsl:template match="Opus_Document" mode="xmetadissplus">
        <xMetaDiss:xMetaDiss
            xmlns:xMetaDiss="http://www.d-nb.de/standards/xmetadissplus/"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.d-nb.de/standards/xmetadissplus/ http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd">

            <!-- dc:title -->
            <xsl:apply-templates select="TitleMain" mode="xmetadissplus" />
            <!-- dc:creator -->
            <xsl:apply-templates select="PersonAuthor" mode="xmetadissplus" />
            <!-- dc:subject -->
            <xsl:apply-templates select="Collection[@RoleName='ddc' and @Visible=1]" mode="xmetadissplus" />
            <xsl:apply-templates select="Subject[@Type='swd']" mode="xmetadissplus" />
            <xsl:apply-templates select="Subject[@Type='uncontrolled']" mode="xmetadissplus" />
            <!-- dc:abstract -->
            <xsl:apply-templates select="TitleAbstract" mode="xmetadissplus" />
            <!-- dc:publisher -->
            <xsl:apply-templates select="ThesisPublisher" mode="xmetadissplus" />
            <!-- dc:contributor -->
            <xsl:apply-templates select="PersonAdvisor" mode="xmetadissplus" />
            <xsl:apply-templates select="PersonReferee" mode="xmetadissplus" />

            <xsl:choose>
                <xsl:when test="ThesisDateAccepted">
                    <dcterms:dateAccepted xsi:type="dcterms:W3CDTF">
                        <xsl:value-of select="ThesisDateAccepted/@Year"/>-<xsl:value-of select="format-number(ThesisDateAccepted/@Month,'00')"/>-<xsl:value-of select="format-number(ThesisDateAccepted/@Day,'00')"/>
                    </dcterms:dateAccepted>
                </xsl:when>
                <xsl:when test="@ThesisYearAccepted">
                    <dcterms:dateAccepted xsi:type="dcterms:W3CDTF">
                        <xsl:value-of select="@ThesisYearAccepted"/>
                    </dcterms:dateAccepted>
                </xsl:when>
            </xsl:choose>

            <dcterms:issued xsi:type="dcterms:W3CDTF">
                <xsl:choose>
                  <xsl:when test="PublishedDate">
                    <xsl:value-of select="PublishedDate/@Year"/>-<xsl:value-of select="format-number(PublishedDate/@Month,'00')"/>-<xsl:value-of select="format-number(PublishedDate/@Day,'00')"/>
                  </xsl:when>
                  <xsl:when test="CompletedDate">
                    <xsl:value-of select="CompletedDate/@Year"/>-<xsl:value-of select="format-number(CompletedDate/@Month,'00')"/>-<xsl:value-of select="format-number(CompletedDate/@Day,'00')"/>
                  </xsl:when>
                  <xsl:when test="@PublishedYear">
                    <xsl:value-of select="@PublishedYear"/>
                  </xsl:when>
                  <xsl:when test="@CompletedYear">
                    <xsl:value-of select="@CompletedYear"/>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="ServerDatePublished/@Year"/>-<xsl:value-of select="format-number(ServerDatePublished/@Month,'00')"/>-<xsl:value-of select="format-number(ServerDatePublished/@Day,'00')"/>
                  </xsl:otherwise>
                </xsl:choose>
            </dcterms:issued>

            <dc:type xsi:type="dini:PublType">
                 <xsl:choose>
                   <xsl:when test="@Type='article'">
                       <xsl:text>article</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='bachelorthesis'">
                       <xsl:text>bachelorThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='book'">
                       <xsl:text>book</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='bookpart'">
                       <xsl:text>bookPart</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='conferenceobject'">
                       <xsl:text>conferenceObject</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='contributiontoperiodical'">
                       <xsl:text>contributionToPeriodical</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='coursematerial'">
                       <xsl:text>CourseMaterial</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='doctoralthesis'">
                       <xsl:text>doctoralThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='habilitation'">
                       <xsl:text>doctoralThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='image'">
                       <xsl:text>Image</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='lecture'">
                       <xsl:text>lecture</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='masterthesis'">
                       <xsl:text>masterThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='movingimage'">
                       <xsl:text>MovingImage</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='other'">
                       <xsl:text>Other</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='preprint'">
                       <xsl:text>preprint</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='report'">
                       <xsl:text>report</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='review'">
                       <xsl:text>review</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='sound'">
                       <xsl:text>sound</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='studythesis'">
                       <xsl:text>StudyThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='workingpaper'">
                       <xsl:text>workingPaper</xsl:text>
                   </xsl:when>
                   <xsl:otherwise>
                     <xsl:value-of select="@Type" />
                       <xsl:text>Other</xsl:text>
                   </xsl:otherwise>    
                 </xsl:choose>  
            </dc:type>

            <xsl:apply-templates select="IdentifierUrn" mode="xmetadissplus" />
            <dc:language xsi:type="dcterms:ISO639-2">
                 <xsl:choose>
                   <xsl:when test="@Language='deu'">
                      <xsl:text>ger</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>     
                       <xsl:value-of select="@Language" />
                    </xsl:otherwise>
                 </xsl:choose>
            </dc:language >
            <xsl:apply-templates select="Licence" mode="xmetadissplus" />

           <!--  thesis.degree only, if type doctoral or habilitation -->
            <xsl:if test="@Type='bachelorthesis' or @Type='doctoralthesis' or @Type='habilitation' or @Type='masterthesis'">
                <thesis:degree>
                   <thesis:level>
                     <xsl:choose>
                       <xsl:when test="@Type='bachelorthesis'">
                           <xsl:text>bachelor</xsl:text>
                       </xsl:when>
                       <xsl:when test="@Type='doctoralthesis'">
                           <xsl:text>thesis.doctoral</xsl:text>
                       </xsl:when>
                       <xsl:when test="@Type='habilitation'">
                           <xsl:text>thesis.habilitation</xsl:text>
                       </xsl:when>
                       <xsl:when test="@Type='masterthesis'">
                           <xsl:text>master</xsl:text>
                       </xsl:when>
                       <xsl:otherwise>
                           <xsl:text>other</xsl:text> 
                       </xsl:otherwise>    
                     </xsl:choose>  
                   </thesis:level>

                <thesis:grantor xsi:type="cc:Corporate">
                   <cc:universityOrInstitution>
                       <cc:name>
                          <xsl:value-of select="ThesisGrantor/@Name" />
                       </cc:name>   
                       <cc:place>
                          <xsl:value-of select="ThesisGrantor/@City" />
                       </cc:place>
                       <cc:department>
                          <cc:name>
                             <xsl:value-of select="ThesisGrantor/@Name" />
                          </cc:name>
                       </cc:department>
                   </cc:universityOrInstitution>
                </thesis:grantor>    

                </thesis:degree>
            </xsl:if>

            <ddb:contact>
                <xsl:attribute name="ddb:contactID"><xsl:value-of select="ThesisPublisher/@DnbContactId" /></xsl:attribute>
            </ddb:contact>
            <ddb:fileNumber>
              <xsl:value-of select="count(File)"/>
            </ddb:fileNumber>
            <xsl:apply-templates select="File" mode="xmetadissplus" />
            <xsl:if test="File">
                <xsl:apply-templates select="TransferUrl" mode="xmetadissplus" />
            </xsl:if>
            <xsl:apply-templates select="IdentifierUrl" mode="xmetadissplus" />
            <ddb:rights ddb:kind="free" />
         </xMetaDiss:xMetaDiss>
    </xsl:template>

    <xsl:template match="TitleMain" mode="xmetadissplus">
        <dc:title xsi:type="ddb:titleISO639-2">
            <xsl:attribute name="lang">
               <xsl:choose>
                  <xsl:when test="@Language='deu'">
                    <xsl:text>ger</xsl:text>
                  </xsl:when>
                  <xsl:otherwise>     
                     <xsl:value-of select="@Language" />
                  </xsl:otherwise>
               </xsl:choose>
            </xsl:attribute>
            <xsl:choose>
              <xsl:when test="../@Language!=@Language">
                 <xsl:attribute name="ddb:type"><xsl:text>translated</xsl:text></xsl:attribute>
              </xsl:when>
              <xsl:otherwise>
              </xsl:otherwise>
            </xsl:choose>
            <xsl:value-of select="@Value" />
        </dc:title>
    </xsl:template>

    <xsl:template match="PersonAuthor" mode="xmetadissplus">
       <dc:creator xsi:type="pc:MetaPers">
         <pc:person>
          <pc:name type="nameUsedByThePerson">
             <pc:foreName>
               <xsl:value-of select="@FirstName" />
             </pc:foreName>
             <pc:surName>
               <xsl:value-of select="@LastName" />
             </pc:surName>
          </pc:name>
          <xsl:if test="normalize-space(@AcademicTitle)">
             <pc:academicTitle>
               <xsl:value-of select="@AcademicTitle" />
             </pc:academicTitle>
          </xsl:if>
         </pc:person>
       </dc:creator>
    </xsl:template>

    <xsl:template match="Collection[@RoleName='ddc' and @Visible=1]" mode="xmetadissplus">
        <dc:subject xsi:type="dcterms:DDC">
            <xsl:value-of select="@Number" />
        </dc:subject>
    </xsl:template>

    <xsl:template match="Subject[@Type='swd']" mode="xmetadissplus">
        <dc:subject xsi:type="xMetaDiss:SWD">
            <xsl:value-of select="@Value" />
        </dc:subject>
    </xsl:template>

    <xsl:template match="Subject[@Type='uncontrolled']" mode="xmetadissplus">
        <dc:subject xsi:type="xMetaDiss:noScheme">
            <xsl:value-of select="@Value" />
        </dc:subject>
    </xsl:template>

    <xsl:template match="TitleAbstract" mode="xmetadissplus">
        <dcterms:abstract>
            <xsl:attribute name="xsi:type"><xsl:text>ddb:contentISO639-2</xsl:text></xsl:attribute>
            <xsl:attribute name="lang">
               <xsl:choose>
                  <xsl:when test="@Language='deu'">
                    <xsl:text>ger</xsl:text>
                  </xsl:when>
                  <xsl:otherwise>     
                     <xsl:value-of select="@Language" />
                  </xsl:otherwise>
               </xsl:choose>
            </xsl:attribute>
            <xsl:attribute name="ddb:type"><xsl:text>noScheme</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </dcterms:abstract>
    </xsl:template>


    <xsl:template match="PersonAdvisor" mode="xmetadissplus">
       <dc:contributor>
         <xsl:attribute name="xsi:type"><xsl:text>pc:Contributor</xsl:text></xsl:attribute>
         <xsl:attribute name="type"><xsl:text>dcterms:ISO3166</xsl:text></xsl:attribute>
         <xsl:attribute name="thesis:role"><xsl:text>advisor</xsl:text></xsl:attribute>
         <xsl:attribute name="countryCode"><xsl:text>DE</xsl:text></xsl:attribute>
           <pc:person>
             <pc:name type="nameUsedByThePerson">
                <pc:foreName>
                  <xsl:value-of select="@FirstName" />
                </pc:foreName>
                <pc:surName>
                  <xsl:value-of select="@LastName" />
                </pc:surName>
             </pc:name>
             <xsl:if test="normalize-space(@AcademicTitle)">
                <pc:academicTitle>
                  <xsl:value-of select="@AcademicTitle" />
                </pc:academicTitle>
             </xsl:if>
           </pc:person>
       </dc:contributor>
    </xsl:template>

    <xsl:template match="PersonReferee" mode="xmetadissplus">
       <dc:contributor>
         <xsl:attribute name="xsi:type"><xsl:text>pc:Contributor</xsl:text></xsl:attribute>
         <xsl:attribute name="type"><xsl:text>dcterms:ISO3166</xsl:text></xsl:attribute>
         <xsl:attribute name="thesis:role"><xsl:text>referee</xsl:text></xsl:attribute>
         <xsl:attribute name="countryCode"><xsl:text>DE</xsl:text></xsl:attribute>
           <pc:person>
             <pc:name type="nameUsedByThePerson">
                <pc:foreName>
                  <xsl:value-of select="@FirstName" />
                </pc:foreName>
                <pc:surName>
                  <xsl:value-of select="@LastName" />
                </pc:surName>
             </pc:name>
             <xsl:if test="normalize-space(@AcademicTitle)">
                <pc:academicTitle>
                  <xsl:value-of select="@AcademicTitle" />
                </pc:academicTitle>
             </xsl:if>
           </pc:person>
       </dc:contributor>
    </xsl:template>

    <xsl:template match="ThesisPublisher" mode="xmetadissplus">
        <dc:publisher>
           <xsl:attribute name="xsi:type"><xsl:text>cc:Publisher</xsl:text></xsl:attribute>
           <xsl:attribute name="type"><xsl:text>dcterms:ISO3166</xsl:text></xsl:attribute>
               <cc:universityOrInstitution>
                 <cc:name>
                     <xsl:value-of select="@Name" />
                 </cc:name>
                 <cc:place>
                    <xsl:value-of select="@City" />
                 </cc:place>
               </cc:universityOrInstitution>
                 <cc:address cc:Scheme="DIN5008">
                    <xsl:value-of select="@Address" />
                 </cc:address>
        </dc:publisher>
    </xsl:template>          

    <xsl:template match="IdentifierUrn" mode="xmetadissplus">
        <dc:identifier xsi:type="urn:nbn">
            <xsl:value-of select="@Value" />
        </dc:identifier>
    </xsl:template>

    <xsl:template match="Licence" mode="xmetadissplus">
        <dc:rights>
            <xsl:value-of select="@NameLong" />
        </dc:rights>
    </xsl:template>
  
    <xsl:template match="File" mode="xmetadissplus">
        <ddb:fileProperties>
            <xsl:attribute name="ddb:fileName">
                <xsl:value-of select="@PathName" />
            </xsl:attribute>
            <xsl:attribute name="ddb:fileID">
                <xsl:text>file</xsl:text><xsl:value-of select="../@Id"/>-<xsl:value-of select="position()-1"/>
            </xsl:attribute>
            <xsl:attribute name="ddb:fileSize">
                <xsl:value-of select="@FileSize"/>
            </xsl:attribute>
        </ddb:fileProperties>
        <dcterms:medium xsi:type="dcterms:IMT">
            <xsl:value-of select="@MimeType" />
        </dcterms:medium>
    </xsl:template>

    <xsl:template match="TransferUrl" mode="xmetadissplus">
        <ddb:transfer ddb:type="dcterms:URI">
            <xsl:value-of select="@PathName" />
        </ddb:transfer>
    </xsl:template>

    <xsl:template match="IdentifierUrl" mode="xmetadissplus">
        <ddb:identifier ddb:type="URL">
            <xsl:value-of select="@Value" />
        </ddb:identifier>
    </xsl:template>

</xsl:stylesheet>
