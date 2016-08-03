<?php
/**
 * TOP API: taobao.wangwang.eservice.groupmember.get request
 * 
 * @author auto create
 * @since 1.0, 2012-06-28 16:31:13
 */
class WangwangEserviceGroupmemberGetRequest
{
	/** 
	 * 被查询用户组管理员ID：cntaobao+淘宝nick，例如cntaobaotest
	 **/
	private $managerId;
	
	private $apiParas = array();
	
	public function setManagerId($managerId)
	{
		$this->managerId = $managerId;
		$this->apiParas["manager_id"] = $managerId;
	}

	public function getManagerId()
	{
		return $this->managerId;
	}

	public function getApiMethodName()
	{
		return "taobao.wangwang.eservice.groupmember.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->managerId,"managerId");
		RequestCheckUtil::checkMaxLength($this->managerId,128,"managerId");
	}
}