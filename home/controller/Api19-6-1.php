<?php



namespace app\home\controller;

class Api

{

    /**

     * @return \think\response\Json

     * 二手房价格走势

     */

    public function getSecondPrice()

    {

        $result = $this->getAverageByMonth('second_house');

        return json($result);

    }



    /**

     * @return \think\response\Json

     * 新房价格走势

     */

    public function getHousePrice()

    {

        $result = $this->getAverageByMonth();

        return json($result);

    }



    /**

     * @return \think\response\Json

     * 获取楼栋详细及户型

     */

    public function getBanInfoById()

    {

        $id             = input('get.id/d',0);

        $return['code'] = 0;

        if($id)

        {

            $where['id']        = $id;

            $where['status']    = 1;

            $info               = model('house_sand')->where($where)->find();

            $info['sale_status_name'] = getLinkMenuName(5,$info['sale_status']);

            $data['ban_info']   = $info;

            $data['type_lists'] = [];

            if($info && isset($info['house_type_id']))

            {

                //$info['open_time'] = date('Y-m-d');

                //$info['complate_time'] = date('Y-m-d');

                $type_lists = model('house_type')->where('status',1)->where('id','in',$info['house_type_id'])->field('id,title,room,img,orientation,living_room,acreage,price,sale_status')->select();

                if($type_lists)

                {

                    foreach($type_lists as &$v)

                    {

                        $v['url'] = url('House/roomDetail',['id'=>$v['id']]);

                        $v['sale_status'] = getLinkMenuName(5,$v['sale_status']);

                        $v['orientation'] = getLinkMenuName(4,$v['orientation']);

                    }

                }

                $data['type_lists'] = $type_lists;

            }

            $return['code'] = 1;

            $return['data'] = $data;

        }

        return json($return);

    }



    /**

     * @return \think\response\Json

     * 提交 问题

     */

    public function subQuestion()

    {

        $id      = input('post.house_id/d',0);

        $content = input('post.content');

        $token   = input('post.token');

        $ip      = request()->ip();

        $city    = \util\Ip::find($ip);

        $return['code'] = 0;

        if(request()->isAjax())

        {

            if(!$id)

            {

                $return['msg'] = '参数错误！';

            }elseif($token != session('__token__')){

                $return['msg'] = '操作失败';

                \think\facade\Log::write('提交问题异常操作'.'异常ip:'.request()->ip(),'error');

            }else{

                $data['house_id'] = $id;

                $data['content']  = $content;

                $data['ip']       = $ip;

                $data['city']     = $city[1].$city[2];//用户所在城市

                $data['create_time'] = time();

                $data['status'] = getSettingCache('user','check_question');

                $data['city_id'] = $this->getCityIdByHouse($id);//楼盘所属城市id

                if($userInfo = $this->getUserInfo())

                {

                    $data['user_id'] = $userInfo['id'];

                    $data['user_name'] = $userInfo['nick_name'];

                }

                if(db('question')->insert($data))

                {

                    session('__token__',null);

                    $return['code'] = 1;

                    $return['msg']  = '提交成功！';

                }else{

                    $return['msg']   = '保存失败！';

                }

            }

        }else{

            $return['msg']  = '提交成功！';

        }

        return json($return);

    }



    /**

     * @return \think\response\Json

     * 回答问题

     */

    public function answer()

    {

        $question_id = input('post.question_id/d',0);

        $content     = input('post.content');

        $token       = input('post.token');

        $return['code'] = 0;

        if(request()->isAjax())

        {

            if(!$this->getUserInfo())

            {

                $return['msg'] = '请登录后再回答问题！';

            }elseif($token != session('__token__')){

                $return['msg'] = '操作失败';

            }else{

                $userInfo = $this->getUserInfo();

                $data['broker_id'] = $userInfo['id'];

                $data['broker_name'] = $userInfo['nick_name'];

                $data['question_id'] = $question_id;

                $data['mobile']      = $userInfo['mobile'];

                $data['content']     = $content;

                $data['create_time'] = time();

                $data['status']      = 1;

                if(db('answer')->insert($data))

                {

                    db('question')->where('id',$question_id)->setInc('reply_num');

                    session('__token__',null);

                    $return['code'] = 1;

                    $return['msg']  = '提交成功';

                }else{

                    $return['msg'] = '提交失败';

                }

            }

        }else{

            $return['msg']  = '提交成功';

        }

        return json($return);

    }



