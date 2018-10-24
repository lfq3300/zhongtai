<?php
namespace Admin\Builder;
use Think\View;
class AdminListBuilder extends AdminBuilder{

    private $powerArr;
    private $add;
    private $remove;
    private $edit;
    private $query;
    private $verify;
    private $thisUrl;
    private $export;

    public function _initialize()
    {
        if(cookieDecrypt(cookie("level")) == C(ROOT_LEVEL)){
            $this->add =1;
            $this->remove = 1;
            $this->edit = 1;
            $this->query = 1;
            $this->verify = 1;
            $this->export = 1;
        }else{
            //权限检测
            $controllername = CONTROLLER_NAME;
            $actionname = ACTION_NAME;
            //获取当前控制器/方法名
            $this->thisurl = $controllername."/".$actionname;
            $role_id  = cookieDecrypt(cookie("role_id"));
            //先判断是否有这个目录
            list($this->powerArr) = M()->query("SELECT C.`add`,C.`edit`,C.`export`,C.`query`,C.`remove`,C.`verify`  FROM mc_access AS A  INNER JOIN mc_admin_menu AS B  ON A.node_id = B.id INNER JOIN mc_power AS C  ON B.id = C.menu_id WHERE B.url = '$this->thisurl' AND A.role_id = $role_id AND C.role_id = $role_id");
            $this->add =$this->powerArr["add"];
            $this->remove = $this->powerArr["remove"];
            $this->edit = $this->powerArr["edit"];
            $this->query = $this->powerArr["query"];
            $this->verify = $this->powerArr["verify"];
            $this->export = $this->powerArr["export"];
        }
    }

    private  $_title;
    private  $_buttonList = array();
    private  $_keyList = array();
    private  $_data = array();
    private $_query = array();
    private $_select = array();
    private $_queryselect = array();
    private $_startime = array();
    private $_endtime = array();
    private $_pagination = array();
    private $_grossincome = array();
    private  $_setStatusUrl;
    private  $_otherData;
    private $_hidequery = true;
    private $_excel = array();
    private $_excel2 = array();

    public function setStatusUrl($url)
    {
        $this->_setStatusUrl = $url;
        return $this;
    }
    public function otherData($list)
    {
        $this->_otherData = $list;
        return $this;
    }
    public function data($list)
    {
        $this->_data = $list;
        return $this;
    }

    public function key($name, $title,$type,$opt = null)
    {
       $this->_keyList[] = array('name' => $name, 'title' => $title,'type'=>$type,'opt' => $opt);
        return $this;
    }
     /*按钮*/
    public function button($title, $attr)
    {
        $this->_buttonList[] = array('title' => $title, 'attr' => $attr);
        return $this;
    }

    /*分页*/
     public function pagination($totalCount, $listRows)
    {
        $this->_pagination = array('totalCount' => $totalCount, 'listRows' => $listRows);
        return $this;
    }

