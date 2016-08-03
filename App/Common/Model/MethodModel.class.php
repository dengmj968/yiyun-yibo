<?php
namespace Common\Model;
/**
 * url资源管理
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0
 * creat Time  2014-11-20 17:45
 */
class MethodModel extends BaseModel {

	protected $tableName = 'method';
    
    
    public function parseData( array $data ){
        $data['update_time'] = time();
        return $data;
    }

}