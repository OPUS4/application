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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * Transforms the xml representation of an Opus_Model_Document to xMetaDiss
 * xml as required by the OAI-PMH protocol.
 *
 * @category    Application
 * @package     Module_Oai
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.bsz-bw.de/xmetadissplus/1.3"
    xmlns:xMetaDiss="http://www.d-nb.de/standards/xMetaDiss/"
    xmlns:cc="http://www.d-nb.de/standards/cc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:dcmitype="http://purl.org/dc/dcmitype/"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:bszterms="http://www.bsz-bw.de/xmetadissplus/1.3/terms/"
    xmlns:pc="http://www.d-nb.de/standards/pc/"
    xmlns:urn="http://www.d-nb.de/standards/urn/"
    xmlns:thesis="http://www.ndltd.org/standards/metadata/etdms/1.0/"
    xmlns:ddb="http://www.d-nb.de/standards/ddb/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" />

    <xsl:template match="Opus_Document" mode="xmetadissplus">
        <xMetaDiss
            xsi:schemaLocation="http://www.bsz-bw.de/xmetadissplus/1.3 http://www.bsz-bw.de/xmetadissplus/1.3/xmetadissplus.xsd">
            <!-- dc:title -->
            <xsl:apply-templates select="TitleMain" mode="xmetadissplus" />
            <!-- dc:creator -->
            <xsl:element name="dc:creator">
                <xsl:attribute name="xsi:type"><xsl:text>pc:MetaPers</xsl:text></xsl:attribute>
                <xsl:apply-templates select="PersonAuthor" mode="xmetadissplus" />
            </xsl:element>
            <!-- dc:subject -->
            <xsl:apply-templates select="SubjectDdc" mode="xmetadissplus" />
            <xsl:apply-templates select="SubjectSwd" mode="xmetadissplus" />
            <xsl:apply-templates select="SubjectUncontrolled" mode="xmetadissplus" />
            <!-- dc:abstract -->
            <xsl:apply-templates select="TitleAbstract" mode="xmetadissplus" />
            <!-- dc:publisher -->
            <xsl:apply-templates select="ThesisPublisher" mode="xmetadissplus" />
            <!-- dc:contributor -->
            <xsl:apply-templates select="PersonAdvisor" mode="xmetadissplus" />
            <xsl:apply-templates select="PersonReferee" mode="xmetadissplus" />

