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
    xmlns:xMetaDiss="http://www.d-nb.de/standards/xMetaDiss/"
    xmlns:cc="http://www.d-nb.de/standards/cc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:dcmitype="http://purl.org/dc/dcmitype/"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:pc="http://www.d-nb.de/standards/pc/"
    xmlns:urn="http://www.d-nb.de/standards/urn/"
    xmlns:thesis="http://www.ndltd.org/standards/metadata/etdms/1.0"
    xmlns:ddb="http://www.d-nb.de/standards/ddb/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" />


    <xsl:template match="Opus_Document" mode="xmetadiss">
        <xsl:element name="xMetaDiss:xMetaDiss">
            <xsl:attribute name="xsi:schemaLocation">
              http://www.d-nb.de/standards/xMetaDiss/
              http://www.d-nb.de/standards/xmetadiss/xmetadiss.xsd
            </xsl:attribute>
            <!-- dc:title -->
            <xsl:apply-templates select="TitleMain" mode="xmetadiss" />
            <!-- dc:creator -->
            <xsl:element name="dc:creator">
                <xsl:attribute name="xsi:type">
                    pc:MetaPers
                </xsl:attribute>
                <xsl:apply-templates select="PersonAuthor" mode="xmetadiss" />
            </xsl:element>
            <!-- dc:subject -->
            <!-- ddc-Sachgruppe fehlt noch im Modell -->
            <xsl:apply-templates select="SubjectDdc" mode="xmetadiss" />
            <xsl:apply-templates select="SubjectSwd" mode="xmetadiss" />
            <xsl:apply-templates select="SubjectUncontrolled" mode="xmetadiss" />
            <!-- dc:abstract -->
            <xsl:apply-templates select="TitleAbstract" mode="xmetadiss" />
            <!-- dc:publisher -->
            <xsl:element name="dc:publisher">
               <xsl:attribute name="xsi:type">
                  cc:Publisher
               </xsl:attribute>
                <xsl:element name="cc:universityOrInstitution">
                  <xsl:apply-templates select="PublisherName" mode="xmetadiss" />
                  <xsl:apply-templates select="PublisherPlace" mode="xmetadiss" />
                </xsl:element>             
               <xsl:apply-templates select="PublisherAddress" mode="xmetadiss" />
            </xsl:element>
            <!-- dc:contributor -->
            <xsl:apply-templates select="PersonAdvisor" mode="xmetadiss" />
            <xsl:apply-templates select="CompletedDate" mode="xmetadiss" />
            <xsl:element name="dc:type">
               <xsl:attribute name="xsi:type">
                  ddb:PublType
               </xsl:attribute>
               ElectronicThesisandDissertation
            </xsl:element>
            <xsl:apply-templates select="IdentifierUrn" mode="xmetadiss" />
            <xsl:apply-templates select="Language" mode="xmetadiss" />
            <xsl:apply-templates select="Licence" mode="xmetadiss" />
            <xsl:element name="thesis:degree">
               <xsl:element name="thesis:level">
                   thesis:doctoral
               </xsl:element>
               <xsl:element name="thesis:grantor">
                  <xsl:attribute name="xsi:type">
                       cc:Corporate
                  </xsl:attribute>
                  <xsl:element name="cc:universityOrInstitution">
                  </xsl:element>
               </xsl:element>    
            </xsl:element>
            <xsl:element name="ddb:contact">
                <xsl:attribute name="ddb:contactID">
                   F6000-0422
                </xsl:attribute>
            </xsl:element>
            <xsl:apply-templates select="IdentifierUrl" mode="xmetadiss" />
            <xsl:element name="ddb:rights">
               <xsl:attribute name="ddb:kind">free</xsl:attribute>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleMain" mode="xmetadiss">
        <xsl:element name="dc:title">
            <xsl:attribute name="xsi:type">
              ddb:titleISO639-2
            </xsl:attribute>
            <xsl:attribute name="lang">
                <xsl:value-of select="@Language" />
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="PersonAuthor" mode="xmetadiss">
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

    <xsl:template match="SubjectDdc" mode="xmetadiss">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type">
                xMetaDiss:DDC-SG
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectSwd" mode="xmetadiss">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type">
                xMetaDiss:SWD
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="SubjectUncontrolled" mode="xmetadiss">
        <xsl:element name="dc:subject">
            <xsl:attribute name="xsi:type">
                xMetaDiss:noScheme
            </xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleAbstract" mode="xmetadiss">
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
          <xsl:attribute name="cc:Scheme">
             DIN5008
          </xsl:attribute>
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="PersonAdvisor" mode="xmetadiss">
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

    <xsl:template match="@CompletedDate" mode="xmetadiss">
        <xsl:element name="dcterms:dateAccepted">
          <xsl:attribute name="xsi:type">
             dcterms:W3CDTF
          </xsl:attribute>
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <!--xsl:template match="IdentifierIsbn|IdentifierUrn" mode="epicur"-->
    <xsl:template match="IdentifierUrn" mode="xmetadiss">
        <xsl:element name="identifier">
            <xsl:attribute name="xsi:type">urn:nbn</xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="@Language" mode="xmetadiss">
        <xsl:element name="dc:language">
           <xsl:attribute name="xsi:type">
               dcterms:ISO639-2
           </xsl:attribute>    
           <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Licence" mode="xmetadiss">
        <xsl:element name="dc:rights">
            <xsl:value-of select="@NameLong" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="IdentifierUrl" mode="xmetadiss">
        <xsl:element name="ddb:identifier">
            <xsl:attribute name="ddb:type">URL</xsl:attribute>
            <xsl:value-of select="@Value" />
        </xsl:element>
    </xsl:template>


</xsl:stylesheet>


<!-- folgende Felder sind unklar bzw. fehlen noch im Datenmodell bzw.
     in der xml-Darstellung
DDC-Sachgruppe : hier bisher bezeichnet mit SubjectDdc
SubjectSwd : freie Schlagwoerter tauchen in der XML-Darstellung
             doppelt auf, einmal als
             SubjectSwd Type=uncontrolled  und zweitens als
             SubjectUncontrolled
             Welches soll ich nehmen? Wird eines wegfallen?
             z.Zt. werden sie auch hier doppelt ausgegeben
PublisherName  : hier z.Zt. PublisherName, gesucht als Attribut
PublisherPlace : hier z.Zt. PublisherPlace, gesucht als Attribut
PublisherAddress: hier z.Zt. PublisherAddress, gesucht als Attribut
DateAccepted: hier z.Zt. CompletedDate (in Opus3 Tag der muendl. Pruefung)
ddb:PublType: hier fix ElectronicThesisandDissertation gesetzt
thesis:grantor in Opus 3 Fakultaet, hier???
ddb:file (fileNumber, fileName, fileID, fileSize, fileDirectory), 
          (Information ueber zum Dokument gehoerende Dateien)
          fehlt hier z.Zt. noch komplett

-->
