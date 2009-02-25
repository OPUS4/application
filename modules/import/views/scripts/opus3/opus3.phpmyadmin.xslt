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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="xml" indent="no" />

    <xsl:template match="/">
        <xsl:element name="Documents">
            <xsl:apply-templates select="node()/opus" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="opus">
        <xsl:variable name="OriginalID"><xsl:value-of select="source_opus" /></xsl:variable>
        <xsl:element name="Opus_Document">
            <xsl:attribute name="Language"><xsl:value-of select="language" /></xsl:attribute>
            <xsl:attribute name="CreatingCorporation"><xsl:value-of select="creator_corporate" /></xsl:attribute>
            <xsl:attribute name="ContributingCorporation"><xsl:value-of select="contributors_corporate" /></xsl:attribute>
            <xsl:attribute name="PublishedYear"><xsl:value-of select="date_year" /></xsl:attribute>
            <!-- Dummy attribute, needs to be filled (if possible)! -->
            <xsl:attribute name="PublishedDate"></xsl:attribute>
            <!-- Dummy attribute, needs to be filled (if possible)! -->
            <xsl:attribute name="Edition"></xsl:attribute>
            <!-- Dummy attribute, needs to be filled (if possible)! -->
            <xsl:attribute name="PageNumber"></xsl:attribute>
            <!-- Dummy attribute, needs to be filled (if possible)! -->
            <xsl:attribute name="NonInstituteAffiliation"></xsl:attribute>
            <xsl:attribute name="Type">
                <xsl:choose>
                    <xsl:when test="type='1'">manual</xsl:when>
                    <xsl:when test="type='2'">article</xsl:when>
                    <xsl:when test="type='4'">monograph</xsl:when>
                    <xsl:when test="type='5'">book section</xsl:when>
                    <xsl:when test="type='7'">master thesis</xsl:when>
                    <xsl:when test="type='8'">doctoral thesis</xsl:when>
                    <xsl:when test="type='9'">honour thesis</xsl:when>
                    <xsl:when test="type='11'">journal</xsl:when>
                    <xsl:when test="type='15'">conference</xsl:when>
                    <xsl:when test="type='16'">conference item</xsl:when>
                    <xsl:when test="type='17'">paper</xsl:when>
                    <xsl:when test="type='19'">study paper</xsl:when>
                    <xsl:when test="type='20'">report</xsl:when>
                    <xsl:when test="type='22'">preprint</xsl:when>
                    <xsl:when test="type='23'">other</xsl:when>
                    <xsl:when test="type='24'">habil thesis</xsl:when>
                    <xsl:when test="type='25'">bachelor thesis</xsl:when>
                    <xsl:when test="type='26'">lecture</xsl:when>
                    <xsl:otherwise>other</xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>

            <xsl:element name="Urn">
                <xsl:attribute name="Value"><xsl:value-of select="urn" /></xsl:attribute>
            </xsl:element>

            <xsl:element name="PersonAuthor">
                <xsl:attribute name="AcademicTitle"></xsl:attribute>
                <xsl:attribute name="DateOfBirth"></xsl:attribute>
                <xsl:attribute name="PlaceOfBirth"></xsl:attribute>
                <xsl:attribute name="Email"></xsl:attribute>
                <xsl:attribute name="FirstName">
                    <xsl:value-of select="substring-after(../opus_autor[source_opus=$OriginalID]/creator_name,', ')" />
                </xsl:attribute>
                <xsl:attribute name="LastName">
                    <xsl:value-of select="substring-before(../opus_autor[source_opus=$OriginalID]/creator_name,', ')" />
                </xsl:attribute>
            </xsl:element>

            <xsl:element name="PersonAdvisor">
                <xsl:attribute name="AcademicTitle">
                    <xsl:value-of select="substring-after(substring-before(../opus_diss[source_opus=$OriginalID]/advisor,')'),'(')" />
                </xsl:attribute>
                <xsl:attribute name="DateOfBirth"></xsl:attribute>
                <xsl:attribute name="PlaceOfBirth"></xsl:attribute>
                <xsl:attribute name="Email"></xsl:attribute>
                <xsl:attribute name="FirstName">
                    <xsl:value-of select="substring-after(substring-before(../opus_diss[source_opus=$OriginalID]/advisor,' ('),', ')" />
                </xsl:attribute>
                <xsl:attribute name="LastName">
                    <xsl:value-of select="substring-before(../opus_diss[source_opus=$OriginalID]/advisor,', ')" />
                </xsl:attribute>
            </xsl:element>

            <xsl:element name="TitleMain">
                <xsl:attribute name="Language"><xsl:value-of select="language" /></xsl:attribute>
                <xsl:attribute name="Value"><xsl:value-of select="title" /></xsl:attribute>
            </xsl:element>

            <xsl:element name="TitleAbstract">
                <xsl:attribute name="Language"><xsl:value-of select="description_lang" /></xsl:attribute>
                <xsl:attribute name="Value"><xsl:value-of select="description" /></xsl:attribute>
            </xsl:element>

            <xsl:element name="TitleAbstract">
                <xsl:attribute name="Language"><xsl:value-of select="description2_lang" /></xsl:attribute>
                <xsl:attribute name="Value"><xsl:value-of select="description2" /></xsl:attribute>
            </xsl:element>
        </xsl:element>

    </xsl:template>

    <!-- Quelldatei
    <opus>
        <title>Wilhelm Ostwald, the &quot;Brücke&quot; (Bridge), and connections to other bibliographic activities at the beginning of the twentieth century</title>
        <creator_corporate></creator_corporate>
        <subject_swd>Informationssystem  , Geschichte  , Ostwald, Wilhelm , Informations- und Dokumentationswissenschaft</subject_swd>
        <description>This paper gives a summary of the activities of the German chemist and
