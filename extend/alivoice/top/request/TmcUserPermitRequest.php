<?php
/**
 * TOP API: taobao.topats.task.delete request
 * 
 * @author auto create
 * @since 1.0, 2014.03.27
 */
class TmcUserPermitRequest
{
	/** 
	 * 需要取消的任务ID
	 **/
	private $topics;
	
	private $apiParas = array();
	
	public function setTopics($topics)
	{
		$this->topics = $topics;
		$this->apiParas["topics"] = $topics;
	}

	public function getTopics()
	{
		return $this->topics;
	}

	public function getApiMethodName()
	{
		return "taobao.tmc.user.permit";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	//	RequestCheckUtil::checkNotNull($this->topics,"topics");
	}
	
//	public function putOtherTextParam($key, $value) {
//		$this->apiParas[$key] = $value;
//		$this->$key = $value;
//	}
}
