<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : nav.xsl.xsl
    Created on : 16 January 2009, 09:26
    Author     : chris
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html"/>

    <xsl:template match="navigation">
        <ul>
            <xsl:apply-templates select=".//breadcrumb"/>
            <xsl:for-each select=".//menu">
                <li class="sectionheader">
                    <xsl:choose>
                        <xsl:when test="url">
                            <a href="{url}"><xsl:value-of select="menu-header"/></a>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="menu-header"/>
                        </xsl:otherwise>
                    </xsl:choose>
                    <ul class="sectionmenu">
                        <xsl:apply-templates/>
                    </ul>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>
    
    <xsl:template match="navigation/breadcrumb">
        <li class="nav-secondary-breadcrumb">
            <a href="{url}"><xsl:value-of select="sectionname"/></a>
        </li>
    </xsl:template>
    
    
    <xsl:template match="navigation//entry">
            <li>
                <span>
                    <xsl:choose>
                        <xsl:when test="url">
                            <a href="{url}"><xsl:value-of select="title"/></a>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="title"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </span>
                <ul class="sectionmenu submenu">
                    <xsl:apply-templates />
                </ul>
            </li>
    </xsl:template>

    <xsl:template match="navigation//url"></xsl:template>
    <xsl:template match="navigation//title"></xsl:template>
    <xsl:template match="navigation//menu-header"></xsl:template>
    <xsl:template match="navigation//description"></xsl:template>

</xsl:stylesheet>
