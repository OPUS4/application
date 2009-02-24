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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: oai-pmh.xslt 1948 2009-02-17 15:17:01Z claussnitzer $
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_Import
 */
-->

<xsl:stylesheet version="1.0"
    xmlns="http://www.openarchives.org/OAI/2.0/"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:fn="http://www.w3.org/2004/07/xpath-functions">


    <xsl:output method="xml" indent="yes" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <!--<xsl:template match="*" />
    <xsl:template match="*" mode="oai_dc" />-->

    <xsl:template match="/">
		<Documents>
			<xsl:apply-templates select=".//opus" />
		</Documents>
    </xsl:template>

    <xsl:template match="opus">
		<xsl:variable name="TypeID"><xsl:value-of select="type" /></xsl:variable>
		<xsl:variable name="OriginalID"><xsl:value-of select="source_opus" /></xsl:variable>
		<xsl:variable name="DocLanguage"><xsl:value-of select="language" /></xsl:variable>
		<Opus_Document> 
			<xsl:attribute name="Language"><xsl:value-of select="$DocLanguage" /></xsl:attribute>
			<xsl:attribute name="CreatingCorporation"><xsl:value-of select="creator_corporate" /></xsl:attribute>
			<!-- Dummy attribute, needs to be filled! -->
			<xsl:attribute name="ContributingCorporation"></xsl:attribute>
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
					<xsl:when test="$TypeID='1'">manual</xsl:when>
					<xsl:when test="$TypeID='2'">article</xsl:when>
					<xsl:when test="$TypeID='4'">monograph</xsl:when>
					<xsl:when test="$TypeID='5'">book section</xsl:when>
					<xsl:when test="$TypeID='7'">master thesis</xsl:when>
					<xsl:when test="$TypeID='8'">doctoral thesis</xsl:when>
					<xsl:when test="$TypeID='9'">honour thesis</xsl:when>
					<xsl:when test="$TypeID='11'">journal</xsl:when>
					<xsl:when test="$TypeID='15'">conference</xsl:when>
					<xsl:when test="$TypeID='16'">conference item</xsl:when>
					<xsl:when test="$TypeID='17'">paper</xsl:when>
					<xsl:when test="$TypeID='19'">study paper</xsl:when>
					<xsl:when test="$TypeID='20'">report</xsl:when>
					<xsl:when test="$TypeID='22'">preprint</xsl:when>
					<xsl:when test="$TypeID='23'">other</xsl:when>
					<xsl:when test="$TypeID='24'">habil thesis</xsl:when>
					<xsl:when test="$TypeID='25'">bachelor thesis</xsl:when>
					<xsl:when test="$TypeID='26'">lecture</xsl:when>
					<xsl:otherwise>other</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			
			<Urn>
				<xsl:attribute name="Value"><xsl:value-of select="urn" /></xsl:attribute>
			</Urn>

			<xsl:call-template name="getAuthor"><xsl:with-param name="ID"><xsl:value-of select="$OriginalID" /></xsl:with-param></xsl:call-template>

			<TitleMain> 
				<xsl:attribute name="Language"><xsl:value-of select="$DocLanguage" /></xsl:attribute>
				<xsl:attribute name="Value"><xsl:value-of select="title" /></xsl:attribute>
			</TitleMain>

			<TitleAbstract>
				<xsl:attribute name="Language"><xsl:value-of select="description_lang" /></xsl:attribute>
				<xsl:attribute name="Value"><xsl:value-of select="description" /></xsl:attribute>
			</TitleAbstract>
			<TitleAbstract>
				<xsl:attribute name="Language"><xsl:value-of select="description2_lang" /></xsl:attribute>
				<xsl:attribute name="Value"><xsl:value-of select="description2" /></xsl:attribute>
			</TitleAbstract>
		</Opus_Document>
    </xsl:template>

	<xsl:template name="getAuthor">
		<xsl:param name="ID" required="yes" />
		<xsl:for-each select="//opus_autor">
			<xsl:if test="$ID=source_opus">
				<PersonAuthor> 
					<xsl:attribute name="AcademicTitle"></xsl:attribute>
					<xsl:attribute name="DateOfBirth"></xsl:attribute>
					<xsl:attribute name="PlaceOfBirth"></xsl:attribute>
					<xsl:attribute name="Email"></xsl:attribute>
					<xsl:attribute name="FirstName">
						<xsl:value-of select="substring-after(creator_name,', ')" />
					</xsl:attribute>
					<xsl:attribute name="LastName">
						<xsl:value-of select="substring-before(creator_name,',')" />
					</xsl:attribute>
				</PersonAuthor>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>

<!-- Quelldatei
	<opus>
        <subject_swd>Informationssystem  , Geschichte  , Ostwald, Wilhelm , Informations- und Dokumentationswissenschaft</subject_swd>
        <publisher_university>TUB</publisher_university>
        <contributors_name></contributors_name>
        <contributors_corporate></contributors_corporate>
        <date_creation>1020078886</date_creation>
        <date_modified>1142593859</date_modified>
        <source_opus>22</source_opus>
        <source_title>http://www.chemheritage.org/HistoricalServices/ASIS_documents/ASIS98_Hapke.pdf</source_title>
        <source_swb></source_swb>
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
        <bereich_id>1</bereich_id>
        <lic>publ-ohne-pod</lic>
        <isbn>0-8412-2772-1</isbn>
        <bem_intern></bem_intern>
        <bem_extern></bem_extern>
    </opus>
...
    <opus_autor>
        <source_opus>39</source_opus>
        <creator_name>Jungfer, Martin</creator_name>
        <reihenfolge>1</reihenfolge>
    </opus_autor>
...    
    <opus_diss>
        <source_opus>38</source_opus>
        <date_accepted>1024264800</date_accepted>
        <advisor>Krautschneider,  Wolfgang (Prof. Dr.)</advisor>
        <title_de></title_de>
        <publisher_faculty>4</publisher_faculty>
    </opus_diss>
...
    <opus_hashes>
        <source_opus>96</source_opus>
        <filename>/usr/local/wwwtubdok/htdocs/volltexte/2005/96/pdf/DISCUS_ABIT.pdf</filename>
        <hash>877c060747a086d7279f238dea46fa6b</hash>
    </opus_hashes>
...
    <opus_inst>
        <source_opus>37</source_opus>
        <inst_nr>69</inst_nr>
    </opus_inst>
...
    <opus_msc>
        <source_opus>158</source_opus>
        <class>65F20</class>
    </opus_msc>
...
    <opus_pacs>
        <source_opus>75</source_opus>
        <class>06.20.Dk</class>
    </opus_pacs>
...
    <opus_schriftenreihe>
        <source_opus>321</source_opus>
        <sr_id>1</sr_id>
        <sequence_nr>Mai 2006</sequence_nr>
    </opus_schriftenreihe>
...

Ende der Quelldatei
-->
</xsl:stylesheet>
