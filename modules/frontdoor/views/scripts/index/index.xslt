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
 * @author      Michael Lang <lang@zib.de>
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Simone Finkbeiner <simone.finkbeiner@ub.uni-stuttgart.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @copyright   Copyright (c) 2009-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:php="http://php.net/xsl"
                exclude-result-prefixes="php">

   <xsl:include href="templates/services.xslt"/>
   <xsl:include href="templates/metadata.xslt"/>
   <xsl:include href="templates/functions.xslt"/>
   <xsl:include href="templates/main.xslt"/>

    <xsl:output method="xml" omit-xml-declaration="yes" />

    <xsl:param name="baseUrlServer" />
    <xsl:param name="baseUrl" />
    <xsl:param name="layoutPath" />
    <xsl:param name="isMailPossible" />
    <xsl:param name="numOfShortAbstractChars" />
    <xsl:param name="docLang" select="//Opus_Document/@Language" />
    <xsl:param name="urnResolverUrl" />

    <xsl:key name="list" match="/Opus/Opus_Document/Subject[@Type='uncontrolled']" use="@Language"/>
    <xsl:key name="userCollections-by-roleId" match="Collection[@RoleName!='institutes' and @RoleName!='projects' and @RoleName!='ccs' and @RoleName!='ddc' and @RoleName!='msc' and @RoleName!='pacs' and @RoleName!='bk' and @RoleName!='jel']" use="@RoleId"/>

    <xsl:template match="/">
        <div about="{/Opus/Opus_Document/TitleMain/@Value}">
            <xsl:apply-templates select="Opus/Opus_Document" />
        </div>
    </xsl:template>

    <!-- Suppress spilling values with no corresponding templates -->
    <xsl:template match="@*|node()" />

    <!-- here you can change the order of the fields, just change the order of the apply-templates-rows
    if there is a choose-block for the field, you have to move the whole choose-block
    if you wish new fields, you have to add a new line xsl:apply-templates...
    and a special template for each new field below, too -->
    <xsl:template match="Opus_Document">

      <!-- main data templates defined in templates/main.xsl -->
       <div id="titlemain-wrapper">
         <xsl:call-template name="Title" />
      </div>

      <div id="result-data">
         <div id ="author">
            <xsl:call-template name="Author" />
         </div>

         <div id="abstract">
            <xsl:call-template name="SortedAbstracts" />
         </div>
      </div>

      <!-- service templates defined in templates/services.xsl -->
      <div id="services" class="services-menu">
         <xsl:if test="normalize-space(File/@PathName) and File[@VisibleInFrontdoor='1']">
             <xsl:choose>
                <xsl:when test="php:functionString('Application_Xslt::embargoHasPassed', @Id)">
                    <div id="download-fulltext" class="services">
                       <h3>
                          <xsl:call-template name="translateString">
                             <xsl:with-param name="string">frontdoor_download_options</xsl:with-param>
                          </xsl:call-template>
                       </h3>
                       <ul>
                           <xsl:choose>
                               <xsl:when test="php:functionString('Application_Xslt::customFileSortingEnabled', @Id)">
                                  <xsl:apply-templates select="File[@VisibleInFrontdoor='1']">
                                     <xsl:sort select="@SortOrder" data-type="number" />
                                  </xsl:apply-templates>
                               </xsl:when>
                               <xsl:otherwise>
                                   <xsl:apply-templates select="File[@VisibleInFrontdoor='1']">
                                     <xsl:sort select="@Label"/>
                                  </xsl:apply-templates>
                               </xsl:otherwise>
                           </xsl:choose>
                       </ul>
                    </div>
                </xsl:when>
                <xsl:otherwise>
                     <xsl:apply-templates select="EmbargoDate" mode="fileDownloadEmbargo"/>
                </xsl:otherwise>

             </xsl:choose>
         </xsl:if>

          <div id="export" class="services">
            <h3>
               <xsl:call-template name="translateString">
                  <xsl:with-param name="string">frontdoor_export_options</xsl:with-param>
               </xsl:call-template>
            </h3>
            <xsl:call-template name="ExportFunctions" />
         </div>

         <xsl:if test="$printOnDemandEnabled and Licence[@PodAllowed='1']">
            <div id="print-on-demand" class="services">
               <h3>
                  <xsl:call-template name="translateString">
                     <xsl:with-param name="string">frontdoor_pod_options</xsl:with-param>
                  </xsl:call-template>
               </h3>
               <ul>
                  <xsl:call-template name="PrintOnDemand" />
               </ul>
            </div>
         </xsl:if>

         <div id="additional-services" class="services">
            <h3>
               <xsl:call-template name="translateString">
                  <xsl:with-param name="string">frontdoor_additional_options</xsl:with-param>
               </xsl:call-template>
            </h3>
            <div>
               <xsl:call-template name="AdditionalServices"/>
               <xsl:call-template name="MailToAuthor"/>
            </div>
         </div>

      </div>

      <!-- metadata templates defined in templates/metadata.xsl -->
   <table class="result-data frontdoordata">
            <caption>Metadaten</caption>
            <colgroup class="angaben">
                <col class="name"/>
            </colgroup>

            <xsl:apply-templates select="PersonAuthor" />
            <xsl:apply-templates select="IdentifierUrn" />
            <xsl:apply-templates select="IdentifierUrl" />
            <xsl:apply-templates select="IdentifierHandle" />
            <xsl:apply-templates select="IdentifierDoi" />
            <xsl:apply-templates select="IdentifierIsbn" />
            <xsl:apply-templates select="IdentifierIssn" />
            <xsl:apply-templates select="IdentifierArxiv" />
            <xsl:apply-templates select="IdentifierPubmed" />
            <xsl:apply-templates select="TitleParent" mode="mainLanguage" />
            <xsl:apply-templates select="TitleParent" mode="otherLanguage" />
            <xsl:apply-templates select="TitleSub" mode="mainLanguage" />
            <xsl:apply-templates select="TitleSub" mode="otherLanguage" />
            <xsl:apply-templates select="TitleAdditional" mode="mainLanguage" />
            <xsl:apply-templates select="TitleAdditional" mode="otherLanguage" />
            <xsl:apply-templates select="Series[@Visible=1]" >
                <xsl:sort select="@SortOrder" data-type="number" />
            </xsl:apply-templates>
            <xsl:apply-templates select="@PublisherName" />
            <xsl:apply-templates select="@PublisherPlace" />
            <xsl:apply-templates select="PersonEditor" />
            <xsl:apply-templates select="PersonTranslator" />
            <xsl:apply-templates select="PersonContributor" />
            <xsl:apply-templates select="PersonOther" />
            <xsl:apply-templates select="PersonReferee" />
            <xsl:apply-templates select="PersonAdvisor" />
            <xsl:apply-templates select="@Type" />
            <xsl:apply-templates select="@Language" />

            <xsl:choose>
                <xsl:when test="string-length(normalize-space(CompletedDate/@Year)) > 0">
                    <xsl:apply-templates select="CompletedDate" />
                </xsl:when>
                <xsl:when test="normalize-space(@CompletedYear) != '0000'">
                    <xsl:apply-templates select="@CompletedYear" />
                </xsl:when>
            </xsl:choose>
            <xsl:choose>
                <xsl:when test="string-length(normalize-space(PublishedDate/@Year)) > 0">
                    <xsl:apply-templates select="PublishedDate" />
                </xsl:when>
                <xsl:when test="normalize-space(@PublishedYear) != '0000'">
                    <xsl:apply-templates select="@PublishedYear" />
                </xsl:when>
            </xsl:choose>
            <xsl:choose>
                <xsl:when test="string-length(normalize-space(EmbargoDate/@Year)) > 0">
                    <xsl:apply-templates select="EmbargoDate" mode="metadataEmbargo" />
                </xsl:when>
            </xsl:choose>

            <xsl:apply-templates select="ThesisPublisher" />
            <xsl:apply-templates select="ThesisGrantor" />
            <xsl:apply-templates select="ThesisDateAccepted" />
            <xsl:apply-templates select="@CreatingCorporation" />
            <xsl:apply-templates select="@ContributingCorporation" />

            <xsl:apply-templates select="ServerDatePublished" />

            <!-- Subjects section:  New subjects must be introduced right here. -->
            <!-- we need to apply a hack (so called Muenchian grouping) here since XSLT's 2.0 for-each-group feature is currently not supported -->
            <xsl:if test="Subject[@Type='uncontrolled']">
                <tr>
                    <th class="name">
                        <xsl:call-template name="translateString">
                            <xsl:with-param name="string">subject_frontdoor_uncontrolled</xsl:with-param>
                        </xsl:call-template>
                        <xsl:text>:</xsl:text>
                    </th>

                    <td>
                        <em class="data-marker">
                            <xsl:for-each select="Subject[@Type='uncontrolled'][generate-id(.)=generate-id(key('list', @Language))]/@Language">
                                <xsl:sort/>
                                <xsl:for-each select="key('list', .)">
                                    <xsl:sort select="@Value" />
                                    <xsl:value-of select="@Value"/>
                                    <xsl:if test="position() != last()">; </xsl:if>
                                </xsl:for-each>
                                <xsl:if test="position() != last()">
                                    <br/>
                                </xsl:if>
                            </xsl:for-each>
                        </em>
                    </td>
                </tr>
            </xsl:if>
            <xsl:choose>
                <xsl:when test="php:functionString('Application_Xslt::optionEnabled', 'frontdoor.subjects.alphabeticalSorting')">
                    <xsl:apply-templates select="Subject[@Type='swd']">
                        <xsl:sort select="@Value"/>
                    </xsl:apply-templates>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates select="Subject[@Type='swd']" />
                </xsl:otherwise>
            </xsl:choose>
           <xsl:apply-templates select="Subject[@Type='psyndex']">
                <xsl:sort select="@Value"/>
            </xsl:apply-templates>
            <!-- End Subjects -->

            <xsl:apply-templates select="@Volume" />
            <xsl:apply-templates select="@Issue" />
            <xsl:apply-templates select="@Edition" />
            <xsl:apply-templates select="@PageNumber" />
            <xsl:apply-templates select="@PageFirst" />
            <xsl:apply-templates select="@PageLast" />
            <xsl:apply-templates select="Note[@Visibility='public']" />

            <!-- Enrichment Section: add the enrichment keys that have to be displayed in frontdoor -->
            <xsl:apply-templates select="Enrichment[@KeyName='Event']" />
            <xsl:apply-templates select="Enrichment[@KeyName='Relation']" />
            <xsl:apply-templates select="Enrichment[@KeyName='City']" />
            <xsl:apply-templates select="Enrichment[@KeyName='Country']" />
            <!-- Enrichment Fields for Opus3 Documents -->
            <xsl:apply-templates select="Enrichment[@KeyName='SourceTitle']" />
            <xsl:apply-templates select="Enrichment[@KeyName='SourceSwb']" />
            <xsl:apply-templates select="Enrichment[@KeyName='ClassRvk']" />
            <xsl:apply-templates select="Enrichment[@KeyName='ContributorsName']" />
            <xsl:apply-templates select="Enrichment[@KeyName='NeuesSelect']" />
            <!-- End Enrichtments -->

            <!-- Collection Roles Section: add the collection roles keys that have to be displayed in frontdoor -->
            <xsl:apply-templates select="Collection[@RoleName='institutes']" />
            <xsl:apply-templates select="Collection[@RoleName='projects']" />

            <xsl:apply-templates select="Collection[@RoleName='ccs']" />
            <xsl:apply-templates select="Collection[@RoleName='ddc']" />
            <xsl:apply-templates select="Collection[@RoleName='msc']" >
                <xsl:sort select="@Number"/>
            </xsl:apply-templates>
            <xsl:apply-templates select="Collection[@RoleName='pacs']" />
            <xsl:apply-templates select="Collection[@RoleName='bk']" />
            <xsl:apply-templates select="Collection[@RoleName='jel']" />
            <xsl:apply-templates select="IdentifierSerial" />

            <xsl:for-each select="Collection[@RoleName!='institutes' and @RoleName!='projects' and @RoleName!='ccs' and @RoleName!='ddc' and @RoleName!='msc' and @RoleName!='pacs' and @RoleName!='bk' and @RoleName!='jel'][count(. | key('userCollections-by-roleId', @RoleId)[1]) = 1]">
                <xsl:apply-templates select="key('userCollections-by-roleId', @RoleId)" />
            </xsl:for-each>
            <!-- End Collection Roles -->

            <xsl:apply-templates select="Patent" />
            <xsl:apply-templates select="Licence" />

            <xsl:if test="php:functionString('Application_Xslt::isDisplayField', 'BelongsToBibliography')">
                <xsl:apply-templates select="@BelongsToBibliography" />
            </xsl:if>
        </table>

    </xsl:template>

</xsl:stylesheet>
