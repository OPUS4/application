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
 * @package     Module_Frontdoor
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de> 
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_Frontdoor
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    exclude-result-prefixes="php">

    <xsl:output method="text" omit-xml-declaration="yes" />  
 
    <xsl:template match="/"> 
      <xsl:apply-templates select="Opus/Opus_Model_Filter" />   
    </xsl:template>

    <!-- Suppress spilling values with no corresponding templates -->
      <xsl:template match="@*|node()" /> 

<!-- here you can change the order of the fields, just change the order of the apply-templates-rows
     if there is a choose-block for the field, you have to move the whole choose-block
     if you wish new fields, you have to add a new line xsl:apply-templates...
     and a special template for each new field below, too -->
    <xsl:template match="Opus_Model_Filter">
       @article{OPUS-Bibtex<xsl:value-of select="@Id" />,
       author   =  "<xsl:apply-templates select="PersonAuthor" />",
       title    =  '<xsl:apply-templates select="TitleMain" />',
       journal  =  "<xsl:apply-templates select="TitleParent" />",
       <xsl:choose>
         <xsl:when test="normalize-space(@CompletedYear)">
             year = <xsl:value-of select="@CompletedYear" />,
         </xsl:when>
         <xsl:when test="string-length(ComletedDate/@Year)>0">
           year = <xsl:value-of select="ComletedDate/@Year" />,
         </xsl:when>
         <xsl:when test="normalize-space(PublishedDate/@Year)">
             year = <xsl:value-of select="PublishedDate/@Year" />,
         </xsl:when>
         <xsl:otherwise>
               year = <xsl:value-of select="@PublishedYear" />,
         </xsl:otherwise>
       </xsl:choose>
       <xsl:if test="string-length(IdentifierUrn/@Value)>0">
           urn     =  <xsl:apply-templates select="IdentifierUrn" />,
           url     =  http://nbn-resolving.de/urn/resolver.pl?<xsl:apply-templates select="IdentifierUrn" />,
       </xsl:if>
       <xsl:if test="string-length(IdentifierUrl/@Value)>0">
           url     =  <xsl:apply-templates select="IdentifierUrl" />,
       </xsl:if>
       <xsl:if test="string-length(Note/@Message)>0">
           note    =  "<xsl:apply-templates select="Note" />",
       </xsl:if>
       <xsl:if test="string-length(@PageFirst)>0">
           pages   =  "<xsl:value-of select="@PageFirst" />, <xsl:value-of select="@PageLast" />",
       </xsl:if>
       <xsl:if test="string-length(@Volume)>0">
           volume  =  <xsl:apply-templates select="@Volume" />,
       </xsl:if>   
       <xsl:if test="string-length(@Issue)>0">
           number =  "<xsl:value-of select="@Issue" />",
       </xsl:if>
       <xsl:if test="string-length(@ContributingCorporation)>0">
           organization =  "<xsl:value-of select="@ContributingCorporation" />",
       </xsl:if>
       url         =   <xsl:value-of select="$url_prefix" />/frontdoor/index/index/docId/<xsl:value-of select="@Id" />,
       howpublished=  'online'
       }
    </xsl:template>

    <!-- here begins the special templates for the fields -->
    <!-- Templates for "internal fields". -->
    <xsl:template match="@CompletedYear">
      <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@ContributingCorporation">
      <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@CreatingCorporation">
      <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@Edition">
     <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@Issue">
      <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@PageNumber">
      <xsl:value-of select="." />  
    </xsl:template>

    <xsl:template match="@PublishedYear">
      <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@PublisherName">
      <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@PublisherPlace">
      <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@Volume">
      <xsl:value-of select="." />
    </xsl:template>


    <!-- Templates for "external fields". -->
    <xsl:template match="CompletedDate">
      <xsl:value-of select="@Year" />
    </xsl:template>

    <xsl:template match="IdentifierUrn">
      <xsl:value-of select="@Value" />
    </xsl:template>

    <xsl:template match="IdentifierUrl">
      <xsl:value-of select="@Value" />
    </xsl:template>

    <xsl:template match="Note">
      <xsl:value-of select="@Message" />
    </xsl:template>
 
    <xsl:template match="Institute"/>
    <xsl:template match="Patent"/>
 
    <xsl:template match="PersonAuthor">
      <xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
      <xsl:choose>
         <xsl:when test="position()=last()">
            <xsl:text></xsl:text>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text> and </xsl:text>
         </xsl:otherwise>
      </xsl:choose>
    </xsl:template>  
          
    <xsl:template match="PersonEditor">
      <xsl:value-of select="concat(@LastName, ', ', @FirstName)" />
      <xsl:choose>
         <xsl:when test="position()=last()">
            <xsl:text></xsl:text>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text> and </xsl:text>
         </xsl:otherwise>
      </xsl:choose>
    </xsl:template>
 
    <xsl:template match="PublisherUniversity"/>

    <xsl:template match="TitleMain">
        <xsl:value-of select="@Value" />
    </xsl:template>

    <xsl:template match="TitleParent">
      <xsl:value-of select="@Value" />
    </xsl:template>

    <!-- Named template to translate an arbitrary string. Needs the translation key as a parameter. -->
    <xsl:template name="translateString">
        <xsl:param name="string" />
        <xsl:value-of select="string" />
    </xsl:template>

</xsl:stylesheet>
