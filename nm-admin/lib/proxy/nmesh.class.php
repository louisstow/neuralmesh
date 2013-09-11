<?php
/**
 * NMesh object. This class is the base class.
 * 
 * Warning: A lot of effort and sanity has gone into optimizing
 * this, so some methods may not look pretty but it's all for the greater
 * good of performance. PHP is not typically recognised for it's speed,
 * nor are neural networks, so this combination was never destined to be
 * enjoyable or even possible yet here it is in all its glory.
 * 
 * Based on the PHPNN class
 * 
 * @author Louis Stowasser
 */
class nmesh
{
	/** Array of layer objects */
	public $layer = array();
	/** Count of inputs */
	public $inputs;
	/** Count of outputs */
	public $outputs;
	/** Momentum rate generalized for entire network */
	static public $momentumrate = 0.5;
	/** Cached output */
	static public $cache;
	
	function sigmoid($value) {
		return 1 / (1+exp(-1*$value));
	}
	
	/**
	 * Takes some input and runs the network
	 * @param $inputarray Input data
	 * @return The output data
	 */	
	function run($inputarray) 
	{
		$output = array();
		$l_count = count($this->layer);
		
		for($l = 0; $l < $l_count; ++$l) { // Now we walk through each layer of the net
			
			foreach($this->layer[$l]->neuron as $neuron) {
				$inputs = ($l===0) ? $inputarray : $output[$l];
				
				$x = 0; $sum = 0;
				foreach($neuron->synapse as $synapse) {
					$synapse->input = $inputs[$x];
					$sum += $synapse->weight * $synapse->input;
					++$x;
				}

				$value = 1/(1+exp(-1*($sum+$neuron->bias)));
				$neuron->value = $value;
				$output[$l+1][] = $value;
			}
		}
		$data = $output[$l];
		self::$cache = $data;
		return $data;
	}
	
	/**
	 * Do a quick unsupervised training set
	 * @param $id ID of the network
	 * @param $epochs Amount of epochs to run
	 * @param $inputs String of inputs
	 * @param $outputs String of desired outputs
	 * @return Array of results
	 */
	function quick_train($id,$epochs,$lr,$inputs,$outputs) {
		$inputarray = str_split($inputs); //convert from string to array
		$outputarray = str_split($outputs);
		
		$start = microtime(true);
		for($i=0;$i<$epochs;$i++) {
			$end_mse = $this->train($inputarray,$outputarray,$lr);
			if($i===1) $start_mse = $end_mse; 
		}
		$exectime = microtime(true)-$start;
		return array("startmse"=>$start_mse,"endmse"=>$end_mse,"time"=>$exectime);
	}
	
	/**
	 * Main function to train the network based on some inputs and
	 * desired outputs
	 * @param $inputarray Inputs to train
	 * @param $outputarray Desired outputs
	 * @param $learningrate The rate at which it learns
	 * @return The global MSE (how intelligent the network is)
	 */
	function train($inputarray,$outputarray,$learningrate)
	{
		$this->run($inputarray);  //Run a feedforward pass as normal
		return $this->calculate_deltas($outputarray,$learningrate);
	}
	
	/**
	 * This peforms the backpropagation algorithm on the network
	 * @param $outputarray Based on the last run, teach it to return this
	 * @param $lr The learning rate
	 * @return The global MSE of this training epoch
	 */
	function calculate_deltas($outputarray,$lr)
	{
		$mse_sum = 0;
		$l_count = count($this->layer)-1;
		$m = nmesh::$momentumrate;
		$error = array();
		$output_count = count($this->layer[$l_count]->neuron);
		
		for($l = $l_count; $l >= 0; --$l) {
			
			$error[$l] = array_fill(0,count($this->layer[$l]->neuron[0]->synapse),0);
			
			foreach($this->layer[$l]->neuron as $n=>$neuron) 
			{
				if($l===$l_count) {
					$n_error = $outputarray[$n] - $neuron->value;
					$mse_sum += $n_error * $n_error;
				} else $n_error = $error[$l+1][$n];
				
				$delta = $n_error * $neuron->value * (1 - $neuron->value);
				
				foreach($neuron->synapse as $s=>$synapse)
				{
					$wc = $delta * $synapse->input * $lr + $synapse->momentum * $m;
					$synapse->momentum = $wc;
					$synapse->weight += $wc;
					$error[$l][$s] += $delta * $synapse->weight;
				}
				//And lets go ahead and adjust the bias too
				$biaschange = $delta * $lr + $neuron->momentum * $m;
				$neuron->momentum = $biaschange;
				$neuron->bias += $biaschange;
			}
		}
		return $mse_sum / $output_count;
	}
	
	/*
	 * Basic sigmoid derivative
	 */
	function sigmoid_derivative($value)
	{
		return $value * (1 - $value);
	}
	
	/**
	 * Network manipulation to remove an amount of neurons
	 * @param $inputs Amount of input neurons to remove
	 */
	function remove_inputs($inputs) {
		if($this->inputs - $inputs < 1) 
			throw new Exception("Cannot remove neurons!"); //can't remove
		
		$this->inputs -= $inputs;
		for($i=0;$i<$inputs;$i++) {
			$n_count = count($this->layer[0]->neuron);
			for($n=0;$n<$n_count;$n++) {
				$this->layer[0]->neuron[$n]->remove_synapse();
			}
		}
	}
	
