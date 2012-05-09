<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE xsl:stylesheet [ <!ENTITY nbsp "&#160;"> ]>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" >
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/">
        <xsl:apply-templates select="/data"/>
    </xsl:template>

    <xsl:template match="data">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <base href="http://{template/HOST}:{template/PORT}{template/ROOT_URI}/"/>
    <link href="{template/ROOT_URI}/web/css/style.css" type="text/css" rel="stylesheet" media="screen" />
    <script src="web/javascript/jquery.js" type="text/javascript"></script>
    <script src="web/javascript/menu.js" type="text/javascript"></script>
	<title>Framework</title>
</head>
<body class="page-content-twocolumn" id="site_id">
	<div><a name="pagetop"></a></div>
	<div class="nav-skiplinks">
        |&nbsp;<a href="#nav-skiplink-target-content">Skip to main content</a>&nbsp;
        |&nbsp;<a href="#nav-skiplink-target-navigation">Skip to navigation</a>&nbsp;
        |&nbsp;<a href="#nav-skiplink-target-search">Skip to Search</a>&nbsp;
        |
    </div>
    <div id="header">
        <div id="logo">
            <a href="http://www.phramework.co.uk/" title="Phramework Homepage">
                <img src="{template/ROOT_URI}/web/images/logo.gif" alt="Phramework" />
            </a>
        </div>
    </div>
   
	<div class="nav-skiplink-target"><a name="nav-skiplink-target-navigation"></a></div>
	<div id="main">

        <div id="nav">
            <xsl:apply-templates select="navigation"/>
        </div>

        <div class="nav-skiplink-target"><a name="nav-skiplink-target-content"></a></div>

        <div id="content">
            <xsl:apply-templates select="content"/>
        </div>

    </div>

    <div id="footer">
        <div class="footer">
            <p>
                |&nbsp;<a href="http://www.phramework.co.uk" title="Home Page">Phramework Home</a>&nbsp;
                |&nbsp;Phramework 2009
            </p>
        </div>
    </div>
    <div class="nav-skiplinks">
        |&nbsp;<a href="#nav-skiplink-target-content">Skip to main content</a>&nbsp;
        |&nbsp;<a href="#nav-skiplink-target-navigation">Skip to navigation</a>&nbsp;
        |&nbsp;<a href="#nav-skiplink-target-search">Skip to Search</a>&nbsp;
        |
    </div>

    <xsl:apply-templates select="//exception"/>

</body>
</html>

    </xsl:template>

    
</xsl:stylesheet>
