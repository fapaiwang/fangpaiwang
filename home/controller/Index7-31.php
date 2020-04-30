<?php
namespace app\home\controller;
class Index extends \app\common\controller\HomeBase
{
    public function index()
    {
        $city_id  = $this->cityInfo['id'];
        $city     = getCity();
        if($city_id && isset($city[$city_id]) && isset($city[$city_id]['_child']))
        {
            $city  = $city[$city_id]['_child'];
        }
        //优惠，人气，热销楼盘
        $house = [$this->getPositionHouse(4,4),$this->getPositionHouse(3,4),$this->getPositionHouse(1,4)];


        $full_screen = db('module')->field('id')->where('type',1)->where('status',1)->where('terminal',1)->order('ordid asc')->select();
        $fix_module  = db('module')->field('id')->where('type',2)->where('status',1)->where('terminal',1)->order('ordid asc')->select();
        $this->assign('full_module',$full_screen);
        $this->assign('fix_module',$fix_module);

        $this->assign('area',$city);
        $this->assign('search_city',$city);
        $this->assign('group',$this->getNewGroup());
        $this->assign('rec_house',$house);
        $this->assign('discount_house',$this->getDiscountHouse());
        $this->assign('hot_house',$this->getHotHouse());
        $this->assign('recommon',$this->getPositionHouse(2,3));//热盘推荐
        $this->assign('neareast_house',$this->getNearestOpenedHouse());//近期开盘
        $this->assign('house_news',$this->getNewsByCateId(0,14));//楼盘资讯
        $this->assign('house_guide',$this->getSpecailHouse());//楼盘导购
        $this->assign('news_cate_2',$this->getNewsByCateId(2));
        $this->assign('news_cate_3',$this->getNewsByCateId(3));
        $this->assign('second_house',$this->getSecondHouse());//二手房
        $this->assign('rental_house',$this->getRentalHouse());//出租房
        $this->assign('estate',$this->getEstate());
        $this->assign('ups_downs_house',$this->getUpsAndDownsHouse());//新盘涨幅
        $this->assign('ups_downs_second_house',$this->getUpsAndDownsSecondHouse());//二手房涨幅
        $this->assign('news_cate_5',$this->getNewsByCateId(5,8));//优惠活动
        $this->assign('special',getLinkMenuCache(3));//特色
        $this->assign('house_type',getLinkMenuCache(2));//新房类型
        $this->assign('rental_type',getLinkMenuCache(9));//出租房类型
        $this->assign('broker',$this->getBroker());//经纪人
        $this->assign('new_subscribe',$this->getNewSubcribe());//最新预约
        $this->assign('time',time());

// zp


            $objs   = model('second_house');
            $times=time();
            $zzcount = $objs->where('fcstatus',169)->where('status',1)->where('timeout','lt',$times)->count();
            // $zzcount = model('second_house')->where('fcstatus',169)->count();
            $jjcount = $objs->where('fcstatus',170)->where('status',1)->count();
            // $jjcount = $objs->where('fcstatus',170)->where('status',1)->count();
        
// print_r($zzcount);exit();
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $jrcount = $objs->where('create_time','between',[$beginToday,$endToday])->where('status',1)->count();
            $this->assign('zzcount',$zzcount);
            $this->assign('jjcount',$jjcount);
            $this->assign('jrcount',$jrcount);

// zpend



        return $this->fetch('index/index');
    }

    /**
     * @return array
     * 特色标签楼盘
     */
    private function getSpecailHouse()
    {
        $tags = getLinkMenuCache(3);
        $data = [];
        if($tags)
        {
            foreach($tags as $v)
            {
                $data[]  = $this->getHouseBySpecialId($v['id']);
            }
        }
        return $data;
    }
    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 优惠楼盘
     */
    private function getDiscountHouse($num = 6)
    {
        $where['status'] = 1;
        $where['is_discount'] = 1;
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field = 'id,title,img,price,unit,city,discount';
        $lists = model('house')->where($where)->field($field)->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();
        return $lists;
    }

    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 人气楼盘
     */
    private function getHotHouse($num = 6)
    {
        $where['status'] = 1;
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field = 'id,title,img,price,unit,city';
        $lists = model('house')->where($where)->field($field)->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();
        return $lists;
    }

    /**
     * @param $pos_id @推荐位id
     * @param int $num @读取数量
     * @return array|\PDOStatement|string|\think\Collection
     * 获取推荐位楼盘
     */
    private function getPositionHouse($pos_id,$num = 6)
    {
        $service = controller('common/Position','service');
        $service->field = 'h.id,h.img,h.title,h.price,h.unit,h.city';
        $service->city  = $this->getCityChild();
        $service->cate_id = $pos_id;
        $service->num     = $num;
        $lists = $service->lists();
        if($lists)
        {
            foreach($lists as &$v)
            {
                $v['unit'] = $v['price'] > 0 ? getUnitData($v['unit']) : '';
            }
        }
        return $lists;
    }

