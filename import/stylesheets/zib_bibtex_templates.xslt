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
   <xsl:template name="getReportId">
        <xsl:param name="id" required="yes" />
        <xsl:variable name="temp">
            <xsl:value-of select="php:function('preg_replace', '/_/','-', $id)" />
        </xsl:variable>
        <xsl:value-of select="php:function('preg_replace', '/^ZR-/','', $temp)" />
    </xsl:template>

   <!-- Das Mapping der Sprachen -->
   <xsl:template name="mapLanguage">
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

   <!-- Add Identifier -->
   <xsl:template name="AddIdentifier">
        <xsl:param name="url" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($url, 'http://www2.zib.de/PaperWeb/abstracts/')"></xsl:when>
            <xsl:when test="contains($url, 'http://www2.zib.de/Publications/abstracts/')"></xsl:when>
            <xsl:when test="contains($url, 'http://www2.zib.de/Publications/Reports/')"></xsl:when>
            <xsl:when test="contains($url, 'http://opus.kobv.de/zib/volltexte/')"></xsl:when>
            <xsl:when test="contains($url, 'dx.doi.org')">
                <xsl:element name="IdentifierDoi">
                    <xsl:attribute name="Value"><xsl:value-of select="substring-after($url,'dx.doi.org/')" /></xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:when test="contains($url, 'doi.ieeecomputersociety.org')">
                <xsl:element name="IdentifierDoi">
                    <xsl:attribute name="Value"><xsl:value-of select="substring-after($url,'doi.ieeecomputersociety.org/')" /></xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:element name="IdentifierUrl">
                    <xsl:attribute name="Value"><xsl:value-of select="$url" /></xsl:attribute>
                </xsl:element>
            </xsl:otherwise>
       </xsl:choose>
    </xsl:template>

   <!-- Add Identifier -->
   <xsl:template name="AddReference">
        <xsl:param name="label" required="yes" />
        <xsl:param name="url" required="yes" />
        <xsl:choose>
            <xsl:when test="contains($url, 'http://www2.zib.de/PaperWeb/abstracts/')"></xsl:when>
            <xsl:when test="contains($url, 'http://www2.zib.de/Publications/abstracts/')"></xsl:when>
            <xsl:when test="contains($url, 'http://www2.zib.de/Publications/Reports/')"></xsl:when>
            <xsl:when test="contains($url, 'http://opus.kobv.de/zib/volltexte/')"></xsl:when>
            <xsl:otherwise>
                <xsl:element name="ReferenceUrl">
                    <xsl:attribute name="Label"><xsl:value-of select="$label" /></xsl:attribute>
                    <xsl:attribute name="Value"><xsl:value-of select="$url" /></xsl:attribute>
                </xsl:element>
            </xsl:otherwise>
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

    <!-- Extrahiert die Arbeitsgruppen -->
    <xsl:template name="AddPublicationLists">
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
        <xsl:call-template name="AddPublicationList">
             <xsl:with-param name="person" select="normalize-space($first)" />
        </xsl:call-template>
        <xsl:if test="$remaining">
            <xsl:call-template name="AddPublicationLists">
                <xsl:with-param name="list"><xsl:value-of select="$remaining" /> </xsl:with-param>
                <xsl:with-param name="delimiter"><xsl:value-of select="$delimiter" /></xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>


   <!-- Das Mapping der Personen -->
   <xsl:template name="AddPublicationList">
        <xsl:param name="tag" required="yes" />
        <xsl:choose>
            <!-- Mapping Personen Numerik -->
            <xsl:when test="$tag = 'Andrae'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="andrae" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Baum'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="baum" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Bujotzek'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="bujotzek" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Burger'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="burger" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Cordes'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="cordes" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Deuflhard'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="deuflhard" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Durmaz'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="durmaz" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Ehrig'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="ehrig" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Erdmann'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="erdmann" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Fackeldey'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="fackeldey" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Hege'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="hege" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Kettner'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="kettner" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Klapproth'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="klapproth" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Klimm'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="klimm" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Lockau'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="lockau" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Nowak'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="nowak" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Pollock'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="pollok" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Pollok'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="pollok" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Pomplun'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="pomplun" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Roeblitz'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="susanna.roeblitz" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Roitzsch'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="roitzsch" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Scharkoi'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="scharkoi" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Schiela'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="schiela" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Schmidt'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="frank.schmidt" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Stoetzel'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="stoetzel" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Weber'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="weber" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Weiser'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="weiser" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Zachow'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="zachow" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Zschiedrich'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="zschiedrich" /></xsl:call-template></xsl:when>
            <!-- Mapping Personen Optimierung -->
            <xsl:when test="$tag = 'berthold'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'brandt'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'cardonha'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'philipp.friese'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'fuegenschuh'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'giovanidis'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'gleixner'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'groetschel'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'hiller'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'koch'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'miltenberger'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'marika.neumann'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'raack'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'schlechte'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'schweiger'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'stephan'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'swarat'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'szabo'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'tuchscherer'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'werner'"><xsl:call-template name="AddPublicationPerson"><xsl:with-param name="person" select="$tag" /></xsl:call-template></xsl:when>

            <!-- Mapping WorkingGroups / Departments -->
            <!-- Numerik -->
            <xsl:when test="$tag = 'CompMedicine'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'CompSysBio'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'DrugDesign'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'NanoOptik'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Numerik'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <!-- Optimierung -->
            <xsl:when test="$tag = 'opt'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'mip'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'tele'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'traffic'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <!-- Visualisierung -->
            <xsl:when test="$tag = 'visual'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'systems'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'scivis'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'medical'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'compvis'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'geom'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <!-- Sis -->
            <xsl:when test="$tag = 'sis'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>
            <!-- Parallel -->
            <xsl:when test="$tag = 'parallel'"><xsl:call-template name="AddPublicationGroup"><xsl:with-param name="group" select="$tag" /></xsl:call-template></xsl:when>

            <!-- Mapping Projects Optimierung -->
            <xsl:when test="$tag = 'MIP'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="MIP" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'MATHEON-B20: OPTIMIZATION OF GAS TRANSPORT'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="MATHEON-B20: Optimization of Gas Transport" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'MATHEON-B12: IPSym'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="MATHEON-B12: IPSym" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'IBM'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="IBM" /></xsl:call-template></xsl:when>

            <!-- Mapping Projects Numerik-->
            <!-- <xsl:when test="$tag = 'B4'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when> -->
            <xsl:when test="$tag = 'BAM-Thermography'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="BAM-Thermography" /></xsl:call-template></xsl:when>
            <!-- <xsl:when test="$tag = 'Bands'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when> -->
            <!-- <xsl:when test="$tag = 'BMBF'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when> -->
            <xsl:when test="$tag = 'BovCyc'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="BovCyc" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Cardio'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Cardio" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'CAS'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="CAS" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'confKin'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Conformation Kinetics" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'conFlow'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="ConFlow" /></xsl:call-template></xsl:when>
            <!-- <xsl:when test="$tag = 'Crystals'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when> -->
            <!-- <xsl:when test="$tag = 'Crystals Bands'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when> -->
            <!--
            <xsl:when test="$tag = 'D15'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'D9dev'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'D9Dev'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'D9Field'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'D9Fields'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'D9Fields Crystals'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'DAAD'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'DFG_1'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'DFG_2'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'DFG_3'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'DFG_4'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'DFG_5'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'DFG_6'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            -->
            <xsl:when test="$tag = 'DTBC-H'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="DTBC Helmholtz" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'DTBC-S'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="DTBC Schrödinger" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'EigenH'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Eigenvalue Helmholtz" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'EigenM'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Eigenvalue Maxwell" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Epos'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Epos" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'EUV'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="EUV" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Fado'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="FADO" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'FemCyc'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="FemCyc" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Fresnel'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Fresnel" /></xsl:call-template></xsl:when>
            <!-- <xsl:when test="$tag = 'HASSIP'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when> -->
            <xsl:when test="$tag = 'Hyper-OC'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Hyper-OC" /></xsl:call-template></xsl:when>
            <!--
            <xsl:when test="$tag = 'IMAGE_1'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'IMAGE_2'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'InverseProblems'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            -->
            <xsl:when test="$tag = 'jump'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="ConfJump" /></xsl:call-template></xsl:when>
            <!--
            <xsl:when test="$tag = 'KLARA'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Masks'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            -->
            <xsl:when test="$tag = 'Matheon-A13'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="MATHEON-A13" /></xsl:call-template></xsl:when>
            <!--
            <xsl:when test="$tag = 'Matheon-A1-NUMOPT'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Matheon-A1-Thermo'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Matheon-A1-THERMO'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Matheon-A2'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            -->
            <xsl:when test="$tag = 'Matheon-A4'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Matheon-A4" /></xsl:call-template></xsl:when>
            <!--
            <xsl:when test="$tag = 'MEPROS'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'meshlessConfDyn'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            -->
            <xsl:when test="$tag = 'molDesign'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Molecular Design" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'MolRate'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="MolRate" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'multiscale'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="MultiscaleVis" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Nonlin'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Nonlinear Schrödinger" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'OpenRes'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="Open Resonators" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'PCCA+'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="PCCA+" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'QuantDyn'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="QuantDyn" /></xsl:call-template></xsl:when>
            <!--
            <xsl:when test="$tag = 'RADAR_1'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'ReducedBasis'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            -->
            <xsl:when test="$tag = 'Sfb-765'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="SFB-765, Teilprojekt C2" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'SFB-765'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="SFB-765, Teilprojekt C2" /></xsl:call-template></xsl:when>
            <!--
            <xsl:when test="$tag = 'SPP1113'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'SPP113'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'Tibia'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'webportal'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            <xsl:when test="$tag = 'ZMFree'"><xsl:call-template name="AddPublicationProject"><xsl:with-param name="project" select="X" /></xsl:call-template></xsl:when>
            -->

        </xsl:choose>
    </xsl:template>


   <!-- Fuegt Person hinzu -->
   <xsl:template name="AddPublicationPerson">
        <xsl:param name="person" required="yes" />
        <xsl:element name="PublicationPerson">
            <xsl:attribute name="Value"><xsl:value-of select="$person" /></xsl:attribute>
        </xsl:element>
    </xsl:template>


    <!-- Das Mapping der Arbeitsgruppen -->
   <xsl:template name="AddPublicationGroup">
        <xsl:param name="group" required="yes" />
        <xsl:element name="PublicationGroup">
            <xsl:attribute name="Value"><xsl:value-of select="$group" /></xsl:attribute>
        </xsl:element>
    </xsl:template>

   <!-- Das unscharfe Mapping der Projecte -->
   <xsl:template name="AddPublicationProject">
        <xsl:param name="project" required="yes" />
        <xsl:element name="PublicationProject">
            <xsl:attribute name="Value"><xsl:value-of select="$project" /></xsl:attribute>
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
