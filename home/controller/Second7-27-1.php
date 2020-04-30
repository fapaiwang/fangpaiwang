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
        // print_r($lists);
        // print_r($lists);
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
                //二手房价格走势

                // $c1 = strtotime(date('Y-01-01 00:00:00'));
                // $c2 = strtotime(date('Y-02-01 00:00:00'));
                // $c3 = strtotime(date('Y-03-01 00:00:00'));
                // $c4 = strtotime(date('Y-04-01 00:00:00'));
                // $c5 = strtotime(date('Y-05-01 00:00:00'));
                // $c6 = strtotime(date('Y-06-01 00:00:00'));
                // $c7 = strtotime(date('Y-07-01 00:00:00'));
                // $c8 = strtotime(date('Y-08-01 00:00:00'));
                // $c9 = strtotime(date('Y-09-01 00:00:00'));
                // $c10 = strtotime(date('Y-10-01 00:00:00'));
                // $c11 = strtotime(date('Y-11-01 00:00:00'));
                // $c12 = strtotime(date('Y-12-01 00:00:00'));
                // $c13 = strtotime(date('Y-12-31 23:59:59'));
                $c1 = date('Y-01-01');
                $c2 = date('Y-02-01');
                $c3 = date('Y-03-01');
                $c4 = date('Y-04-01');
                $c5 = date('Y-05-01');
                $c6 = date('Y-06-01');
                $c7 = date('Y-07-01');
                $c8 = date('Y-08-01');
                $c9 = date('Y-09-01');
                $c10 = date('Y-10-01');
                $c11 = date('Y-11-01');
                $c12 = date('Y-12-01');
                $c13 = date('Y-12-31');
                $xiaoquid=$estate['id'];
                //一月
                $d1[] = ['complate_time','egt',$c1];
                $d1[] = ['complate_time','elt',$c2];
                $d1[] = ['estate_id','eq',$xiaoquid];
                $a1 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d1)->find();               
                $l1 = model('transaction_record')->where($d1)->count();  
                  
                if($l1==0){
                    $y1=0;
                }else{

                 $y1=$a1['cjprices']/$l1;
                }         
                $this->assign('y1',$y1);
                $this->assign('l1',$l1);
                //二月
                $d2[] = ['complate_time','egt',$c2];
                $d2[] = ['complate_time','elt',$c3];
                $d2[] = ['estate_id','eq',$xiaoquid];
                $a2 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d2)->find();
                $l2 = model('transaction_record')->where($d2)->count();  
                if($l2==0){
                    $y2=0;
                }else{

                 $y2=$a2['cjprices']/$l2;
                }            
                // $y2=$a2['cjprices']/$l2;
                $this->assign('y2',$y2);
                $this->assign('l2',$l2);
                //三月
                $d3[] = ['complate_time','egt',$c3];
                $d3[] = ['complate_time','elt',$c4];
                $d3[] = ['estate_id','eq',$xiaoquid];
                $a3 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d3)->find();
                $l3 = model('transaction_record')->where($d3)->count();     
                if($l3==0){
                    $y3=0;
                }else{

                 $y3=$a3['cjprices']/$l3;
                }  
                $this->assign('y3',$y3);
                $this->assign('l3',$l3);
                //四月
                $d4[] = ['complate_time','egt',$c4];
                $d4[] = ['complate_time','elt',$c5];
                $d4[] = ['estate_id','eq',$xiaoquid];
                $a4 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d4)->find();
                $l4 = model('transaction_record')->where($d4)->count();      
                if($l4==0){
                    $y4=0;
                }else{

                 $y4=$a4['cjprices']/$l4;
                }         
                $this->assign('y4',$y4);
                $this->assign('l4',$l4);
                //五月
                $d5[] = ['complate_time','egt',$c5];
                $d5[] = ['complate_time','elt',$c6];
                $d5[] = ['estate_id','eq',$xiaoquid];
                $a5 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d5)->find();
                $l5 = model('transaction_record')->where($d5)->count(); 
                if($l5==0){
                    $y5=0;
                }else{

                 $y5=$a5['cjprices']/$l5;
                }              
                $this->assign('y5',$y5);
                $this->assign('l5',$l5);
                //六月
                $d6[] = ['complate_time','egt',$c6];
                $d6[] = ['complate_time','elt',$c7];
                $d6[] = ['estate_id','eq',$xiaoquid];
                $a6 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d6)->find();
                $l6 = model('transaction_record')->where($d6)->count();   
                if($l6==0){
                    $y6=0;
                }else{

                 $y6=$a6['cjprices']/$l6;
                }            
                $this->assign('y6',$y6);
                $this->assign('l6',$l6);
                //七月
                $d7[] = ['complate_time','egt',$c7];
                $d7[] = ['complate_time','elt',$c8];
                $d7[] = ['estate_id','eq',$xiaoquid];
                $a7 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d7)->find();
                $l7 = model('transaction_record')->where($d7)->count();  
                if($l7==0){
                    $y7=0;
                }else{

                 $y7=$a7['cjprices']/$l7;
                }             
                $this->assign('y7',$y7);
                $this->assign('l7',$l7);
                //八月
                $d8[] = ['complate_time','egt',$c8];
                $d8[] = ['complate_time','elt',$c9];
                $d8[] = ['estate_id','eq',$xiaoquid];
                $a8 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d8)->find();
                $l8 = model('transaction_record')->where($d8)->count(); 
                if($l8==0){
                    $y8=0;
                }else{

                 $y8=$a8['cjprices']/$l8;
                }  
                $this->assign('y8',$y8);
                $this->assign('l8',$l8);
                //九月
                $d9[] = ['complate_time','egt',$c9];
                $d9[] = ['complate_time','elt',$c10];
                $d9[] = ['estate_id','eq',$xiaoquid];
                $a9 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d9)->find();
                $l9 = model('transaction_record')->where($d9)->count();    
                if($l9==0){
                    $y9=0;
                }else{

                 $y9=$a9['cjprices']/$l9;
                }  
                $this->assign('y9',$y9);
                $this->assign('l9',$l9);
                //十月
                $d10[] = ['complate_time','egt',$c10];
                $d10[] = ['complate_time','elt',$c11];
                $d10[] = ['estate_id','eq',$xiaoquid];
                $a10 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d10)->find();
                $l10 = model('transaction_record')->where($d10)->count();   
                if($l10==0){
                    $y10=0;
                }else{

                 $y10=$a10['cjprices']/$l10;
                }           
                
                $this->assign('y10',$y10);
                $this->assign('l10',$l10);
                //十一月
                $d11[] = ['complate_time','egt',$c11];
                $d11[] = ['complate_time','elt',$c12];
                $d11[] = ['estate_id','eq',$xiaoquid];
                $a11 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d11)->find();
                $l11 = model('transaction_record')->where($d11)->count();  
                if($l11==0){
                    $y11=0;
                }else{

                 $y11=$a11['cjprices']/$l11;
                }            
                
                $this->assign('y11',$y11);
                $this->assign('l11',$l11);
                //十二月
                $d12[] = ['complate_time','egt',$c12];
                $d12[] = ['complate_time','elt',$c13];
                $d12[] = ['estate_id','eq',$xiaoquid];
                $a12 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d12)->find();
                $l12 = model('transaction_record')->where($d12)->count(); 
                if($l12==0){
                    $y12=0;
                }else{

                 $y12=$a12['cjprices']/$l12;
                }             
                
                $this->assign('y12',$y12);
                $this->assign('l12',$l12);



                $info['total']        = $obj->where('estate_id',$info['estate_id'])->where('status',1)->count();

                $info['rental_total'] = model('rental')->where('estate_id',$info['estate_id'])->where('status',1)->count();
				
				$info['junjia']=ceil(intval($info['qipai'])/intval($info['acreage']));
                // print_r($info['qipai']);
                // print_r($info['price']);exit();
                $info['chajia']=intval($info['price'])-intval($info['qipai']);
				
				
				
				
				
				//print_r($info['price']/$info['qipai']);
				
				
				//exit();
				// print_r($info);
				

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

                $objss   = model('second_house')->alias('s');
                $joinss  = [['estate m','m.id = s.estate_id']];
                
                
