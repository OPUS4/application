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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: opus3.xslt 5665 2010-09-21 12:54:10Z gmaiwald $
 */
-->

<xsl:stylesheet version="1.0"
    	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    	xmlns:bibtex="http://bibtexml.sf.net/"
        xmlns:php="http://php.net/xsl">

   <xsl:output method="xml" indent="no" />

   <!-- Das Mapping der Sprachen -->
   <xsl:template name="getLanguage">
        <xsl:param name="lang" required="yes" />
        <xsl:choose>
            <xsl:when test="$lang='German'"><xsl:text>deu</xsl:text></xsl:when>
            <xsl:when test="$lang='English'"><xsl:text>eng</xsl:text></xsl:when>
            <xsl:otherwise><xsl:text>eng</xsl:text></xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- Holt die Startseite bei Seitenangaben -->
   <xsl:template name="getFirstPage">
        <xsl:param name="pages" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($pages, '--')">
                <xsl:value-of select="substring-before($pages,'--')" />
            </xsl:when>
            <xsl:when test="contains($pages, '-')">
                <xsl:value-of select="substring-before($pages,'-')" />
            </xsl:when>
        </xsl:choose>
    </xsl:template>

   <!-- Holt die Endseite bei Seitenangaben -->
   <xsl:template name="getLastPage">
        <xsl:param name="pages" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($pages, '--')">
                <xsl:value-of select="substring-after($pages,'--')" />
            </xsl:when>
            <xsl:when test="contains($pages, '-')">
                <xsl:value-of select="substring-after($pages,'-')" />
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <!-- Erzeugt die Personenelemente -->
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
   <xsl:template name="getFirstName">
        <xsl:param name="name" required="yes" />
        <xsl:choose>
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
    
</xsl:stylesheet>
