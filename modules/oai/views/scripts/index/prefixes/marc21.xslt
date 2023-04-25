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

    <xsl:template match="Opus_Document" mode="marc21">
        <marc:collection xmlns:marc="http://www.loc.gov/MARC21/slim"
                         xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">
            <marc:record>

                <xsl:variable name="aufnahmeart">
                    <xsl:choose>
                        <xsl:when test="./@Type='article'">                  <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='bachelorthesis'">           <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='book'">                     <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='bookpart'">                 <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceobject'">         <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='contributiontoperiodical'"> <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='coursematerial'">           <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='diplom'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='doctoralthesis'">           <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='examen'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='habilitation'">             <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='image'">                    <xsl:value-of select="'k'"/></xsl:when>
                        <xsl:when test="./@Type='lecture'">                  <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='magister'">                 <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='masterthesis'">             <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='movingimage'">              <xsl:value-of select="'g'"/></xsl:when>
                        <xsl:when test="./@Type='other'">                    <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='periodical'">               <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='periodicalpart'">           <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='preprint'">                 <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='report'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='review'">                   <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='sound'">                    <xsl:value-of select="'i'"/></xsl:when>
                        <xsl:when test="./@Type='studythesis'">              <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='workingpaper'">             <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:otherwise>
                            <xsl:text>a</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>

                <xsl:variable name="bibliographischesLevel">
                    <xsl:choose>
                        <xsl:when test="./@Type='article'">                  <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='bachelorthesis'">           <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='book'">                     <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='bookpart'">                 <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='conferenceobject'">         <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='contributiontoperiodical'"> <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='coursematerial'">           <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='diplom'">                   <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='doctoralthesis'">           <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='examen'">                   <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='habilitation'">             <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='image'">                    <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='lecture'">                  <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='magister'">                 <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='masterthesis'">             <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='movingimage'">              <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='other'">                    <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='periodical'">               <xsl:value-of select="'s'"/></xsl:when>
                        <xsl:when test="./@Type='periodicalpart'">           <xsl:value-of select="'a'"/></xsl:when>
                        <xsl:when test="./@Type='preprint'">                 <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='report'">                   <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='review'">                   <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='sound'">                    <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='studythesis'">              <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:when test="./@Type='workingpaper'">             <xsl:value-of select="'m'"/></xsl:when>
                        <xsl:otherwise>
                            <xsl:text>m</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>

                <!-- Reihenfolge Verwendung Jahr:
                    1.	CompletedYear
                    2.	CompletedDate
                    3.	PublishedYear
                    4.	PublishedDate
                    5.	ServerDatePublished (Fallback; ist immer vorhanden, wenn das Dokument freigeschaltet wurde)
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

                <xsl:if test="not(./TitleParent) and ./Identifier[@Type = 'isbn']">
                    <xsl:for-each select="./Identifier[@Type = 'isbn']">
                        <marc:datafield ind1=" " ind2=" " tag="020">
                            <marc:subfield code="a">
                                <xsl:value-of select="./@Value"/>
                            </marc:subfield>
                        </marc:datafield>
                    </xsl:for-each>
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
                        <xsl:choose>
                            <xsl:when test="./@Language='fra'">
                                <xsl:text>fre</xsl:text>
                            </xsl:when>
                            <xsl:when test="./@Language='deu'">
                                <xsl:text>ger</xsl:text>
                            </xsl:when>
                            <xsl:when test="./@Language='ces'">
                                <xsl:text>cze</xsl:text>
                            </xsl:when>
                            <xsl:when test="./@Language='slk'">
                                <xsl:text>slo</xsl:text>
                            </xsl:when>
                            <xsl:when test="./@Language='zho'">
                                <xsl:text>chi</xsl:text>
                            </xsl:when>
                            <xsl:when test="./@Language='nld'">
                                <xsl:text>dut</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="@Language"/>
                            </xsl:otherwise>
                        </xsl:choose>
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
                <xsl:apply-templates select="./PersonAuthor[position() = 1]" mode="marc21">
                    <xsl:with-param name="tag">100</xsl:with-param>
                    <xsl:with-param name="role">aut</xsl:with-param>
                </xsl:apply-templates>

                <!-- CreatingCorporation -->
                <xsl:if test="./@CreatingCorporation">
                    <marc:datafield ind1="2" ind2=" " tag="110">
                        <marc:subfield code="a">
                            <xsl:value-of select="./@CreatingCorporation"/>
                        </marc:subfield>
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

                <!-- Schlagworte -->
                <xsl:for-each select="Subject[@Type='uncontrolled' or @Type='swd']">
                    <marc:datafield ind1=" " ind2=" " tag="653">
                        <marc:subfield code="a">
                            <xsl:value-of select="./@Value"/>
                        </marc:subfield>
                    </marc:datafield>
                </xsl:for-each>

                <marc:datafield ind1=" " ind2="4" tag="655">
                    <marc:subfield code="a">
                        <xsl:choose>
                            <xsl:when test="./@Type='article'">                  <xsl:text>article</xsl:text></xsl:when>
                            <xsl:when test="./@Type='bachelorthesis'">           <xsl:text>bachelorThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='book'">                     <xsl:text>book</xsl:text></xsl:when>
                            <xsl:when test="./@Type='bookpart'">                 <xsl:text>bookPart</xsl:text></xsl:when>
                            <xsl:when test="./@Type='conferenceobject'">         <xsl:text>conferenceObject</xsl:text></xsl:when>
                            <xsl:when test="./@Type='contributiontoperiodical'"> <xsl:text>contributionToPeriodical</xsl:text></xsl:when>
                            <xsl:when test="./@Type='coursematerial'">           <xsl:text>CourseMaterial</xsl:text></xsl:when>
                            <xsl:when test="./@Type='diplom'">                   <xsl:text>masterThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='doctoralthesis'">           <xsl:text>doctoralThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='examen'">                   <xsl:text>masterThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='habilitation'">             <xsl:text>doctoralThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='image'">                    <xsl:text>Image</xsl:text></xsl:when>
                            <xsl:when test="./@Type='lecture'">                  <xsl:text>lecture</xsl:text></xsl:when>
                            <xsl:when test="./@Type='magister'">                 <xsl:text>masterThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='masterthesis'">             <xsl:text>masterThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='movingimage'">              <xsl:text>MovingImage</xsl:text></xsl:when>
                            <xsl:when test="./@Type='other'">                    <xsl:text>Other</xsl:text></xsl:when>
                            <xsl:when test="./@Type='periodical'">               <xsl:text>Periodical</xsl:text></xsl:when>
                            <xsl:when test="./@Type='periodicalpart'">           <xsl:text>PeriodicalPart</xsl:text></xsl:when>
                            <xsl:when test="./@Type='preprint'">                 <xsl:text>preprint</xsl:text></xsl:when>
                            <xsl:when test="./@Type='report'">                   <xsl:text>report</xsl:text></xsl:when>
                            <xsl:when test="./@Type='review'">                   <xsl:text>review</xsl:text></xsl:when>
                            <xsl:when test="./@Type='sound'">                    <xsl:text>Sound</xsl:text></xsl:when>
                            <xsl:when test="./@Type='studythesis'">              <xsl:text>StudyThesis</xsl:text></xsl:when>
                            <xsl:when test="./@Type='workingpaper'">             <xsl:text>workingPaper</xsl:text></xsl:when>
                            <xsl:otherwise>
                                <xsl:text>Other</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </marc:subfield>
                </marc:datafield>

                <!-- Sonstige Autoren -->
                <xsl:apply-templates select="./PersonAuthor[position() &gt; 1]" mode="marc21">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">aut</xsl:with-param>
                </xsl:apply-templates>

                <!-- PersonEditor -->
                <xsl:apply-templates select="./PersonEditor" mode="marc21">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">edt</xsl:with-param>
                </xsl:apply-templates>

                <!-- PersonAdvisor -->
                <xsl:apply-templates select="./PersonAdvisor" mode="marc21">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">dgs</xsl:with-param>
                </xsl:apply-templates>

                <!-- PersonContributor -->
                <xsl:apply-templates select="./PersonContributor" mode="marc21">
                    <xsl:with-param name="tag">700</xsl:with-param>
                    <xsl:with-param name="role">ctb</xsl:with-param>
                </xsl:apply-templates>

                <xsl:if test="((not(./TitleParent) or count(TitleParent) &gt; 1)) and (./@Volume or ./@Issue or (./@PageFirst or ./@PageLast))">
                    <marc:datafield ind1="0" ind2=" " tag="773">
                        <xsl:call-template name="subfieldG">
                            <xsl:with-param name="volume" select="./@Volume"/>
                            <xsl:with-param name="issue" select="./@Issue"/>
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
                        <xsl:if test="./Licence">
                            <marc:subfield code="z">
                                <xsl:value-of select="./Licence[1]/@NameLong"/>
                            </marc:subfield>
                        </xsl:if>
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
                        <xsl:if test="../Licence">
                            <marc:subfield code="z">
                                <xsl:value-of select="../Licence[1]/@NameLong"/>
                            </marc:subfield>
                        </xsl:if>
                    </marc:datafield>
                </xsl:for-each>
            </marc:record>
        </marc:collection>
    </xsl:template>

    <xsl:template match="PersonAuthor|PersonEditor|PersonAdvisor|PersonContributor" mode="marc21">
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
            <marc:subfield code="4">
                <xsl:value-of select="$role"/>
            </marc:subfield>
        </marc:datafield>
    </xsl:template>

    <xsl:template name="subfieldG">
        <xsl:param name="volume"/>
        <xsl:param name="issue"/>
        <xsl:param name="pageFirst"/>
        <xsl:param name="pageLast"/>
        <xsl:if test="$volume or $issue or ($pageFirst and $pageLast)">
            <marc:subfield code="g">
                <xsl:if test="$volume">
                    <xsl:text>Jahrgang </xsl:text>
                    <xsl:value-of select="$volume"/>
                    <xsl:if test="($issue) or (not($issue) and ($pageFirst and $pageLast))">
                        <xsl:text>, </xsl:text>
                    </xsl:if>
                </xsl:if>
                <xsl:if test="$issue">
                    <xsl:text>Heft </xsl:text>
                    <xsl:value-of select="$issue"/>
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

</xsl:stylesheet>
