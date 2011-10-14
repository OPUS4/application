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
 * @package     Module_Export
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: style_ieee.xslt 9112 2011-10-13 10:07:40Z gmaiwald $
 */
-->

<xsl:stylesheet version="1.0"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" indent="yes" omit-xml-declaration="yes" standalone='yes'/>

  <xsl:template match="/">
    <xsl:apply-templates select="export"/>
  </xsl:template>

  <xsl:key name="year" match="Opus_Document" use="@Year"/>
  <xsl:template match="export">
      <xsl:element name="div">
      <xsl:for-each select="Opus_Document[count(. | key('year', @Year)[1]) = 1]">
        <xsl:sort select="@Year" order="descending"/>
	<xsl:element name="h2">
            <xsl:value-of select="@Year" />
	</xsl:element>
	<xsl:for-each select="key('year', @Year)">
            <xsl:sort select="TitleMain/@Value" order="ascending"/>
            <xsl:choose>
                <xsl:when test="@Type = 'book'">
                    <xsl:call-template name="render_book"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="render_book"/>
                </xsl:otherwise>
            </xsl:choose>
	</xsl:for-each>
    </xsl:for-each>
    </xsl:element>
  </xsl:template>

  <!-- rendering templates for doctypes -->

  <xsl:template name="render_article">
    <xsl:element name="p">
	<xsl:apply-templates select="PersonAuthor" />
        <xsl:text>"</xsl:text>
        <xsl:value-of select="@Title" />
        <xsl:text>"</xsl:text>
        <xsl:text>.</xsl:text>
        <xsl:apply-templates select="@Volume" />
        <xsl:apply-templates select="@Issue" />
        <xsl:value-of select="@Year" />
        <xsl:text>.</xsl:text>
    </xsl:element>
  </xsl:template>


  <xsl:template name="render_book">
    <xsl:element name="p">
	<xsl:apply-templates select="PersonAuthor" />
	<xsl:apply-templates select="TitleMain[position() = 1]" />
        <xsl:text>.</xsl:text>
        <xsl:call-template name="Published"/>
        <xsl:value-of select="@Year" />
        <xsl:text>.</xsl:text>
    </xsl:element>  
  </xsl:template>

  <xsl:template name="render_bookpart">
    <xsl:element name="i">
	<xsl:apply-templates select="PersonAuthor" />
        <xsl:text>"</xsl:text>
        <xsl:value-of select="@Title" />
        <xsl:text>"</xsl:text>
        <xsl:apply-templates select="TitleParent" />
        <xsl:call-template name="Published"/>
        <xsl:value-of select="@Year" />
        <xsl:text>, </xsl:text>
        <xsl:call-template name="Pages"/>
        <xsl:text>.</xsl:text>
    </xsl:element>
  </xsl:template>

  <xsl:template name="render_conferenceobject">
    <xsl:element name="p">
	<xsl:apply-templates select="PersonAuthor" />
        <xsl:text>"</xsl:text>
        <xsl:value-of select="@Title" />
        <xsl:text>"</xsl:text>
        <xsl:text>.</xsl:text>
        <xsl:apply-templates select="@Volume" />
        <xsl:apply-templates select="@Issue" />
        <xsl:value-of select="@Year" />
        <xsl:text>.</xsl:text>
    </xsl:element>
  </xsl:template>

<!-- matched templates -->

  <xsl:template match="Issue">
        <xsl:text> no. </xsl:text>
	<xsl:value-of select="@Value" />
        <xsl:text>, </xsl:text>
  </xsl:template>

  <xsl:template match="PersonAuthor">
	<xsl:if test="count(../PersonAuthor) &lt; 4">
		<xsl:value-of select="substring(@FirstName,1,1)" />
		<xsl:text>. </xsl:text>
		<xsl:value-of select="@LastName" />
		<xsl:if test="position() != last()"> and </xsl:if>
	</xsl:if>
	<xsl:if test="count(../PersonAuthor) > 3">
		<xsl:if test="position() = 1">
			<xsl:value-of select="substring(@FirstName,1,1)" />
			<xsl:text>. </xsl:text>
			<xsl:value-of select="@LastName" />
			<xsl:text>et al.</xsl:text>
		</xsl:if>
	</xsl:if>	
	<xsl:if test="position() = last()">, </xsl:if>	
  </xsl:template>
  
   <xsl:template match="TitleMain">
        <xsl:element name ="i"> 
	<xsl:value-of select="@Value" />
        </xsl:element>
  </xsl:template> 

  <xsl:template match="TitleParent">
        <xsl:text> In </xsl:text>
	<xsl:value-of select="@Value" />
        <xsl:text>. </xsl:text>
  </xsl:template>

  <xsl:template match="Volume">
        <xsl:text> vol. </xsl:text>
	<xsl:value-of select="@Value" />
        <xsl:text>, </xsl:text>
  </xsl:template>
  
<!-- named templates -->

  <xsl:template name="Published">
      <xsl:if test="@PublisherName != '' or @PublisherPlace !=''">
          <xsl:choose>
            <xsl:when test="@PublisherName != '' and @PublisherPlace !=''">
              <xsl:value-of select="@PublisherName" />
              <xsl:text>: </xsl:text>
              <xsl:value-of select="@PublisherPlace" />
            </xsl:when>
            <xsl:when test="@PublisherName != ''">
              <xsl:value-of select="@PublisherName" />
            </xsl:when>
            <xsl:when test="@PublisherPlace != ''">
              <xsl:value-of select="@PublisherPlace" />
             </xsl:when>
          </xsl:choose>
          <xsl:text>, </xsl:text>
      </xsl:if>
  </xsl:template>

  <xsl:template name="Pages">
      <xsl:if test="@PageFirst != ''">
          <xsl:text>pp. </xsl:text>
          <xsl:choose>
            <xsl:when test="@PageFirst  != '' and @PageLast !=''">
              <xsl:value-of select="@PageFirst" />
              <xsl:text>-</xsl:text>
              <xsl:value-of select="@PageLast" />
            </xsl:when>
            <xsl:when test="@PageFirst != ''">
              <xsl:value-of select="@PageFirst" />
            </xsl:when>
          </xsl:choose>
      </xsl:if>
  </xsl:template>

 
</xsl:stylesheet>

