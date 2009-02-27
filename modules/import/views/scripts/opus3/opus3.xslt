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
    <xsl:template match="table_data/row/field[@xsi:nil='true']" priority="1" />

    <xsl:template match="table_data[@name='opus']/row">
        <xsl:element name="Opus_Document">            
            <xsl:variable name="OriginalID"><xsl:value-of select="field[@name='source_opus']" /></xsl:variable>
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
            
            <!-- Language might be a multivalue field in Opus4; in Opus3 there can be only one language -->
            <!-- if the document type defines it as multivalue, there will be problems importing -->
            <xsl:attribute name="Language">
                <xsl:value-of select="field[@name='language']" />
            </xsl:attribute>
            <xsl:attribute name="CreatingCorporation">
                <xsl:value-of select="field[@name='creator_corporate']" />
            </xsl:attribute>
            <!--<xsl:attribute name="ContributingCorporation">
                <xsl:value-of select="field[@name='contributors_corporate']" />
            </xsl:attribute>-->
            <!-- Source holds source_title? -->
            <!--<xsl:attribute name="Source">
                <xsl:value-of select="field[@name='source_title']" />
            </xsl:attribute>
            <xsl:attribute name="SwbId">
                <xsl:value-of select="field[@name='source_swb']" />
            </xsl:attribute>
            <xsl:attribute name="RangeId">
                <xsl:value-of select="field[@name='bereich_id']" />
            </xsl:attribute>-->
            
            
            <!--CompletedYear und CompletedDate are not stored by Opus 3, so we leave it out -->
            <!--<xsl:attribute name="CompletedYear">
                <xsl:value-of select="field[@name='date_year']" />
            </xsl:attribute>
            <xsl:attribute name="CompletedDate">
                <xsl:value-of select="field[@name='date_year']" /><xsl:text>-01-01</xsl:text>
            </xsl:attribute>-->
            
            <!-- Take date_creation from OPUS3 as PublishedYear, PublishedDate and ServerDatePublished -->
            <!-- date_creation is stored as a unix timestamp -->
            <!-- its transferred by date function in PHP -->
            <xsl:variable name="date_creation"><xsl:value-of select="field[@name='date_creation']" /></xsl:variable>
            <xsl:variable name="date_modified"><xsl:value-of select="field[@name='date_modified']" /></xsl:variable>
            <xsl:variable name="date_valid"><xsl:value-of select="field[@name='date_valid']" /></xsl:variable>
            <xsl:attribute name="PublishedYear">
                <xsl:value-of select="field[@name='date_year']" />
            </xsl:attribute>
            <!--PublishedDate is left out, because Opus3 only stores the year -->
            <!--<xsl:attribute name="PublishedDate">
                <xsl:value-of select="php:function('date', 'Y-m-d', $date_creation)" />
            </xsl:attribute>-->
            <!--<xsl:attribute name="ServerDatePublished">
                <xsl:value-of select="php:function('date', 'Y-m-d H:i:s', $date_creation)" />
            </xsl:attribute>
            <xsl:attribute name="ServerDateModified">
                <xsl:value-of select="php:function('date', 'Y-m-d H:i:s', $date_modified)" />
            </xsl:attribute>
            <xsl:if test="$date_valid>0">
                <xsl:attribute name="ServerDateValid">
                    <xsl:value-of select="php:function('date', 'Y-m-d H:i:s', $date_valid)" />
                </xsl:attribute>
            </xsl:if>
            -->
            <xsl:variable name="dateaccepted"><xsl:value-of select="/mysqldump/database/table_data[@name='opus_diss']/row[field[@name='source_opus']=$OriginalID]/field[@name='date_accepted']" /></xsl:variable>
            <xsl:if test="string-length($dateaccepted)>0">
                <xsl:attribute name="DateAccepted">
                    <xsl:value-of select="php:function('date', 'Y-m-d', $dateaccepted)" />
                </xsl:attribute>
            </xsl:if>
            
            <!--
            <xsl:attribute name="PublisherUniversity">
                <xsl:value-of select="field[@name='publisher_university']" />
            </xsl:attribute>           
            -->
            
            <!-- Find persons associated with the document -->
            <xsl:call-template name="getAuthors"><xsl:with-param name="OriginalID"><xsl:value-of select="$OriginalID" /></xsl:with-param></xsl:call-template>
            <!--<xsl:call-template name="getContributors"><xsl:with-param name="contributors"><xsl:value-of select="field[@name='contributors_name']" /></xsl:with-param></xsl:call-template>-->
            <xsl:call-template name="getAdvisors"><xsl:with-param name="OriginalID"><xsl:value-of select="$OriginalID" /></xsl:with-param></xsl:call-template>
            
            <!--
            Missing fields from opus table:
            subject_type (classification system the document is classified with) erstmal ausklammern
            lic (License information, not sure how this is handled currently) <field name="Licence" mandatory="yes" multiplicity="4" />
            
            Missing fields in other opus3 tables:
            opus_coll <field name="Collection" />
            opus_hashes - geht in File mit ein <field name="File" multiplicity="4" />
            opus_inst (+ institutes + faculties) <field name="Institute" />
            opus_msc, opus_pacs, opus_ccs (, opus_bk) + Klassifikationstabellen (realisiert in Collections)
            opus_schriftenreihe (+ schriftenreihe) <field name="TitleParent" mandatory="yes" multiplicity="4" />
            
            university_lang not to be migrated (part of configuration)
	        -->
            
            <xsl:apply-templates select="field" />
        </xsl:element>
    </xsl:template>

    <!-- Notes -->
    <!--
    <xsl:template match="table_data[@name='opus']/row/field[@name='bem_intern']">
        <xsl:element name="Note">
            <xsl:attribute name="Scope">
                <xsl:text>private</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="Message">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='bem_extern']">
        <xsl:element name="Note">
            <xsl:attribute name="Scope">
                <xsl:text>public</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="Message">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    -->

    <!-- Subjects and Classifications -->
    <!--
    <xsl:template match="table_data[@name='opus']/row/field[@name='sachgruppe_ddc']">
        - The value represents a key in the sachgruppe_ddc_de or _en-table -
        <xsl:element name="SubjectDdc">
            <xsl:attribute name="Language">
                <xsl:text>ger</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="table_data[@name='sachgruppe_ddc_de']/row[field[@name='nr']=.]/field[@name='sachgruppe']" />
            </xsl:attribute>
        </xsl:element>
        <xsl:element name="SubjectDdc">
            <xsl:attribute name="Language">
                <xsl:text>eng</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="table_data[@name='sachgruppe_ddc_en']/row[field[@name='nr']=.]/field[@name='sachgruppe']" />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='subject_swd']">
        - Split values from field by <Space>,<Space> -
        - each value gets its own element -
        <xsl:element name="SubjectSwd">
            <xsl:attribute name="Language">
                <xsl:text>ger</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='subject_uncontrolled_english']">
        - WARNING: the uncontrolled subjects have no standardized format -
        - take them as one value in one field ??? -
        <xsl:element name="SubjectUncontrolled">
            <xsl:attribute name="Language">
                <xsl:text>eng</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='subject_uncontrolled_german']">
        - WARNING: the uncontrolled subjects have no standardized format -
        - take them as one value in one field ??? -
        <xsl:element name="SubjectUncontrolled">
            <xsl:attribute name="Language">
                <xsl:text>ger</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    -->
    
    <!-- Titles and abstracts -->
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
        <xsl:if test="string-length(.)>0">
            <xsl:element name="TitleMain">
                <xsl:attribute name="Language">
                    <xsl:text>eng</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='description']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="TitleAbstract">
                <xsl:attribute name="Language">
                    <xsl:value-of select="../field[@name='description_lang']" />
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='description2']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="TitleAbstract">
                <xsl:attribute name="Language">
                    <xsl:value-of select="../field[@name='description2_lang']" />
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template name="getGermanTitle">
        <xsl:if test="string-length(field[@name='title_de'])>0">
            <xsl:element name="TitleMain">
                <xsl:attribute name="Language">
                    <xsl:text>ger</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="field[@name='title_de']" />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    
    <!-- Identifiers -->
    <xsl:template match="table_data[@name='opus']/row/field[@name='urn']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="IdentifierUrn">
                <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='isbn']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="IdentifierIsbn">
                <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <!-- url-field is not used in Opus3, so dont import it -->
    <!--
    <xsl:template match="table_data[@name='opus']/row/field[@name='url']">
        <xsl:element name="Url">
            <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
        </xsl:element>
    </xsl:template>
    -->
    
    <!-- Person templates, called in main template -->
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
            <xsl:call-template name="getGermanTitle" />
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
