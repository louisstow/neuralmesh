<?php
set_time_limit(0); //optional, but would be better than a timeout
require("nm-admin/lib/controller.class.php");

/**
 * Neural Mesh Factory pattern. Instance based on existing or new
 * @author Louis Stowasser
 */
class NeuralMesh {

	static private $instance = null; 
	static public $app = null;
	
	static private function init() {
		if(self::$app === null) {
			self::$app = new Controller;
			self::$app->inc("nmesh");
		}
	}
	
	/**
	 * Get a nmesh instance from authkey
	 * @param $authkey Unique key to network
	 * @return NeuralMesh instance (singleton)
	 */
	static public function getNetwork($authkey) {
		self::init();
		$data = self::$app->model->network->getAuth($authkey);
		$nn = self::$app->model->network->nn;
		nmesh::$momentumrate = $data['momentumrate'];
		return ($data['networkType'] == "managed") ? 
			new ManagedNetwork($data['networkID'],$data['learningrate'],$nn) : 
			new UnmanagedNetwork($data['networkID'],$data['learningrate'],$nn);
	}
	
	/**
	 * Create a new network and store it
	 * @param $input
	 * @param $output
	 * @param $hidden
	 * @param $layers
	 * @param $bias
	 * @param $weightrange
	 * @return unknown_type
	 */
	static public function createNetwork($input,$output,$hidden,$layers) {
		self::init();
		$nn = new nmesh($input,$output,$hidden,$layers);
		$authkey = sha1(uniqid());
		$id = self::$app->model->network->add("Temp",$authkey,"unmanaged");
		self::$app->model->network->save($nn,$id);
		
		return new UnmanagedNetwork($id,$authkey,1,$nn);
	}
}

abstract class AbstractNetwork {
	/** Nmesh instance */
	private $nn;
	/** Learning rate */
	private $lr;
	/** Network ID */
	private $id;
	
	/**
	 * Run the network or grab from the cache
	 * @param $inputs String of inputs
	 * @return array of outputs
	 */
	abstract public function run($inputs);
	
	/**
	 * Training function must comply with these
	 */
	abstract public function train($inputs,$outputs,$epochs=30);
} 

/**
 * UnmanagedNetwork is a network that doesn't get viewed in the Manager
 * and is usually temporary
 * @author Louis Stowasser
 */
class UnmanagedNetwork extends AbstractNetwork {
	
	public $authkey;
	
	public function UnmanagedNetwork($id,$authkey,$lr,$nn) {
		$this->authkey = $authkey;
		$this->id = $id;//
		$this->lr = $lr;
	}
	
	/**
	 * Quickly train network. Don't log the epoch
	 * @param $inputs String of inputs
	 * @param $outputs String of desired outputs
	 * @param $epochs Amount of times to train pattern
	 */
	public function train($inputs,$outputs,$epochs=30) {
		$id = $this->id;
		$lr = $this->lr;
		
		$data = $this->nn->quick_train($id,$epochs,$lr,$inputs,$outputs);
		NeuralMesh::$app->model->network->save($this->nn,$id);
	}
	
	public function run($inputs) {
		$inputarray = str_split($inputs);
		if(count($inputarray) != $this->nn->inputs) die("Incorrect number of entries! Expected ".$this->nn->inputs." got ".count($inputarray));
		return $this->nn->run($inputarray);
	}
	
	public function destory() {
		NeuralMesh::$app->model->network->destory($this->id);
	}
}

/**
 * ManagedNetwork is a pre-existing network that is more volatile and logs history, gets cached etc
 * @author Louis Stowasser
 */
class ManagedNetwork extends AbstractNetwork {
	
	public function ManagedNetwork($id,$lr,$nn) {
		$this->lr = $lr;
		$this->id = $id;		
		$this->nn = $nn;
	}
	
	/**
	 * Quickly train network
	 * @param $inputs String of inputs
	 * @param $outputs String of desired outputs
	 * @param $epochs Amount of times to train pattern
	 */
	public function train($inputs,$outputs,$epochs=30) {
		$id = $this->id;
		$lr = $this->lr;
		
		$data = $this->nn->quick_train($id,$epochs,$lr,$inputs,$outputs);
		NeuralMesh::$app->model->train->saveEpoch($id,$epochs,$data['startmse'],$data['endmse'],$data['time']);
		NeuralMesh::$app->model->quick_cache($id,$inputs,nmesh::$cache);
		
		NeuralMesh::$app->model->network->save($this->nn,$id);
	}
	
	public function run($inputs) {
		$id = $this->id;
		$data = NeuralMesh::$app->model->getCache($id.$inputs);
		
		if($data === null) { //not found in cache
			$inputarray = str_split($inputs);
			if(count($inputarray) != $this->nn->inputs) die("Incorrect number of entries! Expected ".$this->nn->inputs." got ".count($inputarray));
			$outputs = $this->nn->run($inputarray);
			//save into cache
			$hash = $id.$inputs;
			NeuralMesh::$app->model->saveCache($hash,$id,implode("|",$outputs));
		} else $outputs = explode("|",$data); //get from cache
		return $outputs;
	}
}
?>