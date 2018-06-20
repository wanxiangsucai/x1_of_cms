<?php
namespace app\common\field;

/**
 * 表单自定义字段
 */
class Form extends Base
{
    /**
     * 取字段的值, 对于 数组变量名比如 postdb[title] 要特别处理
     * @param string $name
     * @param array $info
     * @return unknown|string
     */
    public static function get_field_value($name='',$info=[]){
        if(strstr($name,'[')){
            $detail = explode('[',str_replace(']', '', $name));
            if(count($detail)==2){
                return $info[$detail[0]][$detail[1]];
            }elseif(count($detail)==3){
                return $info[$detail[0]][$detail[1]][$detail[3]];
            }else{
                return '不能太多项了!';
            }
        }else{
            return $info[$name];
        }
    }
    
    /**
     * 取得某个字段的表单HTML代码
     * @param array $field 具体某个字段的配置参数, 只能是数据库中的格式,不能是程序中定义的数字下标的格式
     * @param array $info 信息内容
     * @return string[]|unknown[]|mixed[]
     */
    public static function get_field($field=[],$info=[]){
        
        // 是否为必填选项
        if ($field['mustfill'] == '1') {
            $mustfill = '(<font color=red>*</font>)';
            $ifmust = " data-ifmust='1' ";
        }
        
        $_show = $show = '';
        $name = $field['name'];
        
        $info[$name] = self::get_field_value($name,$info);
        
        if(!isset($info[$name]) && $field['value']){
            $info[$name] = $field['value'];         //新发表 或 修改的时候,如果变量不存在,就使用字段的默认值
        }

//         if(empty($info)){   //新发表,就用初始值
//             $info[$name] = $field['value'];
//         }
        
        if ( ($show = self::get_item($field['type'],$field,$info)) !='' ) {    //个性定义的表单模板,优先级最高
            
        }elseif ($field['type'] == 'jcrop') {    // 截图
            
            $show = self::get_item('image',$field,$info);
            
        }elseif ($field['type'] == 'hidden') {    // 隐藏域
            
            $show = "<input type='hidden' name='{$name}' id='atc_{$name}' class='c_{$name}' value='{$info[$name]}' />";
            
        }elseif ($field['type'] == 'button') {    // 按钮
            
            $array = $field['title'];
            $show = "<a onclick=\"layer.open({type: 2,title:false,area: ['850px', '650px'],content:'{$array['href']}',})\" name='{$name}'  id='atc_{$name}' class='c_{$name} {$array['class']}'/><i class='{$array['icon']}'></i> {$array['title']}</a>";
            $field['title'] = '';
            $field['about'] = '';
            
        }elseif ($field['type'] == 'textarea') {    // 多行文本框
            
            $field['input_width'] && $field['input_width']="width:{$field['input_width']};";
            $field['input_height'] && $field['input_height']="width:{$field['input_height']};";
            $show = "<textarea $ifmust name='{$name}' id='atc_{$name}' placeholder='请输入{$field['title']}' class='layui-textarea c_{$name}  {$field['css']}' style='{$field['input_width']}{$field['input_height']}'>{$info[$name]}</textarea>";
            
        }elseif ($field['type'] == 'select') {      // 下拉框
            
            $detail = is_array($field['options']) ? $field['options'] : str_array($field['options']);
            $i = 0;
            foreach ($detail as $key => $value) {
                $cked = $info[$name]==$key?' selected ':'';
                $i++;
                if($i==1&&!empty($key)){
                    $_show .= "<option value=''>请选择...</option>";
                }
                $_show .= "<option value='$key' $cked>$value</option>";
            }            
            $show = "<select $ifmust name='{$name}' id='atc_{$name}'>$_show</select>";
        
        }elseif ($field['type'] == 'radio') {    // 单选按钮
            
            $detail = is_array($field['options']) ? $field['options'] : str_array($field['options']);
            foreach ($detail as $key => $value) {
                $cked = $info[$name]==$key?' checked ':'';
                $_show .= "<input $ifmust type='radio' name='{$name}' id='atc_{$name}{$key}' value='$key' {$cked} title='$value'><span class='m_title'> $value </span>";
            }
            $show = $_show ;
       
        }elseif ($field['type'] == 'checkbox') {    // 多选按钮
            
            $_detail = explode(',',$info[$name]);
            $detail = is_array($field['options']) ? $field['options'] : str_array($field['options']);
            foreach ($detail as $key => $value) {
                $cked = in_array($key, $_detail)?' checked ':'';
                $_show .= " <input $ifmust type='checkbox' name='{$name}[]'  id='atc_{$name}{$key}' value='$key' {$cked}  title='$value'><span class='m_title'> $value </span>";
            }            
            $show = "$_show "; 
            
        }elseif ($field['type'] == 'checkboxtree') {    // 树状多选按钮

            $detail = $field['value'];
            foreach ($detail as $key => $value) {
                $cked = in_array($key, $info[$name])?' checked ':'';
                $_show .= " <input $ifmust type='checkbox' name='{$name}[]' value='$key' {$cked}  title='$value'><span class='m_title'> $value </span><br>";
            }
            $show = "$_show "; 
            
            $field['about'] && $field['about'] = '<br>'.$field['about'];
            
        }elseif(in_array($field['type'], ['time','date','datetime'])){
            $field['input_width'] && $field['input_width']="width:{$field['input_width']};";
            $static = config('view_replace_str.__STATIC__');
            $show = " <input placeholder='点击选择{$field[title]}'  style='{$field['input_width']}' $ifmust  type='text' name='{$name}' id='atc_{$name}'  class='layui-input c_{$name} {$field['css']}' value='{$info[$name]}' />";
            $show .="
                            <script src='$static/layui/layui.js'></script>
                            <script>
                            layui.use('laydate', function(){
                              var laydate = layui.laydate;
                              laydate.render({
                                elem: '#atc_{$name}',
                                type: '{$field['type']}'
                              });
                            });
                            </script>";
        }else{      // 全部归为单行文本框
            
            $jsck = '';
            
            // 检验表单
            if ($field['match']) {
                $jsck = ' onBlur="if(this.value!=\'\'&&' . $field['match'] . '.test(this.value)==false){layer.alert(\'' . '你输入的内容不符合要求' . '\');this.focus();}"';
            }
            
            $readonly = $field['type'] == 'static' ? ' readonly ' : '';
            
            if( in_array($field['type'], ['number','money']) ){
                $type = 'number';
            }elseif($field['type'] == 'password'){
                $type = 'password';
            }else{
                $type = 'text';
            }
            $step = $field['type']=='money' ? " step='0.01' " : '';
            
            $field['input_width'] && $field['input_width']="width:{$field['input_width']};";
            $show = " <input $readonly placeholder='请输入{$field[title]}' $step $ifmust $jsck type='$type' name='{$name}' id='atc_{$name}' style='{$field['input_width']}' class='layui-input c_{$name} {$field['css']}' value='{$info[$name]}' />";

        }

        return [
                'value'=>$show,
                'title'=>$field['title'],
                'need'=>$mustfill,
                'about'=>$field['about'],
                'ifhide'=>$field['type'] == 'hidden' ? true : false,
        ];        
    }
    
    /**
     * 触发选项
     * @param array $trigger
     * @return void|string
     */
    public static function setTrigger($trigger=[]){
        if (empty($trigger)) {
            return ;
        }        
        foreach ($trigger as $rs) {
            $field_hide   .= $rs[2].',';
            $field_values .= $rs[1].',';
            $field_triggers[$rs[0]][] = "['{$rs[1]}', '{$rs[2]}']";
        }
        $show = '';
        foreach($field_triggers as $field=>$ar){
            $show .="'$field' : [".implode(',',$ar)."],";
        }
        $show ="'triggers' : { $show },";
        $show .= "\r\n'field_hide': '$field_hide',\r\n'field_values': '$field_values',";
        return $show;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