$field   = "s.*,s.endtime";
$field .= ',m.years';

                $tong = $objss->field($field)->join($joinss)->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('s.status',1)->limit(5)->select();
                $count = count($tong);
                // print_r($count);exit();
                // echo "123****";
                // $data = $tong->paginate(1,$count);
                // print_r($data);exit();
                // $pages1=2.2;
                // $this->assign('pages1',$pages1->render());
                // $tong = $objs->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('status',1)->select();
              	$tcou = $objs->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('status',1)->count();
           		$this->assign('tcou',$tcou);
                $this->assign('tong',$tong);
// print_r($tcou);exit();
                
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
                $jjr = model('fydp')->where('house_id',$fangid)->where('user_id',$info['broker_id'])->group('user_id')->count();
                
                
                if($jjr==0){
                    $gl=$obj->join($join)->where('s.house_id',$fangid)->where('s.model','second_house')->group('s.user_id')->limit(3)->select();
                }else{
                    $gls=model('fydp')->alias('s')->join([['user m','m.id = s.user_id']])->where('s.house_id','eq',$fangid)->where('s.model','eq','second_house')->where('s.user_id','eq',$info['broker_id'])->group('s.user_id')->limit(1)->select();
                    // print_r($gls);
                    $this->assign('gls',$gls);
                    $gl=$obj->join($join)->where('s.house_id','eq',$fangid)->where('s.model','eq','second_house')->where('s.user_id','neq',$info['broker_id'])->group('s.user_id')->limit(2)->select();
                }

                    
                // $gl=$obj->join($join)->where('s.house_id',$fangid)->where('s.model','second_house')->group('s.user_id')->limit(3)->select();
                // print_r($gl);
                // $this->assign('gls',$gls);
                $this->assign('jjr',$jjr);
                $this->assign('gl',$gl);
                $this->assign('ud',$ud);

     


            $txq = model('second_house')

                 ->where('status',1)

                 ->where('estate_id','eq',$xiaoqu)

                 ->where('id','neq',$info['id'])

                 ->field('id,title,room,living_room,toilet,acreage,fcstatus,price,img')

                ->order('id desc')


                 ->select();

              $txq_count=count($txq);
              // print_r($txq);


