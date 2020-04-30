<?php




namespace app\home\controller;

use app\common\controller\HomeBase;

use app\common\service\Metro;

class Second extends HomeBase

{

    /**

     * @return mixed

     * 二手房列表

     */

    public function index()

    {

        $result = $this->getLists();

        $lists  = $result['lists'];
        // foreach ($lists as $key => $value) {
        //     print_r($value);exit();
        //     $lists['city']=getCityName($value['city']);
        // }
        $this->assign('metro',Metro::index($this->cityInfo['id']));//地铁线

        $this->assign('house_type',getLinkMenuCache(9));//类型

        $this->assign('orientations',getLinkMenuCache(4));//朝向
        $this->assign('floor',getLinkMenuCache(7));//朝向
        $this->assign('types',getLinkMenuCache(26));//类型s
        $this->assign('jieduan',getLinkMenuCache(25));//类型s
        $this->assign('fcstatus',getLinkMenuCache(27));//类型s
        $this->assign('renovation',getLinkMenuCache(8));//装修情况

        $this->assign('tags',getLinkMenuCache(14));//标签

        $this->assign('area',$this->getAreaByCityId());

        $this->assign('position',$this->getPositionHouse(5,4));

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        $this->assign('top_lists',$result['top']);

        $this->assign('storage_open',getSettingCache('storage','open'));
        return $this->fetch();

    }



    /**

     * @return mixed

     * 房源详情

     */

    public function detail()

