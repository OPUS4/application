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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:php="http://php.net/xsl"
                xmlns:xml="http://www.w3.org/XML/1998/namespace"
                exclude-result-prefixes="php">

    <xsl:output method="text" omit-xml-declaration="yes"/>

    <!-- bibtex-style for authors  -->
    <xsl:template match="PersonAuthor">
        <xsl:param name="type" required="yes"/>
        <xsl:choose>
            <xsl:when test="$type='author'">
                <xsl:value-of select="concat(@LastName, ', ', @FirstName)"/>
                <xsl:choose>
                    <xsl:when test="position() = last()">
                        <xsl:text></xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text> and </xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="$type='identifier'">
                <xsl:choose>
                    <xsl:when test="position() = 1 or position() = 2 or position() = 3">
                        <xsl:call-template name="replace_strings">
                            <xsl:with-param name="input_text"><xsl:value-of select="@LastName"/></xsl:with-param>
                        </xsl:call-template>
                    </xsl:when>
                    <xsl:when test="position() = 4">
                        <xsl:text>etal.</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text></xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <!-- bibtex-style for editors  -->
    <xsl:template match="PersonEditor">
        <xsl:value-of select="concat(@LastName, ', ', @FirstName)"/>
        <xsl:choose>
            <xsl:when test="position()=last()">
                <xsl:text></xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text> and </xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- bibtex-style for institutions  -->
    <xsl:template match="Collection[@RoleName='institutes']">
        <xsl:value-of select="@Name"/>
        <xsl:choose>
            <xsl:when test="position()=last()">
                <xsl:text></xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>; </xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- bibtex-style for pages  -->
    <xsl:template name="Pages">
        <xsl:param name="first" required="yes"/>
        <xsl:param name="last" required="yes"/>
        <xsl:param name="number" required="yes"/>
        <xsl:param name="articlenumber" required="yes"/>
        <xsl:choose>
            <xsl:when test="string-length($articlenumber) > 0">
                <xsl:value-of select="$articlenumber"/>
            </xsl:when>
            <xsl:when test="string-length($first) > 0 and string-length($last) > 0">
                <xsl:value-of select="$first"/><xsl:text> -- </xsl:text><xsl:value-of select="$last"/>
            </xsl:when>
            <xsl:when test="string-length($first) > 0">
                <xsl:value-of select="$first"/>
            </xsl:when>
            <xsl:when test="string-length($number) > 0">
                <xsl:value-of select="$number"/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
