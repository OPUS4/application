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
        <xsl:element name="export-example">
            <xsl:apply-templates select="Documents"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Documents">
        <xsl:apply-templates select="Opus_Document"/>
    </xsl:template>

    <xsl:template match="Opus_Document">
        <xsl:element name="doc">
            <xsl:element name="id"><xsl:value-of select="@Id"/></xsl:element>
            <xsl:element name="completedYear"><xsl:value-of select="@CompletedYear"/></xsl:element>
            <xsl:element name="publishedYear"><xsl:value-of select="@PublishedYear"/></xsl:element>
            <xsl:element name="thesisYearAccepted"><xsl:value-of select="@ThesisYearAccepted"/></xsl:element>
            <xsl:element name="language"><xsl:value-of select="@Language"/></xsl:element>
            <xsl:element name="pageFirst"><xsl:value-of select="@PageFirst"/></xsl:element>
            <xsl:element name="pageLast"><xsl:value-of select="@PageLast"/></xsl:element>
            <xsl:element name="pageNumber"><xsl:value-of select="@PageNumber"/></xsl:element>
            <xsl:element name="edition"><xsl:value-of select="@Edition"/></xsl:element>
            <xsl:element name="issue"><xsl:value-of select="@Issue"/></xsl:element>
            <xsl:element name="volume"><xsl:value-of select="@Volume"/></xsl:element>
            <xsl:element name="type"><xsl:value-of select="@Type"/></xsl:element>
            <xsl:element name="publisherName"><xsl:value-of select="@PublisherName"/></xsl:element>
            <xsl:element name="publisherPlace"><xsl:value-of select="@PublisherPlace"/></xsl:element>
            <xsl:element name="creatingCorporation"><xsl:value-of select="@CreatingCorporation"/></xsl:element>
            <xsl:element name="contributingCorporation"><xsl:value-of select="@ContributingCorporation"/></xsl:element>
            <xsl:element name="belongsToBibliography"><xsl:value-of select="@BelongsToBibliography"/></xsl:element>
            <xsl:element name="completedDate">
                <xsl:value-of select="CompletedDate/@Year"/>-<xsl:value-of select="CompletedDate/@Month"/>-<xsl:value-of select="CompletedDate/@Day"/>
            </xsl:element>
            <xsl:element name="publishedDate">
                <xsl:value-of select="PublishedDate/@Year"/>-<xsl:value-of select="PublishedDate/@Month"/>-<xsl:value-of select="PublishedDate/@Day"/>
            </xsl:element>
            <xsl:element name="thesisDateAccepted">
                <xsl:value-of select="ThesisDateAccepted/@Year"/>-<xsl:value-of select="ThesisDateAccepted/@Month"/>-<xsl:value-of select="ThesisDateAccepted/@Day"/>
            </xsl:element>

            <xsl:apply-templates select="TitleMain"/>
            <xsl:apply-templates select="TitleAbstract"/>
            <xsl:apply-templates select="TitleParent"/>
            <xsl:apply-templates select="TitleSub"/>
            <xsl:apply-templates select="TitleAdditional"/>

            <xsl:apply-templates select="Identifier"/>
            <xsl:apply-templates select="Note"/>
            <xsl:apply-templates select="Enrichment"/>
            <xsl:apply-templates select="Licence"/>
            <xsl:apply-templates select="Person"/>
            <xsl:apply-templates select="Series"/>
            <xsl:apply-templates select="Subject"/>
            <xsl:apply-templates select="Collection"/>
            <xsl:apply-templates select="ThesisPublisher"/>
            <xsl:apply-templates select="ThesisGrantor"/>

            <xsl:apply-templates select="File"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleMain">
        <xsl:element name="title">
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
        <xsl:element name="parentTitle">
            <xsl:attribute name="language"><xsl:value-of select="@Language"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleSub">
        <xsl:element name="subTitle">
            <xsl:attribute name="language"><xsl:value-of select="@Language"/></xsl:attribute>
            <xsl:value-of select="@Value"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleAdditional">
        <xsl:element name="additionalTitle">
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
        <xsl:if test="@Visibility = 'public'">
            <xsl:element name="note"><xsl:value-of select="@Message"/></xsl:element>
        </xsl:if>
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
        <xsl:if test="@Role = 'author'">
            <xsl:element name="author">
                <xsl:value-of select="@FirstName"/>
                <xsl:text> </xsl:text>
                <xsl:value-of select="@LastName"/>
            </xsl:element>
        </xsl:if>
    </xsl:template>

    <xsl:template match="Series">
        <xsl:element name="series">
            <xsl:element name="title"><xsl:value-of select="@Title"/></xsl:element>
            <xsl:element name="number"><xsl:value-of select="@Number"/></xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Subject">
        <xsl:element name="subject">
            <xsl:element name="language"><xsl:value-of select="@Language"/></xsl:element>
            <xsl:element name="type"><xsl:value-of select="@Type"/></xsl:element>
            <xsl:element name="value"><xsl:value-of select="@Value"/></xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Collection">
        <xsl:if test="@Visible = '1'">
            <xsl:element name="collection">
                <xsl:attribute name="role"><xsl:value-of select="@RoleName"/></xsl:attribute>
                <xsl:attribute name="number"><xsl:value-of select="@Number" /></xsl:attribute>
                <xsl:value-of select="@Name"/>
            </xsl:element>
        </xsl:if>
    </xsl:template>

    <xsl:template match="ThesisPublisher">
        <xsl:element name="thesisPublisher"><xsl:value-of select="@Name"/></xsl:element>
    </xsl:template>

    <xsl:template match="ThesisGrantor">
        <xsl:element name="thesisGrantor"><xsl:value-of select="@Name"/></xsl:element>
    </xsl:template>

    <xsl:template match="File">
        <xsl:if test="@VisibleInFrontdoor = '1' and @VisibleInOai = '1'">
            <xsl:element name="file">
                <xsl:value-of select="$opusUrl"/>
                <xsl:text>/files/</xsl:text>
                <xsl:value-of select="../@Id"/>
                <xsl:text>/</xsl:text>
                <xsl:value-of select="@PathName"/>
            </xsl:element>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>