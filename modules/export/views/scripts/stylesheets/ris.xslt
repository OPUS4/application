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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    exclude-result-prefixes="php">

    <xsl:output method="text" omit-xml-declaration="yes"/>

    <xsl:template match="/">
        <xsl:apply-templates select="Documents" />
    </xsl:template>

    <xsl:template match="Documents">
        <xsl:apply-templates select="Opus_Document" />
    </xsl:template>

    <!-- Suppress spilling values with no corresponding templates
      <xsl:template match="@*|node()" /> -->                                       
    <!--
        here you can change the order of the fields, just change the order of the
        apply-templates-rows
        if there is a choose-block for the field, you have to move the whole
        choose-block
        if you wish new fields, you have to add a new line xsl:apply-templates...
        and a special template for each new field below, too
    -->
    <xsl:template match="Opus_Document">

    <!--  Preprocessing: defining variable year -->
        
        <xsl:variable name="year">
            <xsl:choose>
                <xsl:when test="string-length(normalize-space(CompletedDate/@Year)) > 0">
                    <xsl:value-of select="CompletedDate/@Year" />
                </xsl:when>                
                <xsl:when test="string-length(normalize-space(PublishedDate/@Year)) > 0">
                    <xsl:value-of select="PublishedDate/@Year" />
                </xsl:when>                
                <xsl:when test="string-length(normalize-space(@CompletedYear)) > 0">
                    <xsl:value-of select="@CompletedYear" />
                </xsl:when>
                <xsl:when test="string-length(normalize-space(@PublishedYear)) > 0">
                    <xsl:value-of select="@PublishedYear" />
                </xsl:when>
           </xsl:choose>
        </xsl:variable>

        <xsl:choose>
            <xsl:when test="@Type='annotation'">
                <xsl:text>TY  - LEGAL</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Entscheidungs- oder Urteilsanmerkung</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='article'">
                <xsl:text>TY  - JOUR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Wissenschaftlicher Artikel</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='bachelorthesis'">
                <xsl:text>TY  - THES</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Abschlussarbeit (Bachelor)</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='book'">
                <xsl:text>TY  - BOOK</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Buch</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='bookpart'">
                <xsl:text>TY  - CHAP</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Teil eines Buches</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='conferenceobject'">
                <xsl:text>TY  - CPAPER</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Konferenzveröffentlichung</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='conferenceabstract'">
                <xsl:text>TY  - ABST</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Meeting Abstract</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='conferencepaper'">
                <xsl:text>TY  - CPAPER</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Konferenzpaper</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='conferenceposter'">
                <xsl:text>TY  - CPAPER</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Konferenzposter</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='conferenceproceedings'">
                <xsl:text>TY  - CONF</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Conference proceedings</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='conferenceslides'">
                <xsl:text>TY  - SLIDE</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Konferenzfolien</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='contributiontoperiodical'">
                <xsl:text>TY  - MGZN</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Beitrag zu einem Periodikum</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='corrigendum'">
                <xsl:text>TY  - JOUR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Korrigendum</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='coursematerial'">
               <xsl:text>TY  - GEN</xsl:text>
               <xsl:text>&#10;</xsl:text>
               <xsl:text>U1  - Lehrmaterial</xsl:text>
           </xsl:when>
            <xsl:when test="@Type='datapaper'">
                <xsl:text>TY  - JOUR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Data Paper</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='doctoralthesis'">
                <xsl:text>TY  - THES</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Dissertation oder Habilitation</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='dynamicwebresource'">
                <xsl:text>TY  - BLOG</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Dynamische Online-Ressource</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='dynamicwebresourcepart'">
                <xsl:text>TY  - BLOG</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Teil einer dynamischen Ressource</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='editedcollection'">
                <xsl:text>TY  - EDBOOK</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Sammelband</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='editorial'">
                <xsl:text>TY  - JOUR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Vorwort</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='habilitation'">
                <xsl:text>TY  - THES</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Habilitation</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='image'">
                <xsl:text>TY  - ADVS</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Bild</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='lecture'">
               <xsl:text>TY  - GEN</xsl:text>
               <xsl:text>&#10;</xsl:text>
               <xsl:text>U1  - Vorlesung</xsl:text>
           </xsl:when>
            <xsl:when test="@Type='letter'">
                <xsl:text>TY  - JOUR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>CY  - Letter to the Editor</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='masterthesis'">
                <xsl:text>TY  - THES</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Abschlussarbeit (Master)</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='monograph'">
                <xsl:text>TY  - BOOK</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Monographie</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='movingimage'">
                <xsl:text>TY  - VIDEO</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Bewegte Bilder</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='musicalnotation'">
                <xsl:text>TY  - MUSIC</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Noten (Musik)</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='other'">
                <xsl:text>TY  - GEN</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Sonstiges</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='periodical'">
                <xsl:text>TY  - JFULL</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Periodikum</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='periodicalpart'">
                <xsl:text>TY  - JOUR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Teil eines Periodikums</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='preprint'">
                <xsl:text>TY  - INPR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Preprint</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='report'">
                <xsl:text>TY  - RPRT</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Verschiedenartige Texte</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='researcharticle'">
                <xsl:text>TY  - JOUR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Forschungsartikel</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='researchdata'">
                <xsl:text>TY  - GEN</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Forschungsdaten</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='review'">
                <xsl:text>TY  - GEN</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Rezension</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='reviewarticle'">
                <xsl:text>TY  - JOUR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Übersichtsartikel</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='software'">
                <xsl:text>TY  - COMP</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Software</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='softwarepaper'">
                <xsl:text>TY  - JOUR</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Softwareartikel</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='sound'">
                <xsl:text>TY  - SOUND</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Ton</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='sourceedition'">
                <xsl:text>TY  - EDBOOK</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Quellenedition</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='studythesis'">
                <xsl:text>TY  - THES</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Studienarbeit</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='website'">
                <xsl:text>TY  - WEB</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Website</xsl:text>
            </xsl:when>
            <xsl:when test="@Type='workingpaper'">
                <xsl:text>TY  - RPRT</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Arbeitspapier</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>TY  - GEN</xsl:text>
                <xsl:text>&#10;</xsl:text>
                <xsl:text>U1  - Sonstiges</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:text>&#10;</xsl:text>

        <xsl:if test="string-length(PersonAuthor/@LastName)>0">
            <xsl:apply-templates select="PersonAuthor" />
        </xsl:if>
        <xsl:if test="string-length(@CreatingCorporation)>0">
            <xsl:text>IN  - </xsl:text>
            <xsl:value-of select="@CreatingCorporation"/>
            <xsl:text>&#10;</xsl:text>
        </xsl:if>

        <xsl:if test="string-length(PersonEditor/@LastName)>0">
            <xsl:apply-templates select="PersonEditor" />
        </xsl:if>
        <xsl:if test="string-length(@ContributingCorporation)>0">
            <xsl:text>IN  - </xsl:text>
            <xsl:value-of select="@ContributingCorporation"/>
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        
        <xsl:if test="string-length(TitleMain/@Value)>0">
            <xsl:apply-templates select="TitleMain" />
        </xsl:if>
        <xsl:if test="string-length(TitleSub/@Value)>0">
            <xsl:apply-templates select="TitleSub" />
        </xsl:if>
        <xsl:if test="string-length(TitleParent/@Value)>0">
            <xsl:apply-templates select="TitleParent" />
        </xsl:if>
        <xsl:if test="string-length(TitleAbstract/@Value)>0">
            <xsl:apply-templates select="TitleAbstract" />
        </xsl:if>
        <xsl:if test="string-length(TitleAdditional/@Value)>0">
           <xsl:apply-templates select="TitleAdditional" />
       </xsl:if>                                                      
        <xsl:if test="Series">
            <xsl:apply-templates select="Series" />
        </xsl:if>
        <xsl:if test="string-length(Enrichment[@KeyName='VolumeSource']/@Value)>0">
           <xsl:apply-templates select="Enrichment[@KeyName='VolumeSource']" />
        </xsl:if>                                             
        <xsl:if test="string-length(Subject/@Value)>0">
            <xsl:apply-templates select="Subject" />
        </xsl:if>

        <xsl:if test="string-length($year)>0">
            <xsl:text>Y1  - </xsl:text>
            <xsl:value-of select="$year"/>
            <xsl:text>&#10;</xsl:text>
        </xsl:if>   

        <xsl:if test="string-length(Identifier[@Type = 'isbn']/@Value)>0">
            <xsl:apply-templates select="Identifier[@Type = 'isbn']"/>
        </xsl:if>
        <xsl:if test="string-length(Identifier[@Type = 'issn']/@Value)>0">
            <xsl:apply-templates select="Identifier[@Type = 'issn']"/>
        </xsl:if>
        <xsl:if test="string-length(Identifier[@Type = 'urn']/@Value)>0">
            <xsl:apply-templates select="Identifier[@Type = 'urn']"/>
        </xsl:if>
        <xsl:if test="string-length(Identifier[@Type = 'doi']/@Value) > 0">                                                                                
            <xsl:apply-templates select="Identifier[@Type = 'doi']"/>
        </xsl:if>
        <xsl:if test="string-length(Identifier[@Type = 'url']/@Value)>0">
            <xsl:apply-templates select="Identifier[@Type = 'url']"/>
        </xsl:if>

        <xsl:if test="string-length(Identifier[@Type = 'pmid']/@Value) > 0">
            <xsl:apply-templates select="Identifier[@Type = 'pmid']"/>
        </xsl:if>
        <xsl:if test="string-length(Identifier[@Type = 'arxiv']/@Value) > 0">
            <xsl:apply-templates select="Identifier[@Type = 'arxiv']"/>
        </xsl:if>
        
        <xsl:if test="string-length(Note/@Message)>0">
            <xsl:apply-templates select="Note" />
        </xsl:if>

        <xsl:if test="string-length(@Volume)>0">
            <xsl:text>VL  - </xsl:text>
            <xsl:value-of select="@Volume"/>
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <xsl:if test="string-length(@Issue)>0">
            <xsl:text>IS  - </xsl:text>
            <xsl:value-of select="@Issue"/>
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <xsl:if test="string-length(@ArticleNumber) > 0">
            <xsl:text>AR  - </xsl:text>
            <xsl:value-of select="@ArticleNumber" />
            <xsl:text>&#10;</xsl:text>
            <xsl:text>S2  - </xsl:text>
            <xsl:value-of select="@ArticleNumber"/>
            <xsl:text>&#10;</xsl:text>
        </xsl:if>

        <xsl:if test="string-length(@PageFirst) > 0">
            <xsl:text>SP  - </xsl:text>
            <xsl:value-of select="@PageFirst" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>           
        <xsl:if test="string-length(@PageLast) > 0">
            <xsl:text>EP  - </xsl:text>
            <xsl:value-of select="@PageLast" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <xsl:choose>
            <xsl:when test="string-length(@PageNumber) > 0 and string-length(normalize-space(@PageFirst)) = 0 and string-length(normalize-space(@PageLast)) = 0">
                    <xsl:text>SP  - </xsl:text>
                    <xsl:value-of select="@PageNumber" />
                    <xsl:text>&#10;</xsl:text>
            </xsl:when>
            <xsl:when test="string-length(@PageNumber) > 0">
                    <xsl:text>S1  - </xsl:text>
                    <xsl:value-of select="@PageNumber" />
                    <xsl:text>&#10;</xsl:text>
            </xsl:when>
        </xsl:choose>

        <xsl:if test="string-length(@PublisherName)>0">
            <xsl:text>PB  - </xsl:text>
            <xsl:value-of select="@PublisherName" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>

        <xsl:if test="string-length(@PublisherPlace)>0">
            <xsl:text>CY  - </xsl:text>
            <xsl:value-of select="@PublisherPlace"/>
            <xsl:text>&#10;</xsl:text>
        </xsl:if>

        <xsl:if test="string-length(@Edition)>0">
            <xsl:text>ET  - </xsl:text>
            <xsl:value-of select="@Edition" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>

        <xsl:text>ER  - </xsl:text>
        <xsl:text>&#10;</xsl:text>

    </xsl:template>


    <!-- here begins the special templates for the fields -->
    <!-- Templates for "external fields". -->

    <xsl:template match="Enrichment[@KeyName='VolumeSource']">
        <xsl:text>VL  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'url']">
        <xsl:text>UR  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'doi']">
        <xsl:text>U6  - </xsl:text>
        <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'doi.resolverUrl')"/>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
        <xsl:text>DO  - </xsl:text>
        <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'doi.resolverUrl')"/>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'urn']">
        <xsl:if test="string-length(normalize-space(../Identifier[@Type = 'doi']/@Value)) = 0">
            <xsl:text>U6  - </xsl:text>
            <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'urn.resolverUrl')"/>
            <xsl:value-of select="@Value" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <!--NEW-->
        <xsl:text>UN  - </xsl:text>
        <xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'urn.resolverUrl')"/>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'isbn']">
        <xsl:text>SN  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
        <!--NEW-->
        <xsl:text>SB  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'issn']">
        <xsl:text>SN  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
        <!--NEW-->
        <xsl:text>SS  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>
    
    <xsl:template match="Identifier[@Type = 'pmid']">
        <!--NEW-->
        <xsl:text>PM  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>
    
    <xsl:template match="Identifier[@Type = 'arxiv']">
        <!--NEW-->
        <xsl:text>AX  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Note[@Visibility='public']">
        <xsl:text>N1  - </xsl:text>
        <xsl:value-of select="@Message"/>
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Subject[@Type='uncontrolled' or @Type='swd']">
        <xsl:text>KW  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="PersonAuthor">
        <xsl:text>A1  - </xsl:text>
        <xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="PersonEditor">
        <xsl:text>ED  - </xsl:text>
        <xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="TitleMain">
        <xsl:text>T1  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="TitleSub">
        <xsl:text>BT  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="TitleAbstract">
        <xsl:text>N2  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
        <xsl:text>AB  - </xsl:text>
        <xsl:value-of select="@Value"/>
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <!-- uebergeordnete Einheit wie Buch, Blog etc. nach T2, Serie nach T3, Zeitschriften nach JF -->
    <xsl:template match="TitleParent">
        <xsl:choose>
            <xsl:when test="../@Type='bachelorthesis' or ../@Type='book' or ../@Type='conferenceproceedings' or ../@Type='doctoralthesis' or ../@Type='editedcollection' or ../@Type='habilitation' or ../@Type='lecture' or ../@Type='masterthesis' or ../@Type='monograph' or ../@Type='studythesis' or ../@Type='workingpaper'">
                <xsl:text>T3  - </xsl:text>
                <xsl:value-of select="@Value" />
                <xsl:text>&#10;</xsl:text>
            </xsl:when>
            <xsl:when test="../@Type='article' or ../@Type='conferenceabstract' or ../@Type='contributiontoperiodical' or ../@Type='corrigendum' or ../@Type='datapaper' or ../@Type='dynamicwebresourcepart' or ../@Type='editorial' or ../@Type='letter' or ../@Type='periodicalpart' or ../@Type='researcharticle' or ../@Type='review' or ../@Type='reviewarticle' or ../@Type='softwarepaper'">
                <xsl:text>JF  - </xsl:text>
                <xsl:value-of select="@Value" />
                <xsl:text>&#10;</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>T2  - </xsl:text>
                <xsl:value-of select="@Value" />
                <xsl:text>&#10;</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="TitleAdditional">                           
        <xsl:text>TT  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template> 

    <xsl:template match="Edition">
        <xsl:text>ET  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Series[@Visible='1']">
        <xsl:text>T3  - </xsl:text>
        <xsl:value-of select="@Title" />
        <xsl:if test="@Number != ''">
            <xsl:text> - </xsl:text>
            <xsl:value-of select="@Number" />
            <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

</xsl:stylesheet>