	/**
	 * Add an amount of input neurons
	 * @param $inputs Amount of input neurons to add
	 */
	function add_inputs($inputs) {
		$this->inputs += $inputs;
		for($i=0;$i<$inputs;$i++) {
			$n_count = count($this->layer[0]->neuron);
			for($n=0;$n<$n_count;$n++) {
				$this->layer[0]->neuron[$n]->add_synapse();
			}
		} 
	}
	
	/**
	 * Remove some outputs
	 * @param $outputs Amount of output neurons to remove
	 */
	function remove_outputs($outputs) {
		if($this->outputs - $outputs < 1) throw new Exception("Cannot remove that many outputs!");
		$this->outputs -= $outputs;
		for($i=0;$i<$outputs;$i++) {
			$this->layer[count($this->layer)-1]->remove_neuron();
		}
	}
	
	/**
	 * Add some outputs
	 * @param $outputs Amount to add
	 */
	function add_outputs($outputs) {
		$this->outputs += $outputs;
		for($i=0;$i<$outputs;$i++) {
			$this->layer[count($this->layer)-1]->add_neuron();
		}
	}
	
	/**
	 * Add a hidden layer to the network
	 * @param $neuronal_bias Bias
	 * @param $initial_weightrange Range of random weights
	 */
	function add_layer($neuronal_bias=1,$initial_weightrange=1) {
		$hidden_neurons = count($this->layer[0]->neuron);
		array_splice($this->layer,
					 count($this->layer)-1,//because count is base 0
					 0,
					 //wrapped in array() so it doesnt lose its object cast
					 array(new layer($hidden_neurons,$hidden_neurons,$neuronal_bias,$initial_weightrange)));
	}
	
	/**
	 * Add a neuron to hidden layers
	 * @param $count Amount of neurons to add
	 * @param $bias
	 * @param $weightrange
	 */
	function add_neuron($count=1,$bias=1,$weightrange=1) {
		for($i=0;$i<$count;$i++) { //add a count
			for($l=0;$l<count($this->layer)-1;$l++) { //loop over hidden layers
				$this->layer[$l]->add_neuron($bias,$weightrange);	
				for($n=0;$n<count($this->layer[$l+1]->neuron);$n++) {
					//loop over the neurons and add an extra synapse
					$this->layer[$l+1]->neuron[$n]->add_synapse();
				}
			}
		}
	}
	
	/**
	 * Remove a layer from the network
	 * @param $layer Index of the layer to remove (must be hidden layer)
	 */
	function remove_layer($layer=null) {
		//if is not the output layer and has more than one hidden layer
		$layer = is_null($layer) ? count($this->layer)-2 : $layer;
		if(count($this->layer) > 2 && $this->is_hidden_layer($layer)) {
			array_splice($this->layer,$layer,1);
		}
	}
	
	/**
	 * Remove a neuron from hidden layers
	 * @param $neuron Index of neuron
	 */
	function remove_neuron($count=0) {
		if(count($this->layer[0]->neuron) - $count < 1) 
			throw new Exception("Cannot remove neurons!");
		
		for($l=0;$l<count($this->layer)-1;$l++) { //loop through hidden layers

			for($c=0;$c<$count;$c++) $this->layer[$l]->remove_neuron();

			for($n=0;$n<count($this->layer[$l+1]->neuron);$n++) {
				//loop over the neurons and remove a synapse
				for($c=0;$c<$count;$c++) $this->layer[$l+1]->neuron[$n]->remove_synapse($count);
			}
		}
	}
	
	/**
	 * Constructor for creating a neural network object
	 */
	function nmesh($input_neurons,$output_neurons,$hidden_neurons_per_layer,$hidden_layers,$neuronal_bias=1,$initial_weightrange=1)
	{
		$this->inputs = $input_neurons;
		$this->outputs= $output_neurons;
		$firstlayerflag = 0;
		$total_layers = $hidden_layers+1;
		
		$this->layer[0] = new layer($hidden_neurons_per_layer,$input_neurons,$neuronal_bias,$initial_weightrange);
		for($i=1;$i<$total_layers-1;$i++)
		{
			$inputs = $input_neurons;
			if($firstlayerflag==1) //second hidden layer
			{
				$inputs = $hidden_neurons_per_layer; //use the hidden neurons
			}
			$this->layer[$i] = new layer($hidden_neurons_per_layer,$inputs,$neuronal_bias,$initial_weightrange);
			$firstlayerflag=1;
		}
		$this->layer[$total_layers-1] = new layer($output_neurons,$hidden_neurons_per_layer,$neuronal_bias,$initial_weightrange);
	}
}

/**
 * Layer object holds an array of neurons. Mainly an organisation class.
 * @author Louis Stowasser
 */
class layer
{
	/** Array of neuron objects */
	public $neuron = array();
	
