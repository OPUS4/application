<?xml version="1.0" encoding="utf-8"?>

<!-- stylesheet used by create_doctype-phtml_from_xml.php -->

<xsl:stylesheet version="1.0" 
                xmlns:opus="http://www.opus-repository.org/schema/documenttype"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
>
    <xsl:output method="text" />

    <xsl:template match="/">
        <xsl:apply-templates select="opus:documenttype/opus:field"/>
        <!--<xsl:value-of select="opus:documenttype/opus:field/@name"/>-->
    </xsl:template>
    
    <xsl:template match="opus:field">
        <xsl:choose>
            <!-- The following conditions apply to "group" fields. This might not be complete. -->
            <xsl:when test="@multiplicity != 1 or ./opus:subfield or @root or starts-with(@datatype, 'Title')">
                <xsl:call-template name="create_group" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="create_element" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="create_element">
        <xsl:text>&lt;?= $this->element($this-></xsl:text><xsl:value-of select="@name"/><xsl:text>); ?&gt;
</xsl:text>
    </xsl:template>

    <xsl:template name="create_group">
        <xsl:text>&lt;?= $this->group($this->group</xsl:text><xsl:value-of select="@name"/><xsl:text>); ?&gt;
</xsl:text>
    </xsl:template>


</xsl:stylesheet>