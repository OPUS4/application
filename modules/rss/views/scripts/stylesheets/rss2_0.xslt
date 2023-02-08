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

    <xsl:output method="xml" indent="yes" encoding="utf-8"  />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />

    <xsl:template match="/" >
        <xsl:element name="rss">
            <xsl:attribute name="version">2.0</xsl:attribute>
            <xsl:element name="channel">
                <xsl:element name="title">
                    <xsl:value-of select="$feedTitle" />
                </xsl:element>
                <xsl:element name="description">
                    <xsl:value-of select="$feedDescription" />
                </xsl:element>
                <xsl:element name="link">
                    <xsl:value-of select="$link"/>
                </xsl:element>
                <xsl:element name="pubDate">
                    <xsl:value-of select="$pubDate"/>
                </xsl:element>
                <xsl:element name="lastBuildDate">
                    <xsl:value-of select="$lastBuildDate"/>
                </xsl:element>
                <xsl:apply-templates select="Documents"/>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Documents">
        <xsl:if test="count(Opus_Document) > 0">
            <xsl:apply-templates select="Opus_Document"/>
        </xsl:if>
    </xsl:template>

    <xsl:template match="Opus_Document">
        <xsl:element name="item">
            <xsl:element name="title">
                <xsl:apply-templates select="TitleMain"/>
            </xsl:element>

            <xsl:element name="link">
                <xsl:value-of select="$frontdoorBaseUrl"/><xsl:value-of select="@Id"/>
            </xsl:element>

            <xsl:element name="description">
                <xsl:apply-templates select="TitleAbstract"/>
            </xsl:element>

            <xsl:element name="author">
                <xsl:apply-templates select="PersonAuthor"/>
            </xsl:element>

            <xsl:element name="category">
                <xsl:value-of select="@Type"/>
            </xsl:element>

            <xsl:element name="guid">
                <xsl:value-of select="$frontdoorBaseUrl"/><xsl:value-of select="@Id"/>
            </xsl:element>

            <xsl:element name="pubDate">
                <xsl:value-of select="ItemPubDate"/>
            </xsl:element>

        </xsl:element>
    </xsl:template>

    <xsl:template match="TitleMain">
        <xsl:if test="@Language = ../@Language">
            <xsl:value-of select="@Value"/>
        </xsl:if>
    </xsl:template>

    <xsl:template match="PersonAuthor">
        <xsl:value-of select="@FirstName"/>
        <xsl:text> </xsl:text>
        <xsl:value-of select="@LastName"/>
        <xsl:if test="position() != last()">
            <xsl:text>; </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template match="TitleAbstract">
        <xsl:if test="@Language = ../@Language">
            <xsl:value-of select="@Value"/>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>