Nobel laureate Wilhelm Ostwald (1853 - 1932) in the area of scholarly information, communication, and publication at the beginning of the twentieth
century. In 1911 Ostwald, with others, founded the &quot;Brücke&quot; (Bridge),
an organization with aims similar to those of the famous Institut International
de Bibliographie in Brussels. The paper looks at connections to other
institutions and individuals in the area of documentation and information
science, especially in Germany, for example, the Institut für Techno-
Bibliographie and the German librarian Julius Hanauer, one of the German
promoters of the Universal Decimal Classification.
</description>
        <publisher_university>TUB</publisher_university>
        <contributors_name></contributors_name>
        <contributors_corporate></contributors_corporate>
        <date_year>1999</date_year>
        <date_creation>1020078886</date_creation>
        <date_modified>1142593859</date_modified>
        <type>16</type>
        <source_opus>22</source_opus>
        <source_title>http://www.chemheritage.org/HistoricalServices/ASIS_documents/ASIS98_Hapke.pdf</source_title>
        <source_swb></source_swb>
        <language>eng</language>
        <verification>t-hapke@gmx.de</verification>
        <subject_uncontrolled_german></subject_uncontrolled_german>
        <subject_uncontrolled_english>Wilhelm Ostwald , information science , history</subject_uncontrolled_english>
        <title_en></title_en>
        <description2></description2>
        <subject_type>ccs</subject_type>
        <date_valid>0</date_valid>
        <description_lang>eng</description_lang>
        <description2_lang>eng</description2_lang>
        <sachgruppe_ddc>020</sachgruppe_ddc>
        <urn>urn:nbn:de:gbv:830-opus-225</urn>
    </opus>

    <opus_autor>
        <source_opus>22</source_opus>
        <creator_name>Jungfer, Martin</creator_name>
        <reihenfolge>1</reihenfolge>
    </opus_autor>

    <opus_diss>
        <source_opus>22</source_opus>
        <date_accepted>1024264800</date_accepted>
        <advisor>Krautschneider,  Wolfgang (Prof. Dr.)</advisor>
        <title_de></title_de>
        <publisher_faculty>4</publisher_faculty>
    </opus_diss>

    <opus_hashes>
        <source_opus>22</source_opus>
        <filename>/usr/local/wwwtubdok/htdocs/volltexte/2005/96/pdf/DISCUS_ABIT.pdf</filename>
        <hash>877c060747a086d7279f238dea46fa6b</hash>
    </opus_hashes>

    <opus_inst>
        <source_opus>22</source_opus>
        <inst_nr>69</inst_nr>
    </opus_inst>

    <opus_ccs>
        <source_opus>22</source_opus>
        <class>H.3</class>
    </opus_ccs>

    <opus_msc>
        <source_opus>22</source_opus>
        <class>65F20</class>
    </opus_msc>

    <opus_pacs>
        <source_opus>22</source_opus>
        <class>06.20.Dk</class>
    </opus_pacs>

    <opus_schriftenreihe>
        <source_opus>22</source_opus>
        <sr_id>1</sr_id>
        <sequence_nr>Mai 2006</sequence_nr>
    </opus_schriftenreihe>

    <opus_coll>
        <coll_id>0</coll_id>
        <source_opus>72</source_opus>
    </opus_coll>

    TUBdok-spezifische Tabelle, BK-Klassifikation
    <opus_bk>
        <source_opus>22</source_opus>
        <class>85.15</class>
    </opus_bk>

    TUBdok-spezifische Tabelle, Autorenkennungen LBS
    <opus_autorid>
        <source_opus>22</source_opus>
        <autor_ID>65430</autor_ID>
        <key_fingerprint></key_fingerprint>
    </opus_autorid>

    Ende der Quelldatei
    -->
</xsl:stylesheet>
