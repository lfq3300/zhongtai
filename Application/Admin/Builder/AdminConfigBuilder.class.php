<?php
namespace Admin\Builder;
use Think\View;
class AdminConfigBuilder extends AdminBuilder{
	 private $_title;
	 private $_data = array();
	 private $_keyList = array();
	 private $_formtitle = array();
     private $_buttonList = array();
     private $_savePostUrl = "";
	 public function title($title)
    {
        $this->_title = $title;
        return $this;
    }



    public function formtitle($title ='默认标题')
    {
        $this->_formtitle = $title;
        return $this;
    }
     public function key($name, $type, $opt =[])
    {
        $key = array('name' => $name, 'type' => $type, 'opt' => $opt);
        $this->_keyList[] = $key;
        return $this;
    }

    public function keyCancel(){
        return $this->key('','cancelimg',[]);
    }

    public function data($list = [])
    {
        $this->_data = $list;
        return $this;
    }

    public function button($title, $attr = array())
    {
        $this->_buttonList[] = array('title' => $title, 'attr' => $attr);
        return $this;
    }
    public function savePostUrl($url)
    {
        if ($url) {
            $this->_savePostUrl = $url;
        }
    }
    /*
		$name from 表单提交name
		$title from 表单模块标题
		$subtitle from 表单模块副标题
		placeholder 提示文字
		error   警告文字
        vlaue  keyHidden 默认值
        btntitle  上传图片按钮文字
		$opt array("btntitle"=>"***","vlaue"=>"***","placeholder"=>"****","error"=>"****","title"=>"***","subtitle"=>"****")
    */
    public function keyText($name = 'NULL',$opt =[]){
        $opt["title"]=$opt["title"]?$opt["title"]:"文本框";
    	  return $this->key($name,'text',$opt);
    }
    public function keyTime($name = 'NULL',$opt =[]){
        $opt["title"]=$opt["title"]?$opt["title"]:"时间";
        return $this->key($name,'time',$opt);
    }
    public  function  keyRadio($name = 'NULL',$opt =[]){
        $opt["title"]=$opt["title"]?$opt["title"]:"单选框";
        return $this->key($name,'radio',$opt);
    }
    public function keyNumber($name = 'NULL',$opt =[]){
        $opt["title"]=$opt["title"]?$opt["title"]:"数字框";
        return $this->key($name,'textnumber',$opt);
    }

    public function keyDisabled($name = 'NULL',$opt =[]){
          $opt["title"]=$opt["title"]?$opt["title"]:"不可修改";
           return $this->key($name,'textDisabled',$opt);
    }

    public function keyHidden($name = 'NULL',$opt = []){
         return $this->key($name,'hidden',$opt);
    }
    public function keyTextarea($name = 'NULL',$opt =[]){
        $opt["title"]=$opt["title"]?$opt["title"]:"输入框";
   		 return $this->key($name,'textarea',$opt);
    }
    public function keyTextButton($name = 'NULL',$opt =[]){
        $opt["title"]=$opt["title"]?$opt["title"]:"按钮";
        return $this->key($name,'textbutton',$opt);
    }

    public function keySelect($name = 'NULL',$opt = [],$value = '')
    {
        $opt["title"]=$opt["title"]?$opt["title"]:"筛选框";
        $opt["select"]=$opt["select"]?$opt["select"]:"";
        $opt["value"]=$opt["value"]?$opt["value"]:$value;
        return $this->key($name, 'select', $opt);
    }

    public function keyDisabledStatus($name = 'NULL', $opt =[],$value = ''){
        $opt["title"]=$opt["title"]?$opt["title"]:"不可筛选框";
        $opt["select"]=$opt["select"]?$opt["select"]:"";
        $opt["value"]=$opt["value"]?$opt["value"]:$value;
         return $this->key($name,'DisabledStatus', $opt);
    }

    public function buttonSubmit($url = '', $title = '确定')
    {
        $this->savePostUrl($url);
        $attr = array();
        $attr['class'] = "am-btn am-btn-primary tpl-btn-bg-color-success ajax-post";
        $attr['id'] = 'submit';
        $attr['type'] = 'submit';
        return $this->button($title, $attr);
    }


    public function keyUploadImg($name = 'NULL',$opt =[]){
        $opt["btntitle"]=$opt["btntitle"]?$opt["btntitle"]:"上传图片";
        $opt["title"]=$opt["title"]?$opt["title"]:"上传图片";
    	 return $this->key($name,'uploadimg',$opt);
    }
    public function keyShowImg($name = 'NULL',$opt =[]){
        $opt["btntitle"]=$opt["btntitle"]?$opt["btntitle"]:"上传图片";
        $opt["title"]=$opt["title"]?$opt["title"]:"上传图片";
         return $this->key($name,'showimg',$opt);
    }

    public function keyEditor($name = 'NULL', $opt = array('style'=>"width:720px;height:800px"))
    {
        return $this->key($name, 'editor', $opt);
    }

    public  function  keyHtml($name, $title,$opt =[]){
        return $this->key($name, $title,"Html",$opt);
    }

    //需要修改
    public function keyExcel($len,$opt =[]){
        $key = array('name' =>"excel",'opt'=>$opt, 'len' => $len, 'type' => 'excel');
        $this->_keyList[] = $key;
        return $this;
    }
    

	public function display($solist = ''){
		foreach ($this->_buttonList as &$button) {
            $button['attr'] = $this->compileHtmlAttr($button['attr']);
        }
		foreach ($this->_keyList as &$e) {
			$e['value'] = $this->_data[$e['name']];
		}
		$this->assign('title', $this->_title);
        $this->assign('formtitle', $this->_formtitle);
		$this->assign('keyList', $this->_keyList);
		$this->assign('buttonList', $this->_buttonList);
		$this->assign('savePostUrl', $this->_savePostUrl);
        $this->assign('selectData', $this->_selectData);

		if ($solist) {
				parent::display($solist);
			} else {
				parent::display('admin_congif');
		}
	}
}
?>