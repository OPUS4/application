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
    xmlns:fn="http://www.w3.org/2005/xpath-functions"
    xmlns:xMetaDiss="http://www.d-nb.de/standards/xMetaDiss/"
    xmlns:cc="http://www.d-nb.de/standards/cc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:dcmitype="http://purl.org/dc/dcmitype/"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:bszterms="http://www.bsz-bw.de/xmetadissplus/1.3/terms/"
    xmlns:pc="http://www.d-nb.de/standards/pc/"
    xmlns:urn="http://www.d-nb.de/standards/urn/"
    xmlns:thesis="http://www.ndltd.org/standards/metadata/etdms/1.0"
    xmlns:ddb="http://www.d-nb.de/standards/ddb/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" />


    <xsl:template match="Opus_Document" mode="xmetadissplus">
        <xsl:element name="xMetaDiss">
            <xsl:attribute name="xsi:schemaLocation">
              http://www.bsz-bw.de/xmetadissplus/1.3
              http://www.bsz-bw.de/xmetadissplus/1.3/xmetadissplus.xsd
            </xsl:attribute>
            <!-- dc:title -->
            <xsl:apply-templates select="TitleMain" mode="xmetadissplus" />
            <!-- dc:creator -->
            <xsl:element name="dc:creator">
                <xsl:attribute name="xsi:type">
                    pc:MetaPers
                </xsl:attribute>
                <xsl:apply-templates select="PersonAuthor" mode="xmetadissplus" />
            </xsl:element>
            <!-- dc:subject -->
            <xsl:apply-templates select="SubjectDdc" mode="xmetadissplus" />
            <xsl:apply-templates select="SubjectSwd" mode="xmetadissplus" />
            <xsl:apply-templates select="SubjectUncontrolled" mode="xmetadissplus" />
            <!-- dc:abstract -->
            <xsl:apply-templates select="TitleAbstract" mode="xmetadissplus" />
            <!-- dc:publisher -->
            <xsl:element name="dc:publisher">
               <xsl:attribute name="xsi:type">
                  cc:Publisher
               </xsl:attribute>
                <xsl:element name="cc:universityOrInstitution">
                  <xsl:apply-templates select="@PublisherName" mode="xmetadissplus" />
                  <xsl:apply-templates select="@PublisherPlace" mode="xmetadissplus" />
                </xsl:element>             
               <xsl:apply-templates select="@PublisherAddress" mode="xmetadissplus" />
            </xsl:element>
            <!-- dc:contributor -->
            <xsl:apply-templates select="PersonAdvisor" mode="xmetadissplus" />