	/**
	 * Adds a neuron to its array
	 */
	function add_neuron($bias=1,$weightrange=1) {
		$this->neuron[count($this->neuron)] = new neuron(count($this->neuron[0]->synapse),$bias,$weightrange);
	}
	
	/**
	 * Remove a neuron from the array
	 */
	function remove_neuron() {
		array_splice($this->neuron,0,1);		
	}
	
	/**
	 * Constructor for the layer object
	 * @param $neurons Amount of neurons
	 * @param $inputs Amount of inputs
	 */
	function layer($neurons,$inputs,$bias,$weightrange)
	{
		for($i=0;$i<$neurons;$i++)
		{
			$this->neuron[$i] = new neuron($inputs,$bias,$weightrange);
		}
	}
}

/**
 * The Neuron class does most of the work
 * @author Louis Stowasser
 */
class neuron
{
	/** neurons value */
	public $value;
	/** bias value */
	public $bias;
	/** temporary error */
	//public $error;
	/** momentum value */
	public $momentum;
	/** array of synapse objects */
	public $synapse = array();
	
	/**
	 * Cleanup unnecessary variables to save memory and serialization time
	 */
	function cleanup()
	{
		unset($this->error);
		unset($this->value);
	}
	
	/**
	 * Adjusts the weights of the synapses as well as the bias and momentum
	 * and cleans up uneeded varibles when finished.
	 * @param $learningrate The learning rate
	 */
	function adjust_weights_clean($learningrate,$delta)
	{
		$m = nmesh::$momentumrate;
		
		foreach($this->synapse as $synapse)
		{
			$weightchange = $delta * $synapse->input * $learningrate + $synapse->momentum * $m;
			$synapse->momentum = $weightchange;
			$synapse->weight += $weightchange;
			
			unset($synapse->input); unset($synapse->delta);
		}
		//And lets go ahead and adjust the bias too
		$biaschange = $delta * $learningrate + $this->momentum * $m;
		$this->momentum = $biaschange;
		$this->bias += $biaschange;
	}
	
	/**
	 * Adjusts the weights of the synapses as well as the bias and momentum
	 * but keeps the variables and sets the synapses delta
	 * @param $learningrate The learning rate
	 */
	function adjust_weights($learningrate,$delta)
	{
		$m = nmesh::$momentumrate;
		
		foreach($this->synapse as $synapse)
		{
			$synapse->delta = $delta;
			$weightchange = $delta * $synapse->input * $learningrate + $synapse->momentum * $m;
			$synapse->momentum = $weightchange;
			$synapse->weight += $weightchange;
		}
		//And lets go ahead and adjust the bias too
		$biaschange = $delta * $learningrate + $this->momentum * $m;
		$this->momentum = $biaschange;
		$this->bias += $biaschange;
	}
	
	/**
	 * Set the synapse weights to a random number within a range
	 * @param $weightrange Range of the random weight
	 */
	function randomize_weights($weightrange)
	{		
		$s_count = count($this->synapse);
		for($i=0;$i<$s_count;$i++)
		{
			$this->synapse[$i]->randomize_weight($weightrange);
		}
	}
	
	/**
	 * Mathematical sigmoid function, mainly because PHP doesn't have it natively.
	 * @param $value Number to return the sigmoid of
	 * @return Sigmoid of given number
	 */
	function sigmoid($value)
	{
		return 1 / (1+exp(-1*$value));
	}
	
	/**
	 * Add a synapse to the synapse array
	 * @param $weightrange Range for random weights
	 */
	function add_synapse($weightrange=1) {
		$this->synapse[count($this->synapse)] = new synapse($weightrange);
	}
	
	/**
	 * Remove a synapse from the synapse array
	 */
	function remove_synapse() {
		array_splice($this->synapse,0,1);
	}
	
	/**
	 * Neuron constructor
	 * @param $inputs Amount of inputs
	 */
	function neuron($inputs,$bias,$weightrange)
	{
		unset($this->value); //only set when needed
		unset($this->error);
		$this->bias = $bias;
		$this->momentum = 0;
		
		for($i=0;$i<$inputs;$i++)
		{
			$this->synapse[$i] = new synapse($weightrange);
		}
	}
}

/**
 * Synapse class
 * @author Louis Stowasser
 */
class synapse
{
	/** Input value */
	public $input;
	/** Weight of the synapse */
	public $weight;
	/** Value of error */
	//public $delta;
	/** Momentum value */
	public $momentum;
	
	/**
	 * Cleanup unnecessary variables to save memory and serialization time
	 */
	function cleanup()
	{
		unset($this->input);
		unset($this->delta);
	}
	
	/**
	 * Generate a random weight for the synapse
	 * @param $weightrange Range to generate in
	 */
	function randomize_weight($weightrange)
	{
		$this->weight = (mt_rand(0,$weightrange*2000)/1000)-$weightrange;
	}
	
	/**
	 * Synapse constructor
	 * @param $weightrange Range of random weights
	 */
	function synapse($weightrange)
	{
		unset($this->input);
		unset($this->delta);
		$this->momentum = 0;
		$this->randomize_weight($weightrange);
	}
}
?>