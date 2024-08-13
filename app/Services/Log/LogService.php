<?php
namespace App\Services\Log;

use App\Services\Auth\AuthService;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Di\Annotation\Inject;

class LogService{

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * 构造日志内容
     * @param $operateName
     * @param $operates
     * @return string
     */
    public function buildContent($operateName,$operates){
        $content = '【'.(AuthService::getUserInfo()['username']).'】于'.date('Y-m-d H:i:s').'通过'.
        '【'.$operateName.'】操作 修改内容如下:';
        $operateArr = [];
        foreach ($operates as $key=>$operate){
            $operateType = '';
            switch ($key){
                case 'add':
                    $operateType = '添加';
                    break;
                case 'edit':
                    $operateType = '修改';
                    break;
                case 'delete':
                    $operateType = '删除';
                    break;
            }
            if(is_array($operate)){
                $arr = [];
                foreach ($operate as $key=>$item){
                    $arr[] = $key.' : '.$item;
                }
                $operate = implode(',',$arr);
            }
            $operateArr[] = $operateType.':'. $operate;
        }
        return $content.implode(';',$operateArr);
    }

    /**
     * 添加操作日志
     * @param $data
     * @param $tableName
     * @param $objectId
     * @return void
     */
    public function addLog($data,$tableName,$objectId){
        Db::table('operate_log')->insert([
            'table_name'=>$tableName,
            'object_id'=>$objectId,
            'operate_name'=>$data['operate_name'] ?? '',
            'operate_user_id'=>AuthService::getUserInfo()['user_id'],
            'operate_user_name'=>AuthService::getUserInfo()['username'],
            'content'=>$data['content'] ?? '',
        ]);
    }
    /**
     * 批量添加操作日志
     * @param $data
     * @param $tableName
     * @param $objectId
     * @return void
     */
    public function addLogs($data,$tableName){
        $inserts = [];
        foreach ($data as $item){
            $inserts[] =[
                'table_name'=>$tableName,
                'object_id'=>$item['object_id'],
                'operate_name'=>$item['operate_name'] ?? '',
                'operate_user_id'=>AuthService::getUserInfo()['user_id'],
                'operate_user_name'=>AuthService::getUserInfo()['username'],
                'content'=>$item['content'] ?? '',
            ];
        }
        Db::table('operate_log')->insert($inserts);
    }

    /**
     * 日志列表
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getLogList($params,$tableName,$objectId=0){
        $pageSize = $params['pageSize'] ?? 20;
        $page     = $params['pageIndex'] ?? 1;
        $pageSize = (int)$pageSize;
        $page     = (int)$page;
        if(empty($tableName)){
            throw new \Exception('日志获取失败');
        }
        $query = Db::table('operate_log')->where(['table_name'=>$tableName]);
        if(!empty($objectId)){
            $query->where(['object_id'=>$objectId]);
        }
        $result = $query->selectRaw('id,operate_name,operate_user_name,operate_time,content')
            ->orderBy('id','desc')->paginate($pageSize,['*'], 'page', $page);
        return [
            'list' => $result->items() ?? [],
            'total' => $result->total() ?? 0,
            'current_page' => $result->currentPage(),
            'page_size' => $result->perPage(),
            'page_total' => $result->lastPage(),
        ];
    }
}