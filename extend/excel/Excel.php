<?php
//
final class Excel
{
    //模板
    public $tpl = null;
    //导出的文件名
    public $filename = null;
    //field数据
    public $field = array();
    //block数据
    public $block = array();

    function __construct()
    {
    }

    function export()
    {
        if(is_null($this->tpl) || is_null($this->filename)) {
            return;
        }
        require_once dirname(__FILE__) . '/tbs/tbs_class.php';
        require_once dirname(__FILE__) . '/tbs/tbs_plugin_opentbs.php';
        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

        $TBS->LoadTemplate($this->tpl, false);
        foreach ((array)$this->block as $k => $v) {
            $TBS->MergeBlock($k, $v);
        }
        foreach ((array)$this->field as $k => $v) {
            $TBS->MergeField($k, $v);
        }
        
        $TBS->Show(OPENTBS_DOWNLOAD, $this->filename);
    }

    function get_content()
    {
        if(is_null($this->tpl) || is_null($this->filename)) {
            return;
        }
        require_once dirname(__FILE__) . '/tbs/tbs_class.php';
        require_once dirname(__FILE__) . '/tbs/tbs_plugin_excel.php';
        $TBS = new clsTinyButStrong;
        $TBS->Render = 0;
        $TBS->NoErr = true;
        $TBS->PlugIn(TBS_INSTALL,TBS_EXCEL);
        $TBS->LoadTemplate($this->tpl, false);
        foreach ((array)$this->block as $k => $v) {
            $TBS->MergeBlock($k, $v);
        }
        foreach ((array)$this->field as $k => $v) {
            $TBS->MergeField($k, $v);
        }
        $TBS->PlugIn(TBS_EXCEL,TBS_EXCEL_INLINE, $this->filename);
        $TBS->Show();
        return $TBS->Source;
    }
}