<?php
/**
 * TOP API: alibaba.aliqin.fc.voice.num.doublecall request
 * 
 * @author csl create
 * @since 1.0, 2017.10.17
 */
class TmcMessagesConsumeRequest
{
	/** 
	 * 用户分组名称，不传表示消费默认分组，如果应用没有设置用户分组，传入分组名称将会返回错误
	 **/
        private $group_name;
        
        /*
         * 每次批量消费消息的条数
         *  默认值：100
         *  最小值：10
         *  最大值：200
         */
        private $quantity;

        
        private $apiParas = array();
        
        
        public function setGroupName($group_name){
            $this->group_name=$group_name;
            $this->apiParas["group_name"] = $group_name;
        }
        
        public function setQuantity($quantity){
            $this->quantity=$quantity;
            $this->apiParas["quantity"] = $quantity;
        }

	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->quantity,"quantity");
	}
	public function getApiMethodName()
	{
		return "taobao.tmc.messages.consume";
	}
	public function getApiParas()
	{
		return $this->apiParas;
	}
        
	
}
