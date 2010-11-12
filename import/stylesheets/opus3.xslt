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
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009-2010 OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: opus3.xslt 6088 2010-09-30 13:39:46Z gmaiwald $
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl">

    <xsl:output method="xml" indent="no" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />

    <xsl:template match="/">
        <xsl:element name="Documents">
            <xsl:apply-templates select="/mysqldump/database/table_data[@name='opus']/row" />
        </xsl:element>
    </xsl:template>

    <!--
    Suppress fields with nil value or empty string
    -->
    <xsl:template match="table_data/row/field[@xsi:nil='true']" priority="1" />

    <!-- All Fields of table 'opus' -->
    <xsl:template match="table_data[@name='opus']/row">
        <xsl:element name="Opus_Document">
            <!-- Variables for internal use -->
            <xsl:variable name="OriginalID">
                <xsl:value-of select="field[@name='source_opus']" />
            </xsl:variable>
            <xsl:variable name="date_accepted">
                <xsl:value-of select="/mysqldump/database/table_data[@name='opus_diss']/row[field[@name='source_opus']=$OriginalID]/field[@name='date_accepted']" />
            </xsl:variable>
            <xsl:variable name="date_creation">
                <xsl:value-of select="field[@name='date_creation']" />
            </xsl:variable>
            <xsl:variable name="date_modified">
                <xsl:value-of select="field[@name='date_modified']" />
            </xsl:variable>
            <!-- Ist nicht relevant
            <xsl:variable name="date_valid">
                <xsl:value-of select="field[@name='date_valid']" />
            </xsl:variable>
            -->

            <!-- CompletedDate -->
            <!--CompletedDate is left out, because Opus3 only stores the year -->

            <!-- CompletedYear -->
            <xsl:attribute name="CompletedYear">
                <xsl:value-of select="field[@name='date_year']" />
            </xsl:attribute>

            <!-- ContributingCorporation -->
            <xsl:if test="string-length(field[@name='contributors_corporate']) > 0">
                <xsl:attribute name="ContributingCorporation">
                    <xsl:value-of select="field[@name='contributors_corporate']" />
                </xsl:attribute>
            </xsl:if>

            <!-- CreatingCorporation -->
            <xsl:if test="string-length(field[@name='creator_corporate']) > 0">
                <xsl:attribute name="CreatingCorporation">
                    <xsl:value-of select="field[@name='creator_corporate']" />
                </xsl:attribute>
            </xsl:if>

            <!-- ThesisDateAccepted -->
            <xsl:if test="$date_accepted >0">
                <xsl:attribute name="ThesisDateAccepted">
                    <xsl:value-of select="php:function('date', 'd.m.Y', $date_accepted)" />
                </xsl:attribute>
            </xsl:if>

            <!-- Type-->
            <xsl:attribute name="Type">
                <xsl:choose>
                   <xsl:when test="field[@name='type']='1'">
                        <xsl:text>report</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='2'">
                        <xsl:text>article</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='4'">
                        <xsl:text>book</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='5'">
                        <xsl:text>bookpart</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='7'">
                        <xsl:text>masterthesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='8'">
                        <xsl:text>doctoralthesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='9'">
                        <xsl:text>book</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='11'">
                        <xsl:text>misc</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='15'">
                        <xsl:text>conferenceobject</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='16'">
                        <xsl:text>article</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='17'">
                        <xsl:text>workingpaper</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='19'">
                        <xsl:text>studythesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='20'">
                        <xsl:text>report</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='22'">
                        <xsl:text>preprint</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='23'">
                        <xsl:text>misc</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='24'">
                        <xsl:text>habilitation</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='25'">
                        <xsl:text>bachelorthesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='26'">
                        <xsl:text>lecture</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='71'">
                        <xsl:text>article</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='76'">
                        <xsl:text>misc</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='78'">
                        <xsl:text>lecture</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='79'">
                        <xsl:text>book</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>other</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>

            <!-- Edition -->

            <!-- Issue -->

            <!-- Language -->
            <!-- Language might be a multivalue field in Opus4; in Opus3 there can be only one language -->
            <!-- if the document type defines it as multivalue, there will be problems importing -->
            <xsl:attribute name="Language">
                <xsl:call-template name="mapLanguage">
                    <xsl:with-param name="lang">
                        <xsl:value-of select="field[@name='language']" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>

            <!-- PageFirst -->

            <!-- PageLast -->

            <!-- PageNumber -->

            <!-- PublicationState -->

            <!-- PublishedDate -->
            <!-- PublishedDate is left out, because Opus3 only stores the year -->

            <!-- PublishedYear -->
            <!-- PublishedYear is not stored by Opus 3, but necessary for some doctypes in OPUS4, so import date_year into both fields -->
            <xsl:attribute name="PublishedYear">
                <xsl:value-of select="field[@name='date_year']" />
            </xsl:attribute>

            <!-- PublisherName -->

            <!-- PublisherPlace -->

            <!-- ServerDateModified -->
            <xsl:if test="$date_modified > 0">
                <xsl:attribute name="ServerDateModified">
                    <xsl:value-of select="php:function('date', 'c', $date_modified)" />
                </xsl:attribute>
            </xsl:if>

            <!-- ServerDatePublished -->
            <xsl:if test="$date_creation > 0">
                <xsl:attribute name="ServerDatePublished">
                    <xsl:value-of select="php:function('date', 'c', $date_creation)" />
                </xsl:attribute>
            </xsl:if>

            <!-- ServerDateUnlocking -->

            <!-- ServerState -->
            <!-- All publications in the OPUS table are published, so we can set this statically -->
            <xsl:attribute name="ServerState">
                <xsl:text>published</xsl:text>
            </xsl:attribute>

            <!-- Volume -->

            <!-- BelongstoBibliography -->

            <!-- All Related Identifiers -->
            <!-- IdentifierOpus3 -->
            <xsl:if test="string-length(field[@name='source_opus']) > 0">
                <xsl:element name="IdentifierOpus3">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='source_opus']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- IdentifierUrn -->
            <xsl:if test="string-length(field[@name='urn']) > 0">
                <xsl:element name="IdentifierUrn">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='urn']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- IdentifierIsbn -->
            <xsl:if test="string-length(field[@name='isbn']) > 0">
                <xsl:element name="IdentifierIsbn">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='isbn']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- IdentifierUrl -->
            <xsl:if test="string-length(field[@name='url']) > 0">
                <xsl:element name="IdentifierUrl">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='url']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>


            <!-- All Related Titles (Main/Abstract/...) -->
            <!-- TitleMain -->
            <xsl:if test="string-length(field[@name='title']) > 0">
                <xsl:element name="TitleMain">
                    <xsl:attribute name="Language">
                        <xsl:call-template name="mapLanguage">
                            <xsl:with-param name="lang">
                                <xsl:value-of select="field[@name='language']" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='title']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>
            <!-- TitleMain (english) -->
            <xsl:if test="string-length(field[@name='title_en']) > 0">
                <xsl:element name="TitleMain">
                    <xsl:attribute name="Language">
                        <xsl:call-template name="mapLanguage">
                            <xsl:with-param name="lang">eng</xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='title_en']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- TitleAbstract -->
            <xsl:if test="string-length(normalize-space(field[@name='description'])) > 0">
                <xsl:element name="TitleAbstract">
                    <xsl:attribute name="Language">
                        <xsl:choose>
                            <xsl:when test="string-length(field[@name='description_lang']) > 0">
                                <xsl:call-template name="mapLanguage">
                                    <xsl:with-param name="lang">
                                        <xsl:value-of select="field[@name='description_lang']" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="mapLanguage">
                                    <xsl:with-param name="lang">
                                        <xsl:value-of select="field[@name='language']" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="normalize-space(field[@name='description'])" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>
            <!-- TitleAbstract (2nd) -->
            <xsl:if test="string-length(normalize-space(field[@name='description2'])) > 0">
                <xsl:element name="TitleAbstract">
                    <xsl:attribute name="Language">
                        <xsl:call-template name="mapLanguage">
                            <xsl:with-param name="lang">
                                <xsl:value-of select="field[@name='description2_lang']" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="normalize-space(field[@name='description2'])" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>
            <!-- Dissertation:TitleMain, Language="deu" (2nd) -->
            <xsl:for-each select="/mysqldump/database/table_data[@name='opus_diss']/row[field[@name='source_opus']=$OriginalID]">
                <xsl:if test="string-length(field[@name='title_de'])>0">
                    <xsl:element name="TitleAbstract">
                        <xsl:attribute name="Language">
                            <xsl:call-template name="mapLanguage">
                                <xsl:with-param name="lang">ger</xsl:with-param>
                            </xsl:call-template>
                        </xsl:attribute>
                        <xsl:attribute name="Value">
                            <xsl:value-of select="field[@name='title_de']" />
                        </xsl:attribute>
                    </xsl:element>
                </xsl:if>
            </xsl:for-each>


            <!-- Old: PersonContributor -->
            <!--
            <xsl:if test="string-length(field[@name='contributors_name'])>0">
                <xsl:call-template name="AddPersons">
                    <xsl:with-param name="role">PersonContributor</xsl:with-param>
                    <xsl:with-param name="list">
                        <xsl:value-of select="field[@name='contributors_name']" />
                    </xsl:with-param>
                    <xsl:with-param name="delimiter">;</xsl:with-param>
                </xsl:call-template>
            </xsl:if>
            -->
	    
	   <!-- New: Enrichment-Contributor -->
            <xsl:if test="string-length(field[@name='contributors_name']) > 0">
                <xsl:element name="Enrichment">
                    <xsl:attribute name="KeyName">
                        <xsl:text>contributor</xsl:text>
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="normalize-space(field[@name='contributors_name'])" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>
	    
	   <!-- Enrichment-Source --> 
            <xsl:if test="string-length(field[@name='source_title']) > 0">
                <xsl:element name="Enrichment">
                    <xsl:attribute name="KeyName">
                        <xsl:text>source</xsl:text>
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="normalize-space(field[@name='source_title'])" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>	
            
            <!-- Old: OldPublisherUniversity -->
            <!--
            <xsl:if test="string-length(field[@name='publisher_university']) > 0">
                <xsl:element name="OldPublisherUniversity">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='publisher_university']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>
            -->

           <!-- New: Enrichment Institution -->
            <xsl:if test="string-length(field[@name='publisher_university']) > 0">
                <xsl:element name="Enrichment">
                    <xsl:attribute name="KeyName">
                        <xsl:text>institution</xsl:text>
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="normalize-space(field[@name='publisher_university'])" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- All Related Persons (Author/Editor/Submitter/Contributor/...)-->
            <!-- PersonSubmitter -->
            <xsl:if test="string-length(field[@name='verification'])>0">
                <xsl:call-template name="AddPersons">
                    <xsl:with-param name="role">PersonSubmitter</xsl:with-param>
                    <xsl:with-param name="list">
                        <xsl:value-of select="field[@name='verification']" />
                    </xsl:with-param>
                    <xsl:with-param name="delimiter">;</xsl:with-param>
                </xsl:call-template>
            </xsl:if>
	    
            <!-- PersonAuthor -->
            <xsl:for-each select="/mysqldump/database/table_data[@name='opus_autor']/row[field[@name='source_opus']=$OriginalID]">
                <xsl:call-template name="AddPerson" >
                    <xsl:with-param name="role">PersonAuthor</xsl:with-param>
                    <xsl:with-param name="name">
                        <xsl:value-of select="field[@name='creator_name']" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:for-each>

            <!-- PersonAdvisor -->
            <xsl:for-each select="/mysqldump/database/table_data[@name='opus_diss']/row[field[@name='source_opus']=$OriginalID]">
                <xsl:call-template name="AddPerson" >
                    <xsl:with-param name="role">PersonAdvisor</xsl:with-param>
                    <xsl:with-param name="name">
                        <xsl:value-of select="field[@name='advisor']" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:for-each>

            <!-- Subjects -->
            <xsl:if test="string-length(normalize-space(field[@name='subject_swd'])) > 0">
                <xsl:call-template name="AddSubjects">
                    <xsl:with-param name="type">SubjectSwd</xsl:with-param>
                    <xsl:with-param name="list">
                        <xsl:value-of select="field[@name='subject_swd']" />
                    </xsl:with-param>
                    <xsl:with-param name="delimiter">,</xsl:with-param>
                    <xsl:with-param name="language">ger</xsl:with-param>
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length(normalize-space(field[@name='subject_uncontrolled_german'])) > 0">
                <xsl:call-template name="AddSubjects">
                    <xsl:with-param name="type">SubjectUncontrolled</xsl:with-param>
                    <xsl:with-param name="list">
                        <xsl:value-of select="field[@name='subject_uncontrolled_german']" />
                    </xsl:with-param>
                    <xsl:with-param name="delimiter">,</xsl:with-param>
                    <xsl:with-param name="language">ger</xsl:with-param>
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="string-length(normalize-space(field[@name='subject_uncontrolled_english'])) > 0">
                <xsl:call-template name="AddSubjects">
                    <xsl:with-param name="type">SubjectUncontrolled</xsl:with-param>
                    <xsl:with-param name="list">
                        <xsl:value-of select="field[@name='subject_uncontrolled_english']" />
                    </xsl:with-param>
                    <xsl:with-param name="delimiter">,</xsl:with-param>
                    <xsl:with-param name="language">eng</xsl:with-param>
                </xsl:call-template>
            </xsl:if>

            <!-- Notes -->
            <xsl:if test="string-length(field[@name='bem_intern']) > 0">
                <xsl:element name="Note">
                    <xsl:attribute name="Scope">
                        <xsl:text>private</xsl:text>
                    </xsl:attribute>
                    <xsl:attribute name="Message">
                        <xsl:value-of select="field[@name='bem_intern']" />
                    </xsl:attribute>
                    <xsl:attribute name="Creator">
                        <xsl:text>unknown</xsl:text>
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>
            <xsl:if test="string-length(field[@name='bem_extern']) > 0">
                <xsl:element name="Note">
                    <xsl:attribute name="Scope">
                        <xsl:text>public</xsl:text>
                    </xsl:attribute>
                    <xsl:attribute name="Message">
                        <xsl:value-of select="field[@name='bem_extern']" />
                    </xsl:attribute>
                    <xsl:attribute name="Creator">
                        <xsl:text>unknown</xsl:text>
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>

            <!-- Old Opus3-data must be mapped later -->
            <!-- Old Licence -->
            <xsl:if test="string-length(field[@name='lic']) > 0">
                <xsl:element name="OldLicence">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='lic']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>  
            
            <!-- OldGrantor -->
            <xsl:for-each select="/mysqldump/database/table_data[@name='opus_diss']/row[field[@name='source_opus']=$OriginalID]">
                <xsl:element name="OldGrantor">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='publisher_faculty']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:for-each>

            <!-- OldSeries -->
            <xsl:for-each select="/mysqldump/database/table_data[@name='opus_schriftenreihe']/row[field[@name='source_opus']=$OriginalID]">
                <xsl:element name="OldSeries">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='sr_id']" />
                    </xsl:attribute>
                    <xsl:attribute name="Issue">
                        <xsl:value-of select="field[@name='sequence_nr']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:for-each>

            <!-- OldCollections -->
            <xsl:for-each select="/mysqldump/database/table_data[@name='opus_coll']/row[field[@name='source_opus']=$OriginalID]">
                <xsl:element name="OldCollections">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='coll_id']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:for-each>

            <!-- OldInstitutes -->
            <xsl:for-each select="/mysqldump/database/table_data[@name='opus_inst']/row[field[@name='source_opus']=$OriginalID]">
                <xsl:element name="OldInstitute">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='inst_nr']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:for-each>

            <!-- OldClasses -->
            <xsl:for-each select="/mysqldump/database/table_data[@name='opus_klassifikationen']/row[field[@name='source_opus']=$OriginalID]">
                <xsl:element name="OldClasses">
                    <xsl:attribute name="Key">
                        <xsl:value-of select="field[@name='name_kurz']" />
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='class']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:for-each>

            <!-- OldDdc -->
            <xsl:if test="string-length(field[@name='sachgruppe_ddc']) > 0">
                <xsl:element name="OldDdc">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="field[@name='sachgruppe_ddc']" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>
        </xsl:element>
    </xsl:template>

    <!-- This template maps Language -->
    <xsl:template name="mapLanguage">
        <xsl:param name="lang" required="yes" />
        <xsl:if test="$lang='ger'">
            <xsl:text>deu</xsl:text>
        </xsl:if>
        <xsl:if test="$lang='eng'">
            <xsl:text>eng</xsl:text>
        </xsl:if>
        <xsl:if test="$lang='fre'">
            <xsl:text>fra</xsl:text>
        </xsl:if>
        <xsl:if test="$lang='rus'">
            <xsl:text>rus</xsl:text>
        </xsl:if>
        <xsl:if test="$lang='mul'">
            <xsl:text>deu</xsl:text>
        </xsl:if>
        <xsl:if test="$lang='mis'">
            <xsl:text>eng</xsl:text>
        </xsl:if>
    </xsl:template> 

    <!-- This template adds multiple Persons from a <delimiter>-separated list to Opus4-DB (recursively) -->
    <xsl:template name="AddPersons">
        <xsl:param name="role" required="yes" />
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
        <xsl:call-template name="AddPerson">
             <xsl:with-param name="role" select="$role" />
             <xsl:with-param name="name" select="$first" />
        </xsl:call-template>
        <xsl:if test="$remaining">
            <xsl:call-template name="AddPersons">
                <xsl:with-param name="role"><xsl:value-of select="$role" /> </xsl:with-param>
                <xsl:with-param name="list"><xsl:value-of select="$remaining" /> </xsl:with-param>
                <xsl:with-param name="delimiter"><xsl:value-of select="$delimiter" /> </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <!-- Erzeugt ein Personenelement -->
    <xsl:template name="AddPerson">
        <xsl:param name="role" required="yes" />
        <xsl:param name="name" required="yes" />
        <xsl:element name="{$role}">
            <xsl:attribute name="AcademicTitle">
                <xsl:call-template name="getAcademicTitle">
                    <xsl:with-param name="name">
                        <xsl:value-of select="$name" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>
             <xsl:attribute name="Email">
                <xsl:call-template name="getEmail">
                    <xsl:with-param name="name">
                        <xsl:value-of select="$name" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>
            <xsl:attribute name="FirstName">
                <xsl:call-template name="getFirstName">
                    <xsl:with-param name="name">
                        <xsl:value-of select="$name" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>
            <xsl:attribute name="LastName">
                <xsl:call-template name="getLastName">
                    <xsl:with-param name="name">
                        <xsl:value-of select="$name" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    
   <!-- Holt den Vornamen des Autors -->
   <xsl:template name="getAcademicTitle">
        <xsl:param name="name" required="yes" />
        <xsl:if test="string-length(substring-after(substring-before($name,')'),'(')) > 0">
            <xsl:value-of select="substring-after(substring-before($name,')'),'(')" />
        </xsl:if>
    </xsl:template>

   <!-- Holt den Vornamen des Autors -->
   <xsl:template name="getEmail">
        <xsl:param name="name" required="yes" />
        <xsl:if test="contains($name, '@')">
            <xsl:value-of select="normalize-space($name)" />
        </xsl:if>
    </xsl:template>
    
   <!-- Holt den Vornamen des Autors -->
   <xsl:template name="getFirstName">
        <xsl:param name="name" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($name, '@')">unknown</xsl:when>
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
            <xsl:when test="contains($name, '@')">unknown</xsl:when>
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

    <!-- This Templat add Subjects to Opus4 -->
    <xsl:template name="AddSubjects">
        <xsl:param name="type" required="yes" />
        <xsl:param name="list" required="yes" />
        <xsl:param name="delimiter" required="yes" />
        <xsl:param name="language" required="yes" />
        <xsl:variable name="newlist">
            <xsl:choose>
                <xsl:when test="contains($list, $delimiter)"><xsl:value-of select="normalize-space($list)" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="concat(normalize-space($list), $delimiter)"/></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="first" select="substring-before($newlist, $delimiter)" />
        <xsl:variable name="remaining" select="substring-after($newlist, $delimiter)" />
        <xsl:call-template name="AddSubject">
             <xsl:with-param name="type" select="$type" />
             <xsl:with-param name="subject" select="$first" />
             <xsl:with-param name="language" select="$language" />
        </xsl:call-template>
        <xsl:if test="$remaining">
            <xsl:call-template name="AddSubjects">
                <xsl:with-param name="type"><xsl:value-of select="$type" /> </xsl:with-param>
                <xsl:with-param name="list"><xsl:value-of select="$remaining" /> </xsl:with-param>
                <xsl:with-param name="delimiter"><xsl:value-of select="$delimiter" /> </xsl:with-param>
                <xsl:with-param name="language"><xsl:value-of select="$language" /> </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="AddSubject">
        <xsl:param name="type" required="yes" />
        <xsl:param name="subject" required="yes" />
        <xsl:param name="language" required="yes" />
        <xsl:element name="{$type}">
            <xsl:attribute name="Language">
                <xsl:call-template name="mapLanguage">
                    <xsl:with-param name="lang">
                        <xsl:value-of select="$language" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="$subject" />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