<!--    hier statt DateAccepted Datum der Erstveroeffentlichung -->
            <xsl:apply-templates select="@DateAccepted" mode="xmetadissplus" />

            <xsl:element name="dc:type">
               <xsl:attribute name="xsi:type">
                  bszterms:PublType
               </xsl:attribute>
                 <xsl:choose>
                   <xsl:when test="@Type='manual'">
                       Manual
                   </xsl:when>
                   <xsl:when test="@Type='article'">
                       Article
                   </xsl:when>
                   <xsl:when test="@Type='monograph'">
                       Book
                   </xsl:when>
                   <xsl:when test="@Type='book_section'">
                       InBook
                   </xsl:when>
                   <xsl:when test="@Type='bachelor_thesis'">
                       Thesis.Bachelor
                   </xsl:when>
                   <xsl:when test="@Type='master_thesis'">
                       Thesis.Master
                   </xsl:when>
                   <xsl:when test="@Type='doctoral_thesis'">
                       Thesis.Doctoral
                   </xsl:when>
                   <xsl:when test="@Type='habil_thesis'">
                       Thesis.Habilitation
                   </xsl:when>
        <!--  ist das korrekt ? -->           
                   <xsl:when test="@Type='honour_thesis'">
                       Festschrift
                   </xsl:when>
                   <xsl:when test="@Type='journal'">
                       Journal
                   </xsl:when>
                   <xsl:when test="@Type='conference'">
                       Proceedings
                   </xsl:when>
                   <xsl:when test="@Type='conference_item'">
                       InProceedings
                   </xsl:when>
                   <xsl:when test="@Type='study_paper'">
                       Paper
                   </xsl:when>
                   <xsl:when test="@Type='paper'">
                       ResearchPaper
                   </xsl:when>
                   <xsl:when test="@Type='report'">
                       TechReport
                   </xsl:when>
                   <xsl:when test="@Type='preprint'">
                       Preprint
                   </xsl:when>
                   <xsl:when test="@Type='other'">
                       Misc
                   </xsl:when>
                   <xsl:when test="@Type='lecture'">
                       Lecture
                   </xsl:when>
                   <xsl:otherwise>
                     <xsl:value-of select="@Type" />
                       unbekannter Typ 
                   </xsl:otherwise>    
                 </xsl:choose>  
            </xsl:element>
            <xsl:apply-templates select="IdentifierUrn" mode="xmetadissplus" />
            <xsl:apply-templates select="@Language" mode="xmetadissplus" />
            <xsl:apply-templates select="Licence" mode="xmetadissplus" />

  <!--  evtl. thesis.degree nur ausgeben, wenn Inhalt, also
        ueber ein apply-templates select = noch offen -->
            <xsl:element name="thesis:degree">
               <xsl:element name="thesis:level">
   <!--  noch aendern, kann hier auch andere Werte haben -->            
                 <xsl:choose>
                   <xsl:when test="@Type='doctoral thesis'">
                       thesis:doctoral
                   </xsl:when>
                   <xsl:otherwise>
                       thesis:habilitation 
                   </xsl:otherwise>    
                 </xsl:choose>  
               </xsl:element>
               <xsl:element name="thesis:grantor">
                  <xsl:attribute name="xsi:type">
                       cc:Corporate
                  </xsl:attribute>
                  <xsl:element name="cc:universityOrInstitution">
                  </xsl:element>
   <!--  missing: cc:name,cc:place,cc:department, 
         not yet in xml-output -->
               </xsl:element>    
            </xsl:element>

            <xsl:element name="ddb:contact">
                <xsl:attribute name="ddb:contactID">
               <!--  missing, not yet in xml-output, though set fix --> 
                   F6000-0422
                </xsl:attribute>
            </xsl:element>
            <xsl:element name="ddb:fileNumber">
              <xsl:value-of select="count(//File)"/>
            </xsl:element>
            <xsl:apply-templates select="File" mode="xmetadissplus" />
            <xsl:apply-templates select="IdentifierUrl" mode="xmetadissplus" />
            <xsl:element name="ddb:rights">
               <xsl:attribute name="ddb:kind">free</xsl:attribute>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleMain" mode="xmetadissplus">
        <xsl:element name="dc:title">
            <xsl:attribute name="xsi:type">
              ddb:titleISO639-2
            </xsl:attribute>
            <xsl:attribute name="lang">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:choose>
              <xsl:when test="../@Language!=@Language">
                 <xsl:attribute name="ddb:type">translated</xsl:attribute>
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
             <xsl:attribute name="type">
               nameUsedByThePerson
             </xsl:attribute>
             <xsl:element name="pc:foreName">
               <xsl:value-of select="@FirstName" />
             </xsl:element>
             <xsl:element name="pc:surName">
               <xsl:value-of select="@LastName" />
             </xsl:element>
          </xsl:element>
          <xsl:element name="pc:email">
             <xsl:value-of select="@Email" />
          </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectDdc" mode="xmetadissplus">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type">
                DDC-SG
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectSwd" mode="xmetadissplus">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type">
                SWD
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectUncontrolled" mode="xmetadissplus">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type">
                noScheme
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleAbstract" mode="xmetadissplus">
        <xsl:element name="dcterms:abstract">
            <xsl:attribute name="xsi:type">
              ddb:contentISO639-2
            </xsl:attribute>
            <xsl:attribute name="lang">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:attribute name="ddb:type">
              noScheme
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@PublisherName" mode="xmetadissplus">
        <xsl:element name="cc:name">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@PublisherPlace" mode="xmetadissplus">
        <xsl:element name="cc:place">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@PublisherAddress" mode="xmetadissplus">
        <xsl:element name="cc:address">
          <xsl:attribute name="cc:Scheme">
             DIN5008
          </xsl:attribute>
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="PersonAdvisor" mode="xmetadissplus">
       <xsl:element name="contributor">
         <xsl:attribute name="xsi:type">
             pc:Contributor
         </xsl:attribute>
         <xsl:attribute name="thesis:role">
             advisor
         </xsl:attribute>
           <xsl:element name="pc:person">
             <xsl:element name="pc:name">
                <xsl:attribute name="type">
                  nameUsedByThePerson
                </xsl:attribute>
                <xsl:element name="pc:foreName">
                  <xsl:value-of select="@FirstName" />
                </xsl:element>
                <xsl:element name="pc:surName">
                  <xsl:value-of select="@LastName" />
                </xsl:element>
             </xsl:element>
             <xsl:element name="academicTitle">
                <xsl:value-of select="@AcademicTitle" />
             </xsl:element>
           </xsl:element>
       </xsl:element>
    </xsl:template>

    <xsl:template match="@DateAccepted" mode="xmetadissplus">
        <xsl:element name="dcterms:dateAccepted">
          <xsl:attribute name="xsi:type">
             dcterms:W3CDTF
          </xsl:attribute>
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="IdentifierUrn" mode="xmetadissplus">
        <xsl:element name="dc:identifier">
            <xsl:attribute name="xsi:type">urn:nbn</xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@Language" mode="xmetadissplus">
        <xsl:element name="dc:language">
           <xsl:attribute name="xsi:type">
               dcterms:ISO639-2
           </xsl:attribute>    
           <xsl:value-of select="." />
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
            <xsl:attribute name="ddb:fileID">
               file<xsl:value-of select="@DocumentId"/>-<xsl:number value="position()-1"/>
            </xsl:attribute> 

       <!-- not yet in XML-Output -->            
             <xsl:attribute name="ddb:fileSize">
               <xsl:value-of select="@FileSize"/>
            </xsl:attribute>   
               aus:Praesentationsformat   
        </xsl:element>
    </xsl:template>


    <xsl:template match="IdentifierUrl" mode="xmetadissplus">
        <xsl:element name="ddb:identifier">
            <xsl:attribute name="ddb:type">URL</xsl:attribute>
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
PublisherName, PublisherPlace, PublisherAddress: noch nicht gesehen,
               da keine Testdaten
thesis:degree: akademischer Grad
thesis:grantor Institution, die akad. Grad vergeben hat
               in Opus 3 Fakultaet, hier???
contactID:  von thesis:grantor: z.Zt. fix gesetzt               
fileSize    ist bei den Attributen zum Feld file nicht dabei
            hier z.Zt. FileSize benannt 

-->
