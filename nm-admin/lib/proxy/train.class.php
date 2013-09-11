<?php
class train {
	function get($id) {
		$q = mysql::query("pattern.getSet",array("id"=>$id));
		if(!$q->num_rows) throw new Exception("Training set not found.");
		$training_data = mysql::fetch_all($q);
		return $training_data;
	}
	
	function saveEpoch($nid,$epoch,$start_mse,$end_mse,$time,$sid=NULL) {
		mysql::query("epoch.store",array("id"=>$nid,"iterations"=>$epoch,"startmse"=>$start_mse,"endmse"=>$end_mse,"time"=>$time,"train"=>$sid));
	}

    function listTrainingSets($id) {
		$q = mysql::query("train.getAll",array("id"=>$id));
		if($q->num_rows) {
			$data = mysql::fetch_all($q);
			foreach($data as $row) {
				$tid = $row['trainsetID'];
				echo "<tr><td>".$row['label']."</a></td>";
				echo "<td><a href='nm-edit-set.php?s=$tid&n=$id' title='Manage'><img src='images/pencil.png' /></a> ";
				echo "<a href='nm-manage-set.php?action=delete&s=$tid' title='Delete'><img src='images/cross.png' /></a> ";
				echo "<a href='nm-run-set.php?s=$tid' title='Run Training Set'><img src='images/run.png' /></a> ";
				echo "<a href='nm-set-history.php?s=$tid&n=$id' title='View History'><img src='images/time.png' /></a></td></tr>";
			}
		} else {
			echo "<tr><td colspan='2'><span>No Training Sets</span></td></tr>";
		}
	}
	
	function listPatterns($id) {
		$q = mysql::query("pattern.getAll",array("id"=>$id));
		if($q->num_rows) {
			$data = mysql::fetch_all($q);
			foreach($data as $row) {
				$id = $row['patternID'];
				echo "<tr><td>".$row['pattern']."</td><td><img src='images/arrow.gif'/></td><td>".$row['output']."</td>";
				echo "<td><a href='nm-manage-set.php?action=remove&s=$id'><img src='images/cross.png' /></a></td>";
			}
		} else {
			echo "<tr><td colspan='4'><span>No Patterns</span></td></tr>";
		}
	}
	
	function listHistory($id) {
		$q = mysql::query("epoch.get",array("id"=>$id));
		if($q->num_rows) {
			$data = mysql::fetch_all($q);
			foreach($data as $row) {
				echo "<tr><td>".$row['iterations']."</td>";
				echo "<td>".$row['startMSE']."</td>";
				echo "<td>".$row['endMSE']."</td>";
				echo "<td>".date("j/m/Y g:i:s a",strtotime($row['epochDate']))."</td>";
				echo "<td>".$row['execTime']."</td></tr>";
			}
		} else {
			echo "<tr><td colspan='5'><span>No History</span></td></tr>";
		}
	}
	
	function listAllHistory($id) {
		$q = mysql::query("epoch.getall",array("id"=>$id));
		if($q->num_rows) {
			$data = mysql::fetch_all($q);
			foreach($data as $row) {
				echo "<tr><td>".$row['iterations']."</td>";
				echo "<td>".$row['startMSE']."</td>";
				echo "<td>".$row['endMSE']."</td>";
				echo "<td>".date("j/m/Y g:i:s a",strtotime($row['epochDate']))."</td>";
				echo "<td>".$row['execTime']."</td>";
				echo "<td>".($row['trainsetID'] == null ? "n" : "y")."</td></tr>";
			}
		} else {
			echo "<tr><td colspan='6'><span>No History</span></td></tr>";
		}
	}
	
	function getID($tid) {
		$q = mysql::query("train.get",array("id"=>$tid));
		$data = $q->fetch_assoc();
		return $data['networkID'];
	}
	
	static function validate($id) {
		$q = mysql::query("train.validate",array("id"=>$id,"user"=>$_SESSION['id']));
		return !!$q->num_rows; //convert to boolean
	}
	
}
?>