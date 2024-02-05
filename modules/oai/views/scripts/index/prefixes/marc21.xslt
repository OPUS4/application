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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:marc="http://www.loc.gov/MARC21/slim"
                xmlns:php="http://php.net/xsl"
>
    <xsl:output method="xml" indent="yes"/>

    <xsl:template match="Opus_Document" >
        <marc:collection xmlns:marc="http://www.loc.gov/MARC21/slim"
                         xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">
            <marc:record>

                <xsl:variable name="aufnahmeart">
                    <xsl:choose>
                        <xsl:when test="./@Type='annotation'">               <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='article'">                  <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='bachelorthesis'">           <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='book'">                     <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='bookpart'">                 <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceabstract'">       <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceobject'">         <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferencepaper'">          <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceposter'">         <xsl:value-of select="'k'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceproceedings'">    <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceslides'">         <xsl:value-of select="'g'"/></xsl:when>
                        <xsl:when test="./@Type='contributiontoperiodical'"> <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='corrigendum'">              <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='coursematerial'">           <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='datapaper'">                <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='diplom'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='doctoralthesis'">           <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='dynamicwebresource'">       <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='dynamicwebresourcepart'">   <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='editedcollection'">         <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='editorial'">                <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='examen'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='habilitation'">             <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='image'">                    <xsl:value-of select="'k'"/></xsl:when>
                        <xsl:when test="./@Type='lecture'">                  <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='letter'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='magister'">                 <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='masterthesis'">             <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='monograph'">                <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='movingimage'">              <xsl:value-of select="'g'"/></xsl:when>
                        <xsl:when test="./@Type='musicalnotation'">          <xsl:value-of select="'c'"/></xsl:when>
                        <xsl:when test="./@Type='other'">                    <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='periodical'">               <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='periodicalpart'">           <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='preprint'">                 <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='report'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='researcharticle'">          <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='researchdata'">             <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='review'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='reviewarticle'">            <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='software'">                 <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='softwarepaper'">            <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='sound'">                    <xsl:value-of select="'i'"/></xsl:when>
                        <xsl:when test="./@Type='sourceedition'">            <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='studythesis'">              <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='website'">                  <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='workingpaper'">             <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:otherwise>
                            <xsl:text>a</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>

                <xsl:variable name="bibliographischesLevel">
                    <xsl:choose>
                        <xsl:when test="./@Type='annotation'">               <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='article'">                  <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='bachelorthesis'">           <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='book'">                     <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='bookpart'">                 <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceabstract'">       <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceobject'">         <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferencepaper'">          <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceposter'">         <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceproceedings'">    <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceslides'">         <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='contributiontoperiodical'"> <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='corrigendum'">              <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='coursematerial'">           <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='datapaper'">                <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='diplom'">                   <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='doctoralthesis'">           <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='dynamicwebresource'">       <xsl:value-of select="'i'"/></xsl:when>
                        <xsl:when test="./@Type='dynamicwebresourcepart'">   <xsl:value-of select="'i'"/></xsl:when>
                        <xsl:when test="./@Type='editedcollection'">         <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='editorial'">                <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='examen'">                   <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='letter'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='habilitation'">             <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='image'">                    <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='lecture'">                  <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='magister'">                 <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='masterthesis'">             <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='monograph'">                <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='movingimage'">              <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='musicalnotation'">          <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='other'">                    <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='periodical'">               <xsl:value-of select="'s'"/></xsl:when>
                        <xsl:when test="./@Type='periodicalpart'">           <xsl:value-of select="'b'"/></xsl:when>
                        <xsl:when test="./@Type='preprint'">                 <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='researcharticle'">          <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='researchdata'">             <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='report'">                   <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='review'">                   <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='reviewarticle'">            <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='software'">                 <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='softwarepaper'">            <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='sound'">                    <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='sourceedition'">            <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='studythesis'">              <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='website'">                  <xsl:value-of select="'i'"/></xsl:when>
                        <xsl:when test="./@Type='workingpaper'">             <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:otherwise>
                            <xsl:text>m</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                
                <xsl:variable name="monographisch">
                    <xsl:choose>
                        <xsl:when test="./@Type='bachelorthesis' or ./@Type='book' or ./@Type='conferenceproceedings' or ./@Type='doctoralthesis' or ./@Type='dynamicwebresource' or ./@Type='editedcollection' or ./@Type='habilitation' or ./@Type='masterthesis' or ./@Type='monograph' or ./@Type='periodical' or ./@Type='preprint' or ./@Type='report' or ./@Type='researchdata' or ./@Type='sourceedition' or ./@Type='studythesis' or ./@Type='workingpaper'">
                            <xsl:value-of select="1"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="0"/>
                        </xsl:otherwise>
                    </xsl:choose>               
                </xsl:variable>

                <!-- Reihenfolge Verwendung Jahr:
                    1.  CompletedYear
                    2.  CompletedDate
                    3.  PublishedYear
                    4.  PublishedDate
                    5.  ServerDatePublished (Fallback; ist immer vorhanden, wenn das Dokument freigeschaltet wurde)
                 -->
                <xsl:variable name="year">
                    <xsl:choose>
                        <xsl:when test="./@CompletedYear">
                            <xsl:value-of select="./@CompletedYear"/>
                        </xsl:when>
                        <xsl:when test="./CompletedDate/@Year">
                            <xsl:value-of select="./CompletedDate/@Year"/>
                        </xsl:when>
                        <xsl:when test="./@PublishedYear">
                            <xsl:value-of select="./@PublishedYear"/>
                        </xsl:when>
                        <xsl:when test="./PublishedDate/@Year">
                            <xsl:value-of select="./PublishedDate/@Year"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="./ServerDatePublished/@Year"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>

                <marc:leader>
                    <xsl:text>00000n</xsl:text>
                    <xsl:value-of select="$aufnahmeart"/>
                    <xsl:value-of select="$bibliographischesLevel"/>
                    <xsl:text> a22000005  4500</xsl:text>
                </marc:leader>

                <marc:controlfield tag="001">
                    <xsl:text>docId-</xsl:text>
                    <xsl:value-of select="@Id"/>
                </marc:controlfield>

                <!-- ISIL der Bibliothek-->
                <xsl:variable name="isil">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'isil', 'marc21')"/>
                </xsl:variable>
                <xsl:if test="$isil != ''">
                    <marc:controlfield tag="003">
                        <xsl:value-of select="$isil"/>
                    </marc:controlfield>
                </xsl:if>

                <marc:controlfield tag="007">
                    <xsl:text>cr uuu---uunan</xsl:text>
                </marc:controlfield>
                
                <xsl:variable name="sprachcode">
                    <xsl:choose>
                        <xsl:when test="./@Language='fra'">
                            <xsl:value-of select="'fre'"/>
                        </xsl:when>
                        <xsl:when test="./@Language='deu'">
                            <xsl:value-of select="'ger'"/>
                        </xsl:when>
                        <xsl:when test="./@Language='ces'">
                            <xsl:value-of select="'cze'"/>
                        </xsl:when>
                        <xsl:when test="./@Language='slk'">
                            <xsl:value-of select="'slo'"/>
                        </xsl:when>
                        <xsl:when test="./@Language='zho'">
                            <xsl:value-of select="'chi'"/>
                        </xsl:when>
                        <xsl:when test="./@Language='nld'">
                            <xsl:value-of select="'dut'"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="@Language"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                
                <marc:controlfield tag="008">
                    <!-- Pos. 00-05: Generierungsdatum des Datensatzes -->
                    <xsl:value-of select="substring(./ServerDateCreated/@Year,3,2)"/>
                    <xsl:value-of select="./ServerDateCreated/@Month"/>
                    <xsl:value-of select="./ServerDateCreated/@Day"/>
                    <!-- Pos. 06 s, 07-10: (vermutl.) Erscheinungsjahr -->
                    <xsl:text>s</xsl:text>
                    <xsl:value-of select="$year"/>
                    <xsl:text>    ||||||||||||||||||||</xsl:text>
                    <!-- Pos. 35-37 Sprachcode (analog zu 041) -->
                    <xsl:value-of select="$sprachcode"/>
                    <xsl:text>||</xsl:text>
                </marc:controlfield>
                
                <xsl:if test="./Identifier[@Type = 'isbn']">
                    <xsl:if test="$monographisch = 1 or not(./TitleParent)">
                        <xsl:for-each select="./Identifier[@Type = 'isbn']">
                            <marc:datafield ind1=" " ind2=" " tag="020">
                                <marc:subfield code="a">
                                    <xsl:value-of select="./@Value"/>
                                </marc:subfield>
                            </marc:datafield>
                        </xsl:for-each>
                    </xsl:if>
                </xsl:if>

                <xsl:if test="not(./TitleParent) and ./Identifier[@Type = 'issn']">
                    <xsl:for-each select="./Identifier[@Type = 'issn']">
                        <marc:datafield ind1=" " ind2=" " tag="022">
                            <marc:subfield code="a">
                                <xsl:value-of select="./@Value"/>
                            </marc:subfield>
                        </marc:datafield>
                    </xsl:for-each>
                </xsl:if>
                
                <xsl:if test="./Identifier[@Type = 'doi']">
                    <marc:datafield ind1="7" ind2=" " tag="024">
                        <marc:subfield code="a">
                            <xsl:value-of select="./Identifier[@Type = 'doi']/@Value"/>
                        </marc:subfield>
                        <marc:subfield code="2">
                            <xsl:text>doi</xsl:text>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:if>

                <xsl:if test="./Identifier[@Type = 'urn']">
                    <marc:datafield ind1="7" ind2=" " tag="024">
                        <marc:subfield code="a">
                            <xsl:value-of select="./Identifier[@Type = 'urn']/@Value"/>
                        </marc:subfield>
                        <marc:subfield code="2">
                            <xsl:text>urn</xsl:text>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:if>

                <marc:datafield ind1=" " ind2=" " tag="041">
                    <marc:subfield code="a">
                        <xsl:value-of select="$sprachcode"/>
                    </marc:subfield>
                </marc:datafield>

                <!-- DDC -->
                <xsl:for-each select="./Collection[@RoleName='ddc' and @Visible = 1]">
                    <marc:datafield ind1="0" ind2="4" tag="082">
                        <!-- alternativ bei DDC-Sachgruppen der DNB: <marc:datafield ind1="7" ind2="4" tag="082"> -->
                        <marc:subfield code="a">
                            <xsl:value-of select="@Number"/>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:for-each>

                <!-- PersonAutor, nur der erste Autor -->
                <xsl:apply-templates select="./PersonAuthor[position() = 1]">
                    <xsl:with-param name="tag">100</xsl:with-param>
                    <xsl:with-param name="role">aut</xsl:with-param>
                </xsl:apply-templates>

                <!-- CreatingCorporation, erste Hierarchieebene ins Unterfeld $a, weitere in einzelne $b-Unterfelder -->
                <xsl:if test="./@CreatingCorporation">
                    <marc:datafield ind1="2" ind2=" " tag="110">
                        <xsl:choose>
                            <xsl:when test="contains(./@CreatingCorporation,'. ')">
                                <marc:subfield code="a">
                                    <xsl:value-of select="substring-before(./@CreatingCorporation,'. ')"/>
                                </marc:subfield>
                                <xsl:call-template name="split-string">
                                    <xsl:with-param name="input" select="substring-after(./@CreatingCorporation,'. ')"/>
                                    <xsl:with-param name="substr" select="'.'"/>
                                </xsl:call-template>
                            </xsl:when>
                            <xsl:otherwise>
                                <marc:subfield code="a">
                                    <xsl:value-of select="./@CreatingCorporation"/>
                                </marc:subfield>                            
                            </xsl:otherwise>
                        </xsl:choose>
                    </marc:datafield>
                </xsl:if>

                <!-- TitleMain in Dokumentsprache-->
                <xsl:if test="./TitleMain[@Language = ../@Language]">
                    <marc:datafield ind1="0" ind2="0" tag="245">
                        <marc:subfield code="a">
                            <xsl:value-of select="./TitleMain[@Language = ../@Language]/@Value"/>
                        </marc:subfield>
                        <xsl:if test="./TitleSub[@Language = ../@Language]">
                            <marc:subfield code="b">
                                <xsl:value-of select="./TitleSub[@Language = ../@Language]/@Value"/>
                            </marc:subfield>
                        </xsl:if>
                    </marc:datafield>
                </xsl:if>

                <!-- Berücksichtigung aller TitleMain, die nicht in Dokumentsprache vorliegen -->
                <xsl:if test="count(./TitleMain) &gt; 1">
                    <xsl:for-each select="./TitleMain[@Language != ../@Language]">
                        <xsl:variable name="lang">
                            <xsl:value-of select="@Language"/>
                        </xsl:variable>
                        <marc:datafield ind1="1" ind2="1" tag="246">
                            <marc:subfield code="a">
                                <xsl:value-of select="@Value"/>
                            </marc:subfield>
                            <xsl:if test="../TitleSub[@Language = $lang]">
                                <marc:subfield code="b">
                                    <xsl:value-of select="../TitleSub[@Language = $lang]/@Value"/>
                                </marc:subfield>
                            </xsl:if>
                        </marc:datafield>
                    </xsl:for-each>
                </xsl:if>
                
                <xsl:if test="./@Edition">
                    <marc:datafield ind1=" " ind2=" " tag="250">
                        <marc:subfield code="a">
                            <xsl:value-of select="./@Edition"/>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:if>

                <xsl:variable name="publisherName">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'publisherName', 'marc21')"/>
                </xsl:variable>
                <xsl:variable name="publisherCity">
                    <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'publisherCity', 'marc21')"/>
                </xsl:variable>

                <!-- Jahr nur beim ersten ThesisPublisher ausgeben -->
                <xsl:if test="./ThesisPublisher or ./@PublisherName or ./@PublisherPlace or $publisherName != '' or $publisherCity != '' or $year != ''">
                    <marc:datafield ind1=" " ind2="1" tag="264"> <!-- Das Feld 260 wird mit der Einführung von RDA nicht mehr gebildet -->
                        <xsl:choose>
                            <xsl:when test="./ThesisPublisher"><!-- City und Name sind Pflichtfelder, daher kein zusätzlicher Test -->
                                <marc:subfield code="a">
                                    <xsl:value-of select="./ThesisPublisher[1]/@City"/>
                                </marc:subfield>
                                <marc:subfield code="b">
                                    <xsl:value-of select="./ThesisPublisher[1]/@Name"/>
                                </marc:subfield>
                            </xsl:when>
                            <xsl:when test="./@PublisherName or ./@PublisherPlace">
                                <xsl:if test="./@PublisherPlace">
                                    <marc:subfield code="a">
                                        <xsl:value-of select="./@PublisherPlace"/>
                                    </marc:subfield>
                                </xsl:if>
                                <xsl:if test="./@PublisherName">
                                    <marc:subfield code="b">
                                        <xsl:value-of select="./@PublisherName"/>
                                    </marc:subfield>
                                </xsl:if>
                            </xsl:when>
                            <xsl:otherwise>
                                <!-- Fallback nur dann verwenden, wenn bislang kein Wert für Subfield a oder b erzeugt werden konnte -->
                                <xsl:if test="$publisherCity != ''">
                                    <marc:subfield code="a">
                                        <xsl:value-of select="$publisherCity"/>
                                    </marc:subfield>
                                </xsl:if>
                                <xsl:if test="$publisherName != ''">
                                    <marc:subfield code="b">
                                        <xsl:value-of select="$publisherName"/>
                                    </marc:subfield>
                                </xsl:if>
                            </xsl:otherwise>
                        </xsl:choose>

                        <xsl:if test="$year != ''">
                            <marc:subfield code="c">
                                <xsl:value-of select="$year"/>
                            </marc:subfield>
                        </xsl:if>
                    </marc:datafield>
                </xsl:if>

                <!-- ab dem 2. ThesisPublisher keine Jahresangabe in 264c ausgeben -->
                <xsl:if test="count(./ThesisPublisher) &gt; 1">
                    <xsl:for-each select="./ThesisPublisher">
                        <xsl:if test="position() != 1">
                            <marc:datafield ind1=" " ind2="1" tag="264">
                                <!-- City und Name sind Pflichtfelder, daher kein zusätzlicher Test -->
                                <marc:subfield code="a">
                                    <xsl:value-of select="@City"/>
                                </marc:subfield>
                                <marc:subfield code="b">
                                    <xsl:value-of select="@Name"/>
                                </marc:subfield>
                            </marc:datafield>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:if>

                <!-- Seitenanzahl -->
                <xsl:if test="./@PageNumber">
                    <marc:datafield ind1=" " ind2=" " tag="300">
                        <marc:subfield code="a">
                            <xsl:value-of select="./@PageNumber"/>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:if>

                <!-- Schriftenreihen -->
                <xsl:for-each select="./Series">
                    <xsl:if test="./@Visible = 1">
                        <marc:datafield ind1="1" ind2=" " tag="490">
                            <marc:subfield code="a">
                                <xsl:value-of select="./@Title"/>
                            </marc:subfield>
                            <marc:subfield code="v">
                                <xsl:value-of select="./@Number"/>
                            </marc:subfield>
                        </marc:datafield>
                    </xsl:if>
                </xsl:for-each>
                
                <!-- TitleParent bei monographischen Dokumenttypen als Schriftenreihe ausgeben -->
                <xsl:if test="$monographisch = 1">
                    <xsl:for-each select="./TitleParent">
                        <marc:datafield ind1="0" ind2=" " tag="490">
                            <marc:subfield code="a">
                                <xsl:choose>
                                    <xsl:when test="contains(./@Value,' ; ')">
                                        <xsl:value-of select="substring-before(./@Value,' ; ')"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="./@Value"/>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </marc:subfield>
                            <xsl:if test="substring-after(./@Value,' ; ') != ''">
                                <marc:subfield code="v">
                                    <xsl:value-of select="substring-after(./@Value,' ; ')"/>
                                </marc:subfield>
                            </xsl:if>
                        </marc:datafield>
                    </xsl:for-each>                 
                </xsl:if>
                
                <!-- Oeffentlich sichtbare Bemerkungen -->
                <xsl:for-each select="./Note[@Visibility='public']">
                    <marc:datafield ind1=" " ind2=" " tag="500">
                        <marc:subfield code="a">
                            <xsl:value-of select="./@Message"/>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:for-each>
                
                <!-- Hochschulschriftenvermerk -->
                <xsl:variable name="diniType">
                    <xsl:value-of select="php:functionString('Application_Xslt::dcType', @Type)" />
                </xsl:variable>

                <xsl:if test="(contains($diniType, 'Thesis') or $diniType = 'Habilitation') and $diniType != 'StudyThesis'">
                    <marc:datafield ind1=" " ind2=" " tag="502">
                    
                        <!-- Degree -->
                        <marc:subfield code="b">
                            <xsl:value-of select="$diniType"/>
                        </marc:subfield>
                        
                        <!-- ThesisGrantor -->
                        <marc:subfield code="c">
                            <xsl:choose>
                                <xsl:when test="./ThesisGrantor">
                                    <xsl:value-of select="./ThesisGrantor[1]/@Name"/>
                                </xsl:when>
                                <xsl:when test="./ThesisPublisher">
                                    <xsl:value-of select="./ThesisPublisher[1]/@Name"/>
                                </xsl:when>
                                <xsl:when test="./@PublisherName">
                                    <xsl:value-of select="./@PublisherName"/>
                                </xsl:when>
                                <xsl:when test="$publisherName != ''">
                                    <xsl:value-of select="$publisherName"/>
                                </xsl:when>
                            </xsl:choose>
                        </marc:subfield>
                        
                        <!-- Year -->
                        <marc:subfield code="d">
                            <xsl:choose>
                                <xsl:when test="./@ThesisYearAccepted">
                                    <xsl:value-of select="./@ThesisYearAccepted"/>
                                </xsl:when>
                                <xsl:when test="./ThesisDateAccepted">
                                    <xsl:value-of select="./ThesisDateAccepted/@Year"/>
                                </xsl:when>
                                <xsl:when test="$year != ''">
                                    <xsl:value-of select="$year"/>
                                </xsl:when>
                            </xsl:choose>
                        </marc:subfield>
                    </marc:datafield>

                    <!-- ab dem 2. ThesisGrantor nur Name der Titel-verleihenden Institution in 502c ausgeben -->
                    <xsl:if test="count(./ThesisGrantor) &gt; 1">
                        <xsl:for-each select="./ThesisGrantor">
                            <xsl:if test="position() != 1">
                                <marc:datafield ind1=" " ind2=" " tag="502">
                                    <marc:subfield code="c">
                                        <xsl:value-of select="@Name"/>
                                    </marc:subfield>
                                </marc:datafield>
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:if>
                </xsl:if>                    

                <!-- TitleAbstract in Dokumentsprache zuerst ausgeben -->
                <xsl:if test="./TitleAbstract[@Language = ../@Language]">
                    <marc:datafield ind1=" " ind2=" " tag="520">
                        <marc:subfield code="a">
                            <xsl:value-of select="./TitleAbstract[@Language = ../@Language]/@Value"/>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:if>

                <!-- Behandlung von allen weiteren TitleAbstracs -->
                <xsl:for-each select="./TitleAbstract[@Language != ../@Language]">
                    <marc:datafield ind1=" " ind2=" " tag="520">
                        <marc:subfield code="a">
                            <xsl:value-of select="./@Value"/>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:for-each>

                <!-- Lizenzangabe -->
                <xsl:if test="./Licence">                                                   
                    <marc:datafield ind1=" " ind2=" " tag="540">
                        <marc:subfield code="a">
                            <xsl:value-of select="./Licence[1]/@NameLong"/>
                        </marc:subfield>
                        <xsl:if test="./Licence[1]/@Name">
                            <marc:subfield code="f">
                                <xsl:value-of select="./Licence[1]/@Name"/>
                            </marc:subfield>
                        </xsl:if>
                        <marc:subfield code="u">
                            <xsl:value-of select="./Licence[1]/@LinkLicence"/>
                        </marc:subfield>
                        <xsl:if test="contains(./Licence[1]/@LinkLicence,'creativecommons') or contains(./Licence[1]/@LinkLicence,'rightsstatements')">
                            <marc:subfield code="2">
                                <xsl:choose>
                                    <xsl:when test="contains(./Licence[1]/@LinkLicence,'creativecommons')">
                                        <xsl:text>cc</xsl:text>
                                    </xsl:when>
                                    <xsl:when test="contains(./Licence[1]/@LinkLicence,'rightsstatements')">
                                        <xsl:text>rs</xsl:text>
                                    </xsl:when>
                                </xsl:choose>
                            </marc:subfield>
                        </xsl:if>
                    </marc:datafield>
                </xsl:if>

                <!-- Schlagwoerter -->
                <xsl:for-each select="Subject[@Type='uncontrolled' or @Type='swd']">
                    <xsl:choose>
                        <xsl:when test="@Type='swd' and @ExternalKey">
                            <marc:datafield ind1=" " ind2="7" tag="650">
                                <marc:subfield code="a">
                                    <xsl:value-of select="./@Value"/>
                                </marc:subfield>
                                <marc:subfield code="0">
                                    <xsl:text>(DE-588)</xsl:text>
                                    <xsl:value-of select="./@ExternalKey"/>
                                </marc:subfield>
                                <marc:subfield code="2">gnd</marc:subfield>
                            </marc:datafield>
                        </xsl:when>
                        <xsl:otherwise>
                            <marc:datafield ind1=" " ind2=" " tag="653">
                                <marc:subfield code="a">
                                    <xsl:value-of select="./@Value"/>
                                </marc:subfield>
                            </marc:datafield>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>

                <marc:datafield ind1=" " ind2="4" tag="655">
                    <marc:subfield code="a">
                        <xsl:choose>
                            <xsl:when test="./@Type='annotation'">               <xsl:text>Annotation</xsl:text></xsl:when>
                            <xsl:when test="./@Type='article'">                  <xsl:text>Article</xsl:text></xsl:when>
                            <xsl:when test="./@Type='bachelorthesis'">           <xsl:text>BachelorThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='book'">                     <xsl:text>Book</xsl:text></xsl:when>
                            <xsl:when test="./@Type='bookpart'">                 <xsl:text>BookPart</xsl:text></xsl:when>
                            <xsl:when test="./@Type='conferenceabstract'">       <xsl:text>MeetingAbstract</xsl:text></xsl:when>
                            <xsl:when test="./@Type='conferenceobject'">         <xsl:text>ConferenceObject</xsl:text></xsl:when>
                            <xsl:when test="./@Type='conferencepaper'">          <xsl:text>ConferencePaper</xsl:text></xsl:when>
                            <xsl:when test="./@Type='conferenceposter'">         <xsl:text>ConferencePoster</xsl:text></xsl:when>
                            <xsl:when test="./@Type='conferenceproceedings'">    <xsl:text>ConferenceProceedings</xsl:text></xsl:when>
                            <xsl:when test="./@Type='conferenceslides'">         <xsl:text>ConferenceSlides</xsl:text></xsl:when>
                            <xsl:when test="./@Type='contributiontoperiodical'"> <xsl:text>ContributionToPeriodical</xsl:text></xsl:when>
                            <xsl:when test="./@Type='corrigendum'">              <xsl:text>Corrigendum</xsl:text></xsl:when>
                            <xsl:when test="./@Type='coursematerial'">           <xsl:text>CourseMaterial</xsl:text></xsl:when>
                            <xsl:when test="./@Type='datapaper'">                <xsl:text>DataPaper</xsl:text></xsl:when>
                            <xsl:when test="./@Type='diplom'">                   <xsl:text>MasterThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='doctoralthesis'">           <xsl:text>PhDThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='dynamicwebresource'">       <xsl:text>DynamicWebResource</xsl:text></xsl:when>
                            <xsl:when test="./@Type='dynamicwebresourcepart'">   <xsl:text>PartOfADynamicWebResource</xsl:text></xsl:when>
                            <xsl:when test="./@Type='editedcollection'">         <xsl:text>EditedCollection</xsl:text></xsl:when>
                            <xsl:when test="./@Type='editorial'">                <xsl:text>Editorial</xsl:text></xsl:when>
                            <xsl:when test="./@Type='examen'">                   <xsl:text>MasterThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='habilitation'">             <xsl:text>Habilitation</xsl:text></xsl:when>
                            <xsl:when test="./@Type='image'">                    <xsl:text>Image</xsl:text></xsl:when>
                            <xsl:when test="./@Type='lecture'">                  <xsl:text>Lecture</xsl:text></xsl:when>
                            <xsl:when test="./@Type='letter'">                   <xsl:text>LetterToTheEditor</xsl:text></xsl:when>
                            <xsl:when test="./@Type='magister'">                 <xsl:text>MasterThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='masterthesis'">             <xsl:text>MasterThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='monograph'">                <xsl:text>Monograph</xsl:text></xsl:when>
                            <xsl:when test="./@Type='movingimage'">              <xsl:text>MovingImage</xsl:text></xsl:when>
                            <xsl:when test="./@Type='musicalnotation'">          <xsl:text>MusicalNotation</xsl:text></xsl:when>
                            <xsl:when test="./@Type='other'">                    <xsl:text>Other</xsl:text></xsl:when>                            
                            <xsl:when test="./@Type='periodical'">               <xsl:text>Periodical</xsl:text></xsl:when>
                            <xsl:when test="./@Type='periodicalpart'">           <xsl:text>PeriodicalPart</xsl:text></xsl:when>
                            <xsl:when test="./@Type='preprint'">                 <xsl:text>Preprint</xsl:text></xsl:when>
                            <xsl:when test="./@Type='report'">                   <xsl:text>Report</xsl:text></xsl:when>
                            <xsl:when test="./@Type='researcharticle'">          <xsl:text>ResearchArticle</xsl:text></xsl:when>
                            <xsl:when test="./@Type='researchdata'">             <xsl:text>ResearchData</xsl:text></xsl:when>
                            <xsl:when test="./@Type='review'">                   <xsl:text>Recension</xsl:text></xsl:when>
                            <xsl:when test="./@Type='reviewarticle'">            <xsl:text>ReviewArticle</xsl:text></xsl:when>
                            <xsl:when test="./@Type='software'">                 <xsl:text>Software</xsl:text></xsl:when>
                            <xsl:when test="./@Type='softwarepaper'">            <xsl:text>SoftwarePaper</xsl:text></xsl:when>
                            <xsl:when test="./@Type='sound'">                    <xsl:text>Sound</xsl:text></xsl:when>
                            <xsl:when test="./@Type='sourceedition'">            <xsl:text>SourceEdition</xsl:text></xsl:when>
                            <xsl:when test="./@Type='studythesis'">              <xsl:text>StudyThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='website'">                  <xsl:text>Website</xsl:text></xsl:when>
                            <xsl:when test="./@Type='workingpaper'">             <xsl:text>WorkingPaper</xsl:text></xsl:when>
                            <xsl:otherwise>
                                <xsl:text>Other</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </marc:subfield>
                </marc:datafield>

                <!-- Sonstige Autoren -->
                <xsl:apply-templates select="./PersonAuthor[position() &gt; 1]">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">aut</xsl:with-param>
                </xsl:apply-templates>

                <!-- PersonEditor -->
                <xsl:apply-templates select="./PersonEditor">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">edt</xsl:with-param>
                </xsl:apply-templates>

                <!-- PersonAdvisor -->
                <xsl:apply-templates select="./PersonAdvisor">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">ths</xsl:with-param>
                </xsl:apply-templates>

                <!-- PersonReferee -->
                <xsl:apply-templates select="./PersonReferee">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">dgs</xsl:with-param>
                </xsl:apply-templates>

                <!-- PersonContributor -->
                <xsl:apply-templates select="./PersonContributor">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">ctb</xsl:with-param>
                </xsl:apply-templates>
                
                <!-- PersonTranslator -->
                <xsl:apply-templates select="./PersonTranslator">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">trl</xsl:with-param>
                </xsl:apply-templates>

                <!-- PersonOther -->
                <xsl:apply-templates select="./PersonOther">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">oth</xsl:with-param>
                </xsl:apply-templates>
                
                <!-- ContributingCorporation, erste Hierarchieebene ins Unterfeld $a, weitere in einzelne $b-Unterfelder -->
                <xsl:if test="./@ContributingCorporation">
                    <marc:datafield ind1="2" ind2=" " tag="710">
                        <xsl:choose>
                            <xsl:when test="contains(./@ContributingCorporation,'. ')">
                                <marc:subfield code="a">
                                    <xsl:value-of select="substring-before(./@ContributingCorporation,'. ')"/>
                                </marc:subfield>
                                <xsl:call-template name="split-string">
                                    <xsl:with-param name="input" select="substring-after(./@ContributingCorporation,'. ')"/>
                                    <xsl:with-param name="substr" select="'.'"/>
                                </xsl:call-template>
                            </xsl:when>
                            <xsl:otherwise>
                                <marc:subfield code="a">
                                    <xsl:value-of select="./@ContributingCorporation"/>
                                </marc:subfield>                            
                            </xsl:otherwise>
                        </xsl:choose>
                    </marc:datafield>
                </xsl:if>

                <xsl:if test="$monographisch = 0">
                    <xsl:if test="((not(./TitleParent) or count(TitleParent) &gt; 1)) and (./@Volume or ./@Issue or (./@PageFirst or ./@PageLast))">
                        <marc:datafield ind1="0" ind2=" " tag="773">
                            <xsl:call-template name="subfieldG">
                                <xsl:with-param name="volume" select="./@Volume"/>
                                <xsl:with-param name="issue" select="./@Issue"/>
                                <xsl:with-param name="articleNumber" select="./@ArticleNumber"/>
                                <xsl:with-param name="pageFirst" select="./@PageFirst"/>
                                <xsl:with-param name="pageLast" select="./@PageLast"/>
                            </xsl:call-template>
                        </marc:datafield>
                    </xsl:if>

                    <!-- Behandlung von TitleParent (genau ein TitleParent): TitleParent und Volume, Issue, Pages in gemeinsames 773-Feld -->
                    <xsl:if test="count(./TitleParent) = 1">
                        <marc:datafield ind1="0" ind2=" " tag="773">
                            <marc:subfield code="t">
                                <xsl:value-of select="./TitleParent/@Value"/>
                            </marc:subfield>
                            <xsl:call-template name="subfieldG">
                                <xsl:with-param name="volume" select="./@Volume"/>
                                <xsl:with-param name="issue" select="./@Issue"/>
                                <xsl:with-param name="articleNumber" select="./@ArticleNumber"/>
                                <xsl:with-param name="pageFirst" select="./@PageFirst"/>
                                <xsl:with-param name="pageLast" select="./@PageLast"/>
                            </xsl:call-template>
                            <xsl:if test="(count(./Identifier[@Type = 'issn']) = 1) and not(./Identifier[@Type = 'isbn'])">
                                <marc:subfield code="x">
                                    <xsl:value-of select="./Identifier[@Type = 'issn']/@Value"/>
                                </marc:subfield>
                            </xsl:if>
                            <xsl:if test="(count(./Identifier[@Type = 'isbn']) = 1) and not (./Identifier[@Type = 'issn'])">
                                <marc:subfield code="z">
                                    <xsl:value-of select="./Identifier[@Type = 'isbn']/@Value"/>
                                </marc:subfield>
                            </xsl:if>
                        </marc:datafield>
                    </xsl:if>
                    
                    <!-- Behandlung von TitleParent (mehr als ein TitleParent): jeder TitleParent in ein eigenes 773-Feld -->
                    <xsl:if test="count(./TitleParent) &gt; 1">
                        <xsl:for-each select="./TitleParent">
                            <marc:datafield ind1="0" ind2=" " tag="773">
                                <marc:subfield code="t">
                                    <xsl:value-of select="./@Value"/>
                                </marc:subfield>
                            </marc:datafield>
                        </xsl:for-each>
                    </xsl:if>

                    <!-- beim Vorhandensein mindestens eines TitleParent: ISSNs werden einzeln ausgegeben,
                         wenn es mehr als eine gibt oder mehr als einen TitleParent oder gleichzeitig eine ISBN existiert -->
                    <xsl:if test="./TitleParent and not((count(./TitleParent) = 1) and (count(./Identifier[@Type = 'issn']) = 1) and not(./Identifier[@Type = 'isbn']))">
                        <xsl:for-each select="./Identifier[@Type = 'issn']">
                            <marc:datafield ind1="0" ind2=" " tag="773">
                                <marc:subfield code="x">
                                    <xsl:value-of select="./@Value"/>
                                </marc:subfield>
                            </marc:datafield>
                        </xsl:for-each>
                    </xsl:if>

                    <!-- beim Vorhandensein mindestens eines TitleParent: ISBNs werden einzeln ausgegeben,
                         wenn es mehr als eine gibt oder mehr als einen TitleParent oder gleichzeitig eine ISSN existiert -->
                    <xsl:if test="./TitleParent and not((count(./TitleParent) = 1) and (count(./Identifier[@Type = 'isbn']) = 1) and not(./Identifier[@Type = 'issn']))">
                        <xsl:for-each select="./Identifier[@Type = 'isbn']">
                            <marc:datafield ind1="0" ind2=" " tag="773">
                                <marc:subfield code="z">
                                    <xsl:value-of select="./@Value"/>
                                </marc:subfield>
                            </marc:datafield>
                        </xsl:for-each>
                    </xsl:if>
                </xsl:if>

                <xsl:if test="./Identifier[@Type = 'urn']">
                    <marc:datafield ind1="4" ind2="0" tag="856">
                        <marc:subfield code="u">
                            <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'resolverUrl', 'urn')"/>
                            <xsl:value-of select="./Identifier[@Type = 'urn']/@Value"/>
                        </marc:subfield>
                        <marc:subfield code="x">Resolving-URL</marc:subfield>
                    </marc:datafield>
                </xsl:if>

                <marc:datafield ind1="4" ind2="0" tag="856">
                    <marc:subfield code="u">
                        <xsl:value-of select="php:functionString('Application_Xslt::frontdoorUrl', @Id)"/>
                    </marc:subfield>
                    <marc:subfield code="q">
                        <xsl:text>text/html</xsl:text>
                    </marc:subfield>
                    <marc:subfield code="x">
                        <xsl:text>Frontdoor-URL</xsl:text>
                    </marc:subfield>
                </marc:datafield>

                <!-- wenn mindestens eine Datei (mit visibleInOai = 1) vorhanden ist, erzeuge Transfer-URL für den OAI-Container -->
                <xsl:if test="./File[@VisibleInOai = 1]">
                    <marc:datafield ind1="4" ind2="0" tag="856">
                        <marc:subfield code="u">
                            <xsl:value-of select="php:functionString('Application_Xslt::transferUrl', ./@Id)"/>
                        </marc:subfield>
                        <marc:subfield code="x">
                            <xsl:text>Transfer-URL</xsl:text>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:if>

                <xsl:for-each select="./File[@VisibleInOai = 1]">
                    <marc:datafield ind1="4" ind2="0" tag="856">
                        <marc:subfield code="u">
                            <xsl:value-of select="php:functionString('Application_Xslt::fileUrl', ../@Id, ./@PathName)"/>
                        </marc:subfield>
                        <xsl:if test="./@MimeType">
                            <marc:subfield code="q">
                                <xsl:value-of select="./@MimeType"/>
                            </marc:subfield>
                        </xsl:if>
                    </marc:datafield>
                </xsl:for-each>
            </marc:record>
        </marc:collection>
    </xsl:template>

    <xsl:template match="PersonAuthor|PersonEditor|PersonAdvisor|PersonReferee|PersonContributor|PersonTranslator|PersonOther">
        <xsl:param name="tag"/>
        <xsl:param name="role"/>
        <marc:datafield ind1="1" ind2=" " tag='{$tag}'>
            <marc:subfield code="a">
                <xsl:value-of select="@LastName"/>
                <xsl:if test="@FirstName">
                    <xsl:text>, </xsl:text>
                    <xsl:value-of select="@FirstName"/>
                </xsl:if>
            </marc:subfield>
            <xsl:if test="@IdentifierGnd">
                <marc:subfield code="0">
                    <xsl:text>(DE-588)</xsl:text>
                    <xsl:value-of select="@IdentifierGnd"/>
                </marc:subfield>
            </xsl:if>
            <xsl:if test="@IdentifierOrcid">
                <marc:subfield code="0">
                    <xsl:text>(orcid)</xsl:text>
                    <xsl:value-of select="@IdentifierOrcid"/>
                </marc:subfield>
            </xsl:if>
            <marc:subfield code="4">
                <xsl:value-of select="$role"/>
            </marc:subfield>
        </marc:datafield>
    </xsl:template>

    <xsl:template name="subfieldG">
        <xsl:param name="volume"/>
        <xsl:param name="issue"/>
        <xsl:param name="articleNumber"/>
        <xsl:param name="pageFirst"/>
        <xsl:param name="pageLast"/>
        <xsl:if test="$volume or $issue or $articleNumber or ($pageFirst and $pageLast)">
            <marc:subfield code="g">
                <xsl:if test="$volume">
                    <xsl:text>Jahrgang </xsl:text>
                    <xsl:value-of select="$volume"/>
                    <xsl:if test="$issue or $articleNumber or ($pageFirst and $pageLast)">
                        <xsl:text>, </xsl:text>
                    </xsl:if>
                </xsl:if>
                <xsl:if test="$issue">
                    <xsl:text>Heft </xsl:text>
                    <xsl:value-of select="$issue"/>
                    <xsl:if test="$articleNumber or ($pageFirst and $pageLast)">
                        <xsl:text>, </xsl:text>
                    </xsl:if>
                </xsl:if>
                <xsl:if test="$articleNumber">
                    <xsl:text>Aufsatznummer </xsl:text>
                    <xsl:value-of select="$articleNumber"/>
                    <xsl:if test="$pageFirst and $pageLast">
                        <xsl:text>, </xsl:text>
                    </xsl:if>
                </xsl:if>
                <xsl:if test="$pageFirst and $pageLast">
                    <xsl:text>Seiten </xsl:text>
                    <xsl:value-of select="$pageFirst"/>
                    <xsl:text>-</xsl:text>
                    <xsl:value-of select="$pageLast"/>
                </xsl:if>
            </marc:subfield>
        </xsl:if>
    </xsl:template>

    <!-- Unterhierarchien von Koerperschaften in einzelne $b-Unterfelder aufsplitten -->
    <xsl:template name="split-string">
        <xsl:param name="input"/>
        <xsl:param name="substr"/>    
        <xsl:variable name="hit" select="substring-before($input,$substr)"/> <!-- Erster Teilstring, bis zum ersten Punkt -->
        <xsl:variable name="temp" select="substring-after($input,$substr)"/> <!-- Rest -->
        <marc:subfield code="b">
            <xsl:value-of select="normalize-space($hit)"/>
        </marc:subfield>
        <xsl:choose>        
            <xsl:when test="$substr and contains($temp,$substr)"> <!-- Falls der Indikator weiter enthalten ist, erfolgt weitere rekursive Verarbeitung -->
                <xsl:call-template name="split-string"> <!-- Template ruft sich selbst auf -->
                    <xsl:with-param name="input" select="$temp"/>
                    <xsl:with-param name="substr" select="$substr"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <marc:subfield code="b">
                    <xsl:value-of select="normalize-space($temp)"/>
                </marc:subfield>
            </xsl:otherwise>
        </xsl:choose>  
    </xsl:template>

</xsl:stylesheet>