    {

        $id = input('param.id/d',0);
        $wg = model('second_house')->where('id',$id)->find();
        $wgs=$wg['weiguan']+1;
        model('second_house')->where(['id'=>$id])->update(['weiguan'=>$wgs]);
        if($id)

        {

            $where['h.id']     = $id;

            $where['h.status'] = 1;

            $obj  = model('second_house');

            $join = [['second_house_data d','h.id=d.house_id']];

            $info = $obj->alias('h')->join($join)->where($where)->find();
			
			
			
			//$where1['id']     = $id;
//
//            $where1['status'] = 1;
//			$price=$obj->where($where1)->field('marketprice')->select();
//			
//				
//			//print_r($price[0]['marketprice']);
////				
//			$marketprice=$price[0]['marketprice'];
//			$qipaiprice=$info['qipai'];
//			
//			$jlzs=$marketprice/$qipaiprice;
//			
//			//print_r($jlzs);
//			
//			
//			if(($jlzs>='1.1') && ($jlzs<='2')){
//			
//				$jlzss='1';
//			
//			}
//			if(($jlzs>='1.3') && ($jlzs<='1.4')){
//			
//				$jlzss='2';
//			
//			}
//			if(($jlzs>='1.5') && ($jlzs<='1.6')){
//			
//				$jlzss='3';
//			
//			}
//			if(($jlzs>='1.7') && ($jlzs<='1.8')){
//			
//				$jlzss='4';
//			
//			}
//
//			if($jlzs>'1.8'){
//			
//				$jlzss='5';
//			
//			}
//
//			// echo $jlzs;
//
//			// echo $jlzss;
//			  $this->assign('jlzss',$jlzss);


			//print_r($price);

				
			//print_r($info['price']);
				
			
			
			

            if($info)

            {

                $info['file'] = json_decode($info['file'],true);

                $this->setSeo($info);

                updateHits($info['id'],'second_house');

                $estate = model('estate')->where('id',$info['estate_id'])->find();

                $info['total']        = $obj->where('estate_id',$info['estate_id'])->where('status',1)->count();

                $info['rental_total'] = model('rental')->where('estate_id',$info['estate_id'])->where('status',1)->count();
				
				$info['junjia']=ceil(intval($info['qipai'])/intval($info['acreage']));
                // print_r($info['qipai']);
                // print_r($info['price']);exit();
                $info['chajia']=intval($info['price'])-intval($info['qipai']);
				
				
				
				
				
				//print_r($info['price']/$info['qipai']);
				
				
				//exit();
				
				

                $this->assign('info',$info);
                // print($info['broker_id']);
                $jieduans = model('linkmenu')->where('id',$info['jieduan'])->find();
                // print_r($jieduans);
                $this->assign('jieduans',$jieduans);
                $pinglun = model('user')->where('id',$info['broker_id'])->find();
                // print_r($jieduans);
                $this->assign('pinglun',$pinglun);
                // print_r($pinglun);
                $nianxian = model('user_info')->where('user_id',$info['broker_id'])->find();
                // print_r($jieduans);
                $this->assign('nianxian',$nianxian);

                $counts = model('second_house')->where('estate_name',$info['estate_name'])->count();
                $this->assign('counts',$counts);

                $xiaoqu=$info['estate_id'];
                $objs   = model('second_house');

                $tong = $objs->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('status',1)->select();
              	$tcou = $objs->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('status',1)->count();
           		$this->assign('tcou',$tcou);
                $this->assign('tong',$tong);

                
                $qipai=$info['qipai'];
                $info['qipai']=number_format("$qipai",2,".","");
                $acreage=$info['acreage'];
                $info['acreage']=number_format("$acreage",2,".","");


                // $userInfo = $this->getUserInfo();

                $infos = cookie('userInfo');
                $infos = \org\Crypt::decrypt($infos);
                $this->assign('pdfpy',$infos);
                $user_id  = $infos['id'];
                $follow   = model('follow');
                $guanzhu = $follow->where('house_id',$xiaoqu)->where('user_id',$user_id)->where('model','estate')->count();
                $this->assign('guanzhu',$guanzhu);
                $fangid=$info['id'];
                $gzfang = $follow->where('house_id',$fangid)->where('user_id',$user_id)->where('model','second_house')->count();
                // print_r($fangid);
                // echo "123";
                // print_r($user_id);
                // print_r($gzfang);exit();
                $this->assign('gzfang',$gzfang);
                $models = model('fydp')->where('house_id',$fangid)->where('model','second_house')->count();

                $this->assign('models',$models);
                
                $ud = model('fydp')->where('house_id',$fangid)->where('model','second_house')->group('user_id')->select();

                $obj     = model('fydp')->alias('s');
                $join  = [['user m','m.id = s.user_id']];

                $gl=$obj->join($join)->where('s.house_id',$fangid)->where('s.model','second_house')->group('s.user_id')->limit(3)->select();
                // print_r($gl);
                $this->assign('gl',$gl);
                $this->assign('ud',$ud);







                // print_r($guanzhu);exit();
                // $estateid=$info['estate_id'];
               $city=$info['city'];
              $lists = model('city')->field('id,pid,spid,name,alias')->where('id','eq',$city)->find();
              $spid=$lists['spid'];
              $city_name=$lists['name'];
                 if(substr_count($spid,'|')==2){
                    $listsss = model('city')->field('id,name')->where('id','eq',$lists['pid'])->find();
                    $shi=$listsss['name'];  
                    $citys=$shi.$city_name; 
                 }else{
                    $citys=$city_name;
                 }
                // $v['city'] = getCityName($info['city']);
                  // var_dump($v['city']);
                $this->assign('citys',$citys);


                $this->assign('estate',$estate);

                $this->assign('storage_open',getSettingCache('storage','open'));

                $this->assign('near_by_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));

                $this->assign('same_price_house',$this->samePriceHouse($info->getData('price')));
				
				$this->assign('sames_price_house',$this->samesPriceHouse());


            }else{

                return $this->fetch('public/404');

            }

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

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

     * @return array

     * 获取列表

     */

    private function getLists()

    {

        $time    = time();

        $where   = $this->search();

        $sort    = input('param.sort/d',0);
        // print_r($sort);exit();

        $keyword = input('get.keyword');

        $field   = "s.id,s.title,s.estate_id,s.estate_name,s.chajia,s.junjia,s.marketprice,s.city,s.video,s.total_floor,s.floor,s.img,s.qipai,s.pano_url,s.room,s.living_room,s.toilet,s.price,s.average_price,s.tags,s.address,s.acreage,s.orientations,s.renovation,s.user_type,s.contacts,s.update_time,s.jieduan,s.fcstatus,s.types,s.onestime,s.oneetime,s.oneprice,s.twostime,s.twoetime,s.twoprice,s.bianstime,s.bianetime,s.bianprice";

        $obj     = model('second_house')->alias('s');

        //二手房列表

        if(isset($where['m.metro_id']) || isset($where['m.station_id']))

        {

            //查询地铁关联表

            $field .= ',m.metro_name,m.station_name,m.distance';

            $join  = [['metro_relation m','m.house_id = s.id']];

            $lists = $obj->join($join)->where($where)->where('m.model','second_house')->where('s.top_time','lt',$time)->field($field)->group('s.id')->order($this->getSort($sort))->paginate(10,false,['query'=>['keyword'=>$keyword]]);

        }else{
        if($sort==8){
            $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','neq',169)->field($field)->order($this->getSort($sort))->paginate(10,false,['query'=>['keyword'=>$keyword]]);
        }else if($sort==7){
            $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','eq',170)->field($field)->order($this->getSort($sort))->paginate(10,false,['query'=>['keyword'=>$keyword]]);
        }else
        {
            $lists   = $obj->where($where)->where('s.top_time','lt',$time)->field($field)->order($this->getSort($sort))->paginate(20,false,['query'=>['keyword'=>$keyword]]);
            // print_r($lists);
        }

        }

        if($lists->currentPage() == 1)

        {

            //二手房置顶列表

            $obj = $obj->removeOption()->alias('s');

            //关联地铁表

            if(isset($where['m.metro_id']) || isset($where['m.station_id']))

            {

                $field .= ',m.metro_name,m.station_name,m.distance';

                $join  = [['metro_relation m','m.house_id = s.id']];

                $obj->join($join)->where('m.model','second_house')->group('s.id');

            }

            $top   = $obj->field($field)->where($where)->where('top_time','gt',$time)->order(['top_time'=>'desc','id'=>'desc'])->select();

        }else{

            $top   = false;

        }



            // $join  = [['fang_estate f','f.id = s.estate_id']];
        
            foreach ($lists as $key => $value) {
                $estate_id=$lists[$key]['estate_id'];

               $sql=model('estate')->where('id','eq',$estate_id)->alias('years')->find();
               $years=$sql['years'];
               $lists[$key]['years']=$years;

            }





        return ['lists'=>$lists,'top'=>$top];

    }





    /**

     * @param $price

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 价格相似房源

     */

    private function samePriceHouse($price,$num = 4)

    {

        $min_price = $price - 10;

        $max_price = $price + 10;

        $lists = model('second_house')

                ->where('status',1)

                ->where('price','between',[$min_price,$max_price])

                ->where('city','in',$this->getCityChild())

                ->where('timeout','gt',time())

                ->field('id,title,img,room,living_room,jieduan,types,fcstatus,acreage,price')

                ->order('create_time desc')

                ->limit($num)

                ->select();

        return $lists;

    }
	
	
	 /**

     * @param $price

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 漏检房源

     */

    private function samesPriceHouse($num = 4)

    {

        //$min_price = $price - 10;

        //$max_price = $price + 10;

        $lists = model('second_house')

                ->where('status',1)

                ->where('marketprice',5)
				
				->where('fcstatus',170)

                ->where('city','in',$this->getCityChild())

                ->where('timeout','gt',time())

                ->field('id,title,img,room,living_room,jieduan,types,fcstatus,acreage,price,qipai,marketprice,kptime,fcstatus')

                ->order('create_time desc')

                ->limit($num)

                ->select();

        return $lists;

    }
	
	

    /**

     * @param $lat

     * @param $lng

     * @param int $city

     * @return array|\PDOStatement|string|\think\Collection

     * 附近房源

     */

    private function getNearByHouse($lat,$lng,$city = 0)

    {

        $obj = model('second_house');

        if($lat && $lng){

            $point      = "*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$lng}*PI()/180-lng*PI()/180)/2),2)))*1000) as distance";

            $bindsql    = $obj->field($point)->buildSql();

            $fields_res = 'id,title,price,room,types,jieduan,fcstatus,living_room,acreage,img,distance';

            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('distance','<',2000)->where('timeout','gt',time())->limit(3)->select();

        }else{

            $where['status'] = 1;

            $city && $where['city'] = $city;

            $where[] = ['timeout','gt',time()];

            $lists = $obj->where($where)->field('id,title,price,room,types,jieduan,fcstatus,living_room,acreage,img')->limit(3)->select();

        }

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

        $service->field   = 'h.id,h.img,h.title,h.price,h.estate_name,h.room,h.types,h.jieduan,h.fcstatus,h.living_room,h.acreage,h.city';

        $service->city    = $this->getCityChild();

        $service->cate_id = $pos_id;

        $service->model   = 'second_house';

        $service->num     = $num;

        $lists = $service->lists();



        return $lists;

    }

    /**

     * @return array

     * 搜索条件

     */

    private function search()

    {
        $estate_id     = input('param.estate_id/d',0);//小区id

        $param['area'] = input('param.area/d', $this->cityInfo['id']);

        $param['rading']     = 0;

        $param['tags']       = input('param.tags/d',0);

        $param['price']      = input('param.price',0);

        $param['acreage']    = input('param.acreage',0);//面积

        $param['room']       = input('param.room',0);//户型
        $param['types']       = input('param.types',0);//户型
        $param['jieduan']       = input('param.jieduan',0);//户型
        $param['fcstatus']       = input('param.fcstatus',0);//户型
// print_r($param);exit();
        $param['type']       = input('param.type',0);//物业类型

        $param['renovation'] = input('param.renovation',0);//装修情况

        $param['metro']      = input('param.metro/d',0);//地铁线

        $param['metro_station'] = input('param.metro_station/d',0);//地铁站点

        $param['sort']          = input('param.sort/d',0);//排序

        $param['orientations']  = input('param.orientations/d',0);//朝向

        $param['user_type']  = input('param.user_type/d',0);//1个人房源  2中介房源

        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];

        $param['search_type']   = input('param.search_type/d',1);//查询方式 1按区域查询 2按地铁查询

        $data['s.status']    = 1;

        $keyword = input('get.keyword');

        if(!empty($_GET['zprice1'])){
            $zprice1=$_GET['zprice1'];
        }
        if(!empty($_GET['zprice2'])){
            $zprice2=$_GET['zprice2'];
        }


        if(!empty($_GET['zmianji1'])){
            $zmianji1=$_GET['zmianji1'];
        }
        if(!empty($_GET['zmianji2'])){
            $zmianji2=$_GET['zmianji2'];
        }



        $seo_title = '';

        if($estate_id)

        {

            $data['s.estate_id'] = $estate_id;

            $estate_name = model('estate')->where('id',$estate_id)->value('title');

            $seo_title .= '_'.$estate_name.'二手房';

        }

        if(!empty($param['type']))

        {

            $data['s.house_type'] = $param['type'];

            $seo_title .= '_'.getLinkMenuName(9,$param['type']);

        }

        if(!empty($param['user_type']))

        {

            $data['s.user_type'] = $param['user_type'];

        }

        if(!empty($param['orientations']))

        {

            $data['s.orientations'] = $param['orientations'];

            $seo_title .= '_'.getLinkMenuName(4,$param['orientations']).'朝向';

        }

        if($param['renovation'])

        {

            $data['s.renovation'] = $param['renovation'];

            $seo_title .= '_'.getLinkMenuName(8,$param['renovation']);

        }
// print_r($keyword);exit();
        if($keyword)

        {
            if(!empty($_GET['type'])){
                $house_type=$_GET['type'];
                $param['house_type']=$house_type;
            }
            
            $param['keyword'] = $keyword;
            

            $data[] = ['s.title','like','%'.$keyword.'%'];
            $seo_title .= '_'.$keyword;

        }
// print_r($param);exit();
        if($param['search_type'] == 2)

        {

            if(!empty($param['metro']))

            {

                $data['m.metro_id'] = $param['metro'];

                $seo_title .= '_地铁'.Metro::getMetroName($param['metro']);

                $this->assign('metro_station',Metro::metroStation($param['metro']));

            }else{

                $data[] = ['s.city','in',$this->getCityChild()];

            }

            if(!empty($param['metro_station']))

            {

                $data['m.station_id'] = $param['metro_station'];

                $seo_title .= '_'.Metro::getStationName($param['metro_station']);

            }

        }else{

            if(!empty($param['area']))

            {

                $data[] = ['s.city','in',$this->getCityChild($param['area'])];

                $rading = $this->getRadingByAreaId($param['area']);

                //读取商圈

                $param['rading'] = 0;

                if($rading && array_key_exists($param['area'],$rading))

                {

                    $param['rading']  = $param['area'];

                    $param['area']    = $rading[$param['area']]['pid'];

                }

                $param['area']!=$this->cityInfo['id'] && $seo_title .= '_'.getCityName($param['area'],'').'二手房';

                $this->assign('rading',$rading);

            }

        }

        if(!empty($param['price']))

        {

            $data[] = getSecondPrice($param['price'],'s.price');

            $price  = config('filter.second_price');

            isset($price[$param['price']]) && $seo_title .= '_'.$price[$param['price']]['name'];

        }

        if(!empty($param['room']))

        {

            $data[] = getRoom($param['room'],'s.room');

            $room   = config('filter.room');

            isset($room[$param['room']]) && $seo_title .= '_'.$room[$param['room']];

        }
        if(!empty($param['types']))

        {

            $data['s.types'] = $param['types'];

            $seo_title .= '_'.getLinkMenuName(26,$param['types']);

        }
        if(!empty($param['jieduan']))

        {

            $data['s.jieduan'] = $param['jieduan'];

            $seo_title .= '_'.getLinkMenuName(25,$param['jieduan']);

        }
        if(!empty($param['fcstatus']))

        {

            $data['s.fcstatus'] = $param['fcstatus'];

            $seo_title .= '_'.getLinkMenuName(27,$param['fcstatus']);

        }

        if(!empty($param['acreage']))

        {

            $data[] = getAcreage($param['acreage'],'s.acreage');

            $acreage = config('filter.acreage');

            isset($acreage[$param['acreage']]) && $seo_title .= '_'.$acreage[$param['acreage']]['name'];

        }



        if(!empty($param['tags'])){

            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['tags']},s.tags)")];

            $seo_title .= '_'.getLinkMenuName(14,$param['tags']);

        }

        $data[] = ['s.timeout','gt',time()];
        if(!empty($_GET['zprice1']) && !empty($_GET['zprice2'])){
            $data[] = ['s.price','between',[$zprice1,$zprice2]];
        }
        // print_r($_GET['zmianji1']);
        // print_r($_GET['zmianji2']);exit();
        if(!empty($_GET['zmianji1']) && !empty($_GET['zmianji2'])){
            $data[] = ['s.acreage','between',[$zmianji1,$zmianji2]];
        }


        $search = $param;

        $seo_title  = trim($seo_title,'_');

        $seo_title && $this->setSeo(['seo_title'=>$seo_title,'seo_keys'=>str_replace('_',',',$seo_title)]);

        unset($param['rading']);

        $data = array_filter($data);

        $this->assign('search',$search);
        if(!empty($_GET['zprice1'])){
            $param['zprice1']=$_GET['zprice1'];
        }
        if(!empty($_GET['zprice2'])){
            $param['zprice2']=$_GET['zprice2'];
        }
        if(!empty($_GET['zmianji1'])){
            $param['zmianji1']=$_GET['zmianji1'];
        }
        if(!empty($_GET['zmianji2'])){
            $param['zmianji2']=$_GET['zmianji2'];
        }
        $this->assign('param',$param);
        // print_r($data);exit();
        return $data;

    }



    /**

     * @param $sort

     * @return array

     * 排序

     */

    private function getSort($sort)

    {
// print_r($sort);exit();
        switch($sort)

        {

            case 0:

                $order = ['fcstatus'=>'asc','ordid'=>'asc','id'=>'desc'];

                break;

            case 1:

                $order = ['price'=>'asc','id'=>'desc'];

                break;

            case 2:

                $order = ['price'=>'desc','id'=>'desc'];

                break;

            case 3:

                $order = ['average_price'=>'asc','id'=>'desc'];

                break;

            case 4:

                $order = ['average_price'=>'desc','id'=>'desc'];

                break;

            case 5:

                $order = ['acreage'=>'asc','id'=>'desc'];

                break;

            case 6:

                $order = ['acreage'=>'desc','id'=>'desc'];

                break;
            case 7:

                $order = ['fcstatus'=>'asc','ordid'=>'desc','id'=>'desc'];

                break;
            case 8:

                $order = ['marketprice'=>'desc'];

                break;

            default:

                $order = ['ordid'=>'asc','id'=>'desc'];

                break;

        }

        return $order;

    }

}