    /**

     * 评论经纪人

     */

    public function sendComment()

    {

        $point   = input('post.point/d',5);

        $content = input('post.content');

        $broker_id      = input('post.broker_id/d',0);

        $tags_id        = input('post.tags_id');

        $token          = input('post.token');

        $return['code'] = 0;

        $userInfo = $this->getUserInfo();

        if(request()->isAjax())

        {

            if(!$userInfo)

            {

                $return['msg'] = '请登录后再提交评论';

            }elseif(!$broker_id){

                $return['msg'] = '参数错误';

            }elseif($token!=session('__token__')){

                $return['msg'] = '操作失败';

            }else{

                $data['user_id']   = $userInfo['id'];

                $data['user_name'] = $userInfo['nick_name'];

                $data['broker_id'] = $broker_id;

                $data['content']   = $content;

                $data['point']     = $point;

                $data['tags']      = $tags_id;

                $data['create_time'] = time();

                $data['status']      = getSettingCache('user','check_comment');

                if(db('user_comment')->insert($data))

                {

                    //计算平均分

                    $count = db('user_comment')->field("sum(point) as total_point,count(id) as total")->where(['broker_id'=>$broker_id,'status'=>1])->find();

                    $average_point = $count['total'] > 0 ? ceil($count['total_point'] / $count['total']) : $point;



                    $obj  = model('user_info');

                    $info = $obj::get(['user_id'=>$broker_id]);

                    $tags = $this->updateUserTags($info['tags'],$tags_id);

                    $info->point = $average_point;

                    $info->tags  = $tags;

                    $info->save();

                    session('__token__',null);

                    //model('user_info')->save(['point'=>$average_point],['user_id'=>$broker_id]);

                    $return['code'] = 1;

                    $return['msg'] = '评论成功！';

                }else{

                    $return['msg'] = '评论失败！';

                }

            }

        }else{

            $return['msg'] = '评论成功！';

        }

        return json($return);

    }



    /**

     * @return \think\response\Json

     * 关注楼盘

     */

    public function follow()

