<?php
/**
 * Description of PassThruFilter
 *
 * @author chris
 */
class PassThruFilter extends AbstractFilter{

    private $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

    protected function before() {
        global $APP_ROOT;

        $url = "$APP_ROOT/{$this->url}";

        $this->log->debug("URL to PassThru = $url");

        try {

            
            ob_clean();
            $mimeType = "";
            if (function_exists('mime_content_type')) {
                $this->log->debug("Using mime_content_type");
                $mimeType = mime_content_type($url);
            }

            if (function_exists('finfo_open') && $mimeType == "") {
                $this->log->debug("Using finfo");
                $finfo = finfo_open(FILEINFO_MIME);
                $mimetype = finfo_file($finfo, $url);
                finfo_close($finfo);
            }

            if ($mimeType == "") {
                $this->log->debug("Guessing from filename");
                $ext = strtolower(array_pop(explode('.',$url)));
                $this->log->debug("Extension = $ext");
                if (array_key_exists($ext, $this->mime_types))
                    $mimeType = $this->mime_types[$ext];
            }

            if ($mimeType == "")
                $mimeType = "application/octet-stream";
            
            header("Content-type: $mimeType");

            $this->log->debug("PassThruFilter :: mime type = $mimeType");

            $fp = fopen($url, 'rb');
            fpassthru($fp);

            $this->params['NO_RENDER'] = true;
        } catch (Exception $e) {
            $this->log->debug("PassThruFilter :: " . $e->getMessage());
        }
        return self::CHAIN_END;

    }

    protected function after(){

        return self::CHAIN_END;
    }
}
?>
