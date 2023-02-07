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
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

TODO full export of invisible information like internal notes (export as backup?)
TODO export with/without files
TODO export as ZIP/TAR
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

    <xsl:output method="xml" indent="yes" encoding="utf-8"/>
    <xsl:param name="opusUrl" />
    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*"/>

    <xsl:template match="/">
        <xsl:element name="import">
            <xsl:apply-templates select="Documents"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Documents">
        <xsl:apply-templates select="Opus_Document"/>
    </xsl:template>

    <xsl:template match="Opus_Document">
        <xsl:element name="opusDocument">
            <xsl:attribute name="docId"><xsl:value-of select="@Id"/></xsl:attribute> <!-- TODO oldId? -->
            <xsl:attribute name="language"><xsl:value-of select="@Language"/></xsl:attribute>
            <xsl:attribute name="type"><xsl:value-of select="@Type"/></xsl:attribute>
            <xsl:attribute name="pageFirst"><xsl:value-of select="@PageFirst"/></xsl:attribute>
            <xsl:attribute name="pageLast"><xsl:value-of select="@PageLast"/></xsl:attribute>
            <xsl:attribute name="pageNumber"><xsl:value-of select="@PageNumber"/></xsl:attribute>
            <xsl:attribute name="edition"><xsl:value-of select="@Edition"/></xsl:attribute>
            <xsl:attribute name="volume"><xsl:value-of select="@Volume"/></xsl:attribute>
            <xsl:attribute name="issue"><xsl:value-of select="@Issue"/></xsl:attribute>
            <xsl:attribute name="publisherName"><xsl:value-of select="@PublisherName"/></xsl:attribute>
            <xsl:attribute name="publisherPlace"><xsl:value-of select="@PublisherPlace"/></xsl:attribute>
            <xsl:attribute name="creatingCorporation"><xsl:value-of select="@CreatingCorporation"/></xsl:attribute>
            <xsl:attribute name="contributingCorporation"><xsl:value-of select="@ContributingCorporation"/></xsl:attribute>
            <xsl:attribute name="belongsToBibliography"><xsl:value-of select="@BelongsToBibliography"/></xsl:attribute>
            <!-- TODO serverState -->

            <xsl:element name="titlesMain">
                <xsl:apply-templates select="TitleMain"/>
            </xsl:element>

            <xsl:element name="titles">
                <xsl:apply-templates select="TitleParent"/>
                <xsl:apply-templates select="TitleSub"/>
                <xsl:apply-templates select="TitleAdditional"/>
            </xsl:element>

            <xsl:element name="abstracts">
                <xsl:apply-templates select="TitleAbstract"/>
            </xsl:element>

            <xsl:element name="persons">
                <xsl:apply-templates select="Person"/>
            </xsl:element>

            <xsl:element name="keywords">
                <xsl:apply-templates select="Subject"/>
            </xsl:element>

            <xsl:element name="dnbInstitutions">
                <xsl:apply-templates select="ThesisPublisher"/>
                <xsl:apply-templates select="ThesisGrantor"/>
            </xsl:element>

            <xsl:element name="dates">
                <!-- TODO
                <xsl:element name="completedYear"><xsl:value-of select="@CompletedYear"/></xsl:element>
                <xsl:element name="publishedYear"><xsl:value-of select="@PublishedYear"/></xsl:element>
                <xsl:element name="thesisYearAccepted"><xsl:value-of select="@ThesisYearAccepted"/></xsl:element>
                <xsl:element name="completedDate">
                    <xsl:value-of select="CompletedDate/@Year"/>-<xsl:value-of select="CompletedDate/@Month"/>-<xsl:value-of select="CompletedDate/@Day"/>
                </xsl:element>
                <xsl:element name="publishedDate">
                    <xsl:value-of select="PublishedDate/@Year"/>-<xsl:value-of select="PublishedDate/@Month"/>-<xsl:value-of select="PublishedDate/@Day"/>
                </xsl:element>
                <xsl:element name="thesisDateAccepted">
                    <xsl:value-of select="ThesisDateAccepted/@Year"/>-<xsl:value-of select="ThesisDateAccepted/@Month"/>-<xsl:value-of select="ThesisDateAccepted/@Day"/>
                </xsl:element>
                -->
            </xsl:element>

            <xsl:element name="identifiers">
                <xsl:apply-templates select="Identifier"/>
            </xsl:element>

            <xsl:element name="notes">
                <xsl:apply-templates select="Note"/>
            </xsl:element>

            <xsl:element name="collections">
                <xsl:apply-templates select="Collection"/>
            </xsl:element>

            <xsl:element name="series">
                <xsl:apply-templates select="Series"/>
            </xsl:element>

            <xsl:element name="enrichments">
                <xsl:apply-templates select="Enrichment"/>
            </xsl:element>

            <xsl:element name="licences">
                <xsl:apply-templates select="Licence"/>
            </xsl:element>

            <xsl:element name="files">
                <xsl:apply-templates select="File"/>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleMain">
        <xsl:element name="titleMain">
            <xsl:attribute name="language"><xsl:value-of select="@Language"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleAbstract">
        <xsl:element name="abstract">
            <xsl:attribute name="language"><xsl:value-of select="@Language"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleParent">
        <xsl:element name="title">
            <xsl:attribute name="type">parent</xsl:attribute>
            <xsl:attribute name="language"><xsl:value-of select="@Language"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleSub">
        <xsl:element name="title">
            <xsl:attribute name="type">sub</xsl:attribute>
            <xsl:attribute name="language"><xsl:value-of select="@Language"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleAdditional">
        <xsl:element name="title">
            <xsl:attribute name="type">additional</xsl:attribute>
            <xsl:attribute name="language"><xsl:value-of select="@Language"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Identifier">
        <xsl:element name="identifier">
            <xsl:attribute name="type"><xsl:value-of select="@Type"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Note">
        <!-- TODO export internal notes (?) xsl:if test="@Visibility = 'public'">-->
            <xsl:element name="note">
                <xsl:attribute name="visibility"><xsl:value-of select="@Visibility" /></xsl:attribute>
                <xsl:value-of select="@Message"/>
            </xsl:element>
        <!-- </xsl:if> -->
    </xsl:template>

    <xsl:template match="Enrichment">
        <xsl:element name="enrichment">
            <xsl:attribute name="key"><xsl:value-of select="@KeyName"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Licence">
        <xsl:if test="@Active = '1'">
            <xsl:element name="licence"><xsl:value-of select="@NameLong"/></xsl:element>
        </xsl:if>
    </xsl:template>

    <xsl:template match="Person">
        <xsl:element name="person">
            <xsl:attribute name="role"><xsl:value-of select="@Role"/></xsl:attribute>
            <xsl:attribute name="firstName"><xsl:value-of select="@FirstName"/></xsl:attribute>
            <xsl:attribute name="lastName"><xsl:value-of select="@LastName"/></xsl:attribute>
            <xsl:attribute name="academicTitle"><xsl:value-of select="@AcademicTitle"/></xsl:attribute>
            <xsl:attribute name="email"><xsl:value-of select="@Email"/></xsl:attribute>
            <xsl:attribute name="allowEmailContact"><xsl:value-of select="@AllowEmailContact"/></xsl:attribute>
            <xsl:attribute name="placeOfBirth"><xsl:value-of select="@PlaceOfBirth"/></xsl:attribute>
            <!-- TODO <xsl:attribute name="dateOfBirth"><xsl:value-of select=""/></xsl:attribute> -->
        </xsl:element>
    </xsl:template>

    <xsl:template match="Series">
        <xsl:element name="seriesItem">
            <xsl:attribute name="title"><xsl:value-of select="@Title"/></xsl:attribute>
            <xsl:attribute name="number"><xsl:value-of select="@Number"/></xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Subject">
        <xsl:element name="keyword">
            <xsl:attribute name="language"><xsl:value-of select="@Language"/></xsl:attribute>
            <xsl:attribute name="type"><xsl:value-of select="@Type"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Collection">
        <xsl:if test="@Visible = '1'">
            <xsl:element name="collection">
                <xsl:attribute name="role"><xsl:value-of select="@RoleName"/></xsl:attribute>
                <xsl:attribute name="number"><xsl:value-of select="@Number"/></xsl:attribute>
                <xsl:value-of select="@Name"/>
            </xsl:element>
        </xsl:if>
    </xsl:template>

    <xsl:template match="ThesisPublisher">
        <xsl:element name="dnbInstitution">
            <xsl:attribute name="role">publisher</xsl:attribute>
            <xsl:attribute name="name"><xsl:value-of select="@Name"/></xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match="ThesisGrantor">
        <xsl:element name="dnbInstitution">
            <xsl:attribute name="role">grantor</xsl:attribute>
            <xsl:attribute name="name"><xsl:value-of select="@Name"/></xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match="File">
        <xsl:if test="@VisibleInFrontdoor = '1' and @VisibleInOai = '1'">
            <xsl:element name="file">
                <xsl:text>https://</xsl:text>
                <xsl:value-of select="$opusUrl"/>
                <xsl:text>/files/</xsl:text>
                <xsl:value-of select="../@Id"/>
                <xsl:text>/</xsl:text>
                <xsl:value-of select="@PathName"/>
            </xsl:element>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>