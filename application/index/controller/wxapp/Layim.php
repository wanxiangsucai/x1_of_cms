<?php
namespace app\index\controller\wxapp;


use app\common\controller\IndexBase;
use app\common\model\Msg AS Model;
use app\common\model\Msguser AS MsguserModel;

//LayIM
class Layim extends IndexBase{
    
    /**
     * 获取某个人跟它人或者是圈子的会话记录
     * @param number $uid 正数用户UID,负数圈子ID
     * @param number $id 某条消息的ID
     * @param number $rows 取几条
     * @param number $maxid 消息中最新的那条记录ID
     * @return void|\think\response\Json|void|unknown|\think\response\Json
     */
    public function get_more_msg($uid=0,$id=0,$rows=20,$maxid=0){
        if (empty($this->user) && ($uid>=0 || $id>0)) {
            return $this->err_js("请先登录");
        }
        if ($uid==9999999) {
            $uid = 0;
        }
        $qun_user = $qun_info = '';
        if ($uid<0) {
            if(!modules_config('qun')){
                return $this->err_js('你没有安装圈子模块!');
            }
            if($maxid<1){   //首次加载
                $qun_info = \app\qun\model\Content::getInfoByid(abs($uid),true);
                if ($this->user) {
                    $qun_user = \app\qun\model\Member::where([
                        'aid'=>abs($uid),
                        'uid'=>$this->user['uid'],
                    ])->find();
                    $qun_user = $qun_user?getArray($qun_user):[];
                }
            }
            isset($qun_info['_viewlimit']) || $qun_info['_viewlimit'] = $qun_info['viewlimit'];
            if($qun_info['_viewlimit'] && empty($this->admin) && $qun_info['uid']!=$this->user['uid']){
                if (empty($qun_user)) {
                    return $this->err_js('你不是本圈子成员,无权查看聊天内容!');
                }elseif ($qun_user['type']==0){
                    return $this->err_js('你还没通过审核,无权查看聊天内容!');
                }
            }
        }
        
        $array = model::list_moremsg($this->user['uid'],$uid,$id,$rows,$maxid);

        if ($maxid<1) { //首次加载
            if ($this->user) {
                //更新最后的访问时间,也即把历史消息标为已读
                MsguserModel::where('uid',$this->user['uid'])->where('aid',$uid)->update(['list'=>time()]);
                if($uid>=0){ //干脆点,把第二页第三页未读的也一起标注为已读了
                    model::where('touid',$this->user['uid'])->where('uid',$uid)->where('ifread',0)->update(['ifread'=>1]);
                }
            }
        }
        
        return $this->ok_js($array);
    }
    
    /**
     * 获取消息用户列表
     * @param number $rows
     * @param number $page
     * @return void|\think\response\Json|void|unknown|\think\response\Json
     */
    public function msg_user_list($rows=100,$page=1){
        if (empty($this->user)) {
            return $this->err_js('你还没登录');
        }
        $uid = $this->user['uid'];
        $listdb = Model::get_listuser($uid,$rows,$page,600);
        
        $array = [
            'userinfo'=>fun('member@format',$this->user,$this->user['uid']),
            'mine'=>[
                'username'=>$this->user['username'],
                'id'=>$this->user['uid'],
                'status'=>'online',
                'sign'=>get_word($this->user['introduce'], 30),
                'avatar'=>tempdir($this->user['icon'])?:STATIC_URL.'images/noface.png',
            ],
            'friend'=>[
                [
                    'groupname'=>'客服列表',
                    'id'=>1,
                    'online'=>0,
                ],
                [
                    'groupname'=>'好友列表',
                    'id'=>2,
                    'online'=>2,
                ],                
            ],
        ];
        foreach($listdb AS $rs){
            if (empty($rs['f_icon'])) {
                $rs['f_icon']='/public/static/images/noface.png';
            }
            if ($rs['qun_id']>0) {
                $array['group'][] = [
                    'groupname'=>$rs['f_name'],
                    'id'=>$rs['f_uid'],
                    'avatar'=>$rs['f_icon'],
                    'status'=>$rs['f_uid']%2==0?'offline':'online',
                    'new_num'=>$rs['new_num'],
                    'num'=>$rs['num'],
                ];
            }else{
                $array['friend'][1]['list'][] = [
                    'username'=>$rs['f_name'],
                    'id'=>$rs['f_uid']?:9999999,
                    'status'=>$rs['f_uid']%2==0?'offline':'online',
                    'sign'=>get_word($rs['title'], 30),
                    'avatar'=>$rs['f_icon'],
                    'new_num'=>$rs['new_num'],
                    'num'=>$rs['num'],
                ];
            }
        }
        return $this->ok_js($array);
    }
}