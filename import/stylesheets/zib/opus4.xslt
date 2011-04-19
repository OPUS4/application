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
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009-2010 OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: opus3.xslt 6088 2010-09-30 13:39:46Z gmaiwald $
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl">

    <xsl:output method="xml" indent="no" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />

    <xsl:template match="/">
        <xsl:element name="Documents">
            <xsl:apply-templates select="/Opus/Opus_Document"/>
        </xsl:element>
    </xsl:template>

    <!-- All Fields of table 'opus' -->
    <xsl:template match="/Opus/Opus_Document">
        <xsl:element name="Opus_Document">

            <!-- CompletedYear -->
            <xsl:for-each select="@CompletedYear">
                <xsl:attribute name="CompletedYear">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:for-each>

            <!-- Language -->
            <xsl:for-each select="@Language">
                <xsl:attribute name="Language">
                     <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:for-each>

            <!-- CompletedYear -->
            <xsl:for-each select="@PublishedYear">
                <xsl:attribute name="PublishedYear">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:for-each>

            <!-- CompletedYear -->
            <xsl:for-each select="@ServerState">
                <xsl:attribute name="ServerState">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:for-each>

            <!-- Type -->
            <xsl:for-each select="@Type">
                <xsl:attribute name="Type">
                     <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:for-each>

            <!-- BelongsToBibliography -->
            <xsl:for-each select="@BelongsToBibliography">
                <xsl:attribute name="BelongsToBibliography">
                     <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:for-each>

            <!-- PageNumber -->
            <xsl:for-each select="@PageNumber">
                <xsl:attribute name="PageNumber">
                     <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:for-each>


            <!-- PublishedDate -->
            <xsl:for-each select="PublishedDate">
                <xsl:attribute name="PublishedDate">
                    <xsl:value-of select="@UnixTimestamp" />
                </xsl:attribute>
            </xsl:for-each>

            <!-- CompletedDate -->
            <xsl:for-each select="CompletedDate">
                <xsl:attribute name="CompletedDate">
                    <xsl:value-of select="@UnixTimestamp" />
                </xsl:attribute>
            </xsl:for-each>

            <!-- ThesisDateAccepted -->
            <xsl:for-each select="ThesisDateAccepted">
                <xsl:attribute name="ThesisDateAccepted">
                    <xsl:value-of select="@UnixTimestamp" />
                </xsl:attribute>
            </xsl:for-each>

            <!-- ServerDateModified -->
            <!-- Wird neu gesetzt -->

            <!-- ServerDatePublished -->
            <!-- Wird neu gesetzt -->

            <!-- TitleMain -->
	    <xsl:for-each select="TitleMain">
                <xsl:element name="TitleMain">
                    <xsl:attribute name="Language">
                        <xsl:value-of select="@Language" />
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="@Value" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

            <!-- TitleAbstract -->
	    <xsl:for-each select="TitleAbstract">
                <xsl:element name="TitleAbstract">
                    <xsl:attribute name="Language">
                        <xsl:value-of select="@Language" />
                    </xsl:attribute>
                    <xsl:attribute name="Value">
                        <xsl:value-of select="@Value" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>


            <!-- PersonAuthor -->
             <xsl:for-each select="PersonAuthor">
                 <xsl:element name="PersonAuthor">
                    <xsl:attribute name="FirstName">
                        <xsl:value-of select="@FirstName" />
                    </xsl:attribute>
                    <xsl:attribute name="LastName">
                        <xsl:value-of select="@LastName" />
                    </xsl:attribute>
                    <xsl:attribute name="Email">
                        <xsl:value-of select="@Email" />
                    </xsl:attribute>
                    <xsl:attribute name="AllowEmailContact">
                        <xsl:value-of select="@AllowEmailContact" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

            <!-- PersonSubmitter -->
             <xsl:for-each select="PersonSubmitter">
                 <xsl:element name="PersonSubmitter">
                    <xsl:attribute name="FirstName">
                        <xsl:value-of select="@FirstName" />
                    </xsl:attribute>
                    <xsl:attribute name="LastName">
                        <xsl:value-of select="@LastName" />
                    </xsl:attribute>
                    <xsl:attribute name="Email">
                        <xsl:value-of select="@Email" />
                    </xsl:attribute>
                    <xsl:attribute name="AllowEmailContact">
                        <xsl:value-of select="@AllowEmailContact" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

            <!-- PersonAdvisor -->
             <xsl:for-each select="PersonAdvisor">
                 <xsl:element name="PersonAdvisor">
                    <xsl:attribute name="FirstName">
                        <xsl:value-of select="@FirstName" />
                    </xsl:attribute>
                    <xsl:attribute name="LastName">
                        <xsl:value-of select="@LastName" />
                    </xsl:attribute>
                    <xsl:attribute name="Email">
                        <xsl:value-of select="@Email" />
                    </xsl:attribute>
                    <xsl:attribute name="AllowEmailContact">
                        <xsl:value-of select="@AllowEmailContact" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

            <!-- SubjectUncontrolled -->
	    <xsl:for-each select="SubjectUncontrolled">
                <xsl:element name="SubjectUncontrolled">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="@Value" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

             <!-- ReportId-->
	     <xsl:for-each select="Enrichment[@KeyName='report_id']">
                 <xsl:element name="IdentifierSerial">
                    <xsl:attribute name="Value">
                        <xsl:value-of select="@Value" />
                    </xsl:attribute>
                 </xsl:element>
	     </xsl:for-each>

            <!-- Note -->
	    <xsl:for-each select="Note">
                <xsl:element name="Note">
                    <xsl:attribute name="Visibility">
                        <xsl:value-of select="@Visibility" />
                    </xsl:attribute>
                    <xsl:attribute name="Message">
                        <xsl:value-of select="@Message" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

            <!-- Collection -->
	    <xsl:for-each select="Collection">
                <xsl:element name="OldCollection">
                    <xsl:attribute name="Id">
                        <xsl:value-of select="@Id" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

            <!-- Collection -->
	    <xsl:for-each select="ThesisPublisher">
                <xsl:element name="OldPublisher">
                    <xsl:attribute name="Id">
                        <xsl:value-of select="@Id" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

            <!-- Collection -->
	    <xsl:for-each select="ThesisGrantor">
                <xsl:element name="OldGrantor">
                    <xsl:attribute name="Id">
                        <xsl:value-of select="@Id" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

            <!-- Licence -->
	    <xsl:for-each select="Licence">
                <xsl:element name="OldLicence">
                    <xsl:attribute name="Id">
                        <xsl:value-of select="@Id" />
                    </xsl:attribute>
                </xsl:element>
	    </xsl:for-each>

        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
