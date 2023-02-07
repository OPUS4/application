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
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    exclude-result-prefixes="php">

    <xsl:param name="urnResolverUrl" />

    <xsl:output method="text" omit-xml-declaration="yes"/>

    <xsl:template match="/">
      <xsl:apply-templates select="Opus/Opus_Document" />
    </xsl:template>

    <!-- Suppress spilling values with no corresponding templates -->
    <xsl:template match="@*|node()" />

    <!--
        here you can change the order of the fields, just change the order of the
        apply-templates-rows
        if there is a choose-block for the field, you have to move the whole
        choose-block
        if you wish new fields, you have to add a new line xsl:apply-templates...
        and a special template for each new field below, too
    -->
    <xsl:template match="Opus_Document">
       <xsl:choose>
           <xsl:when test="@Type='book'">
               <xsl:text>TY  - BOOK</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='bookpart'">
               <xsl:text>TY  - CHAP</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='conferenceobject'">
               <xsl:text>TY  - CONF</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='coursematerial' or @Type='image' or @Type='lecture' or @Type='other' or @Type='sound' or @Type='studythesis'">
               <xsl:text>TY  - GEN</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='preprint'">
               <xsl:text>TY  - INPR</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='periodical'">
               <xsl:text>TY  - JFULL</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='article' or @Type='review'">
               <xsl:text>TY  - JOUR</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='contributiontoperiodical'">
               <xsl:text>TY  - NEWS</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='report'">
               <xsl:text>TY  - RPRT</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='bachelorthesis' or @Type='doctoralthesis' or @Type='habilitation' or @Type='masterthesis'">
               <xsl:text>TY  - THES</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='workingpaper'">
               <xsl:text>TY  - UNPD</xsl:text>
           </xsl:when>
           <xsl:when test="@Type='movingimage'">
               <xsl:text>TY  - VIDEO</xsl:text>
           </xsl:when>
           <xsl:otherwise>
               <xsl:text>TY  - GEN</xsl:text>
           </xsl:otherwise>
       </xsl:choose>
       <xsl:text>&#10;</xsl:text>

       <xsl:if test="string-length(PersonAuthor/@LastName)>0">
           <xsl:apply-templates select="PersonAuthor" />
       </xsl:if>
       <xsl:if test="string-length(PersonEditor/@LastName)>0">
           <xsl:apply-templates select="PersonEditor" />
       </xsl:if>
       <xsl:if test="string-length(TitleMain/@Value)>0">
           <xsl:apply-templates select="TitleMain" />
       </xsl:if>
       <xsl:if test="string-length(TitleParent/@Value)>0">
           <xsl:apply-templates select="TitleParent" />
       </xsl:if>
       <xsl:if test="string-length(TitleAbstract/@Value)>0">
           <xsl:apply-templates select="TitleAbstract" />
       </xsl:if>
       <xsl:if test="Series">
           <xsl:apply-templates select="Series" />
       </xsl:if>

       <xsl:if test="string-length(Subject/@Value)>0">
           <xsl:apply-templates select="Subject" />
       </xsl:if>
       <xsl:choose>
         <xsl:when test="normalize-space(CompletedDate/@Year)">
             <xsl:text>Y1  - </xsl:text><xsl:value-of select="CompletedDate/@Year" />
         </xsl:when>
         <xsl:when test="string-length(PublishedDate/@Year)>0">
             <xsl:text>Y1  - </xsl:text><xsl:value-of select="PublishedDate/@Year" />
         </xsl:when>
         <xsl:when test="normalize-space(@CompletedYear)">
             <xsl:text>Y1  - </xsl:text><xsl:value-of select="@CompletedYear" />
         </xsl:when>
         <xsl:otherwise>
             <xsl:text>Y1  - </xsl:text><xsl:value-of select="@PublishedYear" />
         </xsl:otherwise>
       </xsl:choose>
       <xsl:text>&#10;</xsl:text>

        <xsl:text>UR  - </xsl:text>
        <xsl:value-of select="$url_prefix" />
        <xsl:text>/frontdoor/index/index/docId/</xsl:text>
        <xsl:value-of select="@Id" />
        <xsl:text>&#10;</xsl:text>

        <xsl:if test="string-length(Identifier[@Type = 'urn']/@Value)>0">
            <xsl:apply-templates select="Identifier[@Type = 'urn']" />
        </xsl:if>

        <xsl:if test="string-length(Identifier[@Type = 'url']/@Value)>0">
            <xsl:apply-templates select="Identifier[@Type = 'url']" />
        </xsl:if>
        <xsl:if test="string-length(Identifier[@Type = 'isbn']/@Value) > 0">
            <xsl:apply-templates select="Identifier[@Type = 'isbn']" />
        </xsl:if>
        <xsl:if test="string-length(Identifier[@Type='issn']/@Value) > 0">
            <xsl:apply-templates select="Identifier[@Type = 'issn']" />
        </xsl:if>
        <xsl:if test="string-length(Note/@Message)>0">
            <xsl:apply-templates select="Note" />
        </xsl:if>
        <xsl:if test="string-length(@Volume)>0">
            <xsl:text>VL  - </xsl:text>
            <xsl:value-of select="@Volume" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <xsl:if test="string-length(@Issue)>0">
            <xsl:text>IS  - </xsl:text>
            <xsl:value-of select="@Issue" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <xsl:if test="string-length(@PageFirst)>0">
            <xsl:text>SP  - </xsl:text>
            <xsl:value-of select="@PageFirst" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <xsl:if test="string-length(@PageLast)>0">
            <xsl:text>EP  - </xsl:text>
            <xsl:value-of select="@PageLast" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <xsl:if test="string-length(@PublisherName)>0">
            <xsl:text>PB  - </xsl:text>
            <xsl:value-of select="@PublisherName" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <xsl:if test="string-length(@PublisherPlace)>0">
            <xsl:text>CY  - </xsl:text>
            <xsl:value-of select="@PublisherPlace" />
            <xsl:text>&#10;</xsl:text>
        </xsl:if>
        <xsl:text>ER  - </xsl:text>
    </xsl:template>

    <!-- here begins the special templates for the fields -->
    <!-- Templates for "external fields". -->
    <xsl:template match="Identifier[@Type = 'url']">
        <xsl:text>UR  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'urn']">
        <xsl:text>UR  - </xsl:text>
        <xsl:value-of select="$urnResolverUrl" />
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'isbn']">
        <xsl:text>SN  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Identifier[@Type = 'issn']">
        <xsl:text>SN  - </xsl:text>
        <xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Note[@Visibility='public']">
        <xsl:text>N1  - </xsl:text><xsl:value-of select="@Message" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Subject[@Type='uncontrolled' or @Type='swd']">
        <xsl:text>KW  - </xsl:text><xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="PersonAuthor">
        <xsl:text>A1  - </xsl:text><xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="PersonEditor">
        <xsl:text>A2  - </xsl:text><xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="TitleMain">
        <xsl:text>T1  - </xsl:text><xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="TitleAbstract">
        <xsl:text>N2  - </xsl:text><xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="TitleParent">
        <xsl:text>T2  - </xsl:text><xsl:value-of select="@Value" />
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <xsl:template match="Series[@Visible='1']">
        <xsl:text>T3  - </xsl:text>
        <xsl:value-of select="@Title" />
        <xsl:if test="@Number != ''">
            <xsl:text> - </xsl:text>
            <xsl:value-of select="@Number" />
            <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

</xsl:stylesheet>