    /**
     * @param $special_id
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 获取指定特色标签id楼盘
     */
    private function getHouseBySpecialId($special_id,$num = 6)
    {
       // $where[] = ['exp',"find_in_set({$special_id},tags_id)"];
        $where['status'] = 1;
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field = 'id,title,img,price,unit,city';
        $lists = model('house')->where("find_in_set({$special_id},tags_id)")->where($where)->field($field)->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();
        return $lists;
    }

    /**
     * @return array
     * 近期开盘
     */
    private function getNearestOpenedHouse()
    {
        $nearest_time = time()-60*86400;//两个月内的开盘时间
        $where['status'] = 1;
        $where[] = ['opening_time','gt',$nearest_time];
        //$where['status'] = 1;
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $order = ['opening_time'=>'desc','id'=>'desc'];
        $field = 'id,title,price,unit,FROM_UNIXTIME(opening_time,"%m月%d日") as opening_time';
        $lists = model('house')->where($where)->field($field)->order($order)->select();
        $data = [];
        if($lists)
        {
            foreach($lists as $v)
            {
                $data[$v['opening_time']][] = $v;
            }
        }
        return $data;
    }

    /**
     * @param $cate_id
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 根据分类id获取新闻列表
     */
    private function getNewsByCateId($cate_id,$num = 5)
    {
        $where['status'] = 1;
        $cate_id && $where['cate_id'] = $cate_id;
        $city = $this->cityInfo['id'];
        $city && $where[] = ['city','eq',$city];
        $field = 'id,title';
        $order = ['ordid'=>'asc','id'=>'desc'];
        $lists = model('article')->where($where)->field($field)->order($order)->limit($num)->select();
        return $lists;
    }

    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 二手房
     */
    private function getSecondHouse($num = 6)
    {
        $where['status'] = 1;
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field = 'id,title,estate_name,img,average_price,city,room,fcstatus,living_room,acreage';
        $order = ['ordid'=>'asc','id'=>'desc'];
        $lists = model('second_house')->where($where)->field($field)->order($order)->limit($num)->select();
        return $lists;
    }

    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 出租房
     */
    private function getRentalHouse($num = 6)
    {
        $where['status'] = 1;
        $city = $this->getCityChild();
        $city && $where[] = ['city','in',$city];
        $field = 'id,title,estate_name,img,price,city,room,living_room,acreage';
        $order = ['ordid'=>'asc','id'=>'desc'];
        $lists = model('rental')->where($where)->field($field)->order($order)->limit($num)->select();
        return $lists;
    }

    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 小区
     */
    private function getEstate($num = 6)
    {
        $where['status'] = 1;
        $city = $this->getCityChild();
        $city && $where[] = ['city','in',$city];
        $field = 'id,title,img,price,city';
        $order = ['ordid'=>'asc','id'=>'desc'];
        $lists = model('estate')->where($where)->field($field)->order($order)->limit($num)->select();
        return $lists;
    }

    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 新盘涨幅
     */
    private function getUpsAndDownsHouse($num = 8)
    {
        $where['status'] = 1;
        $where[] = ['ratio','<>',0];
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field = 'id,title,ratio,price,unit';
        $order = ['ordid'=>'asc','id'=>'desc'];
        $lists = model('house')->where($where)->field($field)->order($order)->limit($num)->select();
        return $lists;
    }

    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 二手房涨幅
     */
    private function getUpsAndDownsSecondHouse($num = 8)
    {
        $where['status'] = 1;
        $where[] = ['ratio','<>',0];
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field = 'id,title,ratio,average_price';
        $order = ['ordid'=>'asc','id'=>'desc'];
        $lists = model('second_house')->where($where)->field($field)->order($order)->limit($num)->select();
        return $lists;
    }

    /**
     * @return array|\PDOStatement|string|\think\Collection
     * 推荐经纪人
     */
    private function getBroker()
    {
        $where['u.status']   = 1;
        $where['u.recommon'] = 1;
        $city    = $this->getCityChild();
        $orWhere = '';
        if($city)
        {
            foreach($city as $v)
            {
                $orWhere .= " or find_in_set({$v},d.service_area)";
            }
            $where[] = ['','exp',\think\Db::raw(trim($orWhere,' or '))];
        }
        $join   = [['user_info d','u.id=d.user_id'],'left'];
        $lists = model('user')->alias('u')->join($join)->where($where)->field('u.id,u.nick_name')->limit(4)->order('u.id','desc')->select();
        return $lists;
    }

    /**
     * @return array|\PDOStatement|string|\think\Collection
     * 最新预约
     */
    private function getNewSubcribe()
    {
        $lists = model('subscribe')->field('mobile,create_time')->order('create_time','desc')->limit(10)->select();
        return $lists;
    }

    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 最新团购
     */
    private function getNewGroup($num = 4)
    {
        $time            = time();
        $where['status'] = 1;
        $where[]         = ['begin_time','lt',$time];
        $where[]         = ['end_time','gt',$time];
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $lists = model('group')->where($where)->field('id,title,price,end_time,discount,img')->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();
        return $lists;
    }
}