$this->assign('txq_count',$txq_count);


             // print_r($txq);
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

                 $this->assign('txq',$txq);

                $this->assign('storage_open',getSettingCache('storage','open'));

                $this->assign('near_by_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));

                

                $this->assign('same_price_house',$this->samePriceHouse($info->getData('price'),$txq_count));
				
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

        $field   = "s.id,s.title,s.estate_id,s.estate_name,s.chajia,s.junjia,s.marketprice,s.city,s.video,s.total_floor,s.floor,s.img,s.qipai,s.pano_url,s.room,s.living_room,s.toilet,s.price,s.cjprice,s.average_price,s.tags,s.address,s.acreage,s.orientations,s.renovation,s.user_type,s.contacts,s.update_time,s.kptime,s.jieduan,s.fcstatus,s.types,s.onestime,s.oneetime,s.oneprice,s.twostime,s.twoetime,s.twoprice,s.bianstime,s.bianetime,s.bianprice";

        $obj     = model('second_house')->alias('s');

        //二手房列表

        if(isset($where['m.metro_id']) || isset($where['m.station_id']))

        {

            //查询地铁关联表

            $field .= ',m.metro_name,m.station_name,m.distance';

            $join  = [['metro_relation m','m.house_id = s.id']];

            $lists = $obj->join($join)->where($where)->where('m.model','second_house')->where('s.top_time','lt',$time)->field($field)->group('s.id')->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);

        }else{
        if($sort==8){
            $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','neq',169)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
        }else if($sort==7){
            $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','eq',170)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
        }else
        {
            $lists   = $obj->where($where)->where('s.top_time','lt',$time)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
            // print_r($sort);
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

            // $top   = $obj->field($field)->where($where)->where('top_time','gt',$time)->order(['top_time'=>'desc','id'=>'desc'])->select();
            $top   = $obj->field($field)->where($where)->where('top_time','gt',$time)->order(['timeout'=>'desc','id'=>'desc'])->select();

        }else{

            $top   = false;

        }



            // $join  = [['fang_estate f','f.id = s.estate_id']];
        //print_r($lists);
            foreach ($lists as $key => $value) {
                $estate_id=$lists[$key]['estate_id'];

               $sql=model('estate')->where('id','eq',$estate_id)->alias('years')->find();
               $years=$sql['years'];
               $lists[$key]['years']=$years;



                 $city_id=$lists[$key]['city'];

               $sqls=model('city')->where('id','eq',$city_id)->alias('city')->find();
               $citys=$sqls['name'];
               $lists[$key]['city']=$citys;
               $lists[$key]['chajia']=intval($lists[$key]['price'])-intval($lists[$key]['qipai']);
               

$lists[$key]['kptimes']=strtotime($lists[$key]['kptime']);
$sTime=time();
// print_r($lists[$key]['fcstatus']);
if($lists[$key]['fcstatus']==169 || $lists[$key]['fcstatus']==170){

    $ctimes=$sTime-$lists[$key]['kptimes'];
    // print_r($ctimes);
    if($ctimes >= 0 && $ctimes < 3600*24){
        model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>169]);//正在进行
        // print_r($ctimes);echo "aaa";
        }elseif($ctimes >= (3600*24)){
        model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>171]);//已结束171
         // print_r($ctimes);echo "aaa";
        }else{
        model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>170]);//即将开始170
         // print_r($ctimes);echo "aaa";
        }
}



            }


