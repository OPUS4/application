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
    xmlns="http://www.d-nb.de/standards/subject/"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xMetaDiss="http://www.d-nb.de/standards/xMetaDiss/"
    xmlns:cc="http://www.d-nb.de/standards/cc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:dcmitype="http://purl.org/dc/dcmitype/"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:pc="http://www.d-nb.de/standards/pc/"
    xmlns:urn="http://www.d-nb.de/standards/urn/"
    xmlns:thesis="http://www.ndltd.org/standards/metadata/etdms/1.0/"
    xmlns:ddb="http://www.d-nb.de/standards/ddb/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" />

    <xsl:param name="contactId" />

    <xsl:template match="Opus_Document" mode="xmetadiss">
        <xMetaDiss:xMetaDiss
            xsi:schemaLocation="http://www.d-nb.de/standards/xMetaDiss/ http://www.d-nb.de/standards/xmetadiss/xmetadiss.xsd">
            <!-- dc:title -->
            <xsl:apply-templates select="TitleMain" mode="xmetadiss" />
            <!-- dc:creator -->
            <xsl:element name="dc:creator">
                <xsl:attribute name="xsi:type"><xsl:text>pc:MetaPers</xsl:text></xsl:attribute>
                <xsl:apply-templates select="PersonAuthor" mode="xmetadiss" />
            </xsl:element>
            <!-- dc:subject -->
            <xsl:apply-templates select="SubjectDdc" mode="xmetadiss" />
            <xsl:apply-templates select="SubjectSwd" mode="xmetadiss" />
            <xsl:apply-templates select="SubjectUncontrolled" mode="xmetadiss" />
            <!-- dc:abstract -->
            <xsl:apply-templates select="TitleAbstract" mode="xmetadiss" />
            <!-- dc:publisher -->
            <xsl:element name="dc:publisher">
               <xsl:attribute name="xsi:type"><xsl:text>cc:Publisher</xsl:text></xsl:attribute>
                <xsl:element name="cc:universityOrInstitution">
                  <xsl:apply-templates select="@PublisherName" mode="xmetadiss" />
                  <xsl:apply-templates select="@PublisherPlace" mode="xmetadiss" />
                </xsl:element>             
               <xsl:apply-templates select="@PublisherAddress" mode="xmetadiss" />
            </xsl:element>
            <!-- dc:contributor -->
            <xsl:apply-templates select="PersonAdvisor" mode="xmetadiss" />
            <xsl:apply-templates select="PersonReferee" mode="xmetadiss" />
            <xsl:apply-templates select="@DateAccepted" mode="xmetadiss" />
            <xsl:element name="dc:type">
               <xsl:attribute name="xsi:type"><xsl:text>ddb:PublType</xsl:text></xsl:attribute>
               <xsl:text>ElectronicThesisandDissertation</xsl:text>
            </xsl:element>
            <xsl:apply-templates select="IdentifierUrn" mode="xmetadiss" />
            <xsl:element name="dcterms:medium">
               <xsl:attribute name="xsi:type"><xsl:text>dcterms:IMT</xsl:text>
               </xsl:attribute><xsl:text>application/pdf</xsl:text>
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

            <xsl:element name="thesis:degree">
               <xsl:element name="thesis:level">
                 <xsl:choose>
                   <xsl:when test="@Type='doctoral_thesis'">
                       <xsl:text>thesis.doctoral</xsl:text>
                   </xsl:when>
                   <xsl:when test="@Type='habilitation'">
                       <xsl:text>thesis.habilitation</xsl:text>
                   </xsl:when>
                   <xsl:otherwise>
                       <xsl:text>other</xsl:text>
                   </xsl:otherwise>    
                 </xsl:choose>  
               </xsl:element>
               <xsl:element name="thesis:grantor">
                  <xsl:attribute name="xsi:type"><xsl:text>cc:Corporate</xsl:text></xsl:attribute>
                  <xsl:element name="cc:universityOrInstitution">
                  </xsl:element>
   <!--  missing: cc:name,cc:place,cc:department, 
         not yet in xml-output -->
               </xsl:element>    
            </xsl:element>

            <xsl:element name="ddb:contact">
                <xsl:attribute name="ddb:contactID"><xsl:value-of select="$contactId" /></xsl:attribute>
            </xsl:element>
            <xsl:element name="ddb:fileNumber">
              <xsl:value-of select="count(//File)"/>
            </xsl:element>
            <xsl:apply-templates select="File" mode="xmetadiss" />
            <xsl:apply-templates select="IdentifierUrl" mode="xmetadiss" />
            <xsl:element name="ddb:rights">
               <xsl:attribute name="ddb:kind"><xsl:text>free</xsl:text></xsl:attribute>
            </xsl:element>
         </xMetaDiss:xMetaDiss>
    </xsl:template>

    <xsl:template match="TitleMain" mode="xmetadiss">
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

    <xsl:template match="PersonAuthor" mode="xmetadiss">
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
          <xsl:if test="@Email!=''">
             <xsl:element name="pc:email">
               <xsl:value-of select="@Email" />
             </xsl:element>
           </xsl:if>
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectDdc" mode="xmetadiss">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type"><xsl:text>xMetaDiss:DDC-SG</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectSwd" mode="xmetadiss">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type"><xsl:text>xMetaDiss:SWD</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectUncontrolled" mode="xmetadiss">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type"><xsl:text>xMetaDiss:noScheme</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleAbstract" mode="xmetadiss">
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

    <xsl:template match="@PublisherName" mode="xmetadiss">
        <xsl:element name="cc:name">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@PublisherPlace" mode="xmetadiss">
        <xsl:element name="cc:place">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@PublisherAddress" mode="xmetadiss">
        <xsl:element name="cc:address">
          <xsl:attribute name="cc:Scheme"><xsl:text>DIN5008</xsl:text></xsl:attribute>
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="PersonAdvisor" mode="xmetadiss">
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
             <xsl:element name="pc:academicTitle">
                <xsl:value-of select="@AcademicTitle" />
             </xsl:element>
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
             <xsl:element name="pc:academicTitle">
                <xsl:value-of select="@AcademicTitle" />
             </xsl:element>
           </xsl:element>
       </xsl:element>
    </xsl:template>


    <xsl:template match="@DateAccepted" mode="xmetadiss">
        <xsl:element name="dcterms:dateAccepted">
          <xsl:attribute name="xsi:type"><xsl:text>dcterms:W3CDTF</xsl:text></xsl:attribute>
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="IdentifierUrn" mode="xmetadiss">
        <xsl:element name="dc:identifier">
            <xsl:attribute name="xsi:type"><xsl:text>urn:nbn</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Licence" mode="xmetadiss">
        <xsl:element name="dc:rights">
            <xsl:value-of select="@NameLong" />
        </xsl:element>
    </xsl:template>
  
    <xsl:template match="File" mode="xmetadiss">
        <xsl:element name="ddb:fileProperties">
            <xsl:attribute name="ddb:fileName">
               <xsl:value-of select="@PathName" />
            </xsl:attribute>
            <xsl:attribute name="ddb:fileID"><xsl:text>file</xsl:text>
            <xsl:value-of select="@DocumentId"/>-<xsl:number value="position()-1"/>
            </xsl:attribute> 

       <!-- not yet in XML-Output -->            
             <xsl:attribute name="ddb:fileSize">
               <xsl:value-of select="@FileSize"/>
            </xsl:attribute>   

            <xsl:attribute name="ddb:fileDirectory">/</xsl:attribute>
            <xsl:text>aus:Praesentationsformat</xsl:text>   
        </xsl:element>
    </xsl:template>


    <xsl:template match="IdentifierUrl" mode="xmetadiss">
        <xsl:element name="ddb:identifier">
            <xsl:attribute name="ddb:type"><xsl:text>URL</xsl:text></xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>


</xsl:stylesheet>


<!-- folgende Felder sind unklar bzw. fehlen noch im Datenmodell bzw.
     in der xml-Darstellung
SubjectSwd : freie Schlagwoerter tauchen in der XML-Darstellung
             doppelt auf, einmal als
             SubjectSwd Type=uncontrolled  und zweitens als
             SubjectUncontrolled
             Welches soll ich nehmen? Wird eines wegfallen?
             z.Zt. werden sie auch hier doppelt ausgegeben
PublisherName, PublisherPlace, PublisherAddress: noch nicht gesehen,
               da keine Testdaten
thesis:grantor Institution, die akad.Grad vergeben hat 
               in Opus 3 Fakultaet, hier???
fileSize    ist bei den Attributen zum Feld file nicht dabei
            hier z.Zt. FileSize benannt 

-->
