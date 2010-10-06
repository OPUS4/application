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

        <!-- Lese alle Variablen ein -->
        <!-- address: muss nicht zwingend publisher_pplace sein -->
        <xsl:variable name="address"><xsl:value-of select="*/bibtex:address" /></xsl:variable>
        <xsl:variable name="author"><xsl:value-of select="*/bibtex:author" /></xsl:variable>
        <xsl:variable name="booktitle"><xsl:value-of select="*/bibtex:booktitle" /></xsl:variable>
        <xsl:variable name="doi"><xsl:value-of select="*/bibtex:doi" /></xsl:variable>
        <xsl:variable name="edition"><xsl:value-of select="*/bibtex:edition" /></xsl:variable>
        <xsl:variable name="editor"><xsl:value-of select="*/bibtex:editor" /></xsl:variable>
        <xsl:variable name="institution"><xsl:value-of select="*/bibtex:institution" /></xsl:variable>
        <xsl:variable name="isbn"><xsl:value-of select="*/bibtex:isbn" /></xsl:variable>
        <xsl:variable name="issn"><xsl:value-of select="*/bibtex:issn" /></xsl:variable>
        <xsl:variable name="issue"><xsl:value-of select="*/bibtex:issue" /></xsl:variable>
        <xsl:variable name="journal"><xsl:value-of select="*/bibtex:journal" /></xsl:variable>
        <xsl:variable name="language"><xsl:value-of select="*/bibtex:language" /></xsl:variable>
        <!-- listyear: in welchem jahr soll die Publikation gelistet werden (visualisierung) -->
        <xsl:variable name="listyear"><xsl:value-of select="*/bibtex:listyear" /></xsl:variable>
        <xsl:variable name="note"><xsl:value-of select="*/bibtex:note" /></xsl:variable>
        <xsl:variable name="number"><xsl:value-of select="*/bibtex:number" /></xsl:variable>
        <xsl:variable name="pages"><xsl:value-of select="*/bibtex:pages" /></xsl:variable>
        <xsl:variable name="publisher"><xsl:value-of select="*/bibtex:publisher" /></xsl:variable>
        <xsl:variable name="school"><xsl:value-of select="*/bibtex:school" /></xsl:variable>
        <xsl:variable name="series"><xsl:value-of select="*/bibtex:series" /></xsl:variable>
        <xsl:variable name="srcurl"><xsl:value-of select="*/bibtex:srcurl" /></xsl:variable>
        <xsl:variable name="title"><xsl:value-of select="*/bibtex:title" /></xsl:variable>
        <xsl:variable name="type"><xsl:value-of select="*/bibtex:type" /></xsl:variable>
        <xsl:variable name="url"><xsl:value-of select="*/bibtex:url" /></xsl:variable>
        <xsl:variable name="urlpdf"><xsl:value-of select="*/bibtex:urlpdf" /></xsl:variable>
        <xsl:variable name="volume"><xsl:value-of select="*/bibtex:volume" /></xsl:variable>
        <xsl:variable name="year"><xsl:value-of select="*/bibtex:year" /></xsl:variable>

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
            <!--
            "BelongsToBibliography"
            ##"CompletedDate",
            "CompletedYear",
            ##"ContributingCorporation",
            ##"CreatingCorporation",
            ##"ThesisDateAccepted",
            "Edition",
            "Issue",
            "Language",
            "PageFirst",
            "PageLast",
            "PageNumber",
            ##"PublishedDate",
            "PublishedYear",
            "PublisherName",
            "PublisherPlace",
            ##"PublicationState",
            ##"ServerDateModified",
            ##"ServerDatePublished",
            ##"ServerDateUnlocking",
            ##"ServerState",
            "Type",
            "Volume",
            -->

             <xsl:attribute name="BelongsToBibliography"><xsl:text>1</xsl:text></xsl:attribute>

            <xsl:if test="string-length($edition) > 0">
                <xsl:attribute name="Edition"><xsl:value-of select="$edition" /></xsl:attribute>
            </xsl:if>

            <xsl:if test="string-length($issue) > 0">
                <xsl:attribute name="Issue"><xsl:value-of select="$issue" /></xsl:attribute>
            </xsl:if>

            <xsl:if test="string-length($language) > 0">
                <xsl:attribute name="Language">
                    <xsl:call-template name="getLanguage">
                        <xsl:with-param name="lang">
                            <xsl:value-of select="$language" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:attribute>
            </xsl:if>

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

            <xsl:if test="string-length($year) > 0">
                <xsl:choose>
                    <xsl:when test="string-length($listyear) > 0">
                        <xsl:attribute name="CompletedYear"><xsl:value-of select="$year" /></xsl:attribute>
                        <xsl:attribute name="PublishedYear"><xsl:value-of select="$listyear" /></xsl:attribute>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:attribute name="PublishedYear"><xsl:value-of select="$year" /></xsl:attribute>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:if>

            <xsl:if test="string-length($publisher) > 0">
                <xsl:attribute name="PublisherName"><xsl:value-of select="$publisher" /></xsl:attribute>
            </xsl:if>

            <xsl:if test="string-length($address) > 0">
                <xsl:attribute name="PublisherPlace"><xsl:value-of select="$address" /></xsl:attribute>
            </xsl:if>

            <xsl:if test="string-length($doctype) > 0">
                <xsl:attribute name="Type"><xsl:value-of select="$doctype" /></xsl:attribute>
            </xsl:if>

            <xsl:if test="string-length($volume) > 0">
                <xsl:attribute name="Volume"><xsl:value-of select="$volume" /></xsl:attribute>
            </xsl:if>
   
        <!--
            'TitleMain'
            ##'TitleAbstract'
            'TitleParent'
            ##'TitleSub'
            ##'TitleAdditional'

            ##'IdentifierOld'
            ##'IdentifierSerial'
            ##'IdentifierUuid'
            'IdentifierIsbn'
            ##'IdentifierUrn'
            'IdentifierDoi'
            ##'IdentifierHandle'
            'IdentifierUrl'
            'IdentifierIssn'
            ##'IdentifierStdDoi'
            ##'IdentifierCrisLink'
            ##'IdentifierSplashUrl'
            ##'IdentifierOpus3'
            ##'IdentifierOpac'

            ##'ReferenceIsbn'
            ##'ReferenceUrn'
            ##'ReferenceDoi'
            ##'ReferenceHandle'
            ##'ReferenceUrl'
            ##'ReferenceIssn'
            ##'ReferenceStdDoi'
            ##'ReferenceCrisLink'
            ##'ReferenceSplashUrl'

            'Note'
            ##'Patent'
            ##'Enrichment'
            ##'Licence'

            ##'PersonAdvisor'
            'PersonAuthor'
            ##'PersonContributor'
            'PersonEditor'
            ##'PersonReferee'
            ##'PersonOther'
            ##'PersonOwner'
            ##'PersonTranslator'
            ##'PersonSubmitter'

            ##'SubjectSwd'
            ##'SubjectPsyndex'
            ##'SubjectUncontrolled'
            ##'SubjectMSC'
            ##'SubjectDDC'

            ##'File'
            ##'Collection'

            ##'ThesisPublisher'
            ##'ThesisGrantor'
        -->
            <!-- TitleMain -->
            <xsl:if test="string-length($title) > 0">
                <xsl:element name="TitleMain">
                    <xsl:attribute name="Value"><xsl:value-of select="$title" /></xsl:attribute>
                    <xsl:if test="string-length($language) > 0">
                        <xsl:attribute name="Language">
                            <xsl:call-template name="getLanguage">
                                <xsl:with-param name="lang">
                                    <xsl:value-of select="$language" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:attribute>
                    </xsl:if>
                </xsl:element>
            </xsl:if>

             <!-- TitleParent -->
            <xsl:if test="$doctype='article' and string-length($journal) > 0">
                <xsl:element name="TitleParent">
                <xsl:attribute name="Value"><xsl:value-of select="$journal" /></xsl:attribute>
                    <xsl:if test="string-length($language) > 0">
                        <xsl:attribute name="Language">
                            <xsl:call-template name="getLanguage">
                                <xsl:with-param name="lang">
                                    <xsl:value-of select="$language" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:attribute>
                    </xsl:if>
                </xsl:element>
            </xsl:if>
            <xsl:if test="$doctype='bookpart' or $doctype='conferenceobject'">
                <xsl:if test="string-length($booktitle) > 0">
                    <xsl:element name="TitleParent">
                    <xsl:attribute name="Value"><xsl:value-of select="$booktitle" /></xsl:attribute>
                        <xsl:if test="string-length($language) > 0">
                            <xsl:attribute name="Language">
                                <xsl:call-template name="getLanguage">
                                    <xsl:with-param name="lang">
                                        <xsl:value-of select="$language" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:attribute>
                        </xsl:if>
                    </xsl:element>
                </xsl:if>
            </xsl:if>


            <xsl:if test="string-length($isbn) > 0">
                <xsl:element name="IdentifierIsbn">
                    <xsl:attribute name="Value"><xsl:value-of select="$isbn" /></xsl:attribute>
                </xsl:element>
            </xsl:if>


            <xsl:if test="string-length($issn) > 0">
                <xsl:element name="IdentifierIssn">
                    <xsl:attribute name="Value"><xsl:value-of select="$issn" /></xsl:attribute>
                </xsl:element>
            </xsl:if>


            <xsl:if test="string-length($doi) > 0">
                <xsl:element name="IdentifierDoi">
                    <xsl:attribute name="Value"><xsl:value-of select="$doi" /></xsl:attribute>
                </xsl:element>
            </xsl:if>


            <xsl:if test="string-length($srcurl) > 0">
                <xsl:element name="IdentifierUrl">
                    <xsl:attribute name="Value"><xsl:value-of select="$srcurl" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <xsl:if test="string-length($url) > 0">
                <xsl:element name="IdentifierUrl">
                    <xsl:attribute name="Value"><xsl:value-of select="$url" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <xsl:if test="string-length($urlpdf) > 0">
                <xsl:element name="IdentifierUrl">
                    <xsl:attribute name="Value"><xsl:value-of select="$urlpdf" /></xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- TODO: School-Attribute of a Thesis will be mapped to Note -->
            <xsl:if test="string-length($institution) > 0 or string-length($school) > 0 or string-length($note) > 0">
                <xsl:element name="Note">
                    <xsl:attribute name="Message">
                        <xsl:if test="string-length($institution) > 0"><xsl:value-of select="$institution" /></xsl:if>
                        <xsl:if test="string-length($school) > 0"><xsl:value-of select="$school" /></xsl:if>
                        <xsl:if test="string-length($note) > 0"><xsl:value-of select="$note" /></xsl:if>
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>


            <xsl:if test="string-length($author) > 0">
               <xsl:call-template name="getAuthors">
                    <xsl:with-param name="list"><xsl:value-of select="$author" /></xsl:with-param>
                    <xsl:with-param name="delimiter"> and </xsl:with-param>
                </xsl:call-template>
            </xsl:if>        

           
            <xsl:if test="string-length($editor) > 0">
               <xsl:call-template name="getEditors">
                    <xsl:with-param name="list"><xsl:value-of select="$editor" /></xsl:with-param>
                    <xsl:with-param name="delimiter"> and </xsl:with-param>
                </xsl:call-template>
            </xsl:if>

        </xsl:element>
   </xsl:template>

   <!-- Das Mapping der Sprachen -->
   <xsl:template name="getLanguage">
        <xsl:param name="lang" required="yes" />
        <xsl:choose>
            <xsl:when test="$lang='German'"><xsl:text>deu</xsl:text></xsl:when>
            <xsl:when test="$lang='English'"><xsl:text>eng</xsl:text></xsl:when>
            <xsl:otherwise><xsl:text>eng</xsl:text></xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- Holt die Startseite bei Seitenangaben -->
   <xsl:template name="getFirstPage">
        <xsl:param name="pages" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($pages, '--')">
                <xsl:value-of select="substring-before($pages,'--')" />
            </xsl:when>
            <xsl:when test="contains($pages, '-')">
                <xsl:value-of select="substring-before($pages,'-')" />
            </xsl:when>
        </xsl:choose>
    </xsl:template>

   <!-- Holt die Endseite bei Seitenangaben -->
   <xsl:template name="getLastPage">
        <xsl:param name="pages" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($pages, '--')">
                <xsl:value-of select="substring-after($pages,'--')" />
            </xsl:when>
            <xsl:when test="contains($pages, '-')">
                <xsl:value-of select="substring-after($pages,'-')" />
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <!-- Erzeugt eine die Autorenelemente -->
    <xsl:template name="getAuthors">
        <xsl:param name="list" required="yes"/>
        <xsl:param name="delimiter" required="yes"/>
         <xsl:variable name="newlist">
            <xsl:choose>
                <xsl:when test="contains($list, $delimiter)"><xsl:value-of select="normalize-space($list)" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="concat(normalize-space($list), $delimiter)"/></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="first" select="substring-before($newlist, $delimiter)" />
        <xsl:variable name="remaining" select="substring-after($newlist, $delimiter)" />
        <xsl:element name="PersonAuthor">
            <xsl:attribute name="FirstName">
                <xsl:call-template name="getFirstName">
                    <xsl:with-param name="name">
                        <xsl:value-of select="$first" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>
            <xsl:attribute name="LastName">
                <xsl:call-template name="getLastName">
                    <xsl:with-param name="name">
                        <xsl:value-of select="$first" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>
        </xsl:element>
         <xsl:if test="$remaining">
            <xsl:call-template name="getAuthors">
                <xsl:with-param name="list"><xsl:value-of select="$remaining" /> </xsl:with-param>
                <xsl:with-param name="delimiter"><xsl:value-of select="$delimiter" /> </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <!-- Erzeugt  die Editorenelemente -->
    <xsl:template name="getEditors">
        <xsl:param name="list" required="yes"/>
        <xsl:param name="delimiter" required="yes"/>
         <xsl:variable name="newlist">
            <xsl:choose>
                <xsl:when test="contains($list, $delimiter)"><xsl:value-of select="normalize-space($list)" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="concat(normalize-space($list), $delimiter)"/></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="first" select="substring-before($newlist, $delimiter)" />
        <xsl:variable name="remaining" select="substring-after($newlist, $delimiter)" />
        <xsl:element name="PersonEditor">
            <xsl:attribute name="FirstName">
                <xsl:call-template name="getFirstName">
                    <xsl:with-param name="name">
                        <xsl:value-of select="$first" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>
            <xsl:attribute name="LastName">
                <xsl:call-template name="getLastName">
                    <xsl:with-param name="name">
                        <xsl:value-of select="$first" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>
        </xsl:element>
         <xsl:if test="$remaining">
            <xsl:call-template name="getEditors">
                <xsl:with-param name="list"><xsl:value-of select="$remaining" /> </xsl:with-param>
                <xsl:with-param name="delimiter"><xsl:value-of select="$delimiter" /> </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>


   <!-- Holt den Vornamen des Autors -->
   <xsl:template name="getFirstName">
        <xsl:param name="name" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($name, ',')">
                <xsl:value-of select="normalize-space(substring-after($name,','))" />
            </xsl:when>
            <xsl:when test="contains($name, ' ')">
                <xsl:variable name="pos"><xsl:value-of select="php:function('strrpos', $name, ' ')"/></xsl:variable>
                <xsl:value-of select="normalize-space(php:function('substr', $name, 0, $pos))"/>
            </xsl:when>
            <xsl:when test="contains($name, '.')">
                <xsl:variable name="pos"><xsl:value-of select="php:function('strrpos', $name, '.')"/></xsl:variable>
                <xsl:value-of select="normalize-space(php:function('substr', $name, 0, $pos))"/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

   <!-- Holt den Nachnamen des Autors -->
   <xsl:template name="getLastName">
        <xsl:param name="name" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($name, ',')">
                <xsl:value-of select="normalize-space(substring-before($name,','))" />
            </xsl:when>
            <xsl:when test="contains($name, ' ')">
                <xsl:variable name="pos"><xsl:value-of select="php:function('strrpos', $name, ' ')"/></xsl:variable>
                <xsl:value-of select="normalize-space(php:function('substr', $name, $pos+1))"/>
            </xsl:when>
            <xsl:when test="contains($name, '.')">
                <xsl:variable name="pos"><xsl:value-of select="php:function('strrpos', $name, '.')"/></xsl:variable>
                <xsl:value-of select="normalize-space(php:function('substr', $name, $pos+1))"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="normalize-space($name)" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>




</xsl:stylesheet>