// print_r($lists);


        return ['lists'=>$lists,'top'=>$top];

    }





    /**

     * @param $price

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 价格相似房源

     */

    private function samePriceHouse($price,$num)

    {    


          $num1=4-$num;

        $min_price = $price - 10;

        $max_price = $price + 10;


          
        $lists = model('second_house')

                ->where('status',1)

                ->where('price','between',[$min_price,$max_price])

                ->where('city','in',$this->getCityChild())

                ->where('timeout','gt',time())

                ->field('id,title,img,room,living_room,toilet,jieduan,types,fcstatus,acreage,price')

                ->order('create_time desc')

                ->limit($num1)

                ->select();
                // print_r($lists);
       
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

                ->field('id,title,img,room,living_room,toilet,jieduan,types,fcstatus,acreage,price,qipai,marketprice,kptime,fcstatus')

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

            $fields_res = 'id,title,price,room,types,jieduan,fcstatus,living_room,toilet,acreage,img,distance';

            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('distance','<',2000)->where('timeout','gt',time())->limit(3)->select();

        }else{

            $where['status'] = 1;

            $city && $where['city'] = $city;

            $where[] = ['timeout','gt',time()];

            $lists = $obj->where($where)->field('id,title,price,room,types,jieduan,fcstatus,living_room,toilet,acreage,img')->limit(3)->select();

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

        $service->field   = 'h.id,h.img,h.title,h.price,h.estate_name,h.room,h.types,h.jieduan,h.fcstatus,h.living_room,h.toilet,h.acreage,h.city';

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

        if(!empty($_GET['rec_position'])){
            $rec_position=$_GET['rec_position'];
        }

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
                $param['types']=$house_type;
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
        if(!empty($_GET['rec_position'])){
            $data[] = ['rec_position','eq',1];
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
        
        if(!empty($_GET['rec_position'])){
            $param['rec_position']=$_GET['rec_position'];
        }
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

            case 9:

                $order = ['rec_position'=>'desc','fcstatus'=>'asc','marketprice'=>'desc'];

                break;

            default:

                $order = ['ordid'=>'asc','id'=>'desc'];

                break;

        }

        return $order;

    }

}