<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	
	<xsl:template match="exception">
        <div class="error">
            <h2>Error : <xsl:value-of select="error_message"/> : <xsl:value-of select="code"/></h2>
            <div class="error_msg">
                <p>Trace : </p>
                <pre><xsl:value-of select="trace"/></pre>
            </div>
        </div>
	</xsl:template>

</xsl:stylesheet>