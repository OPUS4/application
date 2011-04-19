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
            <xsl:apply-templates select="bibtex:entry"/>
        </xsl:element>
    </xsl:template>

    <!--  Second-Level-Template -->
    <xsl:template match="bibtex:entry">
        <xsl:variable name="id"><xsl:value-of select="@id" /></xsl:variable>

        <xsl:variable name="abstr"><xsl:value-of select="*/bibtex:abstr" /></xsl:variable>                          <!-- ReferenceUrl/@Label='abstract' -->
        <xsl:variable name="abstract"><xsl:value-of select="*/bibtex:abstract" /></xsl:variable>                    <!-- TODO: Das Feld abstract beinhaltet z.t. urls -->
        <xsl:variable name="address"><xsl:value-of select="*/bibtex:address" /></xsl:variable>                      <!-- address: muss nicht zwingend publisher_pplace sein -->
        <xsl:variable name="annote"><xsl:value-of select="*/bibtex:annote" /></xsl:variable>                        <!-- Note -->
        <xsl:variable name="author"><xsl:value-of select="*/bibtex:author" /></xsl:variable>
        <xsl:variable name="bdsk-url-1"><xsl:value-of select="*/bibtex:bdsk-url-1" /></xsl:variable>                <!-- IdentifierUrl -->
        <xsl:variable name="book"><xsl:value-of select="*/bibtex:book" /></xsl:variable>                            <!-- IdentifierUrl -->
        <xsl:variable name="booktitle"><xsl:value-of select="*/bibtex:booktitle" /></xsl:variable>
        <xsl:variable name="chapter"><xsl:value-of select="*/bibtex:chapter" /></xsl:variable>                      <!-- Enrichment/@KeyName='chapter' -->
        <xsl:variable name="comment"><xsl:value-of select="*/bibtex:comment" /></xsl:variable>                      <!-- Note -->
        <!--# <xsl:variable name="crossref"><xsl:value-of select="*/bibtex:crossref" /></xsl:variable> -->          <!-- ignoriert: ohne content -->
        <!--# <xsl:variable name="date-added"><xsl:value-of select="*/bibtex:date-added" /></xsl:variable> -->      <!-- ignoriert: Opus setzt Timestamp selbst -->
        <!--# <xsl:variable name="date-modified"><xsl:value-of select="*/bibtex:date-modified" /></xsl:variable> -->    <!-- ignoriert: Opus setzt Timestamp selbst -->
        <xsl:variable name="doi"><xsl:value-of select="*/bibtex:doi" /></xsl:variable>
        <xsl:variable name="edition"><xsl:value-of select="*/bibtex:edition" /></xsl:variable>
        <xsl:variable name="editor"><xsl:value-of select="*/bibtex:editor" /></xsl:variable>
        <!--# <xsl:variable name="file"><xsl:value-of select="*/bibtex:file" /></xsl:variable> -->                  <!-- ignoriert: unsauberes Feld -->
        <xsl:variable name="howpublished"><xsl:value-of select="*/bibtex:howpublished" /></xsl:variable>
        <xsl:variable name="institution"><xsl:value-of select="*/bibtex:institution" /></xsl:variable>              <!-- Institution eines Techreports -->
        <xsl:variable name="journal"><xsl:value-of select="*/bibtex:journal" /></xsl:variable>
	<xsl:variable name="key"><xsl:value-of select="*/bibtex:key" /></xsl:variable>                              <!-- IdentifierDoi -->
	<xsl:variable name="keywords"><xsl:value-of select="*/bibtex:keywords" /></xsl:variable>                    <!-- SubjectUncontrolled -->
        <xsl:variable name="language"><xsl:value-of select="*/bibtex:language" /></xsl:variable>
        <xsl:variable name="month"><xsl:value-of select="*/bibtex:month" /></xsl:variable>                          <!-- Inproceedings: das Datum der Konferenz, Doctoralthesis das Datum der Abgabe/Verteidigung? -->
        <xsl:variable name="note"><xsl:value-of select="*/bibtex:note" /></xsl:variable>
        <xsl:variable name="number"><xsl:value-of select="*/bibtex:number" /></xsl:variable>                        <!-- Article: der Issue eines Hefts -->
	<xsl:variable name="organization"><xsl:value-of select="*/bibtex:organization" /></xsl:variable>            <!-- CreatingCorporation -->
	<!--# <xsl:variable name="owner"><xsl:value-of select="*/bibtex:owner" /></xsl:variable> -->                <!-- ignoriert: Semantik unklar -->
        <!-- <xsl:variable name="pagef"><xsl:value-of select="*/bibtex:pagef" /></xsl:variable> -->                 <!-- TODO: Semantik unklar, wird das überhaupt gebraucht?? -->
        <xsl:variable name="pages"><xsl:value-of select="*/bibtex:pages" /></xsl:variable>
	<xsl:variable name="pdf"><xsl:value-of select="*/bibtex:pdf" /></xsl:variable>                              <!-- IdentifierUrl -->
	<xsl:variable name="postscript"><xsl:value-of select="*/bibtex:postscript" /></xsl:variable>                <!-- IdentifierUrl -->
        <xsl:variable name="preprint"><xsl:value-of select="*/bibtex:preprint" /></xsl:variable>                    <!-- ReferenceUrl/@Label='preprint' -->
        <xsl:variable name="ps"><xsl:value-of select="*/bibtex:ps" /></xsl:variable>                                <!-- IdentifierUrl -->
        <xsl:variable name="publisher"><xsl:value-of select="*/bibtex:publisher" /></xsl:variable>
        <xsl:variable name="report"><xsl:value-of select="*/bibtex:report" /></xsl:variable>                        <!-- IdentifierUrl -->
        <xsl:variable name="revised"><xsl:value-of select="*/bibtex:revised" /></xsl:variable>                      <!-- IdentifierUrl -->
        <xsl:variable name="school"><xsl:value-of select="*/bibtex:school" /></xsl:variable>
        <xsl:variable name="selectiontags"><xsl:value-of select="*/bibtex:selectiontags" /></xsl:variable>
        <xsl:variable name="series"><xsl:value-of select="*/bibtex:series" /></xsl:variable>
        <!--# <xsl:variable name="timestamp"><xsl:value-of select="*/bibtex:timestamp" /></xsl:variable>-->         <!-- ignoriert: Opus setzt Timestamp selbst -->
        <xsl:variable name="title"><xsl:value-of select="*/bibtex:title" /></xsl:variable>
        <xsl:variable name="type"><xsl:value-of select="*/bibtex:type" /></xsl:variable>                            <!-- Doctoralthesis: Phdthesis -->
        <xsl:variable name="url"><xsl:value-of select="*/bibtex:url" /></xsl:variable>                              <!-- Das Feld Url beinhaltet z.t. ungepruefte Urls -->
        <xsl:variable name="urlabstract"><xsl:value-of select="*/bibtex:urlabstract" /></xsl:variable>              <!-- ReferenceUrl/@Label='abstract' -->
        <xsl:variable name="urlhtml"><xsl:value-of select="*/bibtex:urlhtml" /></xsl:variable>                      <!-- IdentifierUrl -->
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
            	<xsl:when test="bibtex:phdthesis">
                    <xsl:choose>
                        <xsl:when test="contains($type, 'Diploma')"><xsl:text>masterthesis</xsl:text></xsl:when>
                        <xsl:when test="contains($type, 'Dissertation')"><xsl:text>doctoralthesis</xsl:text></xsl:when>
                        <xsl:when test="contains($type, 'Doctoral')"><xsl:text>doctoralthesis</xsl:text></xsl:when>
                        <xsl:when test="contains($type, 'Habilitation')"><xsl:text>habilitation</xsl:text></xsl:when>
                        <xsl:otherwise><xsl:text>doctoralthesis</xsl:text></xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
            	<xsl:when test="bibtex:proceedings"><xsl:text>conferenceobject</xsl:text></xsl:when>
            	<xsl:when test="bibtex:techreport"><xsl:text>report</xsl:text></xsl:when>
            	<xsl:when test="bibtex:unpublished"><xsl:text>preprint</xsl:text></xsl:when>
            	<xsl:otherwise><xsl:text>misc</xsl:text></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <!-- Is ZIB-Publication? -->
        <xsl:variable name="belongsToBibliography">
            <xsl:choose>
                <xsl:when test="$doctype='report' and contains($type, 'ZIB Report')"><xsl:text>0</xsl:text></xsl:when>
                <xsl:when test="$doctype='report' and contains($type, 'ZIB-Report')"><xsl:text>0</xsl:text></xsl:when>
                <xsl:when test="$doctype='report' and contains($institution, 'ZIB')"><xsl:text>0</xsl:text></xsl:when>
                <xsl:when test="$doctype='report' and contains($institution, 'Konrad-Zuse-Zentrum für Informationstechnik Berlin')"><xsl:text>0</xsl:text></xsl:when>
                <xsl:when test="$doctype='report' and contains($institution, 'Konrad-Zuse-Zentrum Berlin')"><xsl:text>0</xsl:text></xsl:when>
                <xsl:when test="$doctype='report' and contains($institution, 'Konrad-Zuse-Zentrum')"><xsl:text>0</xsl:text></xsl:when>
                <xsl:when test="$doctype='report' and contains($institution, 'Zuse Institute, Berlin')"><xsl:text>0</xsl:text></xsl:when>
                <xsl:otherwise><xsl:text>1</xsl:text></xsl:otherwise>
           </xsl:choose>
        </xsl:variable>

        <!-- References ZIB-Publication? -->
        <xsl:variable name="reportid">
            <xsl:choose>
                <!--
                    <bibtex:abstr>http://www2.zib.de/PaperWeb/abstracts/SC-97-55</bibtex:abstr>
                    <bibtex:bdsk-url-1>http://www2.zib.de/PaperWeb/abstracts/SC-98-45</bibtex:bdsk-url-1>
                    <bibtex:url>http://www2.zib.de/PaperWeb/abstracts/SC-98-45</bibtex:url>
                -->
                <xsl:when test="contains($abstr, 'http://www2.zib.de/PaperWeb/abstracts/')">
                    <xsl:value-of select="substring-after(php:function('preg_replace', '/\/$/','', $abstr), '/abstracts/')" />
                </xsl:when>
                <xsl:when test="contains($bdsk-url-1, 'http://www2.zib.de/PaperWeb/abstracts/')">
                    <xsl:value-of select="substring-after(php:function('preg_replace', '/\/$/','', $bdsk-url-1), '/abstracts/')" />
                </xsl:when>
                <xsl:when test="contains($url, 'http://www2.zib.de/PaperWeb/abstracts/')">
                    <xsl:value-of select="substring-after(php:function('preg_replace', '/\/$/','', $url), '/abstracts/')" />
                </xsl:when>
                <!--
                    <bibtex:abstr>http://www2.zib.de/Publications/abstracts/SC-00-01</bibtex:abstr>
                -->
                <xsl:when test="contains($abstr, 'http://www2.zib.de/Publications/abstracts/')">
                    <xsl:value-of select="substring-after(php:function('preg_replace', '/\/$/','', $abstr), '/abstracts/')" />
                </xsl:when>
                <!--
                    <bibtex:pdf>http://www2.zib.de/Publications/Reports/SC-00-01.pdf</bibtex:pdf>
                    <bibtex:report>http://www2.zib.de/Publications/Reports/ZR-02-40.pdf</bibtex:report>
                    <bibtex:postscript>http://www2.zib.de/Publications/Reports/SC-96-02.ps.Z</bibtex:postscript>
                    <bibtex:preprint>http://www2.zib.de/Publications/Reports/SC-97-55.ps.Z</bibtex:preprint>
                    <bibtex:ps>http://www2.zib.de/Publications/Reports/SC-00-01.ps.Z</bibtex:ps>
                -->
                <xsl:when test="contains($pdf, 'http://www2.zib.de/Publications/Reports/')">
                    <xsl:value-of select="substring-before(substring-after($pdf, '/Reports/'), '.pdf')" />
                </xsl:when>
                <xsl:when test="contains($postscript, 'http://www2.zib.de/Publications/Reports/')">
                    <xsl:value-of select="substring-before(substring-after($postscript, '/Reports/'), '.ps.Z')" />
                </xsl:when>
                <xsl:when test="contains($preprint, 'http://www2.zib.de/Publications/Reports/')">
                    <xsl:value-of select="substring-before(substring-after($preprint, '/Reports/'), '.ps.Z')" />
                </xsl:when>
                <xsl:when test="contains($ps, 'http://www2.zib.de/Publications/Reports/')">
                    <xsl:value-of select="substring-before(substring-after($preprint, '/Reports/'), '.ps.Z')" />
                </xsl:when>
                <xsl:when test="contains($report, 'http://www2.zib.de/Publications/Reports/')">
                    <xsl:value-of select="substring-before(substring-after($report, '/Reports/'), '.ps.Z')" />
                </xsl:when>
                <!--
                    <bibtex:preprint>http://opus.kobv.de/zib/volltexte/2006/910/pdf/ZR-06-16.pdf</bibtex:preprint>
                    <bibtex:report>http://opus.kobv.de/zib/volltexte/2005/876/pdf/ZR-05-43.pdf</bibtex:report>
                -->
                <xsl:when test="contains($preprint, 'http://opus.kobv.de/zib/volltexte/')">
                    <xsl:value-of select="substring-before(substring-after($preprint, '/pdf/'), '.pdf')" />
                </xsl:when>
                <xsl:when test="contains($report, 'http://opus.kobv.de/zib/volltexte/')">
                    <xsl:value-of select="substring-before(substring-after($report, '/pdf/'), '.pdf')" />
                </xsl:when>
                <xsl:otherwise></xsl:otherwise>
           </xsl:choose>
        </xsl:variable>


        <!-- References Opus3-Document? -->
        <xsl:variable name="opus3id">
            <xsl:choose>
                <!--
                    <bibtex:abstract>http://opus.kobv.de/zib/volltexte/2010/1229</bibtex:abstract>
                    <bibtex:abstr>http://opus.kobv.de/zib/volltexte/2005/876/</bibtex:abstr>
                    <bibtex:abstr>http://opus.kobv.de/zib/volltexte/2008/1138/index.html</bibtex:abstr>
                -->
                <xsl:when test="contains($abstract, 'http://opus.kobv.de/zib/volltexte/')">
                    <xsl:value-of select="php:function('preg_replace', '/^[\d]{4}\/([\d]+)\/?$/', '$1', substring-after($abstract, '/volltexte/'))" />
                </xsl:when>
                <xsl:when test="contains($abstr, 'http://opus.kobv.de/zib/volltexte/')">
                    <xsl:value-of select="php:function('preg_replace', '/^[\d]{4}\/([\d]+)\/(index\.html)?$/', '$1', substring-after($abstr, '/volltexte/'))" />
                  </xsl:when>
                <xsl:otherwise></xsl:otherwise>
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
            <xsl:if test="string-length($organization) > 0">
                <xsl:attribute name="CreatingCorporation">
                    <xsl:value-of select="$organization" />
                </xsl:attribute>
            </xsl:if>

            <!-- ThesisDateAccepted -->

            <!-- Type -->
            <xsl:if test="string-length($doctype) > 0">
                <xsl:attribute name="Type">
                    <xsl:value-of select="$doctype" />
                </xsl:attribute>
            </xsl:if>

            <!-- Edition -->
            <xsl:if test="string-length($edition) > 0">
                <xsl:attribute name="Edition">
                    <xsl:value-of select="$edition" />
                </xsl:attribute>
            </xsl:if>

            <!-- Issue -->
            <xsl:if test="string-length($number) > 0">
                <xsl:attribute name="Issue">
                    <xsl:value-of select="$number" />
                </xsl:attribute>
            </xsl:if>

            <!-- Language -->
            <xsl:attribute name="Language">
                <xsl:choose>
                    <xsl:when test="string-length($language) > 0">
                        <xsl:call-template name="mapLanguage">
                            <xsl:with-param name="lang">
                                <xsl:value-of select="$language" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>eng</xsl:text>
                    </xsl:otherwise>
               </xsl:choose>
           </xsl:attribute>
            

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
                <xsl:attribute name="PublishedYear"><xsl:value-of select="php:function('preg_replace', '/[^\d]/','', $year)" /></xsl:attribute>
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

            <!-- Belongs to Bibliography -->
            <xsl:attribute name="BelongsToBibliography">
                <xsl:value-of select="$belongsToBibliography" />
            </xsl:attribute>

            <!-- EXTERNAL FILEDS -->
            <!-- TitleMain -->
            <xsl:if test="string-length($title) > 0">
                <xsl:element name="TitleMain">
                    <xsl:attribute name="Value"><xsl:value-of select="$title" /></xsl:attribute>
                    <xsl:attribute name="Language">
                        <xsl:choose>
                            <xsl:when test="string-length($language) > 0">
                                <xsl:call-template name="mapLanguage">
                                    <xsl:with-param name="lang">
                                        <xsl:value-of select="$language" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>eng</xsl:text>
                            </xsl:otherwise>
                       </xsl:choose>
                    </xsl:attribute>
                 </xsl:element>
            </xsl:if>

            <!-- TitleAbstract -->
            <xsl:if test="string-length($abstract) > 0">
                <xsl:element name="TitleAbstract">
                    <xsl:attribute name="Value"><xsl:value-of select="$abstract" /></xsl:attribute>
                    <xsl:if test="string-length($language) > 0">
                        <xsl:attribute name="Language">
                            <xsl:call-template name="mapLanguage">
                                <xsl:with-param name="lang">
                                    <xsl:value-of select="$language" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:attribute>
                    </xsl:if>
                </xsl:element>
            </xsl:if>

            <!-- TitleParent -->
            <xsl:if test="string-length($journal) > 0">
                <xsl:element name="TitleParent">
                    <xsl:attribute name="Value"><xsl:value-of select="$journal" /></xsl:attribute>
                    <xsl:attribute name="Language">
                        <xsl:choose>
                            <xsl:when test="string-length($language) > 0">
                                <xsl:call-template name="mapLanguage">
                                    <xsl:with-param name="lang">
                                        <xsl:value-of select="$language" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>eng</xsl:text>
                            </xsl:otherwise>
                       </xsl:choose>
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>

            <xsl:if test="string-length($booktitle) > 0">
                <xsl:element name="TitleParent">
                    <xsl:attribute name="Value"><xsl:value-of select="$booktitle" /></xsl:attribute>
                    <xsl:attribute name="Language">
                        <xsl:choose>
                            <xsl:when test="string-length($language) > 0">
                                <xsl:call-template name="mapLanguage">
                                    <xsl:with-param name="lang">
                                        <xsl:value-of select="$language" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>eng</xsl:text>
                            </xsl:otherwise>
                       </xsl:choose>
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- TitleSub -->
            <xsl:if test="string-length($series) > 0">
                <xsl:element name="TitleSub">
                <xsl:attribute name="Value">
                        <xsl:value-of select="$series" />
                    </xsl:attribute>
                    <xsl:if test="string-length($language) > 0">
                        <xsl:attribute name="Language">
                            <xsl:call-template name="mapLanguage">
                                <xsl:with-param name="lang">
                                    <xsl:value-of select="$language" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:attribute>
                    </xsl:if>
                </xsl:element>
            </xsl:if>

            <!-- IdentifierIsbn -->

            <!-- IdentifierIssn -->

            <!-- IdentifierDoi -->
            <!-- TODO: vorher parsen -->
            <xsl:if test="string-length($doi) > 0">
                <xsl:element name="IdentifierDoi">
                    <xsl:attribute name="Value"><xsl:value-of select="$doi" /></xsl:attribute>
                </xsl:element>
            </xsl:if>


            <!-- IdentifierUrl -->
            <xsl:if test="string-length($bdsk-url-1) > 0">
                <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$bdsk-url-1" /></xsl:with-param>
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($book) > 0">
                 <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$book" /></xsl:with-param>
                 </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($key) > 0">
                <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$key" /></xsl:with-param>
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($pdf) > 0">
                 <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$pdf" /></xsl:with-param>
                 </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($postscript) > 0">
                 <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$postscript" /></xsl:with-param>
                 </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($ps) > 0">
                 <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$ps" /></xsl:with-param>
                 </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($report) > 0">
                 <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$report" /></xsl:with-param>
                 </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($revised) > 0">
                 <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$revised" /></xsl:with-param>
                 </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($url) > 0">
                <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$url" /></xsl:with-param>
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($urlhtml) > 0">
                 <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$urlhtml" /></xsl:with-param>
                 </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($urlpdf) > 0">
                <xsl:call-template name="AddIdentifier">
                    <xsl:with-param name="url"><xsl:value-of select="$urlpdf" /></xsl:with-param>
                </xsl:call-template>
            </xsl:if>


            <!-- IdentifierOld -->
            <xsl:if test="string-length($id) > 0">
                <xsl:element name="IdentifierOld">
                    <xsl:attribute name="Value"><xsl:value-of select="$id" /></xsl:attribute>
                </xsl:element>
            </xsl:if>


            <!-- ReferenceUrl-->
            <xsl:if test="string-length($abstr) > 0">
                <xsl:call-template name="AddReference">
                    <xsl:with-param name="label">abstract</xsl:with-param>
                    <xsl:with-param name="url"><xsl:value-of select="$abstr" /></xsl:with-param>
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($urlabstract) > 0">
                <xsl:call-template name="AddReference">
                    <xsl:with-param name="label">abstract</xsl:with-param>
                    <xsl:with-param name="url"><xsl:value-of select="$urlabstract" /></xsl:with-param>
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length($preprint) > 0">
                <xsl:call-template name="AddReference">
                    <xsl:with-param name="label">preprint</xsl:with-param>
                    <xsl:with-param name="url"><xsl:value-of select="$preprint" /></xsl:with-param>
                </xsl:call-template>
            </xsl:if>


            <!-- Note -->
            <xsl:if test="string-length($note) > 0">
                <xsl:element name="Note">
                    <xsl:attribute name="Visibility">public</xsl:attribute>
                    <xsl:attribute name="Message"><xsl:value-of select="$note" /></xsl:attribute>
                </xsl:element>
            </xsl:if>
            <xsl:if test="string-length($comment) > 0">
               <xsl:element name="Note">
                    <xsl:attribute name="Visibility">public</xsl:attribute>
                    <xsl:attribute name="Message"><xsl:value-of select="$comment" /></xsl:attribute>
               </xsl:element>
            </xsl:if>
            <xsl:if test="string-length($annote) > 0">
               <xsl:element name="Note">
                    <xsl:attribute name="Visibility">public</xsl:attribute>
                    <xsl:attribute name="Message"><xsl:value-of select="$annote" /></xsl:attribute>
               </xsl:element>
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
            <xsl:if test="string-length($chapter) > 0">
                 <xsl:element name="Enrichment">
                     <xsl:attribute name="KeyName">chapter</xsl:attribute>
                     <xsl:attribute name="Value"><xsl:value-of select="$chapter" /></xsl:attribute>
                 </xsl:element>
            </xsl:if>

            <!-- Reference from Article to Preprint -->
            <xsl:if test="string-length($reportid) > 0">
                <xsl:element name="Enrichment">
                    <xsl:attribute name="KeyName">reportid</xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:call-template name="getReportId">
                            <xsl:with-param name="id">
                                <xsl:value-of select="$reportid" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- Referenced Opus3-Identifier -->
            <xsl:if test="string-length($opus3id) > 0">
                <xsl:element name="Enrichment">
                    <xsl:attribute name="KeyName">opus3id</xsl:attribute>
		<xsl:attribute name="Value"><xsl:value-of select="$opus3id" /></xsl:attribute>
                </xsl:element>
            </xsl:if>	    

            <!-- Institutes / WorkingGroups / Persons -->
            <xsl:if test="string-length($selectiontags) > 0">
               <xsl:call-template name="AddPublicationLists">
                    <xsl:with-param name="list"><xsl:value-of select="$selectiontags" /></xsl:with-param>
                    <xsl:with-param name="delimiter">,</xsl:with-param>
                </xsl:call-template>
            </xsl:if>

            <!-- SubjectUncontrolled -->
            <xsl:if test="string-length($keywords) > 0">
                <xsl:call-template name="AddSubjects">
                    <xsl:with-param name="type">SubjectUncontrolled</xsl:with-param>
                    <xsl:with-param name="list">
                        <xsl:value-of select="$keywords" />
                    </xsl:with-param>
                    <xsl:with-param name="delimiter">,</xsl:with-param>
                    <xsl:with-param name="language">eng</xsl:with-param>
                </xsl:call-template>
            </xsl:if>

        </xsl:element>
   </xsl:template>

</xsl:stylesheet>
