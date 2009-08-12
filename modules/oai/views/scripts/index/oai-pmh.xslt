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
 * @package     Module_Oai
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Simone Finkbeiner <simone.finkbeiner@ub.uni-stuttgart.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_Oai
 */
-->

<xsl:stylesheet version="1.0"
    xmlns="http://www.openarchives.org/OAI/2.0/"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">


    <!-- add include here for each new metadata format    -->

    <xsl:include href="prefixes/oai_dc.xslt"/>
    <xsl:include href="prefixes/oai_pp.xslt"/>
    <xsl:include href="prefixes/epicur.xslt"/>
    <xsl:include href="prefixes/xMetaDiss.xslt"/>
    <xsl:include href="prefixes/XMetaDissPlus.xslt"/>
    <xsl:include href="prefixes/copy_xml.xslt"/>

    <xsl:output method="xml" indent="yes" />

    <xsl:param name="dateTime" />
    <xsl:param name="emailAddress" />
    <xsl:param name="setPubType" />
    <xsl:param name="repName" />
    <xsl:param name="repIdentifier" />
    <xsl:param name="sampleIdentifier" />
    <xsl:param name="docId" />
    <xsl:param name="dateDelete" />
    <xsl:param name="totalIds" />
    <xsl:param name="res" />
    <xsl:param name="cursor" />
    <xsl:param name="oai_verb" />
    <xsl:param name="oai_from" />
    <xsl:param name="oai_until" />
    <xsl:param name="oai_metadataPrefix" />
    <xsl:param name="oai_resumptionToken" />
    <xsl:param name="oai_identifier" />
    <xsl:param name="oai_error_code" />
    <xsl:param name="oai_error_message" />
    <xsl:param name="oai_base_url" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />
    <xsl:template match="*" mode="oai_dc" />

    <!--create the head of oai response  -->
    <xsl:template match="/">
        <xsl:element name="OAI-PMH">
            <xsl:attribute name="xsi:schemaLocation">
                <xsl:text>http://www.openarchives.org/OAI/2.0/
                http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd</xsl:text>
            </xsl:attribute>
            <xsl:element name="responseDate">
                <xsl:value-of select="$dateTime" />
            </xsl:element>
            <xsl:element name="request">
                <xsl:if test="$oai_verb != ''">
                    <xsl:attribute name="verb"><xsl:value-of select="$oai_verb" /></xsl:attribute>
                </xsl:if>
                <xsl:if test="$oai_from != ''">
                    <xsl:attribute name="from"><xsl:value-of select="$oai_from" /></xsl:attribute>
                </xsl:if>
                <xsl:if test="$oai_until != ''">
                    <xsl:attribute name="until"><xsl:value-of select="$oai_until" /></xsl:attribute>
                </xsl:if>
                <xsl:if test="$oai_metadataPrefix != ''">
                    <xsl:attribute name="metadataPrefix"><xsl:value-of select="$oai_metadataPrefix" /></xsl:attribute>
                </xsl:if>
                <xsl:if test="$oai_identifier != ''">
                    <xsl:attribute name="identifier"><xsl:value-of select="$oai_identifier" /></xsl:attribute>
                </xsl:if>
                <xsl:if test="$oai_resumptionToken != ''">
                    <xsl:attribute name="resumptionToken"><xsl:value-of select="$oai_resumptionToken" /></xsl:attribute>
                </xsl:if>
                <xsl:value-of select="$oai_base_url" />
            </xsl:element>
            <xsl:if test="$oai_error_code!=''">
                <xsl:element name="error">
                    <xsl:attribute name="code"><xsl:value-of select="$oai_error_code" /></xsl:attribute>
                    <xsl:value-of select="$oai_error_message" />
                </xsl:element>
            </xsl:if>
            
    <!--create the rest of oai response depending on oai_verb -->
                <xsl:choose>
                <xsl:when test="$oai_verb='GetRecord'">
                    <xsl:apply-templates select="Documents" mode="GetRecord" />
                </xsl:when>
                <xsl:when test="$oai_verb='Identify'">
                    <xsl:apply-templates select="Documents" mode="Identify" />
                </xsl:when>
                <xsl:when test="$oai_verb='ListIdentifiers'">
                    <xsl:apply-templates select="Documents" mode="ListIdentifiers" />
                </xsl:when>
                <xsl:when test="$oai_verb='ListMetadataFormats'">
                    <xsl:apply-templates select="Documents" mode="ListMetadataFormats" />
                </xsl:when>
                <xsl:when test="$oai_verb='ListRecords'">
                    <xsl:apply-templates select="Documents" mode="ListRecords" />
                </xsl:when>
                <xsl:when test="$oai_verb='ListSets'">
                    <xsl:apply-templates select="Documents" mode="ListSets" />
                </xsl:when>
            </xsl:choose>
        </xsl:element>
    </xsl:template>


    <!-- template for Identiy  -->
    <xsl:template match="Documents" mode="Identify">
        <xsl:element name="Identify">
           <xsl:element name="repositoryName">
              <xsl:value-of select="$repName"/>
           </xsl:element>
           <xsl:element name="baseURL">
             <xsl:value-of select="$oai_base_url"/>
           </xsl:element>
           <xsl:element name="protocolVersion">2.0</xsl:element>
           <xsl:element name="adminEmail">
             <xsl:value-of select="$emailAddress"/>
           </xsl:element>
           <xsl:element name="earliestDatestamp">
             <xsl:value-of select="$earliestDate"/>
           </xsl:element>
           <xsl:element name="deletedRecord">no</xsl:element>
           <xsl:element name="granularity">YYYY-MM-DDThh:mm:ssZ</xsl:element>
           <xsl:element name="description">
               <xsl:element name="oai-identifier">
                  <xsl:attribute name="xsi:schemaLocation">
                     http://www.openarchives.org/OAI/2.0/oai-identifier
                     http://www.openarchives.org/OAI/2.0/oai-identifier.xsd
                  </xsl:attribute>
                  <xsl:element name="scheme">oai</xsl:element>
                  <xsl:element name="repositoryIdentifier">
                     <xsl:value-of select="$repIdentifier"/>
                  </xsl:element>
                  <xsl:element name="delimiter">:</xsl:element>
                  <xsl:element name="sampleIdentifier">
                     <xsl:value-of select="$sampleIdentifier"/>
                  </xsl:element>
               </xsl:element>
           </xsl:element>
        </xsl:element>
    </xsl:template>


    <!-- template for ListMetadataFormats  -->
    <xsl:template match="Documents" mode="ListMetadataFormats">
        <xsl:element name="ListMetadataFormats">
          <xsl:element name="metadataFormat">
            <xsl:element name="metadataPrefix">oai_dc</xsl:element>
            <xsl:element name="schema">http://www.openarchives.org/OAI/2.0/oai_dc.xsd</xsl:element>
            <xsl:element name="metadataNamespace">http://www.openarchives.org/OAI/2.0/oai_dc/</xsl:element>
          </xsl:element>
          <xsl:element name="metadataFormat">
            <xsl:element name="metadataPrefix">epicur</xsl:element>
            <xsl:element name="schema">http://www.persistent-identifier.de/xepicur/version1.0/xepicur.xsd</xsl:element>
            <xsl:element name="metadataNamespace">urn:nbn:de:1111-2004033116</xsl:element>
          </xsl:element>
          <xsl:element name="metadataFormat">
            <xsl:element name="metadataPrefix">oai_pp</xsl:element>
            <xsl:element name="schema">http://www.proprint-service.de/xml/schemes/v1/PROPRINT_METADATA_SET.xsd</xsl:element>
            <xsl:element name="metadataNamespace">http://www.proprint-service.de/xml/schemes/v1/</xsl:element>
          </xsl:element>
          <xsl:element name="metadataFormat">
            <xsl:element name="metadataPrefix">xMetaDiss</xsl:element>
            <xsl:element name="schema">http://www.d-nb.de/standards/xmetadiss/xmetadiss.xsd</xsl:element>
            <xsl:element name="metadataNamespace">http://www.d-nb.de/standards/xMetaDiss/</xsl:element>
          </xsl:element>
          <xsl:element name="metadataFormat">
            <xsl:element name="metadataPrefix">XMetaDissPlus</xsl:element>
            <xsl:element name="schema">http://www.bsz-bw.de/xmetadissplus/1.3/xmetadissplus.xsd</xsl:element>
            <xsl:element name="metadataNamespace">http://www.bsz-bw.de/xmetadissplus/1.3</xsl:element>
          </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Documents" mode="ListIdentifiers">
        <xsl:element name="ListIdentifiers">
            <xsl:apply-templates select="Opus_Document" /> 
            <xsl:if test="$totalIds > 0">
                <xsl:element name = "resumptionToken">
                  <xsl:attribute name="expirationDate"><xsl:value-of select="$dateDelete"/></xsl:attribute>
                  <xsl:attribute name="completeListSize"><xsl:value-of select="$totalIds"/>
                  </xsl:attribute><xsl:attribute name="cursor"><xsl:value-of select="$cursor"/>
                  </xsl:attribute><xsl:value-of select="$res"/>
                </xsl:element>
            </xsl:if>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Documents" mode="ListSets">
        <xsl:element name="ListSets">
            <xsl:apply-templates select="Opus_Sets" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Documents" mode="ListRecords">
        <xsl:element name="ListRecords">
            <xsl:apply-templates select="Opus_Document" />
            <xsl:if test="$totalIds > 0">
                <xsl:element name = "resumptionToken">
                  <xsl:attribute name="expirationDate"><xsl:value-of select="$dateDelete"/></xsl:attribute>
                  <xsl:attribute name="completeListSize"><xsl:value-of select="$totalIds"/>
                  </xsl:attribute><xsl:attribute name="cursor"><xsl:value-of select="$cursor"/>
                  </xsl:attribute><xsl:value-of select="$res"/>
                </xsl:element>
            </xsl:if>
        </xsl:element>
    </xsl:template>

    <xsl:template match="Documents" mode="GetRecord">
        <xsl:element name="GetRecord">
            <xsl:apply-templates select="Opus_Document" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="Opus_Sets">
        <xsl:element name="set">
           <xsl:element name="setSpec">pub-type:<xsl:value-of select="@Type"/></xsl:element>
           <xsl:element name="setName"><xsl:value-of select="@TypeName"/></xsl:element>
        </xsl:element>
    </xsl:template>    


    <xsl:template match="Opus_Document">
        <xsl:element name="record">
            <xsl:element name="header">
                <!--
                    This is the identifier for the metadata, not a digital object:
                    http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm
                -->
            <xsl:choose>
              <xsl:when test="$oai_verb='GetRecord'">
                <xsl:element name="identifier">
                    <xsl:value-of select="$oai_identifier" />
                </xsl:element>
              </xsl:when>  
              <xsl:otherwise>  
                <!-- has to be changed !!!!  -->
                <xsl:element name="identifier">
                    oai:<xsl:value-of select="$repIdentifier" />:<xsl:value-of select="$docId" /> !!! Identifier fehlt noch in XML
                </xsl:element>
               </xsl:otherwise> 
            </xsl:choose>
                <xsl:element name="datestamp">
                  <xsl:choose>
                    <xsl:when test="@ServerDateModified ">
                      <xsl:value-of select="@ServerDateModified" />
                    </xsl:when>
                    <xsl:otherwise>
                      <xsl:value-of select="@ServerDatePublished" />
                    </xsl:otherwise>
                  </xsl:choose>
                </xsl:element>
            <xsl:choose>
              <xsl:when test="$oai_verb='GetRecord'">
                 <xsl:element name="setSpec">pub-type:<xsl:value-of select="$setPubType" />
                </xsl:element>
              </xsl:when>
              <xsl:otherwise>
                <xsl:element name="setSpec">pub-type:<xsl:value-of select="@SetPubType"/></xsl:element>
              </xsl:otherwise>
            </xsl:choose>   
            </xsl:element>
            
            
            <!-- choose the corresponding template depending on metadataPrefix -->
            <!-- not, when verb=ListIdentifiers -->
            <xsl:choose>
                 <xsl:when test="$oai_verb!='ListIdentifiers'">  
                 <xsl:element name="metadata">
                 <xsl:choose>
                    <xsl:when test="$oai_metadataPrefix='XMetaDissPlus'">
                       <xsl:apply-templates select="." mode="xmetadissplus" />
                    </xsl:when>
                    <xsl:when test="$oai_metadataPrefix='xMetaDiss'">
                       <xsl:apply-templates select="." mode="xmetadiss" />
                    </xsl:when>
                    <xsl:when test="$oai_metadataPrefix='epicur'">
                       <xsl:apply-templates select="." mode="epicur" />
                    </xsl:when>
                    <xsl:when test="$oai_metadataPrefix='oai_dc'">
                       <xsl:apply-templates select="." mode="oai_dc" />
                    </xsl:when>
                    <xsl:when test="$oai_metadataPrefix='oai_pp'">
                       <xsl:apply-templates select="." mode="oai_pp" />
                    </xsl:when>
                    <xsl:when test="$oai_metadataPrefix='copy_xml'">
                       <xsl:apply-templates select="." mode="copy_xml" />
                    </xsl:when>
                 </xsl:choose>
                 </xsl:element>
            
            </xsl:when>
            </xsl:choose>            
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