    /*标题*/
    public function title($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function keyText($name, $title,$ope = null){
        return $this->key($name, $title,"text",$ope);
    }

    public  function  keyTime($name, $title,$ope = null){
        return $this->key($name, $title,"time",$ope);
    }
     public function keyStatus($name = 'status', $title = '状态', $map)
    {
    //    $map = array(-1 => '删除', 0 => '禁用', 1 => '启用', 2 => '未审核');
        return $this->key($name, $title, 'status', $map);
    }


     public function keyImg($name,$title,$url = ''){
        $key = array('name' => $name,'title' => $title,'type'=>'Img','opt' => null,'mcurl'=>$url);
        $this->_keyList[] = $key;
        return $this;
    }
    /*
     * opt
     * */
    public function query($name='query',$opt = []){
        $opt["state"] = $opt["state"]?$opt["state"]:false;
        $opt["title"] = $opt["title"]?$opt["title"]:"查询条件";
        $opt["value"] = $opt["value"]?$opt["value"]:"";
        $this->_query = array("name"=>$name,'opt'=>$opt);
        return $this;
    }

    public function hidequery(){
        $this->_hidequery = false;
        return $this;
    }

    public  function  queryselect($arrvalue = '',$opt =[],$name='queryType'){
        $opt["defaultvalue"]=$opt["defaultvalue"]?$opt["defaultvalue"]:0;
        $opt["title"]=$opt["title"]?$opt["title"]:0;
        $opt["select"]=$opt["select"]?$opt["select"]:0;
        $this->_queryselect[] = array("arrvalue"=>$arrvalue,"name"=>$name,'opt'=>$opt);
        return $this;
    }


    public  function select($arrvalue,$opt =[],$name='select'){
        $opt["defaultvalue"]=$opt["defaultvalue"]?$opt["defaultvalue"]:"不筛选";
        $opt["title"]=$opt["title"]?$opt["title"]:0;
        $opt["select"]=$opt["select"]?$opt["select"]:0;
        $this->_select =  array("arrvalue"=>$arrvalue,"name"=>$name,'opt'=>$opt);
        return $this;
    }


    /* Y  默认选到年 ,未做
     * m  选到月份
     * d  选到天数
     * */
    public  function  queryStarTime($val = '',$name='startime', $title = '开始时间',$type = "d"){
        $this->_startime = array("name"=>$name,"value"=>$val,"title"=>$title,"type"=>$type);
        return $this;
    }

    public function queryEndTime($val = '',$name = 'endtime', $title = '结束时间')
    {
        $this->_endtime = array("name"=>$name,"value"=>$val,"title"=>$title);
        return $this;
    }
    public function keyGrossincome($val){
        $this->_grossincome  = array("value"=>$val);
        return $this;
    }

    /*
        筛选
        $name 提交值
        selectOnchange 与 select 只允许存在一个
    */
    //根据权限进行是否执行代码

    //编辑

    public  function  powerEdit($getUrl, $text='编辑', $title = '操作'){
        if(!$this->edit)return $this;
        $href = explode("?",$getUrl);
        $href = CONTROLLER_NAME."/".$href[0];
        //获取默认getUrl函数
        if (is_string($getUrl)) {
            $getUrl = $this->createDefaultGetUrlFunction($getUrl);
        }
        //确认已经创建了DOACTIONS字段
        $doActionKey = null;
        foreach ($this->_keyList as $key) {
            if ($key['name'] === 'DOACTIONS') {
                $doActionKey = $key;
                break;
            }
        }
        if (!$doActionKey) {
            $this->key('DOACTIONS', $title, 'doaction', array());
        }
        //找出第一个DOACTIONS字段
        $doActionKey = null;
        foreach ($this->_keyList as &$key) {
            if ($key['name'] == 'DOACTIONS') {
                $doActionKey = &$key;
                break;
            }
        }
        //在DOACTIONS中增加action
        $doActionKey['opt']['actions'][] = array('text' => $text, 'get_url' => $getUrl);
        return $this;
    }
    //审核
    public  function  powerVerify($getUrl, $text='审核', $title = '操作'){
        if(!$this->verify)return $this;
        //获取默认getUrl函数
        if (is_string($getUrl)) {
            $getUrl = $this->createDefaultGetUrlFunction($getUrl);
        }
        //确认已经创建了DOACTIONS字段
        $doActionKey = null;
        foreach ($this->_keyList as $key) {
            if ($key['name'] === 'DOACTIONS') {
                $doActionKey = $key;
                break;
            }
        }
        if (!$doActionKey) {
            $this->key('DOACTIONS', $title, 'doaction', array());
        }
        //找出第一个DOACTIONS字段
        $doActionKey = null;
        foreach ($this->_keyList as &$key) {
            if ($key['name'] == 'DOACTIONS') {
                $doActionKey = &$key;
                break;
            }
        }
        //在DOACTIONS中增加action
        $doActionKey['opt']['actions'][] = array('text' => $text, 'get_url' => $getUrl);
        return $this;
    }

    public  function  powerAdd($href,$title="新增",$attr = array()){
        if(!$this->add) return $this;
        //缓存权限
            $attr['href'] = $href?$href:"JavaScript:void(0)";
            $attr['class'] = "am-btn am-btn-default am-btn-success";
            $attr['icon'] = "<span class='am-icon-plus'></span>";
            $attr['label'] = "a";
            return $this->button($title, $attr);
    }

    /*删除按钮*/
    public function powerRemove($href,$title="删除",$attr = array()){
        if(!$this->remove) return $this;
        //缓存权限
        $attr['href'] = $href?$href:"JavaScript:void(0)";
        $attr['class'] = "am-btn am-btn-default am-btn-danger delete-ajax-post";
        $attr['icon'] = "<span class='am-icon-trash-o'></span>";
        $attr['label'] = "button";
        $attr['target-form'] = 'ids';
        return $this->button($title, $attr);
    }
    /*导出excel*/
    public function powerExport($href){
        if(!$this->export) return $this;
        $this->_excel = array("url"=>$href);
        return $this;
    }

    public function powerExport2($href){
        if(!$this->export) return $this;
        $this->_excel2 = array("url"=>$href);
        return $this;
    }

    /*新增按钮*/
    public function newButton($href,$title="新增",$attr = array()){
        //缓存权限
        $attr['href'] = $href?$href:"JavaScript:void(0)";
        $attr['class'] = "am-btn am-btn-default am-btn-success";
        $attr['icon'] = "<span class='am-icon-plus'></span>";
        $attr['label'] = "a";
        return $this->button($title, $attr);
    }
    public function primaryButton($href,$title="新增",$attr = array()){
        //缓存权限
        $attr['href'] = $href?$href:"JavaScript:void(0)";
        $attr['class'] = "am-btn am-btn-default am-btn-primary";
        $attr['label'] = "a";
        return $this->button($title, $attr);
    }
    /*删除按钮*/
    public function deleteButton($href,$title="删除",$attr = array()){
        //缓存权限
        $attr['href'] = $href?$href:"JavaScript:void(0)";
        $attr['class'] = "am-btn am-btn-default am-btn-danger delete-ajax-post";
        $attr['icon'] = "<span class='am-icon-trash-o'></span>";
        $attr['label'] = "button";
        $attr['target-form'] = 'ids';
        return $this->button($title, $attr);
    }

    public function keyDoAction($getUrl, $text='编辑', $title = '操作')
    {
        //获取默认getUrl函数
        if (is_string($getUrl)) {
            $getUrl = $this->createDefaultGetUrlFunction($getUrl);
        }
        //确认已经创建了DOACTIONS字段
        $doActionKey = null;
        foreach ($this->_keyList as $key) {
            if ($key['name'] === 'DOACTIONS') {
                $doActionKey = $key;
                break;
            }
        }
        if (!$doActionKey) {
            $this->key('DOACTIONS', $title, 'doaction', array());
        }
        //找出第一个DOACTIONS字段
        $doActionKey = null;
        foreach ($this->_keyList as &$key) {
            if ($key['name'] == 'DOACTIONS') {
                $doActionKey = &$key;
                break;
            }
        }
        //在DOACTIONS中增加action
        $doActionKey['opt']['actions'][] = array('text' => $text, 'get_url' => $getUrl);
        return $this;
    }

    public function keyDoActionEdit($getUrl, $text = '编辑')
    {
        //缓存权限
        $href = explode("?",$getUrl);
        $href = CONTROLLER_NAME."/".$href[0];
        return $this->keyDoAction($getUrl, $text);
    }

    public function keyLink($name, $title, $getUrl)
    {
        //如果getUrl是一个字符串，则表示getUrl是一个U函数解析的字符串
        if (is_string($getUrl)) {
            $getUrl = $this->createDefaultGetUrlFunction($getUrl);
        }
        $href = explode("?",$getUrl);
        return $this->key($name, $title, 'link', $getUrl);
    }


    public function display($solist = ''){

        $setStatusUrl = $this->_setStatusUrl;


         $this->convertKey("Img",'html',function($value,$key,$item){
            $html = "<img style='max-width:100px;max-height:100px' src=\"" .$key['mcurl'] .$value ."\" class='tpl-table-line-img' />";
            return $html;
        });

        $this->convertKey('status', 'html', function ($value, $key, $item) use ($setStatusUrl,$that) {
            //如果没有设置修改状态的URL，则直接返回文字
            $map = $key['opt'];
            $text = $map[$value];
            if (!$setStatusUrl) {
                return $text;
            }
            //返回带链接的文字
            $switchStatus = $value == 1 ? 0 : 1;
            $url = $that->addUrlParam($setStatusUrl, array('status' => $switchStatus, 'ids' => $item['id']));
            return "<a href=\"{$url}\" class=\"ajax-get\">$text</a>";
        });
        //time转换成text
        $this->convertKey('time', 'text', function ($value) {
            if ($value != 0) {
                return time_format($value);
            } else {
                return '-';
            }
        });
         $this->convertKey('doaction', 'html', function ($value, $key, $item) {
            $actions = $key['opt']['actions'];
            $result = array();
            foreach ($actions as $action) {
                $getUrl = $action['get_url'];
                $linkText = $action['text'];
                $url = $getUrl($item);
                $result[] = "<a  href=\"$url\">$linkText</a>";
            }
            return implode(' ', $result);
        });

        $this->convertKey('link', 'html', function ($value, $key, $item) {
            $value = htmlspecialchars($value);
            $getUrl = $key['opt'];
            $url = $getUrl($item);
            //允许字段为空，如果字段名为空将标题名填充到A变现里
            return "<a href=\"$url\">$value</a>";
        });
        //显示页面
        $this->assign('title', $this->_title);
        $this->assign('query', $this->_query);
        $this->assign('queryselect', $this->_queryselect);
        $this->assign('excel', $this->_excel);
        $this->assign('excel2', $this->_excel2);
        $this->assign('startime', $this->_startime);
        $this->assign('endtime', $this->_endtime);
        $this->assign('buttonList',$this->_buttonList);
        $this->assign('keyList', $this->_keyList);
        $this->assign('list', $this->_data);
        $this->assign('select',$this->_select);
        $this->assign("grossincome",$this->_grossincome);
        $this->assign("otherdata",$this->_otherData);
        $this->assign("hidequery",$this->_hidequery);

        C('VAR_PAGE', 'page');
        $pager = new \Think\PageBack($this->_pagination['totalCount'], $this->_pagination['listRows'], $_REQUEST);
        $pager->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %HEADER%');
        $paginationHtml = $pager->show();
        $this->assign('pagination', $paginationHtml);
        if ($solist) {
            parent::display($solist);
        } else {
            parent::display('admin_list');
        }

    }

     private function convertKey($from, $to, $convertFunction)
    {
        foreach ($this->_keyList as &$key) {
            if ($key['type'] == $from) {
                $key['type'] = $to;
                foreach ($this->_data as &$data) {
                    $value = &$data[$key['name']];
                    $value = $convertFunction($value, $key, $data);
                    unset($value);
                }
                unset($data);
            }
        }
        unset($key);
    }

    private function createDefaultGetUrlFunction($pattern)
    {

        $explode = explode('|', $pattern);
        $pattern = $explode[0];
        $fun = empty($explode[1]) ? 'U' : $explode[1];
        return function ($item) use ($pattern, $fun) {
          //  $item['time']?$pattern=str_replace('{time}', $item['time'], $pattern):$item['time'];
            $pattern = str_replace('###', $item['id'], $pattern);
            $pattern = str_replace('n#', $item['nick_name'], $pattern);
            //调用ThinkPHP中的解析引擎解析变量
            $view = new \Think\View();
            $view->assign($item);
            $pattern = $view->fetch('', $pattern);
            return $fun($pattern);
        };
    }
    public function addUrlParam($url, $params)
    {
        if (strpos($url, '?') === false) {
            $seperator = '?';
        } else {
            $seperator = '&';
        }
        $params = http_build_query($params);
        return $url . $seperator . $params;
    }

}
?>
