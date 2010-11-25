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
 * @package     Module_Import
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: opus3.xslt 5665 2010-09-21 12:54:10Z gmaiwald $
 */
-->

<xsl:stylesheet version="1.0"
    	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    	xmlns:bibtex="http://bibtexml.sf.net/"
        xmlns:php="http://php.net/xsl">
 
    <xsl:output method="xml" indent="no" />

    <xsl:include href="zib_bibtex_templates.xslt" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />

    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>


    <!--  First-Level-Template (root) -->
    <xsl:template match="bibtex:file">
        <xsl:element name="Documents">
            <xsl:apply-templates>
                <xsl:sort select="*/bibtex:title"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>

    <!--  Second-Level-Template -->
    <xsl:template match="bibtex:entry">
        <xsl:variable name="id"><xsl:value-of select="@id" /></xsl:variable>

        <!-- <xsl:variable name="abstag"><xsl:value-of select="*/bibtex:abstag" /></xsl:variable> -->
        <xsl:variable name="abstract"><xsl:value-of select="*/bibtex:abstract" /></xsl:variable>
        <xsl:variable name="absurl"><xsl:value-of select="*/bibtex:absurl" /></xsl:variable>                        <!-- absurl wird gemappt auf Reference Url -->
        <xsl:variable name="address"><xsl:value-of select="*/bibtex:address" /></xsl:variable>                      <!-- address: muss nicht zwingend publisher_pplace sein -->
        <!-- <xsl:variable name="altsrctag"><xsl:value-of select="*/bibtex:altsrctag" /></xsl:variable> -->         <!-- koennte auf ReferenceUrl (Label = Ä'preprint o.ae.) gemappt werden -->
        <!-- <xsl:variable name="altsrcurl"><xsl:value-of select="*/bibtex:altsrcurl" /></xsl:variable> -->         <!-- koennte auf ReferenceUrl (Label = Ä'preprint o.ae.) gemappt werden -->
        <xsl:variable name="author"><xsl:value-of select="*/bibtex:author" /></xsl:variable>
        <xsl:variable name="booktitle"><xsl:value-of select="*/bibtex:booktitle" /></xsl:variable>
        <!-- <xsl:variable name="chapter"><xsl:value-of select="*/bibtex:chapter" /></xsl:variable> -->
        <xsl:variable name="doi"><xsl:value-of select="*/bibtex:doi" /></xsl:variable>
        <xsl:variable name="editor"><xsl:value-of select="*/bibtex:editor" /></xsl:variable>
        <xsl:variable name="group"><xsl:value-of select="*/bibtex:group" /></xsl:variable>
        <xsl:variable name="howpublished"><xsl:value-of select="*/bibtex:howpublished" /></xsl:variable>
        <!-- <xsl:variable name="href"><xsl:value-of select="*/bibtex:href" /></xsl:variable> -->
        <xsl:variable name="institution"><xsl:value-of select="*/bibtex:institution" /></xsl:variable>              <!-- Institution eines Techreports -->
        <xsl:variable name="isbn"><xsl:value-of select="*/bibtex:isbn" /></xsl:variable>
        <xsl:variable name="issn"><xsl:value-of select="*/bibtex:issn" /></xsl:variable>
        <xsl:variable name="issue"><xsl:value-of select="*/bibtex:issue" /></xsl:variable>
        <xsl:variable name="journal"><xsl:value-of select="*/bibtex:journal" /></xsl:variable>
        <!-- <xsl:variable name="keywords"><xsl:value-of select="*/bibtex:keywords" /></xsl:variable> -->
        <xsl:variable name="listyear"><xsl:value-of select="*/bibtex:listyear" /></xsl:variable>                    <!-- in welchem jahr soll die Publikation gelistet werden (visualisierung) -->
        <!-- <xsl:variable name="location"><xsl:value-of select="*/bibtex:location" /></xsl:variable> -->
        <xsl:variable name="month"><xsl:value-of select="*/bibtex:month" /></xsl:variable>                          <!-- Inproceedings: das Datum der Konferenz, Doctoralthesis das Datum der Abgabe/Verteidigung? -->
        <xsl:variable name="note"><xsl:value-of select="*/bibtex:note" /></xsl:variable>
        <!-- <xsl:variable name="notes"><xsl:value-of select="*/bibtex:notes" /></xsl:variable> -->
        <xsl:variable name="number"><xsl:value-of select="*/bibtex:number" /></xsl:variable>                        <!-- Article: der Issue eines Hefts -->
        <xsl:variable name="numpages"><xsl:value-of select="*/bibtex:numpages" /></xsl:variable>
	<!-- <xsl:variable name="organization"><xsl:value-of select="*/bibtex:organization" /></xsl:variable> -->
        <xsl:variable name="pages"><xsl:value-of select="*/bibtex:pages" /></xsl:variable>
	<!-- <xsl:variable name="pmid"><xsl:value-of select="*/bibtex:pmid" /></xsl:variable> -->
        <xsl:variable name="publisher"><xsl:value-of select="*/bibtex:publisher" /></xsl:variable>
	<!-- <xsl:variable name="rating"><xsl:value-of select="*/bibtex:rating" /></xsl:variable> -->
        <xsl:variable name="school"><xsl:value-of select="*/bibtex:school" /></xsl:variable>
        <xsl:variable name="series"><xsl:value-of select="*/bibtex:series" /></xsl:variable>                        <!-- ConferenceObject: TitleSub -->
        <!--<xsl:variable name="srctag"><xsl:value-of select="*/bibtex:srctag" /></xsl:variable> -->
        <xsl:variable name="srcurl"><xsl:value-of select="*/bibtex:srcurl" /></xsl:variable>
        <!--<xsl:variable name="suppl"><xsl:value-of select="*/bibtex:suppl" /></xsl:variable>-->
        <!--<xsl:variable name="target"><xsl:value-of select="*/bibtex:target" /></xsl:variable>-->                 <!-- 'publi', 'thesis', 'bovis' -->
        <xsl:variable name="title"><xsl:value-of select="*/bibtex:title" /></xsl:variable>
        <xsl:variable name="type"><xsl:value-of select="*/bibtex:type" /></xsl:variable>                            <!-- Doctoralthesis: Phdthesis -->
        <xsl:variable name="url"><xsl:value-of select="*/bibtex:url" /></xsl:variable>                              <!-- Das Feld Url beinhaltet z.t. ungepruefte Urls -->
        <xsl:variable name="volume"><xsl:value-of select="*/bibtex:volume" /></xsl:variable>
        <xsl:variable name="year"><xsl:value-of select="*/bibtex:year" /></xsl:variable>
        <!-- <xsl:variable name="zib"><xsl:value-of select="*/bibtex:zib" /></xsl:variable> -->

        <!-- Der Publikationstyp -->
        <xsl:variable name="doctype">
            <xsl:choose>
            	<xsl:when test="bibtex:article"><xsl:text>article</xsl:text></xsl:when>
            	<xsl:when test="bibtex:book"><xsl:text>book</xsl:text></xsl:when>
            	<xsl:when test="bibtex:booklet"><xsl:text>misc</xsl:text></xsl:when>
            	<xsl:when test="bibtex:conference"><xsl:text>conferenceobject</xsl:text></xsl:when>
            	<xsl:when test="bibtex:inbook"><xsl:text>bookpart</xsl:text></xsl:when>
            	<xsl:when test="bibtex:incollection"><xsl:text>bookpart</xsl:text></xsl:when>
            	<xsl:when test="bibtex:inproceedings"><xsl:text>conferenceobject</xsl:text></xsl:when>
                <xsl:when test="bibtex:mastersthesis"><xsl:text>masterthesis</xsl:text></xsl:when>
                <xsl:when test="bibtex:misc"><xsl:text>misc</xsl:text></xsl:when>
                <!-- Problem: BibTex unterscheidet nicht zwischen doctoralthesis und habilitation -->
            	<xsl:when test="bibtex:phdthesis"><xsl:text>doctoralthesis</xsl:text></xsl:when>
            	<xsl:when test="bibtex:proceedings"><xsl:text>conferenceobject</xsl:text></xsl:when>
            	<xsl:when test="bibtex:techreport"><xsl:text>report</xsl:text></xsl:when>
            	<xsl:when test="bibtex:unpublished"><xsl:text>preprint</xsl:text></xsl:when>
            	<xsl:otherwise><xsl:text>misc</xsl:text></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <!-- Das eigentliche Opus-Dokument -->
        <xsl:element name="Opus_Document">
            <!-- CompletedDate -->

            <!-- CompletedYear -->
            <xsl:if test="string-length($year) > 0">
                <xsl:attribute name="CompletedYear">
                    <xsl:value-of select="php:function('preg_replace', '/[^\d]/','', $year)" />
                </xsl:attribute>
            </xsl:if>

            <!-- ContributingCorporation -->
            <xsl:if test="string-length($institution) > 0">
                <xsl:attribute name="ContributingCorporation">
                    <xsl:value-of select="$institution" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="string-length($school) > 0">
                <xsl:attribute name="ContributingCorporation">
                    <xsl:value-of select="$school" />
                </xsl:attribute>
            </xsl:if>

            <!-- CreatingCorporation -->

            <!-- ThesisDateAccepted -->

            <!-- Type -->
            <xsl:if test="string-length($doctype) > 0">
                <xsl:attribute name="Type">
                    <xsl:value-of select="$doctype" />
                </xsl:attribute>
            </xsl:if>

            <!-- Edition -->

            <!-- Issue -->
            <xsl:if test="string-length($issue) > 0">
                <xsl:attribute name="Issue">
                    <xsl:value-of select="$issue" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="string-length($number) > 0">
                <xsl:attribute name="Issue">
                    <xsl:value-of select="$number" />
                </xsl:attribute>
            </xsl:if>

            <!-- Language -->

            <!-- PageFirst -->
            <!-- PageLast -->
            <!-- PageNumber -->
            <xsl:if test="string-length($pages) > 0">
                <xsl:if test="$doctype='article' or $doctype='bookpart' or $doctype='conferenceobject'">
                    <xsl:attribute name="PageFirst">
                       <xsl:call-template name="getFirstPage">
                            <xsl:with-param name="pages">
                                <xsl:value-of select="$pages" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>
                    <xsl:attribute name="PageLast">
                        <xsl:call-template name="getLastPage">
                            <xsl:with-param name="pages">
                                <xsl:value-of select="$pages" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>
                </xsl:if>
                <xsl:if test="$doctype='book' or $doctype='misc'">
                    <xsl:attribute name="PageNumber"><xsl:value-of select="$pages" /></xsl:attribute>
                </xsl:if>
            </xsl:if>

            <!-- PublicationState -->

            <!-- PublishedDate -->

            <!-- PublishedYear -->
            <xsl:if test="string-length($year) > 0">
                <xsl:choose>
                    <xsl:when test="string-length($listyear) > 0">
                        <xsl:attribute name="PublishedYear"><xsl:value-of select="$listyear" /></xsl:attribute>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:attribute name="PublishedYear"><xsl:value-of select="php:function('preg_replace', '/[^\d]/','', $year)" /></xsl:attribute>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:if>

            <!-- PublisherName -->
            <!-- PublisherPlace -->
            <xsl:if test="string-length($publisher) > 0">
                <xsl:attribute name="PublisherName"><xsl:value-of select="$publisher" /></xsl:attribute>
                <xsl:if test="string-length($address) > 0">
                    <xsl:attribute name="PublisherPlace"><xsl:value-of select="$address" /></xsl:attribute>
                </xsl:if>
            </xsl:if>
            
            <!-- ServerDateModified -->
            <!-- ServerDatePublished -->
            <!-- ServerDateUnlocking -->
            <!-- ServerState -->

            <!-- Volume -->
            <xsl:if test="string-length($volume) > 0">
                <xsl:attribute name="Volume"><xsl:value-of select="$volume" /></xsl:attribute>
            </xsl:if>

            <!-- BelongsToBibliography -->
            <xsl:attribute name="BelongsToBibliography"><xsl:text>1</xsl:text></xsl:attribute>


            <!-- EXTERNAL FILEDS -->
            <!-- TitleMain -->
            <xsl:if test="string-length($title) > 0">
                <xsl:element name="TitleMain">
                    <xsl:attribute name="Value"><xsl:value-of select="$title" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- TitleAbstract -->
            <xsl:if test="string-length($abstract) > 0">
                <xsl:element name="TitleAbstract">
                    <xsl:attribute name="Value"><xsl:value-of select="$abstract" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- TitleParent -->
            <xsl:if test="string-length($journal) > 0">
                <xsl:element name="TitleParent">
                    <xsl:attribute name="Value"><xsl:value-of select="$journal" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <xsl:if test="string-length($booktitle) > 0">
                <xsl:element name="TitleParent">
                    <xsl:attribute name="Value"><xsl:value-of select="$booktitle" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- TitleSub -->
            <xsl:if test="string-length($series) > 0">
                <xsl:element name="TitleSub">
                    <xsl:attribute name="Value"><xsl:value-of select="$series" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- IdentifierIsbn -->
            <xsl:if test="string-length($isbn) > 0">
                <xsl:element name="IdentifierIsbn">
                    <xsl:attribute name="Value"><xsl:value-of select="$isbn" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- IdentifierIssn -->
            <xsl:if test="string-length($issn) > 0">
                <xsl:element name="IdentifierIssn">
                    <xsl:attribute name="Value"><xsl:value-of select="$issn" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- IdentifierDoi -->
            <xsl:if test="string-length($doi) > 0">
                <xsl:element name="IdentifierDoi">
                    <xsl:attribute name="Value"><xsl:value-of select="$doi" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- IdentifierUrl / IdentifierDoi-->
            <xsl:if test="string-length($srcurl) > 0">
                <xsl:choose>
                    <xsl:when test="contains($srcurl, 'dx.doi.org')">
                        <xsl:element name="IdentifierDoi">
                            <xsl:attribute name="Value"><xsl:value-of select="substring-after($srcurl,'dx.doi.org/')" /></xsl:attribute>
                        </xsl:element>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:element name="IdentifierUrl">
                            <xsl:attribute name="Value"><xsl:value-of select="$srcurl" /></xsl:attribute>
                        </xsl:element>
                    </xsl:otherwise>
               </xsl:choose>
            </xsl:if>
            <xsl:if test="string-length($url) > 0">
                <xsl:choose>
                    <xsl:when test="contains($url, 'dx.doi.org')">
                        <xsl:element name="IdentifierDoi">
                            <xsl:attribute name="Value"><xsl:value-of select="substring-after($url,'dx.doi.org/')" /></xsl:attribute>
                        </xsl:element>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:element name="IdentifierUrl">
                            <xsl:attribute name="Value"><xsl:value-of select="$url" /></xsl:attribute>
                        </xsl:element>
                    </xsl:otherwise>
               </xsl:choose>
            </xsl:if>

            <!-- IdentifierOld -->
            <xsl:if test="string-length($id) > 0">
                <xsl:element name="IdentifierOld">
                    <xsl:attribute name="Value"><xsl:value-of select="$id" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- ReferenceUrl-->
            <xsl:if test="string-length($absurl) > 0">
                <xsl:element name="ReferenceUrl">
                    <xsl:attribute name="Label">abstract</xsl:attribute>
                    <xsl:attribute name="Value"><xsl:value-of select="$absurl" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

             <!-- noter can be a Note or a IdentifierUrl -->
            <xsl:if test="string-length($note) > 0">
                <xsl:choose>
                    <xsl:when test="contains($note, 'http://')">
                        <xsl:element name="IdentifierUrl">
                            <xsl:attribute name="Value"><xsl:value-of select="$note" /></xsl:attribute>
                        </xsl:element>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:element name="Note">
                            <xsl:attribute name="Message"><xsl:value-of select="$note" /></xsl:attribute>
                        </xsl:element>
                    </xsl:otherwise>
               </xsl:choose>
            </xsl:if>

            <!-- PersonAuthor -->
            <xsl:if test="string-length($author) > 0">
               <xsl:call-template name="AddPersons">
                    <xsl:with-param name="role">PersonAuthor</xsl:with-param>
                    <xsl:with-param name="list"><xsl:value-of select="$author" /></xsl:with-param>
                    <xsl:with-param name="delimiter"> and </xsl:with-param>
                </xsl:call-template>
            </xsl:if>

            <!-- PersonEditor -->
            <xsl:if test="string-length($editor) > 0">
               <xsl:call-template name="AddPersons">
                    <xsl:with-param name="role">PersonEditor</xsl:with-param>
                    <xsl:with-param name="list"><xsl:value-of select="$editor" /></xsl:with-param>
                    <xsl:with-param name="delimiter"> and </xsl:with-param>
                </xsl:call-template>
            </xsl:if>

            <!-- DocumentEnrichments can be anything -->
            <xsl:if test="string-length($publisher) = 0">
                <xsl:if test="string-length($address) > 0">
                 <xsl:element name="Enrichment">
                     <xsl:attribute name="KeyName">address</xsl:attribute>
                     <xsl:attribute name="Value"><xsl:value-of select="$address" /></xsl:attribute>
                 </xsl:element>
                </xsl:if>
            </xsl:if>

            <xsl:if test="string-length($month) > 0">
                 <xsl:element name="Enrichment">
                     <xsl:attribute name="KeyName">month</xsl:attribute>
                     <xsl:attribute name="Value"><xsl:value-of select="$month" /></xsl:attribute>
                 </xsl:element>
            </xsl:if>
            <xsl:if test="string-length($type) > 0">
                 <xsl:element name="Enrichment">
                     <xsl:attribute name="KeyName">type</xsl:attribute>
                     <xsl:attribute name="Value"><xsl:value-of select="$type" /></xsl:attribute>
                 </xsl:element>
            </xsl:if>
            <xsl:if test="string-length($howpublished) > 0">
                 <xsl:element name="Enrichment">
                     <xsl:attribute name="KeyName">howpublished</xsl:attribute>
                     <xsl:attribute name="Value"><xsl:value-of select="$howpublished" /></xsl:attribute>
                 </xsl:element>
            </xsl:if>

             <!-- Institutes and WorkingGroups -->
            <xsl:element name="OldInstitute">
                <xsl:attribute name="Value">Visualisierung und Datenanalyse</xsl:attribute>
            </xsl:element>

            <xsl:if test="string-length($group) > 0">
               <xsl:call-template name="AddWorkingGroups">
                    <xsl:with-param name="list"><xsl:value-of select="$group" /></xsl:with-param>
                    <xsl:with-param name="delimiter">,</xsl:with-param>
                </xsl:call-template>
            </xsl:if>
        </xsl:element>
   </xsl:template>


    <!-- Extrahiert die Arbeitsgruppen -->
    <xsl:template name="AddWorkingGroups">
        <xsl:param name="list" required="yes" />
        <xsl:param name="delimiter" required="yes" />
        <xsl:variable name="newlist">
            <xsl:choose>
                <xsl:when test="contains($list, $delimiter)"><xsl:value-of select="normalize-space($list)" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="concat(normalize-space($list), $delimiter)"/></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="first" select="substring-before($newlist, $delimiter)" />
        <xsl:variable name="remaining" select="substring-after($newlist, $delimiter)" />
        <xsl:call-template name="MapWorkingGroup">
             <xsl:with-param name="group" select="normalize-space($first)" />
        </xsl:call-template>
        <xsl:if test="$remaining">
            <xsl:call-template name="AddWorkingGroups">
                <xsl:with-param name="list"><xsl:value-of select="$remaining" /> </xsl:with-param>
                <xsl:with-param name="delimiter"><xsl:value-of select="$delimiter" /> </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

   <!-- Das Mapping der Arbeitsgruppen -->
   <xsl:template name="MapWorkingGroup">
        <xsl:param name="group" required="yes" />
        <xsl:element name="OldInstitute">
            <xsl:attribute name="Value">
                <xsl:choose>
                    <xsl:when test="$group='compvis'"><xsl:text>Vergleichende Visualisierung</xsl:text></xsl:when>
                    <xsl:when test="$group='systems'"><xsl:text>Visualisierungssysteme</xsl:text></xsl:when>
                    <xsl:when test="$group='scivis'"><xsl:text>Visualisierungsalgorithmen</xsl:text></xsl:when>
                    <xsl:when test="$group='medical'"><xsl:text>Medizinische Planung</xsl:text></xsl:when>
                </xsl:choose>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
