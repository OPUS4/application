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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_Import
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
    Suppress fields with nil value
    -->
    <xsl:template match="table_data[@name='opus']/row/field[@xsi:nil='true']" />

    <xsl:template match="table_data[@name='opus']/row">
        <xsl:element name="Opus_Document">            
            <xsl:attribute name="Type">
                <xsl:choose>
                    <xsl:when test="field[@name='type']='1'">
                        <xsl:text>manual</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='2'">
                        <xsl:text>article</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='4'">
                        <xsl:text>monograph</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='5'">
                        <xsl:text>book section</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='7'">
                        <xsl:text>master thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='8'">
                        <xsl:text>doctoral thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='9'">
                        <xsl:text>honour thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='11'">
                        <xsl:text>journal</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='15'">
                        <xsl:text>conference</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='16'">
                        <xsl:text>conference item</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='17'">
                        <xsl:text>paper</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='19'">
                        <xsl:text>study paper</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='20'">
                        <xsl:text>report</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='22'">
                        <xsl:text>preprint</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='23'">
                        <xsl:text>other</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='24'">
                        <xsl:text>habil thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='25'">
                        <xsl:text>bachelor thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='26'">
                        <xsl:text>lecture</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>other</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
            
            <!-- All publications in the OPUS table are published, so we can set this statically -->
            <!--<xsl:attribute name="PublicationState">
                <xsl:text>published</xsl:text>
            </xsl:attribute>-->
            
            <xsl:attribute name="Language">
                <xsl:value-of select="field[@name='language']" />
            </xsl:attribute>
            <xsl:attribute name="CreatingCorporation">
                <xsl:value-of select="field[@name='creator_corporate']" />
            </xsl:attribute>
            <!--<xsl:attribute name="ContributingCorporation">
                <xsl:value-of select="field[@name='contributors_corporate']" />
            </xsl:attribute>
            <xsl:attribute name="Source">
                <xsl:value-of select="field[@name='source_title']" />
            </xsl:attribute>
            <xsl:attribute name="SwbId">
                <xsl:value-of select="field[@name='source_swb']" />
            </xsl:attribute>
            <xsl:attribute name="RangeId">
                <xsl:value-of select="field[@name='bereich_id']" />
            </xsl:attribute>-->
            
            
            <!--<xsl:attribute name="CompletedYear">
                <xsl:value-of select="field[@name='date_year']" />
            </xsl:attribute>-->
            <!-- CompletedDate is not stored in OPUS3, so we take Jan 1st of the PublicationYear -->
            <!--<xsl:attribute name="CompletedDate">
                <xsl:value-of select="field[@name='date_year']" /><xsl:text>-01-01</xsl:text>
            </xsl:attribute>-->
            
            <!-- Take date_creation from OPUS3 as PublishedYear, PublishedDate and ServerDatePublished -->
            <!-- date_creation is stored as a unix timestamp -->
            <!-- its transferred by date fuinction in PHP -->
            <xsl:variable name="date_creation"><xsl:value-of select="field[@name='date_creation']" /></xsl:variable>
            <xsl:variable name="date_modified"><xsl:value-of select="field[@name='date_modified']" /></xsl:variable>
            <xsl:attribute name="PublishedYear">
                <xsl:value-of select="php:function('date', 'Y', $date_creation)" />
            </xsl:attribute>
            <xsl:attribute name="PublishedDate">
                <xsl:value-of select="php:function('date', 'Y-m-d', $date_creation)" />
            </xsl:attribute>
            <!--<xsl:attribute name="ServerDatePublished">
                <xsl:value-of select="php:function('date', 'Y-m-d H:i:s', $date_creation)" />
            </xsl:attribute>
            <xsl:attribute name="ServerDateModified">
                <xsl:value-of select="php:function('date', 'Y-m-d H:i:s', $date_modified)" />
            </xsl:attribute>-->
            
            <!-- Find persons associated with the document -->
            <xsl:call-template name="getAuthors"><xsl:with-param name="OriginalID"><xsl:value-of select="field[@name='source_opus']" /></xsl:with-param></xsl:call-template>
            <!--<xsl:call-template name="getContributors"><xsl:with-param name="contributors"><xsl:value-of select="field[@name='contributors_name']" /></xsl:with-param></xsl:call-template>-->
            <xsl:call-template name="getAdvisors"><xsl:with-param name="OriginalID"><xsl:value-of select="field[@name='source_opus']" /></xsl:with-param></xsl:call-template>
            
            <!--
            Missing fields from opus table:
            subject_swd
            publisher_university
            verification (normally eMail address of the one who uploaded the file)
            subject_uncontrolled_german
            subject_uncontrolled_english
            subject_type (classification system the document is classified with)
            date_valid
            sachgruppe_ddc
            lic (License information, not sure how this is handled currently)
            bem_intern
            bem_extern
            
            Missing fields in other opus3 tables:
            opus_coll
            opus_diss (title_de)
            opus_hashes
            opus_inst (+ institutes + faculties)
            opus_msc, opus_pacs, opus_ccs (, opus_bk) + Klassifikationstabellen (realisiert in Collections)
            opus_schriftenreihe
            -->
            
            <xsl:apply-templates select="field" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="table_data[@name='opus']/row/field[@name='title']">
        <xsl:element name="TitleMain">
            <xsl:attribute name="Language">
                <xsl:value-of select="../field[@name='language']" />
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match="table_data[@name='opus']/row/field[@name='title_en']">
        <xsl:element name="TitleMain">
            <xsl:attribute name="Language">
                <xsl:text>en</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match="table_data[@name='opus']/row/field[@name='description']">
        <xsl:element name="TitleAbstract">
            <xsl:attribute name="Language">
                <xsl:value-of select="../field[@name='description_lang']" />
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match="table_data[@name='opus']/row/field[@name='description2']">
        <xsl:element name="TitleAbstract">
            <xsl:attribute name="Language">
                <xsl:value-of select="../field[@name='description2_lang']" />
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="table_data[@name='opus']/row/field[@name='urn']">
        <xsl:element name="Urn">
            <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match="table_data[@name='opus']/row/field[@name='isbn']">
        <xsl:element name="Isbn">
            <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
        </xsl:element>
    </xsl:template>
    
    <!--<xsl:template match="table_data[@name='opus']/row/field[@name='url']">
        <xsl:element name="Url">
            <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
        </xsl:element>
    </xsl:template>-->
    
    <xsl:template name="getAuthors">
        <xsl:param name="OriginalID" required="yes" />
        <xsl:for-each select="/mysqldump/database/table_data[@name='opus_autor']/row[field[@name='source_opus']=$OriginalID]">
            <xsl:call-template name="getAuthor" />
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="getAuthor">
        <xsl:element name="PersonAuthor">
            <xsl:attribute name="AcademicTitle"></xsl:attribute>
            <xsl:attribute name="DateOfBirth"></xsl:attribute>
            <xsl:attribute name="PlaceOfBirth"></xsl:attribute>
            <xsl:attribute name="Email"></xsl:attribute>
            <xsl:attribute name="FirstName">
                <xsl:value-of select="substring-after(field[@name='creator_name'],', ')" />
            </xsl:attribute>
            <xsl:attribute name="LastName">
                <xsl:value-of select="substring-before(field[@name='creator_name'],', ')" />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template name="getContributors">
        <!-- WARNING: no unique format for persons in Opus3! -->
        <!-- there may occur problems -->
        <xsl:param name="contributor" required="yes" />
        <xsl:element name="PersonContributor">
            <xsl:attribute name="AcademicTitle"></xsl:attribute>
            <xsl:attribute name="DateOfBirth"></xsl:attribute>
            <xsl:attribute name="PlaceOfBirth"></xsl:attribute>
            <xsl:attribute name="Email"></xsl:attribute>
            <xsl:attribute name="FirstName">
                <xsl:value-of select="substring-after($contributor,', ')" />
            </xsl:attribute>
            <xsl:attribute name="LastName">
                <xsl:value-of select="substring-before($contributor,', ')" />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template name="getAdvisors">
        <xsl:param name="OriginalID" required="yes" />
        <xsl:for-each select="/mysqldump/database/table_data[@name='opus_diss']/row[field[@name='source_opus']=$OriginalID]">
            <xsl:call-template name="getAdvisor" />
        </xsl:for-each>
     </xsl:template>

    <xsl:template name="getAdvisor">
        <xsl:element name="PersonAdvisor">
            <xsl:attribute name="AcademicTitle">
                <xsl:value-of select="substring-after(substring-before(field[@name='advisor'],')'),'(')" />
            </xsl:attribute>
            <xsl:attribute name="DateOfBirth"></xsl:attribute>
            <xsl:attribute name="PlaceOfBirth"></xsl:attribute>
            <xsl:attribute name="Email"></xsl:attribute>
            <xsl:attribute name="FirstName">
                <xsl:value-of select="substring-after(substring-before(field[@name='advisor'],' ('),', ')" />
            </xsl:attribute>
            <xsl:attribute name="LastName">
                <xsl:value-of select="substring-before(field[@name='advisor'],', ')" />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
