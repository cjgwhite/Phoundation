<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : index.xsl
    Created on : March 19, 2009, 2:12 PM
    Author     : chris
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html"/>

    <xsl:template match="content">
        <h1>Documentation</h1>

        <h2>What Phramework is</h2>
        <p>Phramework is a simple PHP framework for developing dynamic websites. The intention is for Phramework
        to remain simple to implement and simple to develop within.</p>
        <p>There are hundreds of PHP frameworks around, so why another one? To date, I have never found a PHP framework
        that is simple to learn to use, simple to implement and simple to extend and devlop with. These are the very
        essence of what Phramework is about.</p>
        <p>Phramework provides you with, quite simply, a framework with which you can build upon.</p>

        <h2>What Phramework is NOT</h2>
        <p>Phramework is not an all encompassing PHP Rapid Application Development tool. It does not provide you with
        an Object-Relational mapping framework. It does not do any 'scaffolding' for you and it most certainly does not
        do any code generation.</p>

        <h2>The Basics</h2>
        <h3>Installation</h3>
        <p>Get the code archive and expand it in a directory accessible to your webserver (root_dir).</p>
        <p>Edit root_dir/.htaccess for the specifics of your site.</p>
        <p>Edit root_dir/system/config/system.conf for the spcifics of your site.</p>

        <h3>Elements of Phramework</h3>
        <p>Developments using Phramework are built using 'controllers' and 'views'. Controllers are php classes
        that compile and process and data for the specific page that is requested by the user. Views are XSL files
        that define the layout of the elements of the requested page, or the layout of the page itself.</p>
        <p>Although there is more to developing in Phramework than just controllers and views, a simple aplication
        can be built soley using these.</p>

        <h3>The file structure</h3>
        <p><strong>control</strong> - This directory contains all the 'controllers' for pages.</p>
        <p><strong>view</strong> - This directory contains all the 'xls' files.</p>
        <p>The directory structure within these two directories should mirror the URL of the page request. So, for example, a
        request for http://localhost/phramework/documents/index.html will look for a 'controller' in
        root_dir/conrol/documents/index_control.php.</p>

        <h3>Developing in Phramework</h3>
        <p>A simple page can be added by simply creating an xsl file in root_dir/view/pages. By default there is a
        file called __default.xsl. This is used when the system cannot find a conroller or xsl to match the URL
        requested. This should be used as a template for the view file. Create a copy of this file in the same
        directory and call it test.xsl.</p>
        <p>Edit test.xsl by changing the content inside the &lt;template&gt; tags. The content should be XML complient
        HTML. Once saved goto your browser and request http://your.host/url_path_to_install/test.html and you should
        see the page rendered using the standard Phramework template.</p>
        <p>Create a file in root_dir/control called test_control.php with the following code..</p>
        <code>
            <pre>
             class test_control extends DefaultControl {
                public function __default($params) {
                    $data = array(
                        "message" => array(
                                        "Hello World",
                                        "This is my first Phramework Controller"
                                    )
                    );

                    $this->response->addData($data, "content");
                    $this->response->addXsl("pages/test.xsl");
                }
             }
             </pre>
        </code>
        <p>Save this file and open root_dir/view/pages/test.xsl to edit and add the following lines inside the &lt;template&gt;
        element.</p>
        <code>
            <pre>
            &lt;xsl:for-each select="//message"&gt;
                &lt;p style="background-color: red;"&gt;&lt;strong&gt;&lt;xsl:value-of select="."/&gt;&lt;/strong&gt;&lt;/p&gt;
            &lt;/xsl:for-each&gt;
            </pre>
        </code>
        <p>Save this then reload http://your.host/url_path_to_install/test.html in your browser. You should see the
        effect this has on the page immadiately.</p>
    </xsl:template>

</xsl:stylesheet>
