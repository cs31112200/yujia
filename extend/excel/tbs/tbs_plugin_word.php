<?php

/*
********************************************************
TinyButStrong plug-in: Excel Worksheets
Version 1.0.3, on 2006-07-11, by Skrol29
********************************************************
*/

// Name of the class is a keyword used for Plug-In authentication. So i'ts better to save it into a constant.
define('TBS_WORD','clsTbsWord');
define('TBS_WORD_FILENAME',1);
define('TBS_WORD_DOWNLOAD',2);
define('TBS_WORD_INLINE',3);

class clsTbsWord
{

    // TBS events -----------------------------

    function OnInstall()
    {
        // Usefulle properies
        $this->FileName = '';
        $this->TemplateFileName = '';
        $this->ForceDownload = true;
        return array('OnCommand','AfterShow');
    }

    function OnCommand($Cmd,$Value='')
    {
        if ($Cmd==TBS_WORD_FILENAME) {
            // Change file name
            $this->FileName = $Value;
        }
        elseif ($Cmd==TBS_WORD_DOWNLOAD) {
            // Force output to download file
            $this->ForceDownload = true;
        }
        elseif ($Cmd==TBS_WORD_INLINE) {
            // Enables output to display inline (Internet Exlorer only)
            $this->ForceDownload = false;
        }
    }

    function AfterShow(&$Render)
    {
        // Makes a download instead of displaying the result.
        if (($Render & TBS_OUTPUT)==TBS_OUTPUT) {
            $Render = $Render - TBS_OUTPUT;
            $FileName = $this->FileName;
            if ($FileName==='')
                $FileName = $this->f_DefaultFileName();
            $this->f_Display($FileName,$this->ForceDownload); // Output with header
        }

    }

    // Functions for internal job -----------------------------
    function f_Display($FileName,$Download)
    {
        if ($Download) {
            header ('Pragma: no-cache');
            //  header ('Content-type: application/x-msword');
            header ('Content-Type: application/vnd.ms-word');
            header ('Content-Disposition: attachment; filename="'.$FileName.'"');

            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public');
            header('Content-Description: File Transfer');

            header('Content-Transfer-Encoding: binary');
            header('Content-Length: '.strlen($this->TBS->Source));
        } else {
            header('Content-Type: application/x-msword; name="'.$FileName.'"');
            header('Content-Disposition: inline; filename="'.$FileName.'"');
        }
        echo($this->TBS->Source);
    }

    function f_DefaultFileName()
    {
        if ($this->TemplateFileName==='') {
            return 'normal.doc';
        } else {
            $File = $this->TemplateFileName;
            // Keep only the file name
            $Pos = strrpos($File,'/');
            if ($Pos===false)
                $Pos = strrpos($File,'\\');
            if ($Pos!==false)
                $File = substr($File,$Pos+1);
            // Change extention from .xml to .xls in order to have a proper opening file with Explorer
            $Len = strlen($File);
            if ($Len>4) {
                $Ext = substr($File,$Len-4,4);
                if (strtolower($Ext)=='.xml')
                    $File = substr($File,0,$Len-4).'.doc';
            }
            return $File;
        }
    }
}
?>