<!--    hier statt DateAccepted Datum der Erstveroeffentlichung -->
            <xsl:apply-templates select="@DateAccepted" mode="xmetadissplus" />

            <xsl:element name="dc:type">
               <xsl:attribute name="xsi:type"><xsl:text>dini:PublType</xsl:text></xsl:attribute>
                 <xsl:choose>
                   <xsl:when test="@Type='article'">
                       <xsl:text>article</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='book'">
                       <xsl:text>book</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='bookpart'">
                       <xsl:text>bookPart</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='bachelorthesis'">
                       <xsl:text>bachelorThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='masterthesis'">
                       <xsl:text>masterThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='doctoralthesis'">
                       <xsl:text>doctoralThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='habilitation'">
                       <xsl:text>doctoralThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='conferenceobject'">
                       <xsl:text>conferenceObject</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='studythesis'">
                       <xsl:text>StudyThesis</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='workingpaper'">
                       <xsl:text>workingPaper</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='report'">
                       <xsl:text>report</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='preprint'">
                       <xsl:text>preprint</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='other'">
                       <xsl:text>Other</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='lecture'">
                       <xsl:text>lecture</xsl:text>
                   </xsl:when>
                   <xsl:otherwise>
                     <xsl:value-of select="@Type" />
                       <xsl:text>Other</xsl:text>
                   </xsl:otherwise>    
                 </xsl:choose>  
            </xsl:element>

            <xsl:apply-templates select="IdentifierUrn" mode="xmetadissplus" />
            <xsl:element name="dcterms:medium">
               <xsl:attribute name="xsi:type"><xsl:text>dcterms:IMT</xsl:text></xsl:attribute>
               <xsl:text>application/pdf</xsl:text>
            </xsl:element>
            <xsl:element name="dc:language">
              <xsl:attribute name="xsi:type"><xsl:text>dcterms:ISO639-2</xsl:text></xsl:attribute>    
                 <xsl:choose>
                   <xsl:when test="@Language='deu'">
                      <xsl:text>ger</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>     
                       <xsl:value-of select="@Language" />
                    </xsl:otherwise>
                 </xsl:choose>
            </xsl:element>
            <xsl:apply-templates select="Licence" mode="xmetadissplus" />

           <!--  thesis.degree only, if type doctoral or habilitation -->
            <xsl:if test="@Type='doctoralthesis' or @Type='habilitation'">
                <xsl:element name="thesis:degree">
                   <xsl:element name="thesis:level">
                     <xsl:choose>
                       <xsl:when test="@Type='doctoral_thesis'">
                           <xsl:text>thesis:doctoral</xsl:text>
                       </xsl:when>
                       <xsl:when test="@Type='habilitation'">
                           <xsl:text>thesis:habilitation</xsl:text>
                       </xsl:when>
                       <xsl:otherwise>
                           <xsl:text>other</xsl:text> 
                       </xsl:otherwise>    
                     </xsl:choose>  
                   </xsl:element>

                <xsl:element name="thesis:grantor">
                   <xsl:attribute name="xsi:type"><xsl:text>cc:Corporate</xsl:text></xsl:attribute>
                   <xsl:element name="cc:universityOrInstitution">
                       <xsl:element name="cc:name">
                          <xsl:value-of select="ThesisGrantor/@Name" />
                       </xsl:element>   
                       <xsl:element name="cc:place">
                          <xsl:value-of select="ThesisGrantor/@City" />
                       </xsl:element>
                       <xsl:element name="cc:department">
                          <xsl:element name="cc:name">
                             <xsl:value-of select="ThesisGrantor/@Name" />
                          </xsl:element>
                       </xsl:element>
                   </xsl:element>
                </xsl:element>    

                </xsl:element>
            </xsl:if>

            <xsl:element name="ddb:contact">
                <xsl:attribute name="ddb:contactID"><xsl:value-of select="ThesisPublisher/@DnbContactId" /></xsl:attribute>
            </xsl:element>
            <xsl:element name="ddb:fileNumber">
              <xsl:value-of select="count(//File)"/>
            </xsl:element>
            <xsl:apply-templates select="File" mode="xmetadissplus" />
            <xsl:apply-templates select="IdentifierUrl" mode="xmetadissplus" />
            <xsl:element name="ddb:rights">
               <xsl:attribute name="ddb:kind"><xsl:text>free</xsl:text></xsl:attribute>
            </xsl:element>
         </xMetaDiss>
    </xsl:template>

    <xsl:template match="TitleMain" mode="xmetadissplus">
        <xsl:element name="dc:title">
            <xsl:attribute name="xsi:type"><xsl:text>ddb:titleISO639-2</xsl:text></xsl:attribute>
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
        </xsl:element>
    </xsl:template>

    <xsl:template match="PersonAuthor" mode="xmetadissplus">
        <xsl:element name="pc:person">
          <xsl:element name="pc:name">
             <xsl:attribute name="type"><xsl:text>nameUsedByThePerson</xsl:text></xsl:attribute>
             <xsl:element name="pc:foreName">
               <xsl:value-of select="@FirstName" />
             </xsl:element>
             <xsl:element name="pc:surName">
               <xsl:value-of select="@LastName" />
             </xsl:element>
          </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectDdc" mode="xmetadissplus">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type"><xsl:text>DDC-SG</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectSwd" mode="xmetadissplus">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type"><xsl:text>SWD</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectUncontrolled" mode="xmetadissplus">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type"><xsl:text>noScheme</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleAbstract" mode="xmetadissplus">
        <xsl:element name="dcterms:abstract">
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
        </xsl:element>
    </xsl:template>


    <xsl:template match="PersonAdvisor" mode="xmetadissplus">
       <xsl:element name="dc:contributor">
         <xsl:attribute name="xsi:type"><xsl:text>pc:Contributor</xsl:text></xsl:attribute>
         <xsl:attribute name="thesis:role"><xsl:text>advisor</xsl:text></xsl:attribute>
         <xsl:attribute name="countryCode"><xsl:text>DE</xsl:text></xsl:attribute>
           <xsl:element name="pc:person">
             <xsl:element name="pc:name">
                <xsl:attribute name="type"><xsl:text>nameUsedByThePerson</xsl:text></xsl:attribute>
                <xsl:element name="pc:foreName">
                  <xsl:value-of select="@FirstName" />
                </xsl:element>
                <xsl:element name="pc:surName">
                  <xsl:value-of select="@LastName" />
                </xsl:element>
             </xsl:element>
             <xsl:if test="normalize-space(@AcademicTitle)">
                <xsl:element name="academicTitle">
                  <xsl:value-of select="@AcademicTitle" />
                </xsl:element>
             </xsl:if>
           </xsl:element>
       </xsl:element>
    </xsl:template>

    <xsl:template match="PersonReferee" mode="xmetadiss">
       <xsl:element name="dc:contributor">
         <xsl:attribute name="xsi:type"><xsl:text>pc:Contributor</xsl:text></xsl:attribute>
         <xsl:attribute name="thesis:role"><xsl:text>referee</xsl:text></xsl:attribute>
         <xsl:attribute name="countryCode"><xsl:text>DE</xsl:text></xsl:attribute>
           <xsl:element name="pc:person">
             <xsl:element name="pc:name">
                <xsl:attribute name="type"><xsl:text>nameUsedByThePerson</xsl:text></xsl:attribute>
                <xsl:element name="pc:foreName">
                  <xsl:value-of select="@FirstName" />
                </xsl:element>
                <xsl:element name="pc:surName">
                  <xsl:value-of select="@LastName" />
                </xsl:element>
             </xsl:element>
             <xsl:if test="normalize-space(@AcademicTitle)">
                <xsl:element name="academicTitle">
                  <xsl:value-of select="@AcademicTitle" />
                </xsl:element>
             </xsl:if>
           </xsl:element>
       </xsl:element>
    </xsl:template>

    <xsl:template match="ThesisPublisher" mode="xmetadissplus">
        <xsl:element name="dc:publisher">
           <xsl:attribute name="xsi:type"><xsl:text>cc:Publisher</xsl:text></xsl:attribute>
               <xsl:element name="cc:universityOrInstitution">
                 <xsl:element name="cc:name">
                     <xsl:value-of select="@Name" />
                 </xsl:element>
                 <xsl:element name="cc:place">
                    <xsl:value-of select="@City" />
                 </xsl:element>
               </xsl:element>
                 <xsl:element name="cc:address">
                    <xsl:attribute name="cc:Scheme"><xsl:text>DIN5008</xsl:text></xsl:attribute>
                    <xsl:value-of select="@Address" />
                 </xsl:element>
        </xsl:element>
    </xsl:template>          

    <xsl:template match="@DateAccepted" mode="xmetadissplus">
        <xsl:element name="dcterms:dateAccepted">
          <xsl:attribute name="xsi:type"><xsl:text>dcterms:W3CDTF</xsl:text></xsl:attribute>
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="IdentifierUrn" mode="xmetadissplus">
        <xsl:element name="dc:identifier">
            <xsl:attribute name="xsi:type"><xsl:text>urn:nbn</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Licence" mode="xmetadissplus">
        <xsl:element name="dc:rights">
            <xsl:value-of select="@NameLong" />
        </xsl:element>
    </xsl:template>
  
    <xsl:template match="File" mode="xmetadissplus">
        <xsl:element name="ddb:fileProperties">
            <xsl:attribute name="ddb:fileName">
               <xsl:value-of select="@PathName" />
            </xsl:attribute>
            <xsl:attribute name="ddb:fileID"><xsl:text>file</xsl:text>
            <xsl:value-of select="../@Id"/>-<xsl:number value="position()-1"/>
            </xsl:attribute> 

       <!-- not yet in XML-Output -->            
             <xsl:attribute name="ddb:fileSize">
               <xsl:value-of select="@FileSize"/>
            </xsl:attribute>   
            <xsl:attribute name="ddb:fileDirectory">/</xsl:attribute>
            <xsl:text>aus:Praesentationsformat</xsl:text>
        </xsl:element>
    </xsl:template>


    <xsl:template match="IdentifierUrl" mode="xmetadissplus">
        <xsl:element name="ddb:identifier">
            <xsl:attribute name="ddb:type"><xsl:text>URL</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>


</xsl:stylesheet>


<!-- folgende Felder sind unklar bzw. fehlen noch im Datenmodell bzw.
     in der xml-Darstellung
dcterms:issued Datum der Erstveroeffentlichung (z.Zt DateAccepted,
               muss noch geaendert werden
SubjectSwd : freie Schlagwoerter tauchen in der XML-Darstellung
             doppelt auf, einmal als
             SubjectSwd Type=uncontrolled  und zweitens als
             SubjectUncontrolled
             Welches soll ich nehmen? Wird eines wegfallen?
             z.Zt. werden sie auch hier doppelt ausgegeben
fileSize    ist bei den Attributen zum Feld file nicht dabei
            hier z.Zt. FileSize benannt 

-->
