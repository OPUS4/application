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

<xsl:stylesheet version="1.0"
    xmlns="http://www.openarchives.org/OAI/2.0/"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="php">

    <xsl:param name="urnResolverUrl" />

    <!-- add include here for each new metadata format    -->

    <xsl:include href="prefixes/oai_dc.xslt"/>
    <xsl:include href="prefixes/oai_pp.xslt"/>
    <xsl:include href="prefixes/epicur.xslt"/>
    <xsl:include href="prefixes/XMetaDissPlus.xslt"/>
    <xsl:include href="prefixes/copy_xml.xslt"/>

    <xsl:output method="xml" indent="yes" encoding="utf-8" />

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
    <xsl:param name="oai_set" />
    <xsl:param name="oai_metadataPrefix" />
    <xsl:param name="oai_resumptionToken" />
    <xsl:param name="oai_identifier" />
    <xsl:param name="oai_error_code" />
    <xsl:param name="oai_error_message" />
    <xsl:param name="oai_error_code2" />
    <xsl:param name="oai_error_message2" />
    <xsl:param name="oai_base_url" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />
    <xsl:template match="*" mode="oai_dc" />

    <!--create the head of oai response  -->
    <xsl:template match="/">
        <xsl:processing-instruction name="xml-stylesheet">
           <xsl:text>type="text/xsl" href="xsl/oai2.xslt"</xsl:text>
        </xsl:processing-instruction>

        <OAI-PMH xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
            <responseDate>
                <xsl:value-of select="$dateTime" />
            </responseDate>
            <request>
                <xsl:if test="$oai_verb != ''">
                    <xsl:attribute name="verb"><xsl:value-of select="$oai_verb" /></xsl:attribute>
                </xsl:if>
                <xsl:if test="$oai_from != ''">
                    <xsl:attribute name="from"><xsl:value-of select="$oai_from" /></xsl:attribute>
                </xsl:if>
                <xsl:if test="$oai_until != ''">
                    <xsl:attribute name="until"><xsl:value-of select="$oai_until" /></xsl:attribute>
                </xsl:if>
                <xsl:if test="$oai_set != ''">
                    <xsl:attribute name="set"><xsl:value-of select="$oai_set" /></xsl:attribute>
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
            </request>
            <!-- TODO find solution where iterating over any number of errors is possible -->
            <xsl:if test="$oai_error_code!=''">
                <error>
                    <xsl:attribute name="code"><xsl:value-of select="$oai_error_code" /></xsl:attribute>
                    <xsl:value-of select="$oai_error_message" />
                </error>
            </xsl:if>
            <xsl:if test="$oai_error_code2!=''">
                <error>
                    <xsl:attribute name="code"><xsl:value-of select="$oai_error_code2" /></xsl:attribute>
                    <xsl:value-of select="$oai_error_message2" />
                </error>
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
        </OAI-PMH>
    </xsl:template>


    <!-- template for Identiy  -->
    <xsl:template match="Documents" mode="Identify">
        <Identify>
           <repositoryName>
              <xsl:value-of select="$repName"/>
           </repositoryName>
           <baseURL>
             <xsl:value-of select="$oai_base_url"/>
           </baseURL>
           <protocolVersion><xsl:text>2.0</xsl:text></protocolVersion>
           <adminEmail>
             <xsl:value-of select="$emailAddress"/>
           </adminEmail>
           <earliestDatestamp>
             <xsl:value-of select="$earliestDate"/>
           </earliestDatestamp>
           <deletedRecord><xsl:text>persistent</xsl:text></deletedRecord>
           <!--TODO: check granularity throughout the OAI component-->
           <!--xsl:element name="granularity">YYYY-MM-DDThh:mm:ssZ</xsl:element>-->
           <granularity><xsl:text>YYYY-MM-DD</xsl:text></granularity>
           <description>
               <oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">
                  <scheme><xsl:text>oai</xsl:text></scheme>
                  <repositoryIdentifier><xsl:value-of select="$repIdentifier"/></repositoryIdentifier>
                  <delimiter><xsl:text>:</xsl:text></delimiter>
                  <sampleIdentifier><xsl:value-of select="$sampleIdentifier"/></sampleIdentifier>
               </oai-identifier>
           </description>
           <description>
               <eprints xmlns="http://www.openarchives.org/OAI/1.1/eprints"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://www.openarchives.org/OAI/1.1/eprints http://www.openarchives.org/OAI/1.1/eprints.xsd">
                 <xsl:if test="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.content') != '' or php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.content') != ''">
                 <content>
                     <xsl:if test="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.content') != ''">
                     <URL><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.content')" /></URL>
                     </xsl:if>
                     <xsl:if test="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.content') != ''">
                     <text><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.content')" /></text>
                     </xsl:if>
                 </content>
                 </xsl:if>
                 <metadataPolicy>
                     <xsl:if test="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.metadataPolicy') != ''">
                         <URL><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.metadataPolicy')" /></URL>
                     </xsl:if>
                     <xsl:if test="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.metadataPolicy') != ''">
                         <text><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.metadataPolicy')" /></text>
                     </xsl:if>
                 </metadataPolicy>
                 <dataPolicy>
                     <xsl:if test="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.dataPolicy') != ''">
                         <URL><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.dataPolicy')" /></URL>
                     </xsl:if>
                     <xsl:if test="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.dataPolicy') != ''">
                         <text><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.dataPolicy')" /></text>
                     </xsl:if>
                 </dataPolicy>
                   <xsl:if test="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.submissionPolicy') != '' or php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.submissionPolicy') != ''">
                   <submissionPolicy>
                       <xsl:if test="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.submissionPolicy') != ''">
                           <URL><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.submissionPolicy')" /></URL>
                       </xsl:if>
                       <xsl:if test="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.submissionPolicy') != ''">
                           <text><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.submissionPolicy')" /></text>
                       </xsl:if>
                   </submissionPolicy>
                   </xsl:if>
                   <xsl:if test="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.comment') != '' or php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.comment') != ''">
                   <comment>
                       <xsl:if test="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.comment') != ''">
                           <URL><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'url', 'oai.description.eprints.comment')" /></URL>
                       </xsl:if>
                       <xsl:if test="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.comment') != ''">
                           <text><xsl:value-of select="php:functionString('Application_Xslt::optionValue', 'text', 'oai.description.eprints.comment')" /></text>
                       </xsl:if>
                   </comment>
                   </xsl:if>
               </eprints>
           </description>
        </Identify>
    </xsl:template>


    <!-- template for ListMetadataFormats  -->
    <xsl:template match="Documents" mode="ListMetadataFormats">
        <ListMetadataFormats>
          <metadataFormat>
            <metadataPrefix><xsl:text>oai_dc</xsl:text></metadataPrefix>
            <schema><xsl:text>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</xsl:text></schema>
            <metadataNamespace><xsl:text>http://www.openarchives.org/OAI/2.0/oai_dc/</xsl:text></metadataNamespace>
          </metadataFormat>
          <metadataFormat>
            <metadataPrefix><xsl:text>epicur</xsl:text></metadataPrefix>
            <schema><xsl:text>http://www.persistent-identifier.de/xepicur/version1.0/xepicur.xsd</xsl:text></schema>
            <metadataNamespace><xsl:text>urn:nbn:de:1111-2004033116</xsl:text></metadataNamespace>
          </metadataFormat>
          <metadataFormat>
            <metadataPrefix><xsl:text>XMetaDissPlus</xsl:text></metadataPrefix>
            <schema><xsl:text>http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd</xsl:text></schema>
            <metadataNamespace><xsl:text>http://www.d-nb.de/standards/xmetadissplus/</xsl:text></metadataNamespace>
          </metadataFormat>
          <metadataFormat>
            <metadataPrefix><xsl:text>xMetaDissPlus</xsl:text></metadataPrefix>
            <schema><xsl:text>http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd</xsl:text></schema>
            <metadataNamespace><xsl:text>http://www.d-nb.de/standards/xmetadissplus/</xsl:text></metadataNamespace>
          </metadataFormat>
        </ListMetadataFormats>
    </xsl:template>

    <xsl:template match="Documents" mode="ListIdentifiers">
        <xsl:if test="count(Opus_Document) > 0">
            <ListIdentifiers>
                <xsl:apply-templates select="Opus_Document" />
                <xsl:if test="$totalIds > 0">
                    <resumptionToken>
                        <xsl:attribute name="expirationDate"><xsl:value-of select="$dateDelete"/></xsl:attribute>
                        <xsl:attribute name="completeListSize"><xsl:value-of select="$totalIds"/></xsl:attribute>
                        <xsl:attribute name="cursor"><xsl:value-of select="$cursor"/></xsl:attribute>
                        <xsl:value-of select="$res"/>
                    </resumptionToken>
                </xsl:if>
            </ListIdentifiers>
        </xsl:if>
    </xsl:template>

    <xsl:template match="Documents" mode="ListSets">
        <ListSets>
            <xsl:apply-templates select="Opus_Sets" />
        </ListSets>
    </xsl:template>

    <xsl:template match="Documents" mode="ListRecords">
        <xsl:if test="count(Opus_Document) > 0">
            <ListRecords>
            <xsl:apply-templates select="Opus_Document" />
                <xsl:if test="$totalIds > 0">
                    <resumptionToken>
                        <xsl:attribute name="expirationDate"><xsl:value-of select="$dateDelete"/></xsl:attribute>
                        <xsl:attribute name="completeListSize"><xsl:value-of select="$totalIds"/></xsl:attribute>
                        <xsl:attribute name="cursor"><xsl:value-of select="$cursor"/></xsl:attribute>
                        <xsl:value-of select="$res"/>
                    </resumptionToken>
                </xsl:if>
            </ListRecords>
        </xsl:if>
    </xsl:template>

    <xsl:template match="Documents" mode="GetRecord">
        <GetRecord>
            <xsl:apply-templates select="Opus_Document" />
        </GetRecord>
    </xsl:template>

    <xsl:template match="Opus_Sets">
        <set>
           <setSpec><xsl:value-of select="@Type"/></setSpec>
           <setName><xsl:value-of select="@TypeName"/></setName>
        </set>
    </xsl:template>


    <xsl:template match="Opus_Document">
      <xsl:choose>
         <xsl:when test="$oai_verb='ListIdentifiers'">
           <xsl:call-template name="Opus_Document_Data"/>
         </xsl:when>
         <xsl:otherwise>
           <record>
             <xsl:call-template name="Opus_Document_Data"/>
           </record>
         </xsl:otherwise>
      </xsl:choose>
    </xsl:template>


    <xsl:template name="Opus_Document_Data">
        <header>
            <xsl:if test="@ServerState='deleted'">
                 <xsl:attribute name="status">
                    <xsl:text>deleted</xsl:text>
                 </xsl:attribute>
            </xsl:if>
                <!--
                    This is the identifier for the metadata, not a digital object:
                    http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm
                -->
            <xsl:choose>
              <xsl:when test="$oai_verb='GetRecord'">
                <identifier>
                    <xsl:value-of select="$oai_identifier" />
                </identifier>
              </xsl:when>
              <xsl:otherwise>
                <identifier>
                    <xsl:text>oai:</xsl:text><xsl:value-of select="$repIdentifier" /><xsl:text>:</xsl:text><xsl:value-of select="@Id" />
                </identifier>
               </xsl:otherwise>
            </xsl:choose>
                <datestamp>
                  <xsl:choose>
                    <xsl:when test="./ServerDateModified">
                        <xsl:value-of select="ServerDateModified/@Year"/>-<xsl:value-of select="format-number(ServerDateModified/@Month,'00')"/>-<xsl:value-of select="format-number(ServerDateModified/@Day,'00')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="ServerDatePublished/@Year"/>-<xsl:value-of select="format-number(ServerDatePublished/@Month,'00')"/>-<xsl:value-of select="format-number(ServerDatePublished/@Day,'00')"/>
                    </xsl:otherwise>
                  </xsl:choose>
                </datestamp>
            <xsl:choose>
                <xsl:when test="$oai_set='openaire'">
                    <setSpec>openaire</setSpec>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates select="SetSpec" />
                </xsl:otherwise>
            </xsl:choose>
            </header>
            <!-- choose the corresponding template depending on metadataPrefix -->
            <!-- not, when verb=ListIdentifiers -->
            <xsl:choose>
                 <xsl:when test="$oai_verb!='ListIdentifiers' and @ServerState!='deleted'">
                 <metadata>
                 <xsl:choose>
                    <xsl:when test="$oai_metadataPrefix='XMetaDissPlus'">
                       <xsl:apply-templates select="." mode="xmetadissplus" />
                    </xsl:when>
                    <xsl:when test="$oai_metadataPrefix='xMetaDissPlus'">
                       <xsl:apply-templates select="." mode="xmetadissplus" />
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
                 </metadata>

            </xsl:when>
            </xsl:choose>
    </xsl:template>

    <xsl:template match="SetSpec">
       <setSpec><xsl:value-of select="@Value"/></setSpec>
    </xsl:template>

</xsl:stylesheet>