    {

        $house_id = input('post.house_id/d',0);

        $model    = input('post.model');

        $userInfo = $this->getUserInfo();

        $return['code'] = 0;

        if(!$userInfo)

        {

            $return['msg'] = '请登录后再关注';

        }elseif(!$house_id){

            $return['msg'] = '参数错误';

        }else{

            $data['house_id'] = $house_id;

            $data['model']    = $model;

            $data['user_id']  = $userInfo['id'];

            $data['create_time'] = time();
// print_r($data);exit();
            if($this->cancelFollow($data))

            {

                $return['code'] = 1;

                $return['msg']  = '取消关注成功';

                $return['text'] = '关注房源';

            }else{

                if(db('follow')->insert($data))

                {

                    $return['code'] = 1;

                    $return['msg']  = '关注成功';

                    $return['text'] = '已关注';

                }else{

                    $return['msg']  = '关注失败';

                }

            }

        }

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 判断是否关注过指定房源

     */

    public function isFollow()

    {

        $id    = input('get.house_id/d',0);

        $model = input('get.model');

        $return['code'] = 0;

        $userInfo       = $this->getUserInfo();

        if($userInfo)

        {

            $where['user_id']  = $userInfo['id'];

            $where['model']    = $model;

            $where['house_id'] = $id;

            $info = db('follow')->where($where)->find();

            $info && $return['code'] = 1;

        }

        return json($return);

    }



    /**

     * @return \think\response\Json

     * 取消关注

     */

    public function userCancelFollow()

    {

        $house_id = input('get.house_id/d',0);

        $model    = input('get.model');

        $userInfo = $this->getUserInfo();

        $return['code']    = 0;

        $where['house_id'] = $house_id;

        $where['model']    = $model;

        $where['user_id']  = $userInfo['id'];

        if(model('follow')->where($where)->delete())

        {

            $return['code'] = 1;

            $return['msg']  = '取消成功';

        }else{

            $return['msg']  = '取消失败';

        }

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 预约看房

     */

    public function subscribe()

    {

        $data['house_id']  = input('post.house_id/d',0);

        // $data['user_name'] = input('post.user_name');

        $data['model']     = input('post.model');

        // $data['mobile']    = input('post.mobile');

        $data['type']      = input('post.type/d',1);

        $data['house_name']= input('post.house_name');

        // $data['check_sms'] = input('post.check_sms','yes');

        $data['broker_id'] = input('post.broker_id/d',0);

        // $sms_code          = input('post.sms_code');//短信验证码

        $token             = input('post.__token__');

        $userInfo          = $this->getUserInfo();

        $userInfo && $data['user_id'] = $userInfo['id'];
        $data['user_name']=$userInfo['user_name'];
        $data['mobile']=$userInfo['mobile'];
        $setting        = getSettingCache('user');

        $return['code'] = 0;

        if(request()->isAjax())

        {


        if(!$userInfo)

        {

            $return['msg'] = '请登录后再关注';

        }

            // if($setting['subscribe_sms'] == 1 && $data['check_sms'] == 'yes' && $sms_code != cache($data['mobile']))

            // {

            //     $return['msg'] = '短信验证码不正确！';

            // }elseif($token && session('__token__')!== $token){

            //     $return['msg'] = '操作失败';

            // }else{
            

                else if(model('subscribe')->allowField(true)->save($data))

                {

                    if($data['type'] == 4)

                    {

                        model('group')->where('house_id',$data['house_id'])->where('status',1)->setInc('sign_num');

                    }

                    session('__token__',null);

                    action('home/Sms/sendNoticeSms',['data'=>$data]);

                    $return['code'] = 1;

                    $return['msg']  = '提交成功';

                }else{

                    $return['msg']  = '保存失败';

                }

            // }

        }else{

            $return['msg']  = '提交成功';

        }

        return json($return);

    }



    /**

     * @return \think\response\Json

     * 评论表态

     */

    public function attitude()

    {

        $id     = input('get.id/d',0);

        $type   = input('get.type/d',1);//1赞 2踩

        $status = input('get.status/d',1);//1赞 0取消

        $ip     = request()->ip(1);

        $return['code'] = 0;

        $cache_name     = $ip.'_'.$id.'_'.$type;

        $result         = cache($cache_name);

        $time           = strtotime(date('Y-m-d 23:59:59')) - time();

        if($status == 1 && $result)

        {

            $return['msg'] = '您已经表过态了';

        }else{

            if($type == 1)

            {

                $field = 'good';

            }elseif($type == 2){

                $field = 'bad';

            }else{

                $field = 'good';

            }

            $where['id'] = $id;

            $obj = model('user_comment');

            $obj = $obj->where($where);

            if($status == 1)

            {

                cache($cache_name,$time);//缓存过期时间当天0点

                $obj->setInc($field);

            }else{

                cache($cache_name,null);//取消 删除缓存

                $obj->setDec($field);

            }

            $return['code'] = 1;

            $return['msg']  = '表态成功';

        }

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 提交 评论

     */

    public function subHouseComment()

    {

        $userInfo = $this->getUserInfo();

        $id      = input('post.house_id/d',0);

        $content = input('post.content');

        $token   = input('post.token');

        $verify  = input('post.verify');

        $score   = input('post.score');

        $ip      = request()->ip();

        $city    = \util\Ip::find($ip);

        $score   = json_decode($score,true);//各项分数

        $return['code'] = 0;

        if(request()->isAjax())

        {

            if(!$id)

            {

                $return['msg'] = '参数错误！';

            }elseif(!$userInfo){

                $return['msg'] = '请登录后再提交！';

            }elseif($token != session('__token__')){

                $return['msg'] = '操作太频繁';

                \think\facade\Log::write('提交楼盘点评异常操作'.'异常ip:'.request()->ip(),'error');

            }elseif(!captcha_check($verify)){

                $return['msg'] = '验证码不正确';

            }else{

                try{

                    $avg    = number_format(array_sum($score) / 5,1);

                    $config = getSettingCache('user');

                    $data['score']         = $score;

                    $data['average_score'] = $avg;

                    $data['house_id'] = $id;

                    $data['content']  = $content;

                    $data['ip']       = $ip;

                    $data['city']     = $city[1].$city[2];//用户所在城市

                    $data['create_time'] = time();

                    $data['status']      = isset($config['check_house_comment']) ? $config['check_house_comment'] : 1;

                    $data['user_id']     = $userInfo['id'];

                    $data['user_name']   = $userInfo['nick_name'];

                    $obj = model('comment');

                    if($obj->save($data))

                    {

                        //统计该楼盘所有用户平均分

                        $field = "count(id) as total,sum(average_score) as total_score";

                        $info  = $obj->field($field)->where('house_id',$id)->where('pid',0)->find();

                        $average_score = number_format($info['total_score'] / $info['total'],1);

                        model('house')->save(['score'=>$average_score],['id'=>$id]);

                        session('__token__',null);

                        $return['code'] = 1;

                        $return['msg']  = '提交成功！';

                    }else{

                        $return['msg']  = '保存失败！';

                    }

                }catch(\Exception $e){

                    \think\facade\Log::write('提交楼盘点评出错：'.$e->getMessage(),'error');

                    $return['code'] = 0;

                    $return['msg']  = $e->getMessage();

                }

            }

        }else{

            $return['msg']  = '提交成功！';

        }

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 点评回复

     */

    public function subHouseCommentReply()

    {

        $userInfo = $this->getUserInfo();

        $id      = input('post.house_id/d',0);

        $content = input('post.content');

        $verify  = input('post.verify');

        $pid     = input('post.pid/d',0);

        $ip      = request()->ip();

        $city    = \util\Ip::find($ip);

        $return['code'] = 0;

        if(request()->isAjax())

        {

            if(!$id)

            {

                $return['msg'] = '参数错误！';

            }elseif(!$userInfo){

                $return['msg'] = '请登录后再提交！';

            }elseif(!captcha_check($verify)){

                $return['msg'] = '验证码不正确';

            }else{

                $config = getSettingCache('user');

                $data['pid']      = $pid;

                $data['house_id'] = $id;

                $data['content']  = $content;

                $data['ip']       = $ip;

                $data['city']     = $city[1].$city[2];//用户所在城市

                $data['create_time'] = time();

                $data['status']  = isset($config['check_house_comment']) ? $config['check_house_comment'] : 1;

                $data['user_id'] = $userInfo['id'];

                $data['user_name'] = $userInfo['nick_name'];

                $obj = model('comment');

                if($obj->save($data))

                {

                    $return['code'] = 1;

                    $return['msg']  = '提交成功！';

                    $return['data'] = $this->getReply($pid);

                }else{

                    $return['msg']  = '保存失败！';

                }

            }

        }else{

            $return['msg']  = '提交成功！';

        }

        return json($return);

    }

    //点评支持

    public function support()

    {

        $id = input('get.id/d',0);

        $return['code'] = 0;

        if($id){

            $ip     = request()->ip(1);

            $cache_name     = $ip.'_'.$id;

            $result         = cache($cache_name);

            $time           = strtotime(date('Y-m-d 23:59:59')) - time();

            if($result)

            {

                $return['msg'] = '您已经支持过了';

            }else{

                cache($cache_name,$time);

                $data['support'] = \think\Db::raw('support+1');

                model('comment')->save($data,['id'=>$id]);

                $return['code'] = 1;

                $return['msg'] = '感谢您的参与';

            }

        }else{

            $return['msg'] = '参数错误';

        }

        return json($return);

    }

    //点评回复

    private function getReply($pid)

    {

        $where['pid']    = $pid;

        $where['status'] = 1;

        $lists = model('comment')->where($where)->field('id,pid,user_id,user_name,content,create_time,support')->order('create_time desc')->limit(15)->select();

        if(!$lists->isEmpty())

        {

            foreach($lists as &$v)

            {

                $v['avatar'] = getAvatar($v['user_id'],45);

                $v['create_time'] = date("Y-m-d H:i:s");

            }

        }

        return $lists;

    }

    /**

     * 设置置顶

     */

    public function setTop()

    {

        $return['code'] = 0;

        if(request()->isAjax())

        {

            $userInfo = $this->getUserInfo();

            if(!$userInfo)

            {

                $return['msg'] = '请登录';

            }else{

                $model    = input('post.model');

                $day      = input('post.t/d',1);

                $house_id = input('post.house_id/d',0);

                \think\Db::startTrans();

                try{

                    $user_cate = $userInfo['model'];

                    $setting = getUserCate();

                    $setting = $setting[$user_cate];//用户设置

                    $top_money = $setting['fee_top'];//置顶每条每天的费用

                    $price = $top_money * $day;//置顶所需费用



                    $obj      = model($model);

                    $top_time = strtotime("+".$day." days");

                    $where['id'] = $house_id;

                    $where['broker_id'] = $userInfo['id'];



                    $count = $obj->where($where)->where('top_time','gt',time())->count();

                    if($count > 0)

                    {

                        $return['msg'] = "该房源已置顶！";

                    }else{

                        if($top_money > 0)

                        {

                            $record['price'] = $price;

                            $record['memo']  = "置顶扣除费用：".$price;

                            $result = \app\common\service\Account::optionMoney($userInfo['id'],$record,-1);

                            if($result['code'] == 1)

                            {

                                $obj->where($where)->setField('top_time',$top_time);

                                $return['code'] = 1;

                                $return['msg']  = '置顶成功';

                            }else{

                                $return = $result;

                            }

                        }else{

                            $obj->where($where)->setField('top_time',$top_time);

                            $return['code'] = 1;

                            $return['msg']  = '置顶成功';

                        }

                    }

                    \think\Db::commit();

                }catch (\Exception $e){

                    \think\facade\Log::write('设置置顶出错：'.$e->getFile().$e->getLine().$e->getMessage());

                    $return['msg'] = $e->getMessage();

                    \think\Db::rollback();

                }

            }

        }

        return json($return);

    }

    /**

     * @param $data

     * @return array|false|\PDOStatement|string|\think\Model

     * 取消关注

     */

    private function cancelFollow($data)

    {

        $where['user_id']  = $data['user_id'];

        $where['house_id'] = $data['house_id'];

        $where['model']    = $data['model'];

        $obj  = db('follow');

        $info = $obj->where($where)->find();

        if($info)

        {

            //取消关注

            $obj->where($where)->delete();

        }

        return $info;

    }

    /**

     * @param $old_tag

     * @param $new_tag

     * @return string

     * 更新经纪人印象标签

     */

    private function updateUserTags($old_tag,$new_tag)

    {

        $new_tag = array_filter(explode(',',$new_tag));

        $old_tag .= ',';

        $add = [];

        foreach($new_tag as $v)

        {

            if(strpos($old_tag,$v.',')=== FALSE)

            {

                $add[] = $v;

            }

        }

        $tag_str = implode(',',$add);

        $old_tag .= $tag_str;

        $old_tag  = trim($old_tag,',');

        return $old_tag;

    }

    /**

     * @param string $model

     * @return mixed

     * 获取月均价

     */

    private function getAverageByMonth($model = 'house')

    {

        $where['model'] = $model;

        $where[] = ['create_time','gt',strtotime('-1 year')];

        $field = "max(price) as price,FROM_UNIXTIME(create_time,'%Y-%m') as monthes";

        $lists = db('house_price')->where($where)->field($field)->group('monthes')->select();

        $return['code'] = 0;

        if($lists)

        {

            $month = [];

            $data  = [];

            $return['code'] = 1;

            foreach($lists as $v)

            {

                $month[] = $v['monthes'];

                $data[]  = $v['price'];

            }

            $return['month'] = $month;

            $return['data']  = $data;

        }

        return $return;

    }



    /**

     * @return mixed|string

     * 获取用户信息

     */

    private function getUserInfo()

    {

        $info = cookie('userInfo');

        $info = \org\Crypt::decrypt($info);

        return $info;

    }



    /**

     * @param $house_id

     * @param string $model

     * @return mixed

     * 获取指定楼盘所在城市id

     */

    private function getCityIdByHouse($house_id,$model = 'house')

    {

        $city_id = model($model)->where('id',$house_id)->value('city');

        return $city_id;

    }

